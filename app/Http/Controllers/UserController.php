<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use App\Models\TransactionItem;
use App\Models\UserReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        return success($user);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function goodsReview(Request $request)
    {
        $validated = $this->validate($request, [
            'goods_id' => 'required|integer',
            'star' => 'integer|max:5|min:1',
            'comment' => 'required|string|min:10|max:200',
            'images' => 'array',
            'images.*' => 'url'
        ]);

        $item = Goods::findOrFail($validated['goods_id']);
        $validated['user_id'] = Auth::id() ?? 0;
        $validated['ip'] = $request->ip();
        $review = UserReview::create($validated);
        $where = ['transaction_id' => $request->input('transaction_id', 0), 'goods_id' => $validated['goods_id']];
        TransactionItem::where($where)->update(['review' => 1]);

        return success($review);
    }
}
