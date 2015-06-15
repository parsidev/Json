<?php namespace Parsidev\Json;

use Parsidev\Json\Exception\EncodingFailedException;
use Parsidev\Json\Exception\InvalidSchemaException;
use Parsidev\Json\Exception\ValidationFailedException;

class JsonEncoder
{
    const JSON_ARRAY = 1;
    const JSON_OBJECT = 2;
    const JSON_STRING = 3;
    const JSON_NUMBER = 4;

    private $validator;
    private $arrayEncoding = self::JSON_ARRAY;
    private $numericEncoding = self::JSON_STRING;
    private $gtLtEscaped = false;
    private $ampersandEscaped = false;
    private $singleQuoteEscaped = false;
    private $doubleQuoteEscaped = false;
    private $slashEscaped = true;
    private $unicodeEscaped = true;
    private $prettyPrinting = false;
    private $terminatedWithLineFeed = false;
    private $maxDepth = 512;

    public function __construct()
    {
        $this->validator = new JsonValidator();
    }

    public function encodeFile($data, $file, $schema = null)
    {
        try {
            file_put_contents($file, $this->encode($data, $schema));
        } catch (EncodingFailedException $e) {
            throw new EncodingFailedException(sprintf(
                'هنگام رمزگذاری خطا رخ داده است %s: %s',
                $file,
                $e->getMessage()
            ), $e->getCode(), $e);
        } catch (ValidationFailedException $e) {
            throw new ValidationFailedException(sprintf(
                "هنگام رمزگذاری اعتبارسنجی شکست خورد %s:\n%s",
                $file,
                $e->getErrorsAsString()
            ), $e->getErrors(), $e->getCode(), $e);
        } catch (InvalidSchemaException $e) {
            throw new InvalidSchemaException(sprintf(
                'هنگام رمزگذاری خطا رخ داده است %s: %s',
                $file,
                $e->getMessage()
            ), $e->getCode(), $e);
        }
    }

    public function encode($data, $schema = null)
    {
        if (null !== $schema) {
            $errors = $this->validator->validate($data, $schema);
            if (count($errors) > 0) {
                throw ValidationFailedException::fromErrors($errors);
            }
        }

        $options = 0;

        if (self::JSON_OBJECT === $this->arrayEncoding) {
            $options |= JSON_FORCE_OBJECT;
        }

        if (self::JSON_NUMBER === $this->numericEncoding) {
            $options |= JSON_NUMERIC_CHECK;
        }

        if ($this->gtLtEscaped) {
            $options |= JSON_HEX_TAG;
        }
        if ($this->ampersandEscaped) {
            $options |= JSON_HEX_AMP;
        }
        if ($this->singleQuoteEscaped) {
            $options |= JSON_HEX_APOS;
        }
        if ($this->doubleQuoteEscaped) {
            $options |= JSON_HEX_QUOT;
        }

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            if (!$this->slashEscaped) {
                $options |= JSON_UNESCAPED_SLASHES;
            }
            if (!$this->unicodeEscaped) {
                $options |= JSON_UNESCAPED_UNICODE;
            }
            if ($this->prettyPrinting) {
                $options |= JSON_PRETTY_PRINT;
            }
        }

        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            $maxDepth = $this->maxDepth;
            if (!defined('HHVM_VERSION')) {
                --$maxDepth;
            }
            $encoded = json_encode($data, $options, $maxDepth);
        } else {
            $encoded = json_encode($data, $options);
        }

        if ($encoded === false) {
            throw new EncodingFailedException(sprintf(
                'داده ها نمی توانند به عنوان JSON کدگذاری شوند: %s',
                JsonError::getLastErrorMessage()
            ), json_last_error());
        }
        if ($this->terminatedWithLineFeed) {
            $encoded .= "\n";
        }
        return $encoded;
    }

    public function getArrayEncoding()
    {
        return $this->arrayEncoding;
    }

    public function setArrayEncoding($encoding)
    {
        if (self::JSON_ARRAY !== $encoding && self::JSON_OBJECT !== $encoding) {
            throw new \InvalidArgumentException(sprintf(
                'Expected JsonEncoder::JSON_ARRAY or JsonEncoder::JSON_OBJECT. ' .
                'Got: %s',
                $encoding
            ));
        }
        $this->arrayEncoding = $encoding;
    }

    public function getNumericEncoding()
    {
        return $this->numericEncoding;
    }


    public function setNumericEncoding($encoding)
    {
        if (self::JSON_NUMBER !== $encoding && self::JSON_STRING !== $encoding) {
            throw new \InvalidArgumentException(sprintf(
                'Expected JsonEncoder::JSON_NUMBER or JsonEncoder::JSON_STRING. ' .
                'Got: %s',
                $encoding
            ));
        }
        $this->numericEncoding = $encoding;
    }


    public function isAmpersandEscaped()
    {
        return $this->ampersandEscaped;
    }


    public function setEscapeAmpersand($enabled)
    {
        $this->ampersandEscaped = $enabled;
    }


    public function isDoubleQuoteEscaped()
    {
        return $this->doubleQuoteEscaped;
    }


    public function setEscapeDoubleQuote($enabled)
    {
        $this->doubleQuoteEscaped = $enabled;
    }

    public function isSingleQuoteEscaped()
    {
        return $this->singleQuoteEscaped;
    }


    public function setEscapeSingleQuote($enabled)
    {
        $this->singleQuoteEscaped = $enabled;
    }


    public function isSlashEscaped()
    {
        return $this->slashEscaped;
    }

    public function setEscapeSlash($enabled)
    {
        $this->slashEscaped = $enabled;
    }


    public function isGtLtEscaped()
    {
        return $this->gtLtEscaped;
    }

    public function setEscapeGtLt($enabled)
    {
        $this->gtLtEscaped = $enabled;
    }

    public function isUnicodeEscaped()
    {
        return $this->unicodeEscaped;
    }


    public function setEscapeUnicode($enabled)
    {
        $this->unicodeEscaped = $enabled;
    }

    public function isPrettyPrinting()
    {
        return $this->prettyPrinting;
    }


    public function setPrettyPrinting($prettyPrinting)
    {
        $this->prettyPrinting = $prettyPrinting;
    }


    public function isTerminatedWithLineFeed()
    {
        return $this->terminatedWithLineFeed;
    }

    public function setTerminateWithLineFeed($enabled)
    {
        $this->terminatedWithLineFeed = $enabled;
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
}