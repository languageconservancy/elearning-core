<?php

namespace App\Lib;

use Cake\Core\Configure;
use Cake\Log\Log;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use App\Lib\UtilLibrary;

/* Create constants from Configure and create array so we can error check */
define("CLEVER_CLIENT_ID", Configure::read('Clever.client_id'));
define("CLEVER_CLIENT_SECRET", Configure::read('Clever.client_secret'));
define("CLEVER_TOKENS_URL", Configure::read('Clever.tokens_url'));
define("CLEVER_USER_IDENTITY_URL", Configure::read('Clever.user_identity_url'));
define("CLEVER_USER_INFO_URL", Configure::read('Clever.user_info_url'));
const CLEVER = [
    "client_id" => CLEVER_CLIENT_ID,
    "client_secret" => CLEVER_CLIENT_SECRET,
    "tokens_url" => CLEVER_TOKENS_URL,
    "user_identity_url" => CLEVER_USER_IDENTITY_URL,
    "user_info_url" => CLEVER_USER_INFO_URL
];

/**
 * Library of helper functions for making Clever API requests
 */
class CleverLibrary
{
    /**
     * Sets Clever user data in passed in array, which is then returned to
     * the calling function.
     * @param array $user - Array for Clever user data to be added to
     * @param array $data - Data to be copied to user data array
     * @return array User data array
     */
    public static function setUserInfo(array $user, array $data): array
    {
        $user['id'] = $data['id'];
        $user['firstname'] = empty($data['name']['first']) ? '' : $data['name']['first'];
        $user['lastname'] = empty($data['name']['last']) ? '' : $data['name']['last'];
        $user['email'] = empty($data['email']) ? '' : $data['email'];
        $user['district'] = empty($data['district']) ? '' : $data['district'];
        $user['roles'] = array_keys($data['roles']);
        return $user;
    }

    /**
     * Check that CLEVER Configure constants are all defined.
     * This function should be called and failure handled properly
     * before calling any other functions in this class.
     * @return array {Array} {
     *  {boolean} status, {string} message, {array} data
     * }
     */
    public static function checkCleverConsts(): array
    {
        return UtilLibrary::checkArrayMembers(
            CLEVER,
            ['client_id', 'client_secret', 'tokens_url', 'user_identity_url', 'user_info_url']
        );
    }

    /**
     * @param {array} $cleverDdata - Clever redirect data
     * @param null $handlerStack
     * @return array {array} {
     *  {boolean} status, {string} message, {array} data
     * }
     * @throws GuzzleException
     */
    public static function getCleverAccessToken($cleverData, $handlerStack = null): array
    {
        $responseStatus = false;
        $responseData = array();

        // Ensure constants and POST data are valid
        $result = UtilLibrary::checkArrayMembers(
            $cleverData,
            ['code', 'scope', 'redirect_uri']
        );
        if (!$result['status']) {
            return $result;
        }

        // Convenience variables
        $cleverCode = $cleverData['code'];
        $cleverScope = $cleverData['scope'];
        $cleverRedirectUri = $cleverData['redirect_uri'];

        // Create POST url, data and headers
        $requestUrl = CLEVER['tokens_url'];
        $postData = [
            "code" => $cleverCode,
            "grant_type" => "authorization_code",
            "redirect_uri" => $cleverRedirectUri
        ];
        $basicAuthHeader = "Basic " . base64_encode(
            CLEVER['client_id'] . ":" . CLEVER['client_secret']
        );
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $basicAuthHeader
        ];

        // Create HTTP Client and add unit test handler if provided
        if ($handlerStack != null) {
            $httpClient = new HttpClient(['handler' => $handlerStack]);
        } else {
            $httpClient = new HttpClient();
        }

        try {
            $response = $httpClient->request('POST', $requestUrl, [
                'headers' => $headers,
                'json' => $postData
            ]);
            $responseBody = json_decode($response->getBody()->getContents(), true);

            if (!isset($responseBody['access_token'])) {
                $responseMsg = "Failure to get access_token";
            } else {
                $responseStatus = true;
                $responseMsg = "Succesfully exchanged code for access token";
                $responseData = $responseBody;
            }
        } catch (RequestException $e) {
            $responseMsg = CleverLibrary::extractGuzzleExceptionMessage($e);
        }

