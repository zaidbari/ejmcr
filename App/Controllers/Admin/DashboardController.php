<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $this->view('admin/dashboard/index', [
            'meta' => [
                'title' => "Dashboard",
                'description' => "Journal dashboard",
            ],
        ]);
    }
}
