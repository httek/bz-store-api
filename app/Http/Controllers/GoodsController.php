<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Goods;
use App\Http\Requests\Goods\Search;
use App\Models\UserFavorite;
use App\Models\UserHistory;
use App\Models\UserReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Search $search)
    {
        $query = Goods::with(['store']);
        if ($category = $search->input('category')) {
            $query->where('category_id', $category);
        } else if ($parentCategory = $search->input('parent_category')) {
            $query->whereIn('category_id', Category::where('parent_id', $parentCategory)->pluck('id')->toArray());
        }

        $sortFunc = 'latest';
        $sortFiled = 'sequence';
        if ($st = $search->input('sortType', 'latest')) {
            if (in_array($st, ['latest', 'oldest'])) {
                $sortFunc = $st;
            }
        }

        $sf = $search->input('sort', 'sequence');
        if (in_array($sf, ['sequence', 'sold', 'sale_price'])) {
            $sortFiled = $sf;
        }

        if ($name = $search->input('name')) {
            $query->where('name', 'like', "%{$name}%")
                ->orWhereRaw(DB::raw("JSON_OVERLAPS(tags, '\"{$name}\"')"));
        }

        $items = $query->{$sortFunc}($sortFiled)
            ->paginate($this->getPageSize($search));

        return success($items);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $item = Goods::with(['store', 'delivery'])
            ->where('id', $id)
            ->firstOrFail();

        $favorite = ['user_id' => $request->user()->id ?? 0, 'goods_id' => $id];
        $item->user_favorite = UserFavorite::where($favorite)->exists();
        $item->review = [
            'count' => $item->review()->count(),
            'items' => $item->review()->limit(2)->get()
        ];

        UserHistory::firstOrCreate($favorite);

        return success($item);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function review(Request $request, int $id)
    {
        $where = ['goods_id' => $id];
        $items = UserReview::where($where)
            ->with(['user'])
            ->latest('star')
            ->latest('id')
            ->latest('images')
            ->paginate($this->getPageSize());

        return success($items);
    }
}
