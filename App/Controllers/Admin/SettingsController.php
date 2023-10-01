<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Traits\Logs;

class SettingsController extends Controller
{
    public function index()
    {

        $settings = $this->db()->table('settings')->select()->where('id', 1)->one();
        $this->view('admin/settings/index', [
            'data' => $settings,
            'meta' => [
                'title' => "Settings",
                'description' => "Journal Settings",
            ],
        ]);
    }

    public function showHide()
    {
        try {
            $data = file_get_contents('php://input');
            $data = json_decode($data, true);
            $this->db()->table('settings')->update()->set($data)->where('id', 1)->execute();
        } catch (\Exception $th) {
            var_dump("error", $th->getMessage());
        }
    }


    public function updateSettings()
    {
        $data = $_POST;
        $data['short_description'] = htmlentities($data['short_description'] ?? ' ');

        $this->db()->table('settings')->update()->set($data)->where('id', 1)->execute();
        $this->back('success', 'Settings updated successfully');
    }

    public function uploadCover()
    {
        if(!isset($_FILES['cover']) || $_FILES['cover']['error'] == UPLOAD_ERR_NO_FILE) {
            $this->back('error', 'Error uploading cover. No file choosen!');
        }
       

        $file = $_FILES['cover'];
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
                    // destination to be server root/uploads
                    $file_destination = 'uploads/cover/' . $file_name_new;
                    // check if destination exists if not create it
                    
                    
                    if (move_uploaded_file($file_tmp, $file_destination)) {
                        $this->db()->table('settings')->update()->set('journal_cover', $file_name_new)->where('id', 1)->execute();
                        $this->back('success', 'Cover uploaded successfully');
                    } else {
                        $this->back('error', 'Error uploading cover'. $file_tmp . ' ' . $file_destination);
                    }
                } else {
                    $this->back('error', 'File size too large');
                }
            } else {
                $this->back('error', 'Error uploading cover');
            }
        } else {
            $this->back('error', 'File type not allowed');
        }
    }
}
