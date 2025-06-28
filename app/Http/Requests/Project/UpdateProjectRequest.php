<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:projects,name,' . $this->project->id,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'يجب أن يكون لديك اسم للمشروع',
            'name.string' => 'يجب أن يكون الاسم نص',
            'name.max' => 'يجب أن يكون الاسم أقل من 255 حرف',
            'name.unique' => 'يجب أن يكون الاسم مميز',
            'description.nullable' => 'يجب أن يكون لديك وصف للمشروع',
            'description.string' => 'يجب أن يكون الوصف نص',
            'status.required' => 'يجب أن يكون لديك حالة للمشروع',
            'status.in' => 'يجب أن يكون الحالة إما نشط أو غير نشط',
        ];
    }
}
