<?php

namespace App\Http\Controllers;

use App\Models\Member;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function code2session(Request $request)
    {
        $this->validate($request, ['code' => 'required']);

        try
        {
            $app = Factory::miniProgram(config('wechat.app', []));
            $session = $app->auth->session($request->input('code'));
            if ($session->get('errcode', 0) !== 0) {
                throw new \RuntimeException($session->get('errmsg'));
            }

            $where = $session->only(['openid'])->toArray();
            if (! $member = Member::where($where)->first()) {
                $member = Member::create(
                    $session->only(['openid', 'unionid'])->toArray()
                );
            }

            return success($member->withToken());
        }

        catch (\Exception $exception) {
            Log::warning($exception->getMessage());
        }

        return fail();
    }
}
