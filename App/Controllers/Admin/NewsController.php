<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class NewsController extends Controller
{
	public function index()
	{
		$article = $this->db()->table('news')->select()->get();

		$this->view('admin/news/index', [
			'data' => $article,
			'meta' => [
				'title' => "News & Events",
				'description' => "Journal News & Events",
			],
		]);
	}

	public function create()
	{
		$this->view('admin/news/create', [
			'meta' => [
				'title' => "Create news",
				'description' => "Create a news",
			],
		]);
	}

	public function edit($id)
	{
		$data = $this->db()->table('news')->select()->where('id', $id)->one();
		$this->view('admin/news/edit', [
			'data' => $data,
			'meta' => [
				'title' => "Edit news",
				'description' => "Edit news",
			],
		]);
	}

	public function insert()
	{
		$data = $_POST;
		$data['news_description'] = htmlentities($data['news_description']);
		$data['news_image'] = $this->uploadImage();

		$this->db()->table('news')->insert($data)->execute();
		$this->back('success', 'News created successfully');
	}

	public function update($id)
	{
		$data = $_POST;
		$data['news_description'] = htmlentities($data['news_description']);
		if (isset($_FILES['news_image']) && $_FILES['news_image']['error'] == UPLOAD_ERR_OK) {
			$data['news_image'] = $this->uploadImage();
		}

		$this->db()->table('news')->update()->set($data)->where('id', $id)->execute();
		$this->back('success', 'news updated successfully');
	}

	public function delete($id)
	{
		$this->db()->table('news')->delete()->where('id', $id)->execute();
		$this->back('success', 'news deleted successfully');
	}

	public function uploadImage()
	{
		if (!isset($_FILES['news_image']) || $_FILES['news_image']['error'] == UPLOAD_ERR_NO_FILE) {
			return "";
		}


		$file = $_FILES['news_image'];
		$file_name = $file['name'];
		$file_tmp = $file['tmp_name'];
		$file_size = $file['size'];
		$file_error = $file['error'];

		$file_ext = explode('.', $file_name);
		$file_ext = strtolower(end($file_ext));

		$allowed = ['jpg', 'jpeg', 'png'];

		if (!in_array($file_ext, $allowed)) {
			$this->back('error', 'File type not allowed');
		}

		if ($file_error !== 0) {
			$this->back('error', 'Error uploading image');
		}

		if ($file_size > 2097152) {
			$this->back('error', 'File size too large');
		}

		$file_name_new = uniqid('', true) . '.' . $file_ext;
		$file_destination = 'uploads/news/' . $file_name_new;

		if (move_uploaded_file($file_tmp, $file_destination)) {
			return $file_name_new;
		} else {
			$this->back('error', 'Error uploading image');
		}

		// if (in_array($file_ext, $allowed)) {
		// 	if ($file_error === 0) {
		// 		if ($file_size <= 2097152) {
		// 			$file_name_new = uniqid('', true) . '.' . $file_ext;
		// 			$file_destination = 'uploads/news/' . $file_name_new;
		// 			if (move_uploaded_file($file_tmp, $file_destination)) {
		// 				$this->db()->table('news')->update()->set('news_image', $file_name_new)->where('id', $id)->execute();

		// 				$this->back('success', 'Image uploaded successfully');
		// 			} else {
		// 				$this->back('error', 'Error uploading image' . $file_tmp . ' ' . $file_destination);
		// 			}
		// 		} else {
		// 			$this->back('error', 'File size too large');
		// 		}
		// 	} else {
		// 		$this->back('error', 'Error uploading image');
		// 	}
		// } else {
		// 	$this->back('error', 'File type not allowed');
		// }
	}
}
