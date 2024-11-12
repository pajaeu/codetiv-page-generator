<?php

namespace App;

use Core\Http\Page;
use Core\Http\Responses\View;

class Home extends Base
{

    #[Page(path: '/')]
    public function index(): View
    {
        return $this->view('home.index', title: 'Hello World!');
    }
}