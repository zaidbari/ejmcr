<?php

namespace App\Controllers;

use App\Core\Controller;

class ArticlesController extends Controller
{


    public function index()
    {
        // "https://www.ejmanager.com/index_myjournal.php?jid=" . $journal_id . "&mno=" . $_GET['mno']

        $content = file_get_contents("https://www.ejmanager.com/index_myjournal.php?jid=" . $_ENV['JOURNAL_ID'] . "&mno=" . $_GET['mno']);
        $this->dump($content);

        // $this->view('articles/list', [
        //     'meta' => [
        //         'title' => 'Current Issue',
        //         'description' => 'Current Issue',
        //         'keywords' => 'current, issue'
        //     ]
        // ]);
    }
}
