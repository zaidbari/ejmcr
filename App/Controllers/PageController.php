<?php

namespace App\Controllers;
use App\Core\Controller;

class PageController extends Controller
{
    public function index($slug)
    {

        $data = $this->db()->table('pages')->select()->where('page_url', $slug)->first();
        if(!$data) $this->redirect('/404');
        
        $this->view('common/single/index', [
            "meta" => [
            "title" => $data['page_title'],
            "description" => $data['page_title'] . " information for " .  $_ENV['JOURNAL_TITLE'],
            "keywords" =>  $data['page_title'] .  $_ENV['JOURNAL_TITLE'],
            ],
            "content" => $data['page_content']
        ]);
    }

    public function notFound()
    {
        $this->view('common/empty/index');
    }

    public function gfa()
    {
        $content = file_get_contents("https://www.ejmanager.com/index_myjournal.php?jid=".$_ENV['JOURNAL_ID']."&sec=gfa");
        $this->view('common/single/index', [
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
        $this->view('common/single/index', [
            "meta" => [
            "title" => "Editorial Board",
            "description" => "Editorial Board of " . $_ENV['JOURNAL_TITLE'],
            "keywords" => "Editorial Board",
            ],
            "content" => $content
        ]);
    }
}