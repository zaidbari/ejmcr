<?php

namespace App\Controllers;
use App\Core\Controller;

class PageController extends Controller
{
    public function index($slug)
    {

        $data = $this->db()->table('policies')->select()->where('slug', $slug)->get()[0];
        $this->view('single/index', [
            "meta" => [
            "title" => $data['title'],
            "description" => $data['title'] . " information for " .  $_ENV['JOURNAL_TITLE'],
            "keywords" =>  $data['title'] .  $_ENV['JOURNAL_TITLE'],
            ],
            "content" => $data['content']
        ]);
    }

    public function gfa()
    {
        $content = file_get_contents("https://www.ejmanager.com/index_myjournal.php?jid=".$_ENV['JOURNAL_ID']."&sec=gfa");
        $this->view('single/index', [
            "meta" => [
            "title" => "Guide for Authors",
            "description" => "Guide for Authors",
            "keywords" => "Guide for Authors",
            ],
            "content" => $content
        ]);
    }

    public function eboard()
    {
        $content = file_get_contents("https://www.ejmanager.com/index_myjournal.php?jid=".$_ENV['JOURNAL_ID']."&sec=eboard");
        $this->view('single/index', [
            "meta" => [
            "title" => "Editorial Board",
            "description" => "Editorial Board of " . $_ENV['JOURNAL_TITLE'],
            "keywords" => "Editorial Board",
            ],
            "content" => $content
        ]);
    }
}
