<?php

namespace App\Http\Requests\AlcoholEntry;

use App\Enums\AlcoholType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlcoholEntryRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'alcohol_type' => ['required', 'string', Rule::in(AlcoholType::values())],
            'amount_ml' => ['required', 'integer', 'min:1', 'max:5000'],
            'drink_date' => ['required', 'date', 'before_or_equal:today'],
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'alcohol_type' => 'тип алкоголя',
            'amount_ml' => 'количество в мл',
            'drink_date' => 'дата употребления',
            'comment' => 'комментарий',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'alcohol_type.required' => 'Поле :attribute обязательно для заполнения.',
            'alcohol_type.in' => 'Выбранный :attribute недопустим.',
            'amount_ml.required' => 'Поле :attribute обязательно для заполнения.',
            'amount_ml.integer' => 'Поле :attribute должно быть целым числом.',
            'amount_ml.min' => 'Поле :attribute должно быть не менее :min.',
            'amount_ml.max' => 'Поле :attribute не может превышать :max.',
            'drink_date.required' => 'Поле :attribute обязательно для заполнения.',
            'drink_date.date' => 'Поле :attribute должно быть корректной датой.',
            'drink_date.before_or_equal' => 'Поле :attribute не может быть в будущем.',
            'comment.max' => 'Поле :attribute не может превышать :max символов.',
        ];
    }
}


