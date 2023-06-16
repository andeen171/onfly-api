<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ExpenseRequest",
 *     title="Expense Request",
 *     required={"description", "date", "value"},
 *     @OA\Property(property="description", type="string", maxLength=191, example="Expense description"),
 *     @OA\Property(property="date", type="string", format="date", example="2022-01-01"),
 *     @OA\Property(property="value", type="number", minimum=0, example=10.99)
 * )
 */
class ExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'description' => 'required|string|max:191',
            'date' => 'required|date|before_or_equal:today',
            'value' => 'required|numeric|min:0',
        ];
    }
}
