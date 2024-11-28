<?php

declare(strict_types=1);

namespace Doc\Controller;

use Doc\Documentation\DocumentationGenerator;
use Doc\Documentation\OpenApi\OpenApi;
use Doc\Example\ExampleDocumentation;
use Nelmio\ApiDocBundle\Render\Html\AssetsMode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DocumentationController extends AbstractController
{
    private DocumentationGenerator $documentationGenerator;

    public function __construct(DocumentationGenerator $documentationGenerator)
    {
        $this->documentationGenerator = $documentationGenerator;
    }

    /**
     * @Route("/", name="api_doc")
     */
    public function docAction(Request $request): Response
    {
        $doc = $this->getDoc();

        return $this->render(
            '@NelmioApiDoc/SwaggerUi/index.html.twig',
            [
                'swagger_data' => ['spec' => $doc->jsonSerialize()],
                'assets_mode' => AssetsMode::CDN,
                'swagger_ui_config' => [],
            ]
        );
    }

    /**
     * @Route("/raw", name="api_doc_raw")
     */
    public function docActionRaw(Request $request): Response
    {
        $doc = $this->getDoc();

        return (new JsonResponse())->setEncodingOptions(JSON_PRETTY_PRINT)->setData($doc->jsonSerialize());
    }


    private function getDoc(): OpenApi
    {
        return ExampleDocumentation::get($this->documentationGenerator);
    }
}
