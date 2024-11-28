<?php

declare(strict_types=1);

namespace Doc\Documentation\OpenApi;

use Doc\Documentation\PhpTypes;
use JsonSerializable;

class Schema implements JsonSerializable
{
    const TYPE_STRING = 'string';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';

    /** @var class-string */
    private $type;

    /** @var ?Schema[] */
    private $oneOf = null;

    /** @var string */
    private $description = '';

    /** @var bool */
    private $nullable = false;

    /** @var array|scalar|null */
    private $example = null;

    /** @var array|null */
    private $enum = null;

    /** @var array<string, Schema> */
    private $properties = [];

    /** @var ?class-string */
    private $arrayItemsType = null;

    public static function SchemaOneOf(array $types): self
    {
        $schema = new self(self::TYPE_OBJECT);
        $schema->oneOf = $types;

        return $schema;
    }

    public static function SchemaType(string $type): self
    {
        return new self($type);
    }

    public static function SchemaArray(string $itemsType): self
    {
        $schema = new self(self::TYPE_ARRAY);
        $schema->arrayItemsType = $itemsType;

        return $schema;
    }

    public static function SchemaDictionary(string $itemsType): self
    {
        $schema = new self(self::TYPE_OBJECT);
        $schema->arrayItemsType = $itemsType;

        return $schema;
    }

    public static function SchemaObject(): self
    {
        return new self(self::TYPE_OBJECT);
    }

    public static function SchemaString(): self
    {
        return new self(self::TYPE_STRING);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Schema
    {
        $this->description = $description;

        return $this;
    }

    public function getExample()
    {
        return $this->example;
    }

    public function setExample($example): Schema
    {
        $this->example = $example;

        return $this;
    }

    public function getEnum(): ?array
    {
        return $this->enum;
    }

    public function setEnum($enum)
    {
        $this->enum = $enum;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function setNullable(bool $nullable): Schema
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function getOneOf(): ?array
    {
        return $this->oneOf;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getChildrenTypes(): array
    {
        $types = [];
        foreach ($this->properties as $property) {
            foreach ($property->getOneOf() ?? [] as $type) {
                $types[] = $type->type;
            }
            $types[] = $property->getType();
            if ($arrayType = $property->getArrayItemsType()) {
                $types[] = $arrayType;
            }
        }

        return $types;
    }

    public function addProperty(string $name, Schema $schema): self
    {
        $this->properties[$name] = $schema;

        return $this;
    }

    public function getArrayItemsType(): ?string
    {
        return $this->arrayItemsType;
    }

    public function jsonSerialize(): array
    {
        $json = [];

        if($this->oneOf !== null) {
            $json['oneOf'] = [];
            foreach ($this->oneOf as $type) {
                $ref = '#/components/schemas/' . $this->getClass($type->type);
                $json['oneOf'][] = ['$ref' => $ref];
            }
        }
        if ($this->isPrimitiveType($this->type)) {
            $json['type'] = $this->type;
            $json['nullable'] = $this->nullable;
            $json['description'] = $this->description;
            if($this->example) {
                $json['example'] = $this->example;
            }
            if($this->enum) {
                $json['enum'] = $this->enum;
            }
        } else {
            $ref = '#/components/schemas/' . $this->getClass($this->type);
            if ($this->nullable) {
                $json['oneOf'] = [
                    ['$ref' => $ref],
                    ['nullable' => true],
                ];
                $json['nullable'] = $this->nullable;
                $json['description'] = $this->description;
            } else {
                $json['$ref'] = $ref;
            }

            return $json;
        }

        if (self::TYPE_ARRAY === $this->type) {
            if ($this->isPrimitiveType($this->arrayItemsType)) {
                $json['items']['type'] = $this->arrayItemsType;
            } else {
                $json['items']['$ref'] = '#/components/schemas/' . $this->getClass($this->arrayItemsType);
            }
        }
        if (self::TYPE_OBJECT === $this->type) {
            if(null !== $this->arrayItemsType) {
                if ($this->isPrimitiveType($this->arrayItemsType)) {
                    $json['additionalProperties']['type'] = $this->arrayItemsType;
                } else {
                    $json['additionalProperties']['$ref'] = '#/components/schemas/' . $this->getClass($this->arrayItemsType);
                }
            } else {
                foreach ($this->properties as $name => $property) {
                    $json['properties'][$name] = $property->jsonSerialize();
                }
            }
        }

        return $json;
    }

    protected function __construct(string $type)
    {
        $this->type = $type;
    }

    private function isPrimitiveType(string $type): bool
    {
        return PhpTypes::isPrimitive($type);
    }

    private function getClass(string $classPath)
    {
        $parts = explode('\\', $classPath);

        return end($parts);
    }
}
