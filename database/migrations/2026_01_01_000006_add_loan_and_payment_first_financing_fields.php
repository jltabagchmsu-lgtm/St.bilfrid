<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add loan financing breakdown & payment-first policy to projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->string('financing_type')->default('bank_loan')->after('contract_budget')->comment('bank_loan, pagibig_loan, cash_equity, combined');
            $table->string('financing_institution')->nullable()->after('financing_type')->comment('Bank Name (BDO, BPI, Metrobank, etc.) or Pag-IBIG Fund');
            $table->string('loan_account_no')->nullable()->after('financing_institution')->comment('Loan NOA / LOG / Account Reference Number');
            $table->decimal('approved_loan_amount', 12, 2)->default(0.00)->after('loan_account_no')->comment('Approved Loan Portion from Bank/Pag-IBIG');
            $table->decimal('client_equity_amount', 12, 2)->default(0.00)->after('approved_loan_amount')->comment('Client Direct Equity Portion');
            $table->boolean('payment_first_policy')->default(true)->after('client_equity_amount')->comment('Payment First Before Construct Rule');
        });

        // 2. Add loan recording, drawdown tranches & payment-first construction clearance to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->string('financing_type')->default('bank_loan')->after('payment_method')->comment('bank_loan, pagibig_loan, client_equity, cash_progress');
            $table->string('financing_institution')->nullable()->after('financing_type')->comment('Disbursing Bank or Pag-IBIG Fund');
            $table->string('loan_reference_no')->nullable()->after('financing_institution')->comment('Bank/Pag-IBIG LOG, NOA or Cheque No.');
            $table->string('disbursing_entity')->nullable()->after('loan_reference_no')->comment('Bank Loan Disbursement Unit / HDMF / Client');
            $table->string('drawdown_tranche')->nullable()->after('disbursing_entity')->comment('Tranche 1, Tranche 2, Equity Downpayment, etc.');
            $table->boolean('payment_first_cleared')->default(true)->after('drawdown_tranche')->comment('Payment cleared before construction starts');
            $table->string('construction_clearance_status')->default('cleared_to_construct')->after('payment_first_cleared')->comment('cleared_to_construct, pending_bank_release, inspection_scheduled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'financing_type',
                'financing_institution',
                'loan_reference_no',
                'disbursing_entity',
                'drawdown_tranche',
                'payment_first_cleared',
                'construction_clearance_status',
            ]);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'financing_type',
                'financing_institution',
                'loan_account_no',
                'approved_loan_amount',
                'client_equity_amount',
                'payment_first_policy',
            ]);
        });
    }
};
