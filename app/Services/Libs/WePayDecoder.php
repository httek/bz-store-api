<?php
namespace App\Services\Libs;

use App\Models\Payment;
use WeChatPay\Formatter;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Crypto\AesGcm;
use Laravel\Lumen\Http\Request;
use Illuminate\Support\Facades\Log;

class WePayDecoder
{
    /**
     * @param Request $request
     * @param Payment $payment
     * @return array|null
     */
    public static function decodeFromRequest(Request $request, Payment $payment): ?array
    {
        try {
            $serial = $request->header('Wechatpay-Serial', '');
            $cert = Payment::withTrashed()->where(['platform_rsa_key_serial' => $serial])->first();
            if (! $cert) throw new \Exception('Payment not found, serial=' . $serial);

            $key = $cert->secret ?? '';
            $signature = $request->header('Wechatpay-Signature', '');
            $timestamp = $request->header('Wechatpay-Timestamp', 0);
            $body = $request->getContent();
            $notifyId = $request->input('id', null);
            $nonce = $request->header('Wechatpay-Nonce', '');
            $wePayPublicKey = Rsa::from($cert->platform_rsa_public_key ?? '', Rsa::KEY_TYPE_PUBLIC);
            $timeOffsetStatus = 300 >= abs(Formatter::timestamp() - ((int) $timestamp));
            $verifiedStatus = Rsa::verify( // 构造验签名串
                Formatter::joinedByLineFeed($timestamp, $nonce, $body), $signature, $wePayPublicKey
            );

            if ($timeOffsetStatus && $verifiedStatus) {
                $inBodyArray = (array) json_decode($body, true);
                $aad = $inBodyArray['resource']['associated_data'];
                $nonce = $inBodyArray['resource']['nonce'];
                $ciphertext = $inBodyArray['resource']['ciphertext'];

                return ((array) json_decode(AesGcm::decrypt($ciphertext, $key, $nonce, $aad), true));
            }

            throw new \Exception('验签失败');
        }

        catch (\Exception $exception) {
            $ctx = $request->all() + $request->headers->all();
            Log::error(__METHOD__, [
                'error' => $exception->getMessage(), 'context' => $ctx
            ]);
        }

        return null;
    }
}
