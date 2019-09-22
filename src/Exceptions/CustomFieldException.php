<?php

namespace mradang\LaravelFly\Exceptions;

class CustomFieldException extends \Exception
{

    public function __construct($msg = '')
    {
        parent::__construct($msg);
    }

}
