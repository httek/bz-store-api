<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Swiper;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function swipers(Request $request): JsonResponse
    {
        $where = ['position' => $request->input('position', 0)];
        $item = Swiper::where($where)
            ->select(['style', 'items', 'position'])
            ->avaliable()
            ->latest()
            ->first();

        return success($item->items);
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
}
