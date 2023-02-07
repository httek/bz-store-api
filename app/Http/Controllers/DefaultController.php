<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Category;
use App\Models\Config;
use Illuminate\Database\Eloquent\Builder;
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
        $swipers = Config::where('key', 'swiper.home')->first();

        return success($swipers->value ?? []);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function navs(Request $request): JsonResponse
    {
        $navs = Config::where('key', 'nav.home')->first();

        return success($navs->value ?? []);
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
     * @param int $id
     * @return JsonResponse
     */
    public function childCategories(int $id)
    {
        $item = Category::with('children')
            ->whereStatus(1)
            ->findOrFail($id);

        return success($item);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function blocks(Request $request)
    {
        $where = ['position' => $request->input('position', 0), 'status' => 1];
        $items  = Block::where($where)
            ->where(function (Builder $query) {
                $query->whereNull('deadline_at')
                    ->orWhereRaw(DB::raw("deadline_at > NOW()"));
            })
            ->latest('sequence')
            ->get();

        return success($items);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function blockItems(int $id): JsonResponse
    {
        $items = [];

        return success($items);
    }
}
