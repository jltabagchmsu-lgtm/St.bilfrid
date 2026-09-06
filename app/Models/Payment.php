<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'invoice_no',
        'official_receipt_no',
        'payer_name',
        'amount',
        'payment_date',
        'payment_stage',
        'payment_method',
        'financing_type',
        'financing_institution',
        'loan_reference_no',
        'disbursing_entity',
        'drawdown_tranche',
        'payment_first_cleared',
        'construction_clearance_status',
        'bank_reference',
        'received_by',
        'status',
        'notes',
        'receipt_file',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'float',
        'payment_first_cleared' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_file) {
            return null;
        }
        if (str_starts_with($this->receipt_file, 'http') || str_starts_with($this->receipt_file, '/')) {
            return $this->receipt_file;
        }
        return '/uploads/receipts/' . $this->receipt_file;
    }

    public function getEffectiveOrNumberAttribute(): string
    {
        return $this->official_receipt_no ?? ('OR-' . $this->payment_date->format('Ym') . '-' . str_pad($this->id, 4, '0', STR_PAD_LEFT));
    }

    public function getFinancingTypeLabelAttribute(): string
    {
        return match($this->financing_type) {
            'bank_loan' => 'Bank Construction Loan',
            'pagibig_loan' => 'Pag-IBIG (HDMF) Loan',
            'client_equity' => 'Client Direct Equity',
            'cash_progress' => 'Direct Progress Cash',
            default => 'Bank / Financial Loan',
        };
    }

    public function getConstructionClearanceBadgeAttribute(): array
    {
        if ($this->status === 'paid' || $this->payment_first_cleared) {
            return [
                'label' => 'Payment Cleared &bull; Authorized to Construct',
                'color' => '#10b981',
                'bg' => 'rgba(16, 185, 129, 0.12)',
                'border' => 'rgba(16, 185, 129, 0.3)',
                'icon' => '',
                'cleared' => true,
            ];
        }

        if ($this->construction_clearance_status === 'inspection_scheduled') {
            return [
                'label' => 'Bank/Pag-IBIG Inspection Scheduled',
                'color' => '#38bdf8',
                'bg' => 'rgba(56, 189, 248, 0.12)',
                'border' => 'rgba(56, 189, 248, 0.3)',
                'icon' => '',
                'cleared' => false,
            ];
        }

        return [
            'label' => 'Pending Drawdown Release &bull; Hold Site Works',
            'color' => '#ef4444',
            'bg' => 'rgba(239, 68, 68, 0.12)',
            'border' => 'rgba(239, 68, 68, 0.3)',
            'icon' => '',
            'cleared' => false,
        ];
    }
}
