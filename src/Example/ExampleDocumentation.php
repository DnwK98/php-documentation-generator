<?php

declare(strict_types=1);

namespace Doc\Example;

use Doc\Documentation\DocumentationGenerator;
use Doc\Documentation\OpenApi\OpenApi;
use Doc\Documentation\OpenApi\Parameter;
use Doc\Documentation\OpenApi\Path;
use Doc\Documentation\OpenApi\PathOperation;
use Doc\Documentation\OpenApi\Schema;

class ExampleDocumentation
{
    public static function get(DocumentationGenerator $documentationGenerator): OpenApi
    {
        $openApi = new OpenApi();

        $openApi->addPath('/api/users', new Path([
            'get' => PathOperation::Create()
                ->setDescription('Retrieve a list of users')
                ->addResponse(200, Schema::SchemaArray(User::class)),
            'post' => PathOperation::Create()
                ->setDescription('Create a new user')
                ->setRequest(Schema::SchemaType(UserRequest::class))
                ->addResponse(201, Schema::SchemaType(User::class))
                ->addResponse(400, Schema::SchemaObject()
                    ->addProperty('error', Schema::SchemaType('string')->setExample('Invalid input'))
                ),
        ]));

        $openApi->addPath('/api/users/{userId}', new Path([
            'get' => PathOperation::Create()
                ->addParameter(Parameter::Path('userId', 'The ID of the user'))
                ->setDescription('Retrieve a specific user')
                ->addResponse(200, Schema::SchemaType(User::class))
                ->addResponse(404, Schema::SchemaObject()
                    ->addProperty('error', Schema::SchemaType('string')->setExample('User not found'))
                ),
            'put' => PathOperation::Create()
                ->addParameter(Parameter::Path('userId', 'The ID of the user'))
                ->setDescription('Update an existing user')
                ->setRequest(Schema::SchemaType(UserRequest::class))
                ->addResponse(200, Schema::SchemaType(User::class))
                ->addResponse(400, Schema::SchemaObject()
                    ->addProperty('error', Schema::SchemaType('string')->setExample('Invalid input'))
                ),
            'delete' => PathOperation::Create()
                ->addParameter(Parameter::Path('userId', 'The ID of the user'))
                ->setDescription('Delete a user')
                ->addResponse(204, Schema::SchemaObject())
                ->addResponse(404, Schema::SchemaObject()
                    ->addProperty('error', Schema::SchemaType('string')->setExample('User not found'))
                ),
        ]));

        $documentationGenerator->generateObjectDoc($openApi, User::class);
        $documentationGenerator->generateObjectDoc($openApi, UserRequest::class);

        return $openApi;
    }
}