<?php

namespace App\Controllers;
use App\Core\Controller;
use simplehtmldom\HtmlWeb;

class HomeController extends Controller
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

    public function cited()
    {
        $client = new HtmlWeb();
        $html = $client->load("https://www.ejmanager.com/index_myjournal.php?jid=" . $_ENV['JOURNAL_ID']. "&sec=mosta");
        $issues = $this->extract_issue_url($html);

        $this->dump($issues);


        $this->view('empty/index');
    }

    public function index()
    {
        $header_data = $this->db()->table('settings')->select()->where('id', 1)->get()[0];
        $metrics_data = $this->db()->table('metrics')->select()->where('id', 1)->get()[0];
        $featured_article = $this->db()->table('featured_article')->select()->where('id', 1)->get()[0];
        $editorial_board = $this->db()->table('editors')->select()->orderBy(['pos' => 'asc'])->get();

        $this->view('home/index', [
            'meta' => [
                'title' => 'Home',
                'description' => 'Home page',
                'keywords' => 'home, page'
            ],
            "header_data" => $header_data,
            "metrics_data" => $metrics_data,
            "featured_article" => $featured_article,
            "editorial_board" => $editorial_board,
        ]);
    }
}
