<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'payments';

    /**
     * @var array
     */
    protected $guarded = [];
}
