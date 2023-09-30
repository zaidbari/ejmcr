<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class PagesController extends Controller
{
    public function index()
    {
        $pages = $this->db()->table('pages')->select()->where('isDeleted', 0)->get();
        $this->view('admin/pages/index', [
            'data' => $pages,
            'meta' => [
                'title' => "Pages",
                'description' => "Journal pages",
            ],
        ]);
    }

    public function create()
    {
        $this->view('admin/pages/create', [
            'meta' => [
                'title' => "Create Page",
                'description' => "Create a new page",
            ],
        ]);
    }
    
    public function edit($id)
    {
        $data = $this->db()->table('pages')->select()->where('id', $id)->one();
        $this->view('admin/pages/edit', [
            'data' => $data,
            'meta' => [
                'title' => $data['page_title'],
                'description' => "Edit page",
            ],
        ]);
    }

    public function insert()
    {
        $data = $_POST;
        $data['page_content'] = htmlentities($data['page_content']);
        $data['page_url'] = $this->slugify($data['page_title']);

        $this->db()->table('pages')->insert($data)->execute();
        $this->back('success', 'Page created successfully');
    }

    public function update($id) 
    {
        $data = $_POST;
        $data['page_content'] = htmlentities($data['page_content']);
        $data['page_url'] = $this->slugify($data['page_title']);
        $data['updatedAt'] = date('Y-m-d H:i:s');

        $this->db()->table('pages')->update()->set($data)->where('id', $id)->execute();
        $this->back('success', 'Page updated successfully');
    }


    public function put($id, $action)
    {
        if ($action === 'publish') {
            $this->db()->table('pages')->update()->set(['isPublished' => 1])->where('id', $id)->execute();
            $this->back('success', 'Page updated successfully');
        } 

        if ($action === 'draft') {
            $this->db()->table('pages')->update()->set(['isPublished' => 0])->where('id', $id)->execute();
            $this->back('success', 'Page updated successfully');
        }

        if ($action === 'delete') {
            $this->db()->table('pages')->update()->set(['isDeleted' => 1])->where('id', $id)->execute();
            $this->back('success', 'Page deleted successfully');
        }
       
    }
}
