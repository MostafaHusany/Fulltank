<?php

namespace App\Exports;

use App\Models\PromoCode;
use Maatwebsite\Excel\Concerns\FromCollection;

class PromoCodesExport implements FromCollection
{
    protected $is_used;
    protected $group_id;

    public function __construct($group_id, $is_used = false)
    {
        $this->is_used  = $is_used;
        $this->group_id = $group_id;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $promos = PromoCode::query()
        ->select(['code', 'type'])
        ->where('group_id', $this->group_id);

        if (isset($this->is_used))
        $promos->where('is_used', $this->is_used);

        return $promos->get();
    }
}
