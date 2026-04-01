<?php
namespace App\Http\Requests\Book;
use Illuminate\Foundation\Http\FormRequest;
// app/Http/Requests/MassBookActionRequest.php

class MassBookActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // sesuaikan dengan auth/policy kamu
    }

    public function rules(): array
    {
        return [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:books,id',
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Pilih minimal satu buku.',
            'ids.*.exists' => 'Salah satu buku tidak ditemukan.',
        ];
    }
}