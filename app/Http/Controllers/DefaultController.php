<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Category;
use App\Models\Config;
use App\Models\Swiper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function swipers(Request $request): JsonResponse
    {
        $swipers = Config::where('key', 'swiper.mapp.home')->first();

        return success($swipers->value ?? []);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function categories(Request $request)
    {
        $items = Category::with('children')
            ->whereStatus(1)
            ->tree()
            ->latest('sequence')
            ->get();

        return success($items);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function blocks(Request $request)
    {
        $where = ['position' => $request->input('position', 0), 'status' => 1];
        $items  = Block::where($where)
            ->whereRaw(DB::raw("NOW() between visible_begin and visible_ending"))
            ->latest('sequence')
            ->get();

        return success($items);
    }
}
