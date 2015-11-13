<?php
namespace Andrewlamers\Chargify;
use Illuminate\Support\Facades\Facade;
class ChargifyFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'chargify';
    }
}