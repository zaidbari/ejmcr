<?php

namespace App\Controllers;
use App\Core\Controller;
use simplehtmldom\HtmlWeb;

class IssueController extends Controller
{

    public function current_issue()
    {
        $this->view('issues/current', [
            'meta' => [
            'title' => 'Current issue',
            'description' => 'Current issue of the journal',
            'keywords' => 'current issue'
            ],
        ]);
    }
    public function latest_issue()
    {
        $this->view('issues/current', [
            'meta' => [
            'title' => 'Current issue',
            'description' => 'Current issue of the journal',
            'keywords' => 'current issue'
            ],
        ]);
    }
}
