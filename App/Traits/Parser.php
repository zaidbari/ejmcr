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

    public function extract_article_info($content)
    {
        $client = new HtmlDocument();
        $html = $client->load($content);

        $authors = $html->find('span[style|="font-size:1.3em;"]');
        $author_names = [];
        foreach ($authors as $author) {
            $author_names[] = $author->plaintext;
        }
        $affiliations = $html->find('span[style|="font-size:.95em;"]');
        $author_affiliations = [];
        foreach ($affiliations as $affiliation) {
            $author_affiliations[] = $affiliation->plaintext;
        }

        $authors = [];
        foreach ($author_names as $key => $name) {
            $authors[$name] = $author_affiliations[$key];
        }

        $correspond = $html->find('span[style|="font-size:1.0em;"]', 0);
        $correspondence['name'] = trim(str_replace(".", "", explode(";", $correspond->plaintext)[0]));
        $correspondence['email'] = $correspond->find('a', 0)->plaintext;
        
        $history = trim($html->find('span[style|="font-size:1.0em;"]', 1)->innertext);

        return [
            'authors' => $authors,
            'correspondence' => $correspondence,
            'history' => $history
        ];

    }

    public function extract_article_data($content)
    {

        $client = new HtmlDocument();
        $html = $client->load($content);

        $category = $html->find('td b', 0)->plaintext;
        $d = $html->find('#summary text');
        $abstract = $d[1]->plaintext;
        $keywords = $d[3]->plaintext;
        unset($d);

        $title = $html->find('td span', 0)->innertext;
        $authors = explode(", ", trim(str_replace(".", "", $html->find('td i', 0)->plaintext)));
        $author_names = trim(str_replace(".", "", $html->find('td i', 0)->plaintext));
        $doi = $html->find('td div a', 2)->plaintext;

        $issue_link =  explode("iid=", $html->find('td div a', 1)->href)[1];
        $issue_string = explode("-", $issue_link);
        $issue_details['year'] = (int) $issue_string[0];
        $issue_details['volume'] = (int) $issue_string[1];
        $issue_details['issue'] = (int) explode(".", $issue_string[2])[0];
        $issue_details['link'] = $issue_link;
        unset($issue_string);
        unset($issue_link);


        if (str_contains($html->find('td b a', 3)->plaintext, "Cited")) {
            $citation_count = (int) explode(" ", $html->find('td b a', 3)->plaintext)[2];
        } else {
            $citation_count = 0;
        }

        $citations = [];
        foreach ($html->find("tr[valign=middle]") as $item) {
            $title = $item->find('td text', 0)->plaintext;
            $link = $item->find('td a', 0)->href;
            $link_title = $item->find('td a', 0)->plaintext;
            $citations[] = [
                "title" => $title,
                "link" => [
                    "url" => str_replace("http://dx.", "https://", $link),
                    "title" => $link_title
                ]
            ];
        }

        $pa = explode("-", explode(": ", $html->find('td[colspan=3] div div', 0)->find('text', 3)->innertext)[1]);
        $pages = [
            "first" => $pa[0],
            "last" => $pa[1]
        ];

        unset($pa);

        $article_links = ['previous' => null, 'next' => null];
        foreach ($html->find('div a') as $link) {
            if (str_contains($link->innertext, "Previous")) {
                $article_links['previous'] = explode("=", $link->href)[1];
            }
            if (str_contains($link->innertext, "Next")) {
                $article_links['next'] = explode("=", $link->href)[1];
            }
        }

        $c = $html->find('.articletitle', 0)->next_sibling()->find('td', 0);
        $references = [];
        $pubmed_style = [
            "style" => $c->find('b', 0)->plaintext,
            "reference" => $c->find('text', 1)->plaintext
        ];

        $web_style = [
            "style" => $c->find('b', 1)->plaintext,
            "reference" => $c->find('text', 4)->plaintext
        ];

        $references[] = $pubmed_style;
        $references[] = $web_style;

        foreach ($c->find('p') as $item) {
            $ref = trim(explode("doi:", $item->plaintext)[0]);
            $style = $item->prev_sibling()->plaintext;
            $references[] = [
                "style" => $style,
                "reference" => $ref
            ];
        }

        $files = ['html' => null, 'pdf' => null];
        foreach ($html->find('table[class=boxtext] a') as $link) {
            if (str_contains($link->plaintext, "HTML")) {
                $files['html'] = true;
            }
            if (str_contains($link->plaintext, "PDF")) {
                $files['pdf'] = explode("fulltxtp=", $link->href)[1];
            }
        }

        $html = '';

        if ($files['html'] !== null && $_ENV['APP_DEBUG'] !== "true") {
            $h = file_get_contents($_ENV['JOURNAL_DOMAIN'] . "/files_html/" . strtolower($_ENV['JOURNAL_ABBREV'] . "-" . $issue_details['volume'] . "-" . $pages['first'] . '.html'));
            $client = new HtmlDocument();
            $con = $client->load($h);
            $con->find('p', 0)->remove();
            $con->find('p', 1)->remove();
            $con->find('h2', 0)->remove();
            $html = $con->save();
        }

        unset($client);


        return [
            "title" => $title,
            "authors" => $authors,
            "author_names" => $author_names,
            "category" => $category,
            "abstract" => $abstract,
            "keywords" => $keywords,
            "doi" => $doi,
            "issue_details" => $issue_details,
            "citations" => $citations,
            "citation_count" => $citation_count,
            "article_links" => $article_links,
            "pages" => $pages,
            "references" => $references,
            "files" => $files,
            "html" => $html
        ];

    }
}
