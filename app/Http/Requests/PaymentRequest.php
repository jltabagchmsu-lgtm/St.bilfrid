<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
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
        $paymentId = $this->route('payment') 
            ?? $this->route('id') 
            ?? $this->input('payment_id');

        return [
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_stage' => 'required|string|max:255',
            'payment_method' => 'required|string|max:100',
            'status' => 'required|string|in:paid,pending,overdue',
            'invoice_no' => 'nullable|string|max:100',
            'official_receipt_no' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('payments', 'official_receipt_no')->ignore($paymentId),
            ],
            'payer_name' => 'nullable|string|max:255',
            'bank_reference' => 'nullable|string|max:100',
            'received_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:10240',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'official_receipt_no.unique' => 'The Official Receipt Number has already been recorded. Duplicate OR numbers are not allowed.',
        ];
    }
}
