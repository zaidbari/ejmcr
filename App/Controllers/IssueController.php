<?php

namespace App\Controllers;
use App\Core\Controller;
use simplehtmldom\HtmlWeb;

class IssueController extends Controller
{

    public function articles()
    {
        $client = new HtmlWeb();
        $html = $client->load("https://www.ejmanager.com/index_myjournal.php?jid=" . $_ENV['JOURNAL_ID']. "&sec=cissue");

        $this->dump($html->innertext);
    }
    public function current_issue()
    {

        $this->articles();

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
