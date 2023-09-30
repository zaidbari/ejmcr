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
    public function slugify($content)
    {
        $content = strtolower($content);
        $content = str_replace(' ', '-', $content);
        $content = preg_replace('/[^A-Za-z0-9\-]/', '', $content);
        return $content;
    }
}
