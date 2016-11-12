<?php

namespace App\Http\Controllers;

class AdminerController extends Controller
{
    public function adminer()
    {
        require_once base_path().'/public/adminer_object.php';
        require_once base_path().'/public/adminer.php';
    }
}
