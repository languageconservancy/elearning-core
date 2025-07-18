<?php

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Test\TestCase\Library;

use App\Lib\CleverLibrary;
use Cake\TestSuite\IntegrationTestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * CleverLibraryTest class
 * GuzzleHttp Mock info:
 *   - https://docs.guzzlephp.org/en/latest/testing.html#mock-handler
 * GuzzleHttp MockHandler Responses are of the following format:
 *   {number} StatusCode,
 *   {array}  headers,
 *   {any}    body,
 *   {string} version,
 *   {string} reason.
 */
class CleverLibraryTest extends IntegrationTestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testGetAccessTokenSuccessful()
    {
        $data = [
            "code" => "kl2j35",
            "scope" => "scope",
            "redirect_uri" => "http://localhost/"
        ];
        $expectedResponseData = [
            "access_token" => "random_token",
            "token_type" => "token_type"
        ];
        $okResponse = new Response(200, [], json_encode($expectedResponseData));

        // Create a mock and append OK response to queue
        $mock = new MockHandler();
        $mock->append($okResponse);
        // Create handlerStack
        $handlerStack = HandlerStack::create($mock);

        // Call function under test with mock client
        $response = CleverLibrary::getCleverAccessToken($data, $handlerStack);

        $this->assertTrue($response['status']);
        $this->assertTrue($response['message'] ==
            "Succesfully exchanged code for access token");
        $this->assertTrue($response['data'] != null);
        $this->assertTrue($response['data'] == $expectedResponseData);
    }

    public function testGetAccessTokenMissingData()
    {
        $data = [
            "scope" => "scope",
            "redirect_uri" => "http://localhost/"
        ];

        $response = CleverLibrary::getCleverAccessToken($data);

        $this->assertFalse($response['status']);
        $this->assertTrue($response['message'] == "code is not set.");
        $this->assertTrue($response['data'] == null);
    }

    public function testGetAccessTokenBadRequest()
    {
        $data = [
            "code" => "mycode",
            "scope" => "scope",
            "redirect_uri" => "http://localhost/"
        ];
        $badRequestResponse = new Response(400, [], json_encode([
            "error" => "invalid_request",
            "error_description" =>
                "invalid json request body; must provide 'redirect_uri'"
        ]), '1.1', 'Bad Request');

        // Create a mock and append OK response to queue
        $mock = new MockHandler();
        $mock->append($badRequestResponse);
        $handlerStack = HandlerStack::create($mock);

        // Call function under test with mock client
        $response = CleverLibrary::getCleverAccessToken($data, $handlerStack);
        $this->assertFalse($response['status']);
        $this->assertTrue($response['message'] ==
            "400. Bad Request. invalid_request. invalid json request body;" .
            " must provide 'redirect_uri'");
        $this->assertTrue($response['data'] == null);
    }

    public function testGetUserIdentitySuccessful()
    {
        $accessToken = "test_token";
        $okResponseData = [
            "type" => "teacher",
            "data" => [
                "id" => "5c09aa57722cf21688e38661",
                "type" => "teacher",
                "email" => "aperry01@grayusd.edu",
                "name" => [
                    "first" => "Arden",
                    "last" => "Perry"
                ]
            ],
            "sections" => [
                "0" => "5c09aa57722cf21688e386d4"
            ],
            "links" => [
                "0" => [
                    "rel" => "self",
                    "uri" => "/me"
                ],
                "1" => [
                    "rel" => "canonical",
                    "uri" => "/v3.0/teachers/5c09aa57722cf21688e38661"
                ]
            ]
        ];
        $okResponse = new Response(200, [], json_encode($okResponseData));

        // Create a mock and append OK response to queue
        $mock = new MockHandler();
        $mock->append($okResponse);
        $handlerStack = HandlerStack::create($mock);

        // Call function under test with mock client
        $response = CleverLibrary::getCleverUserIdentity($accessToken, $handlerStack);

        $this->assertTrue($response['status']);
        $this->assertTrue($response['message'] == "Successfully got user info");
        $this->assertTrue($response['data'] == $okResponseData);
    }

    public function testGetUserIdentityInvalidAccessToken()
    {
        $accessToken = null;
        // Create a mock and append OK response to queue
        $response = CleverLibrary::getCleverUserIdentity($accessToken);
        $this->assertFalse($response['status']);
        $this->assertTrue($response['data'] == array());
        $this->assertTrue($response['message'] == "Invalid access token");

        $accessToken = "";
        $response = CleverLibrary::getCleverUserIdentity($accessToken);
        $this->assertFalse($response['status']);
        $this->assertTrue($response['data'] == array());
        $this->assertTrue($response['message'] == "Invalid access token");
    }

    public function testGetUserIdentityBadRequest()
    {
        $accessToken = "test_token";
        $badRequestResponse = new Response(400, [], json_encode([
            "error" => "invalid_request",
            "error_description" =>
                "invalid json request body; must provide 'redirect_uri'"
        ]), '1.1', 'Bad Request');

        // Create a mock and append OK response to queue
        $mock = new MockHandler();
        $mock->append($badRequestResponse);
        $handlerStack = HandlerStack::create($mock);

        // Call function under test with mock client
        $response = CleverLibrary::getCleverUserIdentity($accessToken, $handlerStack);
        $this->assertFalse($response['status']);
        $this->assertTrue($response['message'] ==
            "400. Bad Request. invalid_request. invalid json request body;" .
            " must provide 'redirect_uri'");
        $this->assertTrue($response['data'] == null);
    }
}
