## PHP Documentation generator

This documentation generator allows you to programmatically create and customize API documentation 
for your PHP SDK using a OpenApi object. By using methods like `addPath()`, 
you can define endpoints, operations, parameters, requests, and responses directly in code, ensuring 
your documentation is always up-to-date with your API implementation.

Main concepts:

- OpenApi: The main object representing your API documentation.
- Path: Represents a single API path and contains operations (HTTP methods).
- PathOperation: Represents an operation (e.g., GET, POST) on a path.
- Parameter: Represents a parameter in a path, query, header, or cookie.
- Schema: Represents the data models used in requests and responses.

### Including project
To use generator you have to include your project. Simply add symlink in `include` directory.
If it is composer application, it will be autloaded automatically.

### Adding Paths
To add a new path to your API documentation, use the `addPath()` method of the OpenApi object. The addPath() method takes two arguments:

Path String: The URL path (e.g., `/api/users`).
Path Object: An instance of the Path class containing operations.

Example:

```php
$openApi->addPath('/api/users', new Path([
    // Operations will be added here
]));
```

### Defining Path Operations
Within the Path object, you can define operations corresponding to HTTP methods (e.g., GET, POST, PUT, DELETE). Each operation is an instance of PathOperation.

To create a new `PathOperation`, use the `PathOperation::Create()` method.

Example:
```php
new Path([
    'get' => PathOperation::Create()
        ->setDescription('Retrieve a list of users')
        // Additional configuration
]);
```

### Adding Parameters
Parameters can be added to operations to define inputs such as path variables, query parameters, headers, or cookies.

Use the `addParameter()` method on a `PathOperation` instance. The Parameter class provides static methods to define different types of parameters:

Example:
```php
$pathOperation->addParameter(Parameter::Path($name, $description = ''));
$pathOperation->addParameter(Parameter::Query($name, $description = ''));
$pathOperation->addParameter(Parameter::Header($name, $description = ''));
```

### Defining Requests and Responses
Setting the Request Body

To define the request body for operations like POST or PUT, use the `setRequest()` method with a Schema object representing the data model.

Example:
```php
$pathOperation->setRequest(Schema::SchemaType(UserRequest::class));
```

Adding Responses

Use the `addResponse()` method to define possible responses from the operation. Responses are defined with HTTP status codes and corresponding schemas.

Example:
```php
$pathOperation->addResponse(200, Schema::SchemaType(UserResponse::class))
$pathOperation->addResponse(400, Schema::SchemaObject()
    ->addProperty('error', Schema::SchemaType('string'))
);
```

### Generating Schemas
To define the data models used in requests and responses, use the `Schema` class. You can generate schemas for your models using a `DocumentationGenerator` or manually define them.

Using DocumentationGenerator:
```php
$this->documentationGenerator->generateObjectDoc($openApi, User::class);
$this->documentationGenerator->generateObjectDoc($openApi, UserRequest::class);
```

Manually Defining a Schema:
```php
Schema::SchemaObject()
    ->addProperty('id', Schema::SchemaType('integer')->setExample(1))
    ->addProperty('name', Schema::SchemaType('string')->setExample('John Doe'))
    ->addProperty('email', Schema::SchemaType('string')->setExample('john@example.com'));
```

### Putting It All Together
Here's a full example of defining an API endpoint using the OpenApi object:
```php
// Create the OpenApi object
$openApi = new OpenApi();

// Define the /api/users path
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

// Define the /api/users/{userId} path
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

// Generate schemas for models
$this->documentationGenerator->generateObjectDoc($openApi, User::class);
$this->documentationGenerator->generateObjectDoc($openApi, UserRequest::class);
```