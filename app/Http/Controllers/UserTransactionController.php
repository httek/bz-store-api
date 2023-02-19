<?php

namespace App\Http\Controllers;

use App\Services\WeChatPayService;
use Carbon\Carbon;
use App\Models\Goods;
use App\Models\UserCart;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\UserAddress;
use App\Models\UserTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $where = ['user_id' => Auth::id() ?? 0];
        if (($status = $request->input('status', -1)) >= 0) {
            $where['status'] = $status;
        }

        $items = Transaction::with(['items', 'store'])
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
            'items.*.goods_id' => 'required|integer',
            'items.*.total' => 'required|integer|min:0',
            'address_id' => 'required|integer',
            'mark' => 'nullable|string|max:100',
            'purchaser' => 'nullable',
            'purchaser_phone' => 'nullable|min:11|max:11',
            'carts' => 'nullable|array',
            'carts.*' => 'integer',
            'stores' => 'nullable|array'
        ]);

        try {
            DB::beginTransaction();
            // 需生成根订单
            $items = collect($request->input('items', []));
            $goods = Goods::whereIn('id', $items->pluck('goods_id'))
                ->get();
            $needRootOrder = count($request->input('stores', [])) > 1;
            $root = null;
            $totalInput = $items->pluck('total', 'goods_id')->toArray();
            if ($needRootOrder) {
                $rootAmount = 0;
                foreach ($goods as $good) {
                    $rootAmount += ($good->sale_price ?? 0) * ($totalInput[$good->id] ?? 1);
                }

                $root = Transaction::create([
                    'trade_no' => Transaction::makeTradeNo('R'),
                    'user_id' => Auth::id() ?? 0,
                    'root' => 1,
                    'store_id' => 0,
                    'address_id' => $request->input('address_id', 0),
                    'purchaser' => $request->input('purchaser'),
                    'purchaser_phone' => $request->input('purchaser_phone'),
                    'status' => 1,
                    'amount' => $rootAmount,
                    'mark' => $request->input('mark'),
                ]);
            }

            $orders = [];
            foreach ($goods->groupBy('store_id') as $store => $items) {
                $amount = 0;
                foreach ($items as $item) {
                    $amount += ($item->sale_price ?? 0) * ($totalInput[$item->id] ?? 1);
                }

                $row = [
                    'store_id' => $store,
                    'user_id' => Auth::id() ?? 0,
                    'parent_id' => $root->id ?? null,
                    'trade_no' => Transaction::makeTradeNo(),
                    'address_id' => $request->input('address_id', 0),
                    'purchaser' => $request->input('purchaser'),
                    'purchaser_phone' => $request->input('purchaser_phone'),
                    'status' => 1,
                    'amount' => $amount,
                    'mark' => $request->input('mark'),
                ];

                $orders[] = $order = Transaction::create($row);
                foreach ($items as $item) {
                    $order->items()->create([
                        'transaction_id' => $item->id,
                        'store_id' => $item->store_id ?? 0,
                        'goods_id' => $item->id ?? 0,
                        'sale_price' => $item->sale_price ?? 0,
                        'total' => $totalInput[$item->id] ?? 1,
                        'goods' => $item,
                        'created_at' => Carbon::now()->toDateTimeString()
                    ]);
                }
            }

            // Clear carts
            $carts = $request->input('carts', []);
            $carts && UserCart::whereUserId($this->getUserId())->whereIn('id', $carts)
                ->delete();

            DB::commit();

            return success([
                'root' => $root->id ?? 0,
                'orders' => collect($orders)->pluck('id'),
            ]);
        }

        catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Create order fail:', [$exception->getLine() => $exception->getMessage()]);
        }

        return fail('下单失败，请重试');
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

    /**
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function toPay(Request $request, int $id)
    {
        $user = $request->user();
        $item = Transaction::with([])
            ->whereUserId($user->id ?? 0)
            ->whereId($id)
            ->firstOrFail();

        if ($item->status != 1) {
            return fail('该订单无法支付');
        }

        $item->update(['paid_trade_no' => $paidTradeNo = Transaction::makeTradeNo('P')]);
        $amount = env('PAY_DEBUG') ? 1 : $item->amount ?? 0;
        $transaction = WeChatPayService::jsApiTransaction($user->openid, $item->id, $paidTradeNo, $amount);
        if (! $transaction) {
            return fail('支付失败，请重试');
        }

        $results = [
            'orders' => $item,
            'transaction' => $transaction
        ];

        return success($results);
    }
}
