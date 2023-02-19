<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;

class Test extends Command
{
    protected $signature = 'test';

    public function handle()
    {
        $item = Transaction::find(33);

        foreach ($item->children as $child) {
            dump($child->id);
        }
    }
}
