<?php
namespace Andrewlamers\Chargify;
class Collection extends \Illuminate\Support\Collection
{
    protected $_has_error = false;

    public function __construct(array $items)
    {
        parent::__construct($items);
    }

    public function hasErrors()
    {
        return $this->_has_error;
    }

    public function setErrors($errors)
    {
        $this->_has_error = $errors;
    }
}