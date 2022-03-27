<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;
use Osteel\OpenApi\Testing\Exceptions\ValidationException;
use Osteel\OpenApi\Testing\ValidatorBuilder;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected bool $validateOpenApiRequests = true;

    protected bool $validateOpenApiResponse = true;

    protected string $apiVersion = 'v1';

    /**
     * Validate every API request against the API definition
     *
     * @param string $method
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @param bool $isApi
     * @return TestResponse
     * @throws ValidationException
     * @throws ValidationException
     */
    public function json($method, $uri, array $data = [], array $headers = [], bool $isApi = true): TestResponse
    {
        if ($isApi) {
            $uri = '/api/' . $this->apiVersion . $uri;
        }

        $response = parent::json($method, $uri, $data, $headers);
        $validator = ValidatorBuilder::fromYaml(storage_path('api-docs/' . $this->apiVersion . '.yaml'))->getValidator();

        if ($this->validateOpenApiRequests) {
            $files = $this->extractFilesFromDataArray($data);
            $content = json_encode($data);
            $headers = array_merge([
                'CONTENT_LENGTH' => mb_strlen($content, '8bit'),
                'CONTENT_TYPE' => 'application/json',
                'Accept' => 'application/json',
            ], $headers);

            $symfonyRequest = SymfonyRequest::create(
                $this->prepareUrlForRequest($uri),
                $method,
                [],
                $this->prepareCookiesForJsonRequest(),
                $files,
                $this->transformHeadersToServerVars($headers),
                $content
            );

            $validator->validate($symfonyRequest, $uri, $method);
        }

        if ($this->validateOpenApiResponse) {
            $validator->validate($response->baseResponse, $uri, $method);
        }

        return $response;
    }
}
