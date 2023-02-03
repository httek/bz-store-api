<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    public function getValueAttribute($value)
    {
        switch (($type = $this->getAttributeValue('type')))
        {
            case 'array':
            case 'object':
                return json_decode($value, $type == 'array');
        }

        return $value;
    }
}
