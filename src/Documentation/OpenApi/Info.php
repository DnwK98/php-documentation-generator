<?php

declare(strict_types=1);

namespace Doc\Documentation\OpenApi;

use JsonSerializable;

class Info implements JsonSerializable
{
    /** @var string */
    public $title = '';

    /** @var string */
    public $description = '';

    /** @var string */
    public $version = '1.0.0';

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
