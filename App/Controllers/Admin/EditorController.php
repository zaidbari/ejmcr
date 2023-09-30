<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class EditorController extends Controller
{
    public function index()
    {
        $data = $this->db()->table('editors')->select()->get();

        $this->view('admin/editors/index', [
            'data' => $data,
            'meta' => [
                'title' => "Editors",
                'description' => "Journal editors",
            ],
        ]);
    }

    public function create()
    {
        $this->view('admin/editors/create', [
            'meta' => [
                'title' => "Create editor",
                'description' => "Create a new editor",
            ],
        ]);
    }

    public function edit($id)
    {
        $data = $this->db()->table('editors')->select()->where('id', $id)->one();
        $this->view('admin/editors/edit', [
            'data' => $data,
            'meta' => [
                'title' => $data['editor_name'],
                'description' => "Edit editor",
            ],
        ]);
    }

    public function insert()
    {
        $data = $_POST;
        $data['editor_description'] = htmlentities($data['editor_description']);

        $this->db()->table('editors')->insert($data)->execute();
        $this->back('success', 'Editor created successfully');
    }

    public function update($id)
    {
        $data = $_POST;
        $data['editor_description'] = htmlentities($data['editor_description']);

        $this->db()->table('editors')->update()->set($data)->where('id', $id)->execute();
        $this->back('success', 'Editor updated successfully');
    }

    public function delete($id)
    {
        $this->db()->table('editors')->delete($id)->execute();
        $this->back('success', 'Editor deleted successfully');
    }

    public function uploadEditorImage($id)
    {
        if(!isset($_FILES['editor_image']) || $_FILES['editor_image']['error'] == UPLOAD_ERR_NO_FILE) {
            $this->back('error', 'Error uploading image. No file choosen!');
        }
       

        $file = $_FILES['editor_image'];
        $file_name = $file['name'];
        $file_tmp =   $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];

        $file_ext = explode('.', $file_name);
        $file_ext = strtolower(end($file_ext));

        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($file_ext, $allowed)) {
            if ($file_error === 0) {
                if ($file_size <= 2097152) {
                    $file_name_new = uniqid('', true) . '.' . $file_ext;
                    $file_destination = 'uploads/editors/' . $file_name_new;
                    if (move_uploaded_file($file_tmp, $file_destination)) {
                        $this->db()->table('editors')->update()->set('editor_image', $file_name_new)->where('id', $id)->execute();

                        $this->back('success', 'Image uploaded successfully');
                    } else {
                        $this->back('error', 'Error uploading image'. $file_tmp . ' ' . $file_destination);
                    }
                } else {
                    $this->back('error', 'File size too large');
                }
            } else {
                $this->back('error', 'Error uploading image');
            }
        } else {
            $this->back('error', 'File type not allowed');
        }
    }

}
