<?php namespace Parsidev\Json;

use JsonSchema\Exception\InvalidArgumentException;
use JsonSchema\Validator;

class JsonValidator
{
    private $metaSchema;

    public function validate($data, $schema)
    {
        if (is_string($schema)) {
            $schema = $this->loadSchema($schema);
        } else {
            $this->assertSchemaValid($schema);
        }
        $validator = new Validator();
        try {
            $validator->check($data, $schema);
        } catch (InvalidArgumentException $e) {
            throw new InvalidSchemaException(sprintf(
                'الگو نادرست است: %s',
                $e->getMessage()
            ), 0, $e);
        }
        $errors = array();
        if (!$validator->isValid()) {
            $errors = (array)$validator->getErrors();
            foreach ($errors as $key => $error) {
                $prefix = $error['property'] ? $error['property'] . ': ' : '';
                $errors[$key] = $prefix . $error['message'];
            }
        }
        return $errors;
    }

    private function assertSchemaValid($schema)
    {
        if (null === $this->metaSchema) {
            // The meta schema is obviously not validated. If we
            // validate it against itself, we have an endless recursion
            $this->metaSchema = json_decode(file_get_contents(__DIR__ . '/../res/meta-schema.json'));
        }
        if ($schema === $this->metaSchema) {
            return;
        }
        $errors = $this->validate($schema, $this->metaSchema);
        if (count($errors) > 0) {
            throw new InvalidSchemaException(sprintf(
                "الگو نادرست است:\n%s",
                implode("\n", $errors)
            ));
        }
        // not caught by justinrainbow/json-schema
        if (!is_object($schema)) {
            throw new InvalidSchemaException(sprintf(
                'الگو باید یک شی باشد: %s',
                $schema,
                gettype($schema)
            ));
        }
    }

    private function loadSchema($file)
    {
        if (!file_exists($file)) {
            throw new InvalidSchemaException(sprintf(
                'فایل الگو %s وجود ندارد.',
                $file
            ));
        }
        $schema = json_decode(file_get_contents($file));
        try {
            $this->assertSchemaValid($schema);
        } catch (InvalidSchemaException $e) {
            throw new InvalidSchemaException(sprintf(
                'هنگام بارگذاری الگو خطایی رخ داده است %s: %s',
                $file,
                $e->getMessage()
            ), 0, $e);
        }
        return $schema;
    }
}