<?php
namespace Andrewlamers\Chargify\Laravel;
use Illuminate\Support\Facades\Facade;
class ChargifyFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'chargify';
    }
}