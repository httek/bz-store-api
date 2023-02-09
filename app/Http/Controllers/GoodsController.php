<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Goods;
use App\Http\Requests\Goods\Search;
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

        $sf = $search->input('sortFiled', 'sequence');
        if (in_array($sf, ['sequence', 'sold', 'sale_price'])) {
            $sortFiled = $sf;
        }

        if ($name = $search->input('name')) {
            $query->where('name', 'like', "%{$name}%")
                ->orWhereRaw(DB::raw("JSON_OVERLAPS(tags, '\"${name}\"')"));
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
    public function show($id)
    {
        $item = Goods::with(['store'])
            ->where('id', $id)
            ->firstOrFail();


        return success($item);
    }
}