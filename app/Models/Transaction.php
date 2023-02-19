<?php

namespace App\Models;

use App\Models\Traits\SerializeDate;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use SerializeDate;

    public static function makeTradeNo(string $prefix = 'S')
    {
        return strtoupper(join('', [$prefix, date('ymdhis'), mt_rand(1000, 9999)]));
    }

    protected $guarded = ['id'];

    protected $casts = ['express' => 'json', 'paid_notifies' => 'json'];

    /**
     * @var string[]
     */
    protected $appends = ['status_text'];

    /**
     * @return string
     */
    public function getStatusTextAttribute()
    {
        // 0 已取消 1 待支付，2 待发货 3 待收货 4 待评价
        return ['已取消', '待支付', '待发货', '待收货', '待评价'][$this->getAttributeValue('status')] ?? '-';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(self::class, 'transaction_id', 'id');
    }

    public function store()
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id', 'id');
    }
}
