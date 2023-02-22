<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\BlockResource;
use App\Models\Category;
use App\Models\Config;
use App\Models\Goods;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $where = [];
        $items  = Tag::where($where)
            ->whereNav(1)
            ->latest('sequence')
            ->get();

        return success($items);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function blockItems(int $id)
    {
        /** @var LengthAwarePaginator $items */

        $items = Goods::with([])
            ->select([
                'goods.id',
                'goods.name',
                'goods.sale_price',
                'goods.origin_price',
                'goods.badge',
                'goods.covers',
                'goods.store_id',
                'goods.slogan',
                'goods.material',
            ])
            ->latest('goods.sequence')
            ->whereRaw(DB::raw("JSON_CONTAINS(tags, '{$id}', '$')"))
            ->paginate($this->getPageSize());

        return success($items);
    }
}
