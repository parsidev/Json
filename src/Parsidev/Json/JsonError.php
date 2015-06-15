<?php namespace Parsidev\Json;

class JsonError
{

    private function __construct()
    {
    }

    public static function getLastErrorMessage()
    {
        return self::getErrorMessage(json_last_error());
    }

    public static function getErrorMessage($error)
    {
        switch ($error) {
            case JSON_ERROR_NONE:
                return 'JSON_ERROR_NONE';
            case JSON_ERROR_DEPTH:
                return 'JSON_ERROR_DEPTH';
            case JSON_ERROR_STATE_MISMATCH:
                return 'JSON_ERROR_STATE_MISMATCH';
            case JSON_ERROR_CTRL_CHAR:
                return 'JSON_ERROR_CTRL_CHAR';
            case JSON_ERROR_SYNTAX:
                return 'JSON_ERROR_SYNTAX';
            case JSON_ERROR_UTF8:
                return 'JSON_ERROR_UTF8';
        }
        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            switch ($error) {
                case JSON_ERROR_RECURSION:
                    return 'JSON_ERROR_RECURSION';
                case JSON_ERROR_INF_OR_NAN:
                    return 'JSON_ERROR_INF_OR_NAN';
                case JSON_ERROR_UNSUPPORTED_TYPE:
                    return 'JSON_ERROR_UNSUPPORTED_TYPE';
            }
        }
        return 'JSON_ERROR_UNKNOWN';
    }
}