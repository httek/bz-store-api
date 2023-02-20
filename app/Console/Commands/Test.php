<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\UserService;
use Illuminate\Console\Command;

class Test extends Command
{
    protected $signature = 'test';

    public function handle()
    {
        UserService::incrementDroplet(2, 10);
    }
}
