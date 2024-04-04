<?php

namespace App\Controllers;

use App\Core\Controller;
use simplehtmldom\HtmlWeb;

class HomeController extends Controller
{

	private function extract_citation_count($html)
	{
		$cited = $html->find('b', 2)->innertext ?? null; // get the citation information
		if (isset($cited)) {
			preg_match('/(\d+) times/', $cited, $matches); // extract the number of times cited using regex
			return $matches[1]; // get the first matched group which contains the number of times cited
		} else {
			return null;
		}
	}

	private function extract_text($html)
	{
		$text_string = array();
		foreach ($html->find('text') as $text_node) {
			$text = trim($text_node->text() ?? '');
			if (!empty($text)) {
				$text_string[] = $text;
			}
		}
		return $text_string;
	}

	private function extract_issue_details($html)
	{
		preg_match("/(\d{4})\;\s(\d+)\((\d+)\)\:\s(\d+)\-(\d+)/", $html, $matches);
		return $matches;

	}

	public function articles($type)
	{
		$client = new HtmlWeb();
		$html = $client->load("https://www.ejmanager.com/index_myjournal.php?jid=" . $_ENV['JOURNAL_ID'] . "&sec=most" . $type);


		$ol = $html->find('ol', 0);
		if (!isset($ol)) {
			return array();
		}
		$articles = [];
		foreach ($ol->find('li') as $li) {
			$title = $li->find('span', 0)->find('b', 0)->innertext;
			$abstract_url = $li->find('a', 0)->href;
			$doi_url = $li->find('a', 1)->href;

			$mno = explode("=", $abstract_url)[1];
			$doi = explode("doi.org/", $doi_url)[1];

			$text_string = $this->extract_text($li);
			$matches = $this->extract_issue_details($text_string[3]);
			$cited = $this->extract_citation_count($li);

			$article = [
				"title" => $title,
				"mno" => $mno,
				"doi" => $doi,
				"authors" => $text_string[2],
				"cited" => $cited,
				"issue_details" => [
					"year" => $matches[1],
					"volume" => $matches[2],
					"issue" => $matches[3],
					"start_page" => $matches[4],
					"end_page" => $matches[5],
				]

			];


			$articles[] = $article;

		}

		return $articles;
	}

	public function index()
	{

		$featured_article = $this->db()->table('featuredarticle')->select()->first();
		$journal_data = $this->db()->table('settings')->select()->where('id', 1)->first();
		$editorial_board = $this->db()->table('editors')->select()->orderBy('position')->get();
		$news = $this->db()->table('news')->select()->get();
		$most_read_articles = $this->articles("a");
		$most_cited_articles = $this->articles("c");
		$most_downloaded_articles = $this->articles("d");

		$this->view('home/index', [
			"meta" => [
				"title" => "Home",
				"description" => "Home page",
				"keywords" => "home, page"
			],
			"journal_data" => $journal_data,
			"news" => $news,
			"featured_article" => $featured_article,
			"editorial_board" => $editorial_board,
			"most_read_articles" => $most_read_articles,
			"most_cited_articles" => $most_cited_articles,
			"most_downloaded_articles" => $most_downloaded_articles,
		]);
	}
}
