<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    private $Mytransactions;

    public function __construct($resource, $transactions = null)
    {
        parent::__construct($resource);
        $this->Mytransactions = $transactions;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'balance' => $this->balance,
            'transactions' => TransactionResource::collection($this->Mytransactions)->response()->getData(true),
        ];
    }
}

