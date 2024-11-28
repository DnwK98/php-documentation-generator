<?php

declare(strict_types=1);

namespace Doc\Documentation;

use Doc\Documentation\OpenApi\OpenApi;
use Doc\Documentation\OpenApi\Schema;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Type;

class DocumentationGenerator
{
    private PhpDocExtractor $docExtractor;

    public function __construct()
    {
        $this->docExtractor = new PhpDocExtractor();
    }

    public function generateObjectDoc(OpenApi $doc, string $type): void
    {
        if ($this->isNonInternalPhpType($type) && !$doc->hasSchema($type)) {
            $schema = $this->doGenerateObjectDoc($type);
            $doc->addSchema($type, $schema);

            $childTypes = $schema->getChildrenTypes();
            foreach ($childTypes as $childType) {
                $this->generateObjectDoc($doc, $childType);
            }
        }
    }

    private function doGenerateObjectDoc(string $className): Schema
    {
        $reflection = new ReflectionClass($className);
        $docBlock = $this->getDocBlock($reflection);

        $schema = Schema::SchemaObject();
        $schema->setDescription(trim($docBlock->getSummary() . ' ' . $docBlock->getDescription()));

        $parentReflection = $reflection;
        while ($parentReflection = $parentReflection->getParentClass()) {
            $this->reflectProperties($schema, $parentReflection);
        }

        $this->reflectProperties($schema, $reflection);

        return $schema;
    }

    private function reflectProperties(Schema &$schema, ReflectionClass $reflection): void
    {
        foreach ($reflection->getProperties() as $property) {
            /** @var Type[] $propertyTypes */
            $propertyTypes = $this->docExtractor->getTypes($reflection->getName(), $property->getName()) ?? [];
            $propertySchema = $this->reflectProperty($property, $propertyTypes);

            $schema->addProperty($this->formatPropertyName($property->getName()), $propertySchema);
        }
    }


    /**
     * @param Type[] $propertyTypes
     */
    private function reflectProperty(ReflectionProperty $property, array $propertyTypes): Schema
    {
        $docBlock = $this->getDocBlock($property);
        $propertySchema = Schema::SchemaString();

        if ($type = $property->getType()) {
            if ('array' === $type->getName()) {
                $propertySchema = Schema::SchemaObject();
            } else {
                $propertySchema = $this->getOpenApiType($type->getName());
                $propertySchema->setNullable($type->allowsNull());
            }
        }

        if ($type = end($propertyTypes)) {
            if ($type->isCollection()) {
                $keyType = $type->getCollectionKeyTypes()[0];
                $valueType = $type->getCollectionValueTypes()[0];
                $openApiValueType = $this->getOpenApiType($this->typeToString($valueType))->getType();
                if ($keyType->getBuiltInType() === 'string') {
                    $propertySchema = Schema::SchemaDictionary($openApiValueType);
                } else {
                    $propertySchema = Schema::SchemaArray($openApiValueType);
                }

            } else {
                $propertySchema = $this->getOpenApiType($this->typeToString($type));
                $propertySchema->setNullable($type->isNullable());
            }
        }

        if (count($propertyTypes) > 1) {
            $propertyOpenApiTypes = [];
            foreach ($propertyTypes as $type) {
                $propertyType = $this->getOpenApiType($this->typeToString($type));
                $propertyType->setNullable($type->isNullable());
                $propertyOpenApiTypes[] = $propertyType;
            }

            $propertySchema = Schema::SchemaOneOf($propertyOpenApiTypes);
        }

        $examples = $docBlock->getTagsByName('example');
        if ($example = end($examples)) {
            $propertySchema->setExample(json_decode($example->__toString()));
        }

        $enums = $docBlock->getTagsByName('enum');
        if ($enum = end($enums)) {
            $propertySchema->setEnum(json_decode($enum->__toString()));
        }

        $propertySchema->setDescription(trim($docBlock->getSummary() . ' ' . $docBlock->getDescription()));

        return $propertySchema;
    }

    private function getDocBlock(ReflectionClass|ReflectionProperty $reflection): DocBlock
    {
        static $docBlockFactory = null;
        if (null === $docBlockFactory) {
            $docBlockFactory = DocBlockFactory::createInstance();
        }

        $docComment = $reflection->getDocComment();
        if (empty($docComment)) {
            $docComment = '/***/';
        }

        return $docBlockFactory->create($docComment);
    }

    private function isNonInternalPhpType(string $className): bool
    {
        return !PhpTypes::isInternalType($className);
    }

    private function getOpenApiType(string $type): Schema
    {
        $dateTimeTypes = ['DateTime', 'DateTimeImmutable', 'Carbon\Carbon', 'Carbon\CarbonImmutable'];
        if (in_array($type, $dateTimeTypes, true)) {
            $schema = Schema::SchemaType('string');
            $schema->setExample(date('Y-m-d H:i:s'));
            return $schema;
        }

        $type = str_replace('float', 'number', $type);
        $type = str_replace('double', 'number', $type);
        $type = str_replace('int', 'integer', $type);
        $type = str_replace('bool', 'boolean', $type);

        return Schema::SchemaType($type);
    }

    private function typeToString(Type $type): string
    {
        if ($class = $type->getClassName()) {
            return $class;
        }

        return $type->getBuiltinType();
    }

    private function formatPropertyName(string $name): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
    }
}
