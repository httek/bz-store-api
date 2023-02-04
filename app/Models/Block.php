<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    /**
     * @var string[]
     */
    protected $visible = ['id', 'name', 'cover', 'meta'];
}
