<?php
namespace Andrewlamers\Chargify;
class Fluent extends \Illuminate\Support\Fluent
{
    protected $_has_error = false;

    public function setErrors($errors)
    {
        $this->_has_error = $errors;
    }

    public function hasErrors()
    {
        return $this->_has_error;
    }
}