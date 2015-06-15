<?php namespace Parsidev\Json;

class JsonWrapper
{

    protected $decoder;
    protected $encoder;

    public function __construct()
    {
        $this->decoder = new JsonDecoder;
        $this->encoder = new JsonEncoder;
    }

    public function encoder($data, $schema = null)
    {
        return $this->encoder->encode($data, $schema);
    }

    public function encoderFile($data, $file, $schema = null)
    {
        $this->encoder->encodeFile($data, $file, $schema);
    }


    public function decoder($json, $schema = null)
    {
        return $this->decoder->decode($json, $schema);
    }

    public function decoderFile($file, $schema = null)
    {
        return $this->decoder->decodeFile($file, $schema);
    }
}
