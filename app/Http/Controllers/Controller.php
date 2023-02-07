<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function getPageSize(Request $request = null)
    {
        $request = $request ?: \Illuminate\Support\Facades\Request::instance();

        return (int) $request->input('size', 10);
    }
}
