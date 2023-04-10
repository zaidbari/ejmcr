<?php

namespace App\Core;

use App\Traits\Logs;
use App\Traits\Model;
use App\Traits\Request;
use App\Traits\Validation;
use App\Traits\View;

class Controller
{
    use Validation, Request, View, Model, Logs;
}
