<?php

namespace App\Controllers;
use App\Core\Controller;
use simplehtmldom\HtmlDocument;

class IssueController extends Controller
{

    function closetags($html)
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument;
        // set encoding
        $dom->encoding = 'utf-8';

        $dom->loadHTML($html);

        $mock = new \DOMDocument;
        $body = $dom->getElementsByTagName('body')->item(0);
        foreach ($body->childNodes as $child) {
            $mock->appendChild($mock->importNode($child, true));
        }

        $fixed = trim(html_entity_decode($mock->saveHTML()));
        
        return $fixed;
    }
    
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

    private function extract_data($html)
    {
        $li = $html->find('li');
        $cat = '';
        $details = '';

        $articles_data = [];
        foreach ($li as $item) { 
            $isCategory = $item->prev_sibling()->tag !== null && $item->prev_sibling()->tag == 'p';
            if ($isCategory) { 
                $cat = $item->prev_sibling()->find('b', 0)->innertext;
            }

            $title = $item->find('span b', 0)->innertext;
            $author_names = $item->find('.authornames', 0)->innertext;
            $author_names = trim(explode("EJMCR", $item->find('span.authornames', 0)->plaintext)[0]);
            preg_match("/(\d{4})\;\s(\d+)\((\d+)\)\:\s(\d+)\-(\d+)/", $item->find('.journalfont text', 0)->plaintext, $issue_details);

            $links = $item->find('a');

            foreach ($links as $link) {
                if (str_contains($link->plaintext, "PDF")) {
                    $urls['pdf'] = true;
                }
                if (str_contains($link->plaintext, "HTML")) {
                    $urls['html'] = true;
                }
                if (str_contains($link->plaintext, "Abstract")) {
                    $urls['mno'] = explode("=", $link->href)[1];
                } 
                if (str_contains($link->href, "doi.org")) {
                    $urls['doi'] = explode("doi.org/", $link->href)[1];
                }
            }

            $details = [
                "year" => $issue_details[1],
                "volume" => $issue_details[2],
                "issue" => $issue_details[3],
                "urls" => $this->extract_issue_url($html)
            ];

            $articles_data[$cat][] = [
                "title" => $title,
                "authors" => $author_names,
                "issue_details" => [
                    "year" => $issue_details[1],
                    "volume" => $issue_details[2],
                    "issue" => $issue_details[3],
                    "start_page" => $issue_details[4],
                    "end_page" => $issue_details[5],
                ],
                "urls" => $urls
            ];
        }
        
        return [
            'issue_details' => $details,
            'articles' => $articles_data
        ];
    }

    public function articles()
    {

        if ($_ENV['APP_DEBUG'] == true) {
            $content = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/files_html/issue.html");
        } else {
            $content = file_get_contents("https://www.ejmanager.com/index_myjournal.php?jid=" . $_ENV["JOURNAL_ID"]. "&sec=cissue");
        }

        $contents = str_replace("â&#128;&#153;", "'", $this->closetags($content));
        unset($content);

        $client = new HtmlDocument();
        $html = $client->load($contents);

        return  $this->extract_data($html);
    }

    public function current_issue()
    {

        $this->view('issues/index', [
            "meta" => [
                "title" => "Current issue",
                "description" => "Current issue of " . $_ENV["JOURNAL_TITLE"],
                "keywords" => "current issue"
            ],
            "data" => $this->articles()
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
