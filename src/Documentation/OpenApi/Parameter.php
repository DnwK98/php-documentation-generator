<?php

declare(strict_types=1);

namespace Doc\Documentation\OpenApi;

use JsonSerializable;

class Parameter implements JsonSerializable
{
    /** @var string */
    private $name = '';

    /** @var string */
    private $description = '';

    /** @var bool */
    private $required = false;

    /** @var string */
    private $in = 'query';

    public static function Query(string $name, string $description = '', bool $required = false): self
    {
        $p = new self();
        $p->name = $name;
        $p->description = $description;
        $p->required = $required;
        $p->in = 'query';

        return $p;
    }

    public static function Path(string $name, string $description = ''): self
    {
        $p = new self();
        $p->name = $name;
        $p->description = $description;
        $p->required = true;
        $p->in = 'path';

        return $p;
    }

    public static function Header(string $name, string $description = ''): self
    {
        $p = new self();
        $p->name = $name;
        $p->description = $description;
        $p->required = false;
        $p->in = 'header';

        return $p;
    }

    public function setRequired(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'in' => $this->in,
            'description' => $this->description,
            'required' => $this->required,
            'schema' => ['type' => 'string'],
        ];
    }
}
