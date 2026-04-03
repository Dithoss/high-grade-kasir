<?php

namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBook extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'sypnosis' => 'nullable|string|max:255',
            'writer' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|uuid|exists:categories,id',
            'stock' => 'sometimes|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2840',
            'barcode' => 'nullable|string|unique:books,barcode'

        ];
    }
    public function messages():array 
    {
        return [
            'category_id.exists' => 'Kategori tidak valid.',
            'stock.integer' => 'Stok harus berupa angka.',
            'stock.min' => 'Stok tidak boleh kurang dari 0.',
        ];
    }
}
