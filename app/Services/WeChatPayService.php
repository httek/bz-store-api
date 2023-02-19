<?php

namespace App\Services;

use App\Models\App;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\Service;
use App\Services\Libs\WePayDecoder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use WeChatPay\Builder;
use App\Models\Payment;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Formatter;
use WeChatPay\Util\PemUtil;
use Illuminate\Http\Request;
use WeChatPay\BuilderChainable;
use Illuminate\Support\Facades\Log;

class WeChatPayService extends Service
{
    const
        // 微信支付
        WeTransactionSuccess        = 'TRANSACTION.SUCCESS',
        WeRefundSuccess             = 'REFUND.SUCCESS',
        _ = 0;

    /**
     * @return BuilderChainable
     */
    public static function initWePay(Payment $payment): ?BuilderChainable
    {
        // 商户号
        $merchantId = $payment->merchant_no;
        // 从本地文件中加载「商户API私钥」，「商户API私钥」会用来生成请求的签名
        $merchantPrivateKeyInstance = Rsa::from([$payment->rsa_key, $payment->rsa_passphrase ?? null]);
        // 「商户API证书」的「证书序列号」
        $merchantCertificateSerial = $payment->serial;
        // 从本地文件中加载「微信支付平台证书」，用来验证微信支付应答的签名
        $platformCertificate = $payment->platform_rsa_public_key;
        $platformPublicKeyInstance = Rsa::from($platformCertificate, Rsa::KEY_TYPE_PUBLIC);
        // 从「微信支付平台证书」中获取「证书序列号」
        $platformCertificateSerial = PemUtil::parseCertificateSerialNo($platformCertificate);

        $config = [
            'mchid'         => $merchantId,
            'certs'         => [$platformCertificateSerial => $platformPublicKeyInstance],
            'serial'        => $merchantCertificateSerial,
            'privateKey'    => $merchantPrivateKeyInstance,
        ];

        // 构造一个 APIv3 客户端实例
        return Builder::factory($config);
    }

    /**
     * 小程序支付
     *
     * @param string $openId
     * @param string $orderId
     * @param int $amount
     * @param string $title
     * @return array|null
     */
    public static function jsApiTransaction(
        string $openId,
        string $orderId,
        string $paidTradeNo,
        int $amount = 1,
        string $title = '花卉绿植'
    ): ?array
    {
        $jsApiParams = [
            'mchid' => $partnerId = config('wechat.mini_app.default.mch_id'),
            'appid' => $appId = config('wechat.mini_app.default.app_id'),
            'out_trade_no' => $paidTradeNo,
            'description' => $title,
            'notify_url' => join('/', [
                'https://bz.m.api.icraft.ltd/v1/event/wp', $orderId
            ]),
            'amount' => ['total' => $amount, 'currency' => 'CNY'],
            'payer' => ['openid' => $openId],
            'attach' => $orderId
        ];

        $transaction = null;
        try {
            $payment = Payment::where('merchant_no', $partnerId)->first();
            $wePay = self::initWePay($payment);
            $response = $wePay->chain($api = 'v3/pay/transactions/jsapi')->post(['json' => $jsApiParams]);
            if (200 != $response->getStatusCode()) {
                throw new \LogicException(sprintf("call WECHAT API:%s fail, HTTP code: %d", $api, $response->getStatusCode()));
            }

            $prePayID = json_decode($response->getBody()->getContents())->prepay_id ?? null;
            if (! $prePayID) {
                throw new \LogicException(sprintf("call WECHAT API:%s fail, non prepay_id", $api));
            }

            $payParams = [
                'appId' => $appId,
                'partnerId' => $partnerId,
                'prepayId' => $prePayID,
                'package' => "prepay_id={$prePayID}",
                'nonceStr' => $nonce = Formatter::nonce(),
                'timeStamp' => $timestamp = (string) Formatter::timestamp(),
            ];

            $merchantPrivateKeyInstance = Rsa::from([$payment->rsa_key, $payment->rsa_passphrase ?? null]);
            $transaction = array_merge($payParams, [
                'paidTradeNo' => $paidTradeNo,
                'signType' => 'RSA',
                'sign' => Rsa::sign(Formatter::joinedByLineFeed($appId, $timestamp, $nonce, $payParams['package']), $merchantPrivateKeyInstance),
            ]);
        }

        catch (\Exception $e) {
            Log::error(__METHOD__, ['error' => $e->getMessage()]);
        }

        return $transaction;
    }

    /**
     * 支付通知
     *
     * @param Request $request
     * @return bool
     */
    public static function notify(Request $request, string $id): bool
    {
        try {
            $useTransaction = false;
            $partnerId = config('wechat.mini_app.default.mch_id');
            switch ($event = $request->input('event_type', null)) {
                case self::WeTransactionSuccess:
                    $payment = Payment::where('merchant_no', $partnerId)->first();
                    if (!$payload = WePayDecoder::decodeFromRequest($request, $payment)) break;

                    $order = Transaction::find($id);
                    if (! $order) {
                        Log::warning(__METHOD__ . " => Order not found", compact('payload'));

                        return false;
                    }

                    if ($order->status != 1) {
                        Log::warning(__METHOD__ . " => Order paid.", compact('payload', 'order'));

                        return true;
                    }

                    $paidAt = Carbon::now()->toDateTimeString();
                    DB::beginTransaction();
                    $useTransaction = true;

                    $up = [
                        'paid_amount' => $payload['amount']['total'],
                        'paid_at' => $paidAt,
                        'status' => 2,
                        'paid_notifies' => $payload
                    ];

                    $order->update($up);
                    foreach ($order->children() as $child) {
                        $up['paid_amount'] = $child->amount;
                        $up['paid_trade_no'] = $order->paid_trade_no;
                        $child->update($up);
                    }

                    DB::commit();
                    return true;
                case self::WeRefundSuccess:

            }

            return false;
        }

        catch (\Exception $exception)
        {
            if ($useTransaction) DB::rollBack();

            Log::warning(__METHOD__, ['error' => $exception->getMessage()]);

            return false;
        }
    }
}

