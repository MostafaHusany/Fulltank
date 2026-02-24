<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        $paginationLinks = [
            'first' => [
                'url' => $this->url(1),
                'label' => 'First',
                'active' => $this->currentPage() === 1,
            ],
            'last' => [
                'url' => $this->url($this->lastPage()),
                'label' => 'Last',
                'active' => $this->currentPage() === $this->lastPage(),
            ],
            'prev' => [
                'url' => $this->previousPageUrl(),
                'label' => 'Previous',
                'active' => false,
            ],
            'next' => [
                'url' => $this->nextPageUrl(),
                'label' => 'Next',
                'active' => false,
            ],
        ];

        return [
            'success' => true,
            'data' => $this->collection->transform(function ($transaction) {
                return [
                    'id'                =>     $transaction->id,
                    'created_at'        =>     $transaction->created_at,
                    'updated_at'        =>     $transaction->updated_at,
                    'transaction_num'   =>     $transaction->transaction_num,
                    'total_price'       =>     $transaction->total_price,
                    'payment_type'      =>     $transaction->payment_type,
                    'status'            =>     $transaction->status,
                    'reviewe_status'    =>     $transaction->reviewe_status,
                    'status_done_date'  =>     $transaction->status_done_date,
        
                    'workshop'          =>     $transaction->workshop,
                    'client'            =>     $transaction->client,
                    'order'             =>     $transaction->order,
                ];
            }),
            'meta' => [
                'total' => $this->total(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'links' => $paginationLinks,
            ],
        ];
    }
}
