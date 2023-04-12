<?php

namespace App\Traits;

use simplehtmldom\HtmlDocument;

trait Parser
{
    public function fixTags($html)
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument;
        $dom->loadHTML($html);
        $mock = new \DOMDocument;
        $body = $dom->getElementsByTagName('body')->item(0);
        foreach ($body->childNodes as $child) {
            $mock->appendChild($mock->importNode($child, true));
        }

        $fixed = trim($mock->saveHTML());
        return $fixed;

    }

    public function extract_issue_url($html)
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

    public function extract_article_list($content)
    {
        $contents = $this->fixTags(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        unset($content);

        $client = new HtmlDocument();
        $html = $client->load($contents);


        $li = $html->find('li');
        $cat = '';
        $articles_data = [];
        $urls = [];
        $details = [];
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

    public function extract_article_data($content)
    {
        $contents = $this->fixTags(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        unset($content);

        $client = new HtmlDocument();
        $html = $client->load($contents);

    }
}
