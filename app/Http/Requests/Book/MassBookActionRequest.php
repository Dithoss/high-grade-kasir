<?php
namespace App\Http\Requests\Book;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MassBookActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'   => 'required|array|min:1',
            'ids.*' => [
                'required',
                'string',
                'uuid',
                Rule::exists('books', 'id')->withoutTrashed(), 
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required'  => 'Pilih minimal satu buku.',
            'ids.min'       => 'Pilih minimal satu buku.',
            'ids.*.uuid'    => 'ID buku tidak valid.',
            'ids.*.exists'  => 'Salah satu buku tidak ditemukan.',
        ];
    }
}