        return array(
            "status" => $responseStatus,
            "message" => $responseMsg,
            "data" => $responseData
        );
    }

    /**
     * @param {RequestException} $e - Exception object
     * @return string {string} Error - message
     */
    public static function extractGuzzleExceptionMessage($e): string
    {
        $responseMsg = "Unknown request error.";
        if ($e->hasResponse()) {
            $responseMsg = $e->getResponse()->getStatusCode() . ". " . $e->getResponse()->getReasonPhrase();
            Log::error("Guzzle Exception: Response: " . $responseMsg);
            Log::error("Guzzle Exception: Message: " . $e->getMessage());
            $errorContents = json_decode($e->getResponse()->getBody()->getContents(), true);
            if (isset($errorContents['error'])) {
                $responseMsg .= ". " . $errorContents['error'];
            }
            if (isset($errorContents['error_description'])) {
                $responseMsg .= ". " . $errorContents['error_description'];
            }
            Log::error($responseMsg);
        }
        return $responseMsg;
    }

    /**
     * @param {string} $access - Bearer access token to access Clever API
     * @param null $handlerStack
     * @return array {array} {
     *  {boolean} status, {string} message, {array} data
     * }
     * @throws GuzzleException
     * @apram {object} $handleStack - HTTP client mock helper
     */
    public static function getCleverUserIdentity($accessToken, $handlerStack = null): array
    {
        $responseMsg = "";
        $responseStatus = false;
        $responseData = array();

        if (!$accessToken || $accessToken == '') {
            $result['status'] = false;
            $result['message'] = "Invalid access token";
            $result['data'] = array();
            return $result;
        }

        $requestUrl = CLEVER['user_identity_url'];
        return self::getAccessToken($accessToken, $handlerStack, $requestUrl, $responseStatus, $responseData);
    }

    /**
     * @param {string} $access - Bearer access token to access Clever API
     * @param {string} $userId - Clever user ID
     * @apram {object} $handleStack - HTTP client mock helper
     * @return {array} {
     *   {boolean} status, {string} message, {array} data
     * }
     */
    public static function getCleverUserInfo($accessToken, $userId, $handlerStack = null): array
    {
        $responseMsg = "";
        $responseStatus = false;
        $responseData = array();

        if (!$accessToken || $accessToken == '') {
            $result['status'] = false;
            $result['message'] = "Invalid access token";
            $result['data'] = array();
            return $result;
        } elseif (!$userId || $userId == '') {
            $result['status'] = false;
            $result['message'] = "Invalid Clever user ID";
            $result['data'] = array();
            return $result;
        }

        $requestUrl = CLEVER['user_info_url'] . $userId;
        return self::getAccessToken($accessToken, $handlerStack, $requestUrl, $responseStatus, $responseData);
    }

    /**
     * @param $accessToken
     * @param $handlerStack
     * @param $requestUrl
     * @param bool $responseStatus
     * @param $responseData
     * @return array
     * @throws GuzzleException
     */
    public static function getAccessToken(
        $accessToken,
        $handlerStack,
        $requestUrl,
        bool $responseStatus,
        $responseData
    ): array {
        $headers = ['Authorization' => "Bearer " . $accessToken];

        // Create HTTP Client and add unit test handler if provided
        if ($handlerStack != null) {
            $httpClient = new HttpClient(['handler' => $handlerStack]);
        } else {
            $httpClient = new HttpClient();
        }

        try {
            $response = $httpClient->request('GET', $requestUrl, [
                'headers' => $headers
            ]);
            $responseBody = json_decode($response->getBody()->getContents(), true);

            if (!isset($responseBody['data'])) {
                $responseStatus = false;
                $responseMsg = "Failure to get access_token";
            } else {
                $responseStatus = true;
                $responseMsg = "Successfully got user info";
                $responseData = $responseBody;
            }
        } catch (RequestException $e) {
            $responseMsg = CleverLibrary::extractGuzzleExceptionMessage($e);
        }

        return array(
            "status" => $responseStatus,
            "message" => $responseMsg,
            "data" => $responseData
        );
    }
}
