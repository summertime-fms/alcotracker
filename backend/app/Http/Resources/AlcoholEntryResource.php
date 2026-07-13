<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlcoholEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'alcohol_type' => $this->alcohol_type->value,
            'alcohol_type_label' => $this->alcohol_type->label(),
            'amount_ml' => $this->amount_ml,
            'drink_date' => $this->drink_date->format('Y-m-d'),
            'comment' => $this->comment,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}


