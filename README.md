#JSON Encoder/Decoder installation

```php
$ composer require parsidev/json
```
Once composer is finished, you need to add the service provider. Open ```config/app.php```, and add a new item to the providers array.
```
'Parsidev\Json\JsonServiceProvider',
```
Next, add a Facade for more convenient usage. In ```config/app.php``` add the following line to the aliases array:
```
'ParsJSON' => 'Parsidev\Json\Facades\JsonFacade',
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
