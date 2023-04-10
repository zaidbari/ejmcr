<?php

namespace App\Controllers;

use App\Core\Controller;

class ArticlesController extends Controller
{


    public function currentIssue()
    {
        $this->view('articles/list', [
            'meta' => [
                'title' => 'Current Issue',
                'description' => 'Current Issue',
                'keywords' => 'current, issue'
            ]
        ]);
    }
}
