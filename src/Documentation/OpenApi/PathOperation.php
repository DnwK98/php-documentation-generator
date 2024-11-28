<?php

declare(strict_types=1);

namespace Doc\Documentation\OpenApi;

class PathOperation implements \JsonSerializable
{
    /** @var array<int,Schema> */
    private $responses;

    /** @var Parameter[] */
    private $parameters;

    /** @var ?Schema */
    private $request;

    private string $description = '';

    public function __construct(array $responses, array $parameters = [], ?Schema $request = null)
    {
        $this->responses = $responses;
        $this->parameters = $parameters;
        $this->request = $request;
    }

    public static function Create(): self
    {
        return new self([]);
    }

    public function addResponse(int $code, Schema $schema): self
    {
        $this->responses[$code] = $schema;

        return $this;
    }

    public function addParameter(Parameter $parameter): self
    {
        $this->parameters[] = $parameter;

        return $this;
    }

    public function setRequest(?Schema $schema): self
    {
        $this->request = $schema;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $json = [];

        foreach ($this->responses as $code => $response) {
            $json['responses'][$code]['content']['application/json']['schema'] = $response->jsonSerialize();
            $json['responses'][$code]['description'] = '';
        }
        foreach ($this->parameters as $parameter) {
            $json['parameters'][] = $parameter->jsonSerialize();
        }
        if ($this->request) {
            $json['requestBody']['content']['application/json']['schema'] = $this->request->jsonSerialize();
        }
        $json['description'] = $this->description;

        return $json;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }
}
