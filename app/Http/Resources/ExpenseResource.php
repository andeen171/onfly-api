<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * @OA\Schema(
     *     schema="ExpenseResource",
     *     title="Expense Resource",
     *     @OA\Property(property="id", type="integer", example="1"),
     *     @OA\Property(property="description", type="string", example="Expense description"),
     *     @OA\Property(property="date", type="string", format="date", example="2022-01-01"),
     *     @OA\Property(property="value", type="number", format="float", example="10.99"),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2022-01-01T12:00:00Z"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2022-01-01T12:00:00Z")
     * )
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'date' => $this->date->format('Y-m-d'),
            'value' => number_format($this->value, 2, '.', ''),
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->format('Y-m-d\TH:i:s\Z'),
            'updated_at' => $this->updated_at->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
