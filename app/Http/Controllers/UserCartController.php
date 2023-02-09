<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use App\Models\Store;
use App\Models\UserCart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserCartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Goods::with([])
            ->where('user_id', $request->user()->id ?? 0)
            ->leftJoin('user_carts', 'goods.id', '=', 'user_carts.goods_id')
            ->latest('user_carts.id')
            ->select([
                'user_carts.id',
                'goods.id  as goods_id',
                'goods.name',
                'goods.sale_price',
                'goods.covers',
                'goods.store_id',
                'goods.slogan',
                'goods.material',
                'user_carts.total',
            ]);

        if ($status = $request->input('status', -1) >= 0) {
            $query->where('status', $status);
        }

        $items = $query->get();
        $stores = Store::whereIn('id', $items->pluck('store_id'))
            ->select(['id', 'name', 'cover'])
            ->get();

        $result = [
            'count' => $items->count(),
            'items' => []
        ];
        foreach ($items->groupBy('store_id') as $group => $values) {
            $result['items'][] = [
                'store' => $stores->where('id', $group)->first(),
                'items' => $values
            ];
        }

        return success($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $meta = $this->validate($request, ['goods_id' => 'required']);
        $meta['user_id'] = $request->user()->id ?? 0;
        $total = $request->input('total', 1);
        /** @var Model $item */
        if ($item = UserCart::where($meta)->first()) {
            $item->update(['total' => $total]);
        }

        else {
            $item = UserCart::create($meta += ['total' => $total]);
        }

        return success();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        UserCart::destroy($id);

        return success();
    }
}
