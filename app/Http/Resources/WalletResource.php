<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    private $transactions;

    public function __construct($resource, $transactions = null)
    {
        parent::__construct($resource);
        $this->transactions = $transactions;
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
                'transactions' => $this->transactions->items(),
                'meta' => [
                    'current_page' => $this->transactions->currentPage(),
                    'from' => $this->transactions->firstItem(),
                    'last_page' => $this->transactions->lastPage(),
                    'links' => $this->transactions->linkCollection()->toArray(),
                    'path' => $this->transactions->path(),
                    'per_page' => $this->transactions->perPage(),
                    'to' => $this->transactions->lastItem(),
                    'total' => $this->transactions->total(),
                ],
        ];
    }
}

