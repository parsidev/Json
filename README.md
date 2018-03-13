#JSON Encoder/Decoder installation

```php
$ composer require parsidev/json
```

Usage
-----

### Encode
```php
$data = array("test"=>1);
return \ParsJSON::encoder($data);
```

### Encode File
```php
$data = array("test"=>1);
\ParsJSON::encoderFile($data, '/path/to/file.json');
```

### Decode
```php
$json = '{"test":1}';
return \ParsJSON::decoder($json);
```

### Decode File
```php
return \ParsJSON::decoderFile('/path/to/file.json');
```
