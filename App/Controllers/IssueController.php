<?php

namespace App\Controllers;
use App\Core\Controller;
use simplehtmldom\HtmlWeb;

class IssueController extends Controller
{

    private function extract_issue_url($html)
    {
        $issues = ["previous" => null, "next" => null];
        foreach ($html->find('div a') as $element) {
            parse_str(parse_url($element->href, PHP_URL_QUERY), $query);
            $href = explode("&", $query['iid'])[0];
            if (str_contains($element->innertext, "Previous")) { 
                $issues['previous'] = $href;
            }
            if (str_contains($element->innertext, "Next")) { 
                $issues['next'] = $href;
            }
        }

        return $issues;
    }

    public function articles()
    {
        $data = [];
        $client = new HtmlWeb();
        $html = $client->load("https://www.ejmanager.com/index_myjournal.php?jid=" . $_ENV['JOURNAL_ID']. "&sec=cissue");
        $data['issue_links'] = $this->extract_issue_url($html);
        $data['articles'] = [];
        $data['issue_details'] = $html->find('span.journalfont b', 0)->innertext; 
        
        $this->dump($data);
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
