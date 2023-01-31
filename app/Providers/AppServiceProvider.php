<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*
        |--------------------------------------------------------------------------
        | Register Config Files
        |--------------------------------------------------------------------------
        |
        | Now we will register the "app" configuration file. If the file exists in
        | your configuration directory it will be loaded; otherwise, we'll load
        | the default version. You may register other files below as needed.
        |
        */

        foreach (File::files(base_path('config')) as $file) {
            $this->app->configure($file->getBasename('.php'));
        }
    }
}
