<?php

declare(strict_types=1);

namespace Doc\Documentation\OpenApi;

use JsonSerializable;

class OpenApi implements JsonSerializable
{
    /** @var string */
    private $openapi = '3.0.3';

    /** @var Info */
    private $info;

    /** @var array<string, Path> */
    private $paths = [];

    /** @var array<string, Schema> */
    private $schemas = [];

    public function __construct()
    {
        $this->info = new Info();
    }

    public function getOpenapi(): string
    {
        return $this->openapi;
    }

    public function getInfo(): Info
    {
        return $this->info;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function addPath(string $name, Path $path): void
    {
        if (isset($this->paths[$name])) {
            throw new \LogicException("Path $name already exists");
        }

        $this->paths[trim($name)] = $path;
    }

    public function getSchemas(): array
    {
        return $this->schemas;
    }

    public function addSchema(string $name, Schema $schema): void
    {
        if (!isset($this->schemas[$name])) {
            $this->schemas[$name] = $schema;
        }
    }

    public function hasSchema(string $name): bool
    {
        return isset($this->schemas[$name]);
    }

    public function merge(OpenApi $other): void
    {
        foreach ($other->getPaths() as $path => $pathObject) {
            $this->addPath($path, $pathObject);
        }

        foreach ($other->getSchemas() as $name => $schema) {
            $this->addSchema($name, $schema);
        }
    }

    public function jsonSerialize(): array
    {
        $json = [];
        $json['openapi'] = $this->openapi;
        $json['info'] = $this->info->jsonSerialize();
        $json['paths'] = [];
        foreach ($this->paths as $path => $pathObject) {
            $json['paths'][$path] = $pathObject->jsonSerialize();
        }

        $json['components']['schemas'] = [];
        foreach ($this->schemas as $name => $schema) {
            $json['components']['schemas'][$this->getClass($name)] = $schema->jsonSerialize();
        }

        ksort($json['components']['schemas']);

        return $json;
    }

    private function getClass(string $classPath): string
    {
        $parts = explode('\\', $classPath);

        return end($parts);
    }
}
