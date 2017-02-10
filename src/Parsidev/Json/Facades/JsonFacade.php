<?php namespace Parsidev\Json\Facades;

use Illuminate\Support\Facades\Facade;

class JsonFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'parsJSON';
    }
}