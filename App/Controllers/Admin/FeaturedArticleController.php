<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class FeaturedArticleController extends Controller
{
    public function index()
    {
        $article = $this->db()->table('featuredarticle')->select()->where('id', 1)->one();

        $this->view('admin/featuredArticle/index', [
            'data' => $article,
            'meta' => [
                'title' => "Featured article",
                'description' => "Journal featured article",
            ],
        ]);
    }

    public function updateFeatured()
    {
        $data = $_POST;
        $data['featured_text'] = htmlentities($data['featured_text']);

        $this->db()->table('featuredarticle')->update()->set($data)->where('id', 1)->execute();
        $this->back('success', 'Featured article updated successfully');
    }


    public function uploadFeatured()
    {
        if(!isset($_FILES['featured_image']) || $_FILES['featured_image']['error'] == UPLOAD_ERR_NO_FILE) {
            $this->back('error', 'Error uploading image. No file choosen!');
        }
       

        $file = $_FILES['featured_image'];
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
                    $file_destination = 'uploads/featured/' . $file_name_new;
                    // check if destination exists if not create it
                    
                    
                    if (move_uploaded_file($file_tmp, $file_destination)) {
                        $this->db()->table('featuredArticle')->update()->set('featured_image', $file_name_new)->where('id', 1)->execute();

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
