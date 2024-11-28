<?php

declare(strict_types=1);

namespace Doc\Documentation\OpenApi;

use JsonSerializable;

class Path implements JsonSerializable
{
    /** @var ?PathOperation */
    public $get = null;

    /** @var ?PathOperation */
    public $post = null;

    /** @var ?PathOperation */
    public $patch = null;

    /** @var ?PathOperation */
    public $put = null;

    /** @var ?PathOperation */
    public $delete = null;

    /** @var ?PathOperation */
    public $options = null;

    /** @var string */
    public $description = '';

    public function __construct(array $operations = [])
    {
        foreach ($operations as $method => $operation) {
            $this->$method = $operation;
        }
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $json = [];
        foreach (get_object_vars($this) as $method => $definition) {
            if ($definition instanceof PathOperation) {
                $json[$method] = $definition->jsonSerialize();
            }
        }

        $json['description'] = $this->description;

        return $json;
    }
}
