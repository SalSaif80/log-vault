<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class CreateTokenProjectRequest extends FormRequest
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
            'token_name' => 'required|string|max:255',
            'expires_at' => 'nullable|date|after:now',
        ];
    }

    public function messages(): array
    {
        return [
            'token_name.required' => 'يجب أن يكون لديك اسم للتوكن',
            'token_name.string' => 'يجب أن يكون الاسم نص',
            'token_name.max' => 'يجب أن يكون الاسم أقل من 255 حرف',
            'expires_at.nullable' => 'يجب أن يكون لديك تاريخ انتهاء الصلاحية',
            'expires_at.date' => 'يجب أن يكون التاريخ نص',
            'expires_at.after' => 'يجب أن يكون التاريخ بعد الآن',
        ];
    }
}
