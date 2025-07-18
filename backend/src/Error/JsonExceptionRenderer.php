<?php

namespace App\Error;

use Cake\Error\Renderer\WebExceptionRenderer;
use Psr\Http\Message\ResponseInterface;
use Cake\Log\Log;
use Throwable;

class JsonExceptionRenderer extends WebExceptionRenderer
{
    /**
     * For Api routes from a client, return a standard
     * JSON response. Otherwise render CakePHP's standard
     * HTML error page.
     * @return \Psr\Http\Message\ResponseInterface The response to be sent to the client.
     */
    public function render(): \Psr\Http\Message\ResponseInterface
    {
        $request = $this->controller->getRequest();
        if ($request->getParam('prefix') === 'Api') {
            return $this->sendNormalResponseToClient();
        } else {
            return parent::render();
        }
    }

    private function sendNormalResponseToClient(): ResponseInterface
    {
        // Get response object from the controller that initiated the exception.
        $response = $this->controller->getResponse();
        // Extract info from exception object
        $exception = $this->error;
        $statusCode = $exception->getCode();
        $message = $exception->getMessage();
        if ($statusCode < 100 || $statusCode > 500) {
            Log::warning("Got bad status code of " . $statusCode . " for error message: " . $message);
            $statusCode = 500;
        }

        // Create response data
        $responseData = [
            'error' => true,
            'data' => [
                'status' => false,
                'message' => $message,
                'results' => []
            ],
            'message' => $message,
            'code' => $statusCode
        ];
        // Send response as JSON
        $response = $response->withType('json')->withStatus($statusCode);

        $jsonResponse = json_encode($responseData);
        if ($jsonResponse === false) {
            $response = $response->withStringBody('{"error":true,"data":{"status":false,"message":"Error in encoding JSON response","results":[]},"message":"Error in encoding JSON response","code":500}');
        } else {
            $response = $response->withStringBody($jsonResponse);
        }

        $this->controller->setResponse($response);

        return $response;
    }
}
