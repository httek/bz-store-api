<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use App\Models\Transaction;
use App\Models\UserAddress;
use App\Models\UserCart;
use App\Models\UserTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $where = [];
        if (($status = $request->input('status', -1)) >= 0) {
            $where['status'] = $status;
        }

        $items = Transaction::with('items')
            ->select()
            ->where('root', 0)
            ->where($where)
            ->oldest('status')
            ->paginate($this->getPageSize());

        return success($items);
    }


    public function prePost(Request $request)
    {
        $id = explode(',', $request->input('id', ''));
        // if carts
        $items = [];
        if ($request->has('carts')) {
            $items = Goods::with([])
                ->leftJoin('user_carts', 'user_carts.goods_id', 'goods.id')
                ->leftJoin('stores', 'stores.id', 'goods.store_id')
                ->where('user_carts.user_id', $request->user()->id ?? 0)
                ->whereIn('user_carts.id', $id)
                ->select([
                    'goods.id',
                    'goods.name',
                    'goods.covers',
                    'goods.material',
                    'goods.slogan',
                    'stores.id as store_id',
                    'stores.name as store_name',
                    'stores.cover as store_cover',
                    'goods.sale_price',
                    'user_carts.total',
                    DB::raw("goods.sale_price * user_carts.total as total_amount")
                ])
                ->get()
                ->groupBy('store_name');

        }

        else {
            $total = $request->input('total', 1);
            $items = Goods::with([])
                ->leftJoin('stores', 'stores.id', 'goods.store_id')
                ->whereIn('goods.id', $id)
                ->select([
                    'goods.id',
                    'goods.name',
                    'goods.covers',
                    'goods.material',
                    'goods.slogan',
                    'stores.id as store_id',
                    'stores.name as store_name',
                    'stores.cover as store_cover',
                    'goods.sale_price',
                    DB::raw("{$total} as total"),
                    DB::raw("goods.sale_price * {$total} as total_amount")
                ])
                ->get()
                ->groupBy('store_name');
        }

        $results = ['total' => 0];
        foreach ($items as $storeName => $item) {
            $results['items'][] = [
                'store' => $storeName,
                'items' => $item
            ];

            $results['total'] += $item->sum('total_amount');
        }

        $results['address'] = UserAddress::where('user_id', $request->user()->id ?? 0)
            ->latest('defaults')
            ->get();

        return success($results);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'items' => 'required|array',
            'items.goods_id' => 'required|integer',
            'items.total' => 'required|integer|min:0',
            'address_id' => 'required|integer',
            'mark' => 'nullable|string|max:100',
            'purchaser' => 'nullable',
            'purchaser_phone' => 'nullable|min:11|max:11',
            'carts' => 'nullable|array',
            'carts.*' => 'integer',
        ]);

        //


        // Clear carts
        // UserCart::whereUserId($this->getUserId())->whereIn('id', $request->input('carts', []))
        //    ->delete();

        return success($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = Transaction::with(['items'])
            ->whereUserId($this->getUserId())
            ->whereId($id)
            ->firstOrFail();

        return success($item);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $item = Transaction::with([])
            ->whereUserId($request->user()->id ?? 0)
            ->whereId($id)
            ->firstOrFail();

        $validated = $this->validate($request, [
            'address_id' => 'nullable|integer',
            'status' => 'in:0,4' // 取消、收货
        ]);

        $item->update($validated);

        return success($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $item = Transaction::with([])
            ->whereUserId($request->user()->id ?? 0)
            ->whereId($id)
            ->firstOrFail();

        return $item->delete() ? success() : fail();
    }
}
