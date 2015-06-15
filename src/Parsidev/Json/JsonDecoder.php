<?php namespace Parsidev\Json;

use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use Parsidev\Json\Exception\DecodingFailedException;
use Parsidev\Json\Exception\EncodingFailedException;
use Parsidev\Json\Exception\FileNotFoundException;
use Parsidev\Json\Exception\ValidationFailedException;
use Parsidev\Json\Exception\InvalidSchemaException;

class JsonDecoder
{
    const OBJECT = 0;
    const ASSOC_ARRAY = 1;
    const FLOAT = 2;
    const STRING = 3;

    private $validator;
    private $objectDecoding = self::OBJECT;
    private $bigIntDecoding = self::FLOAT;
    private $maxDepth = 512;

    public function __construct()
    {
        $this->validator = new JsonValidator();
    }

    public function decode($json, $schema = null)
    {
        if (self::ASSOC_ARRAY === $this->objectDecoding && null !== $schema) {
            throw new \InvalidArgumentException(
                'اعتبارسنجی الگو زمانی که اشیاء به عنوان آرایه های انجمنی رمزگشایی شده باشد،پشتیبانی نمی کنید. ' .
                'برای اصلاح JsonDecoder::setDecodeObjectsAs(JsonDecoder::JSON_OBJECT) را فراخوانی کنید.');
        }
        $decoded = $this->decodeJson($json);
        if (null !== $schema) {
            $errors = $this->validator->validate($decoded, $schema);
            if (count($errors) > 0) {
                throw ValidationFailedException::fromErrors($errors);
            }
        }
        return $decoded;
    }


    public function decodeFile($file, $schema = null)
    {
        if (!file_exists($file)) {
            throw new FileNotFoundException(sprintf(
                'فایل %s وجود ندارد.',
                $file
            ));
        }

        try {
            return $this->decode(file_get_contents($file), $schema);
        } catch (DecodingFailedException $e) {
            throw new DecodingFailedException(sprintf(
                'هنگام رمزگشایی خطایی رخ داد %s: %s',
                $file,
                $e->getMessage()
            ), $e->getCode(), $e);
        } catch (ValidationFailedException $e) {
            throw new ValidationFailedException(sprintf(
                "اعتبارسنجی  %s شکست خورد:\n%s",
                $file,
                $e->getErrorsAsString()
            ), $e->getErrors(), $e->getCode(), $e);
        } catch (InvalidSchemaException $e) {
            throw new InvalidSchemaException(sprintf(
                'هنگام رمزگشایی خطایی رخ داد %s: %s',
                $file,
                $e->getMessage()
            ), $e->getCode(), $e);
        }
    }

    public function getMaxDepth()
    {
        return $this->maxDepth;
    }

    public function setMaxDepth($maxDepth)
    {
        if (!is_int($maxDepth)) {
            throw new \InvalidArgumentException(sprintf(
                'حداکثر عمق باید یک عدد صحیح باشد: %s',
                is_object($maxDepth) ? get_class($maxDepth) : gettype($maxDepth)
            ));
        }
        if ($maxDepth < 1) {
            throw new \InvalidArgumentException(sprintf(
                'حداکثر عمق باید 1 یا بیشتر باشد: %s',
                $maxDepth
            ));
        }
        $this->maxDepth = $maxDepth;
    }

    public function getObjectDecoding()
    {
        return $this->objectDecoding;
    }

    public function setObjectDecoding($decoding)
    {
        if (self::OBJECT !== $decoding && self::ASSOC_ARRAY !== $decoding) {
            throw new \InvalidArgumentException(sprintf(
                'Expected JsonDecoder::JSON_OBJECT or JsonDecoder::ASSOC_ARRAY. ' .
                'Got: %s',
                $decoding
            ));
        }
        $this->objectDecoding = $decoding;
    }

    public function getBigIntDecoding()
    {
        return $this->bigIntDecoding;
    }

    public function setBigIntDecoding($decoding)
    {
        if (self::FLOAT !== $decoding && self::STRING !== $decoding) {
            throw new \InvalidArgumentException(sprintf(
                'Expected JsonDecoder::FLOAT or JsonDecoder::JSON_STRING. ' .
                'Got: %s',
                $decoding
            ));
        }
        $this->bigIntDecoding = $decoding;
    }

    private function decodeJson($json)
    {
        $assoc = self::ASSOC_ARRAY === $this->objectDecoding;
        if (PHP_VERSION_ID >= 50400 && !defined('JSON_C_VERSION')) {
            $options = self::STRING === $this->bigIntDecoding ? JSON_BIGINT_AS_STRING : 0;
            $decoded = json_decode($json, $assoc, $this->maxDepth, $options);
        } else {
            $decoded = json_decode($json, $assoc, $this->maxDepth);
        }
        if (null === $decoded && 'null' !== $json) {
            $parser = new JsonParser();
            $e = $parser->lint($json);
            if ($e instanceof ParsingException) {
                throw new DecodingFailedException(sprintf(
                    'داده JSON نمی تواند رمزگشایی شود: %s.',
                    $e->getMessage()
                ), 0, $e);
            }
            throw new DecodingFailedException(sprintf(
                'داده JSON نمی تواند رمزگشایی شود: %s.',
                JsonError::getLastErrorMessage()
            ), json_last_error());
        }
        return $decoded;
    }
}