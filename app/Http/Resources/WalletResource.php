<?php

namespace App\Http\Resources;

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
            'transactions' => TransactionResource::collection($this->Mytransactions),
            'meta' => [
                'current_page' => $this->Mytransactions->currentPage(),
                'per_page' => $this->Mytransactions->perPage(),
                'total' => $this->Mytransactions->total(),
                'last_page' => $this->Mytransactions->lastPage(),
                'from' => $this->Mytransactions->firstItem(),
                'to' => $this->Mytransactions->lastItem(),
            ],
        ];
    }
}
