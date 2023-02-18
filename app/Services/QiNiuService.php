<?php

namespace App\Services;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class QiNiuService extends Service
{
    /**
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $name
     * @return \stdClass
     */
    public static function upload(UploadedFile $file, string $directory = 'default', ?string $name = null): \stdClass
    {
        $cdn = config('app.cdn_url') . '/';
        $env = app()->environment();
        $name = $name ? trim($name, '/') : md5(microtime(1));
        $directory = trim(join('/', [$env, $directory]), '/');
        $extension = $file->getClientOriginalExtension();
        $filePath = "{$directory}/{$name}." . $extension;
        $uploaded = [
            'file' => $file->getClientOriginalName(), 'hash' => '',
            'domain' => config('services.7ox.storage.host'),
            'error' => '', 'success' => true, 'resource' => $cdn . $filePath,
        ];

        try {
            $um = new UploadManager();
            $token = (new Auth(
                config('services.7ox.storage.access_key'),
                config('services.7ox.storage.secret_key'))
            )->uploadToken(config('services.7ox.storage.bucket'));

            list($result, $error) = $um->putFile($token, $filePath, $file);

            $uploaded['hash'] = $result['hash'] ?? '';
            $uploaded['success'] = is_null($error);
            $uploaded['error'] = $error ? $error->message() : '';

            Log::debug(__METHOD__, array_merge($uploaded, ['message' => 'upload resource successful.']));
        }

            // Catch error.
        catch (\Exception $e) {
            Log::warning(__METHOD__, array_merge($uploaded, ['message' => $e->getMessage()]));

            $uploaded = array_merge($uploaded, [
                'hash' => '', 'error' => $e->getMessage(), 'success' => false
            ]);
        }

        return (object) $uploaded;
    }
}
