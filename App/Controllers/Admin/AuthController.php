<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class AuthController extends Controller
{
   
    public function login()
    {
        if (isset($_SESSION['user'])) $this->redirect('/admin/dashboard');

        if ($this->method('POST')) {
            $user = $this->db()->table('users')->select()->where('email', $this->param('email'))->one() ?? false;
            if (!$user) $this->back('error', 'Email Incorrect');
            
            $isPasswordValid = password_verify($this->param('password'), $user['password']);
            if (!$isPasswordValid) $this->back( 'error', 'Password Incorrect');

            $_SESSION['user'] = $user;
            $this->redirect('/admin/dashboard');

        }

        $this->view('admin/auth/login', [
            'meta' => [
                'title' => "Login",
                'description' => "Admin Login",
            ],
        ]);
    }

    public function logout()
    {
        unset($_SESSION['user']);
        $this->redirect('/auth/login');
    }
}
