<?php

namespace App\Lib;

/** HTTP status codes */
class HttpStatusCode
{
// Informational 1xx
    public const SWITCHING_PROTOCOLS = 101;
// Successful 2xx
    public const OK = 200;
    public const CREATED = 201;
    public const ACCEPTED = 202;
    public const NONAUTHORITATIVE_INFORMATION = 203;
    public const NO_CONTENT = 204;
    public const RESET_CONTENT = 205;
    public const PARTIAL_CONTENT = 206;
// Redirection 3xx
    public const MULTIPLE_CHOICES = 300;
    public const MOVED_PERMANENTLY = 301;
    public const MOVED_TEMPORARILY = 302;
    public const SEE_OTHER = 303;
    public const NOT_MODIFIED = 304;
    public const USE_PROXY = 305;
// Client Errors 4xx
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const PAYMENT_REQUIRED = 402;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;
    public const NOT_ACCEPTABLE = 406;
    public const PROXY_AUTHENTICATION_REQUIRED = 407;
    public const REQUEST_TIMEOUT = 408;
    public const CONFLICT = 408;
    public const GONE = 410;
    public const LENGTH_REQUIRED = 411;
    public const PRECONDITION_FAILED = 412;
    public const REQUEST_ENTITY_TOO_LARGE = 413;
    public const REQUESTURI_TOO_LARGE = 414;
    public const UNSUPPORTED_MEDIA_TYPE = 415;
    public const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    public const EXPECTATION_FAILED = 417;
    public const IM_A_TEAPOT = 418;
// Server Errors 5xx
    public const INTERNAL_SERVER_ERROR = 500;
    public const NOT_IMPLEMENTED = 501;
    public const BAD_GATEWAY = 502;
    public const SERVICE_UNAVAILABLE = 503;
    public const GATEWAY_TIMEOUT = 504;
    public const HTTP_VERSION_NOT_SUPPORTED = 505;

    public static function toString(int $code)
    {
        switch ($code) {
// Informational 1xx
            case self::SWITCHING_PROTOCOLS:
                return "SWITCHING_PROTOCOLS";
// Successful 2xx
            case self::OK:
                return "OK";
            case self::CREATED:
                return "CREATED";
            case self::ACCEPTED:
                return "ACCEPTED";
            case self::NONAUTHORITATIVE_INFORMATION:
                return "NONAUTHORITATIVE_INFORMATION";
            case self::NO_CONTENT:
                return "NO_CONTENT";
            case self::RESET_CONTENT:
                return "RESET_CONTENT";
            case self::PARTIAL_CONTENT:
                return "PARTIAL_CONTENT";
// Redirection 3xx
            case self::MULTIPLE_CHOICES:
                return "MULTIPLE_CHOICES";
            case self::MOVED_PERMANENTLY:
                return "MOVED_PERMANENTLY";
            case self::MOVED_TEMPORARILY:
                return "MOVED_TEMPORARILY";
            case self::SEE_OTHER:
                return "SEE_OTHER";
            case self::NOT_MODIFIED:
                return "NOT_MODIFIED";
            case self::USE_PROXY:
                return "USE_PROXY";
// Client Errors 4xx
            case self::BAD_REQUEST:
                return "BAD_REQUEST";
            case self::UNAUTHORIZED:
                return "UNAUTHORIZED";
            case self::PAYMENT_REQUIRED:
                return "PAYMENT_REQUIRED";
            case self::FORBIDDEN:
                return "FORBIDDEN";
            case self::NOT_FOUND:
                return "NOT_FOUND";
            case self::METHOD_NOT_ALLOWED:
                return "METHOD_NOT_ALLOWED";
            case self::NOT_ACCEPTABLE:
                return "NOT_ACCEPTABLE";
            case self::PROXY_AUTHENTICATION_REQUIRED:
                return "PROXY_AUTHENTICATION_REQUIRED";
            case self::REQUEST_TIMEOUT:
                return "REQUEST_TIMEOUT";
            case self::CONFLICT:
                return "CONFLICT";
            case self::GONE:
                return "GONE";
            case self::LENGTH_REQUIRED:
                return "LENGTH_REQUIRED";
            case self::PRECONDITION_FAILED:
                return "PRECONDITION_FAILED";
            case self::REQUEST_ENTITY_TOO_LARGE:
                return "REQUEST_ENTITY_TOO_LARGE";
            case self::REQUESTURI_TOO_LARGE:
                return "REQUESTURI_TOO_LARGE";
            case self::UNSUPPORTED_MEDIA_TYPE:
                return "UNSUPPORTED_MEDIA_TYPE";
            case self::REQUESTED_RANGE_NOT_SATISFIABLE:
                return "REQUESTED_RANGE_NOT_SATISFIABLE";
            case self::EXPECTATION_FAILED:
                return "EXPECTATION_FAILED";
            case self::IM_A_TEAPOT:
                return "IM_A_TEAPOT";
// Server Errors 5xx
            case self::INTERNAL_SERVER_ERROR:
                return "INTERNAL_SERVER_ERROR";
            case self::NOT_IMPLEMENTED:
                return "NOT_IMPLEMENTED";
            case self::BAD_GATEWAY:
                return "BAD_GATEWAY";
            case self::SERVICE_UNAVAILABLE:
                return "SERVICE_UNAVAILABLE";
            case self::GATEWAY_TIMEOUT:
                return "GATEWAY_TIMEOUT";
            case self::HTTP_VERSION_NOT_SUPPORTED:
                return "HTTP_VERSION_NOT_SUPPORTED";
            default:
                return "UNDEFINED HTTP STATUS CODE " . $code;
        }
    }
}
