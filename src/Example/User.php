<?php

declare(strict_types=1);

namespace Doc\Example;

class User
{
    /**
     * The unique identifier of the user.
     *
     * @var int
     */
    public $id;

    /**
     * The name of the user.
     *
     * @var string
     * @example "Jan Kowalski"
     */
    public $name;

    /**
     * The email address of the user.
     *
     * @var string
     */
    public $email;
}