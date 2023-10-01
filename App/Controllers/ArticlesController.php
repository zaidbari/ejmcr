<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Traits\Parser;

class ArticlesController extends Controller
{
    use Parser;

    public function index()
    {

        $content = file_get_contents("https://www.ejmanager.com/index_myjournal.php?jid=" . $_ENV['JOURNAL_ID'] . "&mno=" . $_GET['mno']);
        $contents = $this->fixTags(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
        unset($content);

        // $contents = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/files_html/article_fixed.html');
        $data = $this->extract_article_data(str_replace("<br>", "", $contents));
        
        $info_file = file_get_contents("https://www.ejmanager.com/index_myjournal.php?jid=" . $_ENV['JOURNAL_ID'] . "&mno=" . $_GET['mno'] . "&sec=articleInfo");
        $info_contents = $this->fixTags(mb_convert_encoding($info_file, 'HTML-ENTITIES', 'UTF-8'));
        unset($info_file);

        // $info_contents = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/files_html/article_info.html');
        $article_info = $this->extract_article_info($info_contents);

        $this->view('common/articles/index', [
            'meta' => [
                'title' => $data['title'],
                'description' => $data['abstract'],
                'keywords' => $data['keywords']
            ],
            'data' => $data,
            'article_info' => $article_info,
        ]);
    }
}
