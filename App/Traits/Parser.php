<?php

namespace App\Traits;

use simplehtmldom\HtmlDocument;

trait Parser
{
    use Logs;

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
        $contents = str_replace(["<br>", "<br />"], "", $content);
        $contents = $this->fixTags(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        unset($content);

        $client = new HtmlDocument();
        $html = $client->load($contents);
        
        $category = $html->find('td b', 0)->plaintext;
        $d = $html->find('#summary text');
        $abstract = $d[1]->plaintext;
        $keywords = $d[3]->plaintext;

        $title = $html->find('td span', 0)->innertext;
        $authors = explode(", ", trim(str_replace(".", "", $html->find('td i', 0)->plaintext)));
        $doi = $html->find('td div a', 2)->plaintext;
       
        $issue_string = explode("-", explode("iid=", $html->find('td div a', 1)->href)[1]);
        $issue_details['year'] = (int) $issue_string[0];
        $issue_details['volume'] = (int) $issue_string[1];
        $issue_details['issue'] = (int) explode(".", $issue_string[2])[0];


        if (str_contains($html->find('td b a', 3)->plaintext, "Cited")) {
            $citation_count = (int) explode(" ", $html->find('td b a', 3)->plaintext)[2];
        } else {
            $citation_count = 0;
        }


        $cites = $html->find("tr[valign=middle]");
        $citations = [];
        foreach ($cites as $item) {
            $title = $item->find('td text', 0)->plaintext;
            $link = $item->find('td a', 0)->href;
            $link_title = $item->find('td a', 0)->plaintext;
            $citations[] = [
                "title" => $title,
                "link" => [
                    "url" => $link,
                    "title" => $link_title
                ]
            ];
        }

        $article_links = ['previous' => null, 'next' => null];
        foreach ($html->find('div a') as $link) {
            if (str_contains($link->innertext, "Previous")) {
                $article_links['previous'] = explode("=", $link->href)[1];
            }
            if (str_contains($link->innertext, "Next")) {
                $article_links['next'] = explode("=", $link->href)[1];
            }
        }


        $data = [
            "title" => $title,
            "authors" => $authors,
            "category" => $category,
            "abstract" => $abstract,
            "keywords" => $keywords,
            "doi" => $doi,
            "issue_details" => $issue_details,
            "citations" => $citations,
            "citation_count" => $citation_count,
            "article_links" => $article_links
        ];

        $this->dump($data);


    }
}
