<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\Member;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class TokenService extends Service
{
    /**
     * @param Admin $admin
     * @param int $ttl
     * @return string|null
     */
    public static function issue(Member $user, int $ttl = 0): ?string
    {
        try {
            $ttl = $ttl ?: config('auth.jwt.ttl', 1);
            $key = config('auth.jwt.key', '');
            $algo = config('auth.jwt.algo', 'HS256');
            $payloads = [
                'iss' => config('app.url'),
                'aud' => config('app.url'),
                'iat' => time(),
                'nbf' => time(),
                'jti' => $user->getKey(),
                'exp' => Carbon::now()->addDays($ttl)->getTimestamp()
            ];

            return JWT::encode($payloads, $key, $algo, $user->getKey());
        }

        catch (\Throwable $throwable) {
            Log::warning($throwable->getMessage(), [
                'trace' => __METHOD__, 'id' => $user->getKey()
            ]);
        }

        return null;
    }

    /**
     * @param string $jwt
     * @param string|null $key
     * @return string|null
     */
    public static function getPayloads(string $jwt, string $key = null): ?string
    {
        try {
            if (self::invalid($jwt)) {
                return null;
            }

            $keys = config('auth.jwt.key', '');
            $algo = config('auth.jwt.algo', 'HS256');
            $decoded = JWT::decode($jwt, new Key($keys, $algo));
            $payloads = json_decode(json_encode($decoded), true);

            return $key ? Arr::get($payloads, $key) : $payloads;
        }

        catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * @param string $jwt
     * @return string|null
     */
    public static function invalid(string $jwt): ?string
    {
        try {
            $key = config('auth.jwt.key', '');
            $algo = config('auth.jwt.algo', 'HS256');
            JWT::decode($jwt, new Key($key, $algo));

            return null;
        }

        catch (\Throwable $throwable) {
            return $throwable->getMessage();
        }
    }
}
