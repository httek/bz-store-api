<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WeChatPayService;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * 支付通知
     *
     * @param Request $request
     * @return string[]
     */
    public function notify(Request $request, string $id): array
    {
        $context = $request->all() + $request->headers->all() + compact('id');
        Log::info(__METHOD__, $context);

        $handled = WeChatPayService::notify($request, $id);

        return $handled ? ['code' => 'SUCCESS', '成功'] : ['code' => 'FAIL', '失败'];
    }
}
