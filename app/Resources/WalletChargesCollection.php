<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WalletChargesCollection extends ResourceCollection
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
            'data' => $this->collection->transform(function ($charge) {
                return [
                    'id'                    =>     $charge->id,
                    'created_at'            =>     $charge->created_at,
                    'updated_at'            =>     $charge->updated_at,
                    'amount'                =>     $charge->amount,
                    'description'           =>     $charge->description,
                    'payment_type'          =>     $charge->payment_type,
                    'status'                =>     $charge->status,
                    'methods'               =>     $charge->methods,
                    'transaction'           =>     $charge->transaction,
                    'transaction_invoice'   =>     $charge->transaction_invoice,
                    'wallet'                =>     $charge->wallet,
                    'user'                  =>     $charge->user,
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
