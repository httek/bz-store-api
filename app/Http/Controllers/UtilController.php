<?php

namespace App\Http\Controllers;


use App\Services\QiNiuService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class UtilController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request)
    {
        $file = $request->file('file');
        return success(
            QiNiuService::upload($file, 'n4')
        );
    }
}
