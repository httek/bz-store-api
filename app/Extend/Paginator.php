<?php

namespace App\Extend;

use Illuminate\Pagination\LengthAwarePaginator;

class Paginator extends LengthAwarePaginator
{

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'page'      => $this->currentPage(),
            'data'      => $this->items->toArray(),
            'path'      => $this->path(),
            'more'      => $this->hasMorePages(),
            'size'      => $this->perPage(),
            'total'     => $this->total(),
            'last_page' => $this->lastPage(),
        ];
    }
}

