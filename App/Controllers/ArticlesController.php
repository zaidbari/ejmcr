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

        // $content = str_replace("<br>", "", file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/files_html/article_fixed.html'));

        $this->extract_article_data(str_replace("<br>", "", $contents));

        // $this->view('articles/list', [
        //     'meta' => [
        //         'title' => 'Current Issue',
        //         'description' => 'Current Issue',
        //         'keywords' => 'current, issue'
        //     ]
        // ]);
    }
}
