<?php

namespace App\Pages;

use Core\Http\Page;
use Core\Http\Responses\View;

class Home
{

    #[Page(path: '/')]
    public function index(): View
    {
        return new View('home.index', title: 'Hello World!');
    }
}