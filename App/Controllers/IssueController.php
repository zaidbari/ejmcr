<?php

namespace App\Controllers;
use App\Core\Controller;
use App\Traits\Parser;

class IssueController extends Controller
{


    use Parser;


    public function articles($query)
    {
        $content = file_get_contents("https://www.ejmanager.com/index_myjournal.php?jid=" . $_ENV["JOURNAL_ID"]. $query);
        return  $this->extract_article_list($content);
    }

    public function current_issue()
    {

        $this->view('issues/index', [
            "meta" => [
                "title" => "Current issue",
                "description" => "Current issue of " . $_ENV["JOURNAL_TITLE"],
            ],
            "data" => $this->articles("&sec=cissue")
        ]);
    }

    public function latest_issue()
    {
        $this->view('issues/current', [
            'meta' => [
            'title' => 'Current issue',
            'description' => 'Current issue of the journal',
            ],
        ]);
    }

    public function index()
    {
        $data = $this->articles("&iid=" . $_GET['iid'] . "&target=local");
        $description = "Issue " . $data['issue_details']['issue'] . " of " . $_ENV["JOURNAL_TITLE"] . " published in " . $data['issue_details']['year'];
        
        $this->view('issues/index', [
            'meta' => [
                'title' => 'Volume ' . $data['issue_details']['volume'] .' | Issue ' .  $data['issue_details']['issue'],
                'description' => $description,
            ],
            "data" => $data
        ]);
    }

    public function archives()
    {
        // $file_contents = file_get_contents($_SERVER['DOCUMENT_ROOT']. '/files_html/archives.html');
        $file_contents = file_get_contents("https://www.ejmanager.com/index_myjournal.php?jid=".$_ENV['JOURNAL_ID']."&sec=archive");
        $content = str_replace(["&amp;&amp;", "&amp;"], "&", str_replace(["&nbsp;", "&raquo;"], "", $file_contents));
        file_put_contents($_SERVER['DOCUMENT_ROOT']. "/files_html/archives.html", $file_contents);
       
        $class = "class='alert-primary alert mt-3'";
        // $content = file_get_contents($_SERVER['DOCUMENT_ROOT']. '/files_html/archives_fixed.html');
        $content = str_replace('style="padding:19px;"', $class, $content);
        $content = str_replace('?iid=', "/issue?iid=", $content);
        $content = str_replace('&jid=' . $_ENV['JOURNAL_ID'] . "&lng=", "", $content);
        $content = str_replace("pp.", "Page: ", $content);
        $content = str_replace("<a href", "<hr /><a href", $content);
        
        $this->view('issues/archive', [
            'meta' => [
            'title' => 'Archives',
            'description' => 'Archives of the journal'. $_ENV['JOURNAL_TITLE'],
            ],
            'content' => $content
        ]);
    }

}
