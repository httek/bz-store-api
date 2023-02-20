<?php

namespace App\Http\Controllers;

use App\Models\Goods;
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
        $review = UserReview::create($validated);

        return success($review);
    }
}
