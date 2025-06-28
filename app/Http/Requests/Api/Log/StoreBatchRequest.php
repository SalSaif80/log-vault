<?php

namespace App\Http\Requests\Api\Log;

use Illuminate\Foundation\Http\FormRequest;

class StoreBatchRequest extends FormRequest
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
        return  [
            'logs' => 'required|array|min:1',
            'logs.*.external_log_id' => 'required|integer',
            'logs.*.description' => 'required|string',
            'logs.*.causer_type' => 'nullable|string',
            'logs.*.causer_id' => 'nullable|integer',
            'logs.*.subject_type' => 'nullable|string',
            'logs.*.subject_id' => 'nullable|integer',
            'logs.*.project_name' => 'required|string',
            'logs.*.occurred_at' => 'required|string',
            'logs.*.properties' => 'nullable|array',
            'logs.*.event' => 'nullable|string',
            'logs.*.log_name' => 'nullable|string',
            'logs.*.source_system' => 'nullable|string',
        ];
    }
}
