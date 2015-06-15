<?php namespace Parsidev\Json\Exception;

class ValidationFailedException extends \Exception
{
    private $errors;

    public function __construct($message = '', array $errors = array(), $code = 0, \Exception $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public static function fromErrors(array $errors = array(), $code = 0, \Exception $previous = null)
    {
        return new static(sprintf(
            "اعتبار سنجی داده های JSON شکست خورده:\n%s",
            implode("\n", $errors)
        ), $errors, $code, $previous);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getErrorsAsString()
    {
        return implode("\n", $this->errors);
    }
}