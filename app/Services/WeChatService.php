<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use EasyWeChat\MiniProgram\Application;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class WeChatService extends Service
{
    /**
     * @param array $config
     * @return Application|mixed
     */
    public static function miniApplication(array $config = [])
    {
        static $app;
        if (! $app) {
            $config = array_merge(config('wechat.mini_app.default', []), $config);
            $app = new Application($config);
            $cache = new RedisAdapter(
                Redis::connection('wechat')->client()
            );
            $app->rebind('cache', $cache);
        }

        return $app;
    }
}
