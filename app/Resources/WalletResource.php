<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'pendding_balance' => $this->pendding_balance,
            'valide_balance'   => $this->valide_balance,
        ];
    }
}
