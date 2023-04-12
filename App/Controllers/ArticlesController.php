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
        // $content = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/files_html/article_fixed.html');
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/files_html/article.html', $content);
        
        $this->extract_article_data($content);

        // $this->view('articles/list', [
        //     'meta' => [
        //         'title' => 'Current Issue',
        //         'description' => 'Current Issue',
        //         'keywords' => 'current, issue'
        //     ]
        // ]);
    }
}
