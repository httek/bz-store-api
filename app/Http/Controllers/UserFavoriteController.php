<?php

namespace App\Http\Controllers;

use App\Models\UserFavorite;
use Illuminate\Http\Request;

class UserFavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $items = UserFavorite::where('user_favorites.user_id', $request->user()->id ?? 0)
            ->where('goods.status', 2)
            ->leftJoin('goods', 'goods.id', '=', 'user_favorites.goods_id')
            ->select([
                'user_favorites.id',
                'goods.id as goods_id',
                'goods.badge',
                'goods.uuid',
                'goods.slogan',
                'goods.sale_price',
                'goods.origin_price',
                'goods.covers',
                'goods.material',
                'goods.sold',
            ])
            ->paginate($this->getPageSize());

        return success($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, ['goods_id' => 'required|integer']);

        $where = ['user_id' => $request->user()->id ?? 0, 'goods_id' => $request->input('goods_id', 0)];

        if ($item = UserFavorite::where($where)->withTrashed()->first()) {
            $item->restore();
        } else {
            $item = UserFavorite::create($where);
        }

        return success($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, int $id)
    {
        UserFavorite::where('id', $id)
            ->where('user_id', $request->user()->id ?? 0)
            ->delete();

        return success();
    }
}
