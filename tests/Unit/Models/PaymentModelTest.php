<?php

namespace Tests\Unit\Models;

use App\Models\Payment;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * White-Box Test: Payment Casts and Project Relationship
     */
    public function test_payment_casts_and_project_relationship()
    {
        $project = Project::create([
            'project_code' => 'PRJ-PAY-01',
            'title' => 'Greenview Estate',
            'client_name' => 'Michael Chang',
            'location' => 'Cavite',
            'land_area_sqm' => 250.0,
            'floor_area_sqm' => 300.0,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'in_progress',
        ]);

        $payment = Payment::create([
            'project_id' => $project->id,
            'invoice_no' => 'INV-2026-001',
            'official_receipt_no' => 'OR-2026-8888',
            'payer_name' => 'Michael Chang',
            'amount' => 150000.75,
            'payment_date' => '2026-09-01',
            'payment_stage' => 'Mobilization Deposit (15%)',
            'payment_method' => 'bank_transfer',
            'financing_type' => 'bank_loan',
            'payment_first_cleared' => true,
            'status' => 'paid',
        ]);

        $this->assertIsFloat($payment->amount);
        $this->assertEquals(150000.75, $payment->amount);
        $this->assertTrue($payment->payment_first_cleared);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $payment->payment_date);

        $this->assertInstanceOf(Project::class, $payment->project);
        $this->assertEquals($project->id, $payment->project->id);
    }

    /**
     * White-Box Test: getReceiptUrlAttribute Branch Coverage
     * Path 1: receipt_file is null -> null
     * Path 2: receipt_file starts with http -> URL untouched
     * Path 3: receipt_file starts with / -> Path untouched
     * Path 4: receipt_file is filename -> /uploads/receipts/filename
     */
    public function test_receipt_url_attribute_branches()
    {
        $nullReceipt = new Payment(['receipt_file' => null]);
        $this->assertNull($nullReceipt->receipt_url);

        $httpReceipt = new Payment(['receipt_file' => 'https://s3.amazonaws.com/receipts/doc1.pdf']);
        $this->assertEquals('https://s3.amazonaws.com/receipts/doc1.pdf', $httpReceipt->receipt_url);

        $rootReceipt = new Payment(['receipt_file' => '/custom_assets/receipts/doc2.pdf']);
        $this->assertEquals('/custom_assets/receipts/doc2.pdf', $rootReceipt->receipt_url);

        $relReceipt = new Payment(['receipt_file' => 'scanned_receipt_001.jpg']);
        $this->assertEquals('/uploads/receipts/scanned_receipt_001.jpg', $relReceipt->receipt_url);
    }

    /**
     * White-Box Test: getEffectiveOrNumberAttribute Branch Coverage
     * Path 1: official_receipt_no is explicitly populated -> returns official_receipt_no
     * Path 2: official_receipt_no is null -> returns auto-generated formatted fallback OR-YYYYMM-0000
     */
    public function test_effective_or_number_attribute_branches()
    {
        $explicitOr = new Payment([
            'official_receipt_no' => 'OR-OFFICIAL-2026',
            'payment_date' => now(),
        ]);
        $this->assertEquals('OR-OFFICIAL-2026', $explicitOr->effective_or_number);

        $fallbackPayment = new Payment([
            'official_receipt_no' => null,
            'payment_date' => \Carbon\Carbon::parse('2026-09-12'),
        ]);
        $fallbackPayment->id = 42;
        $this->assertEquals('OR-202609-0042', $fallbackPayment->effective_or_number);
    }

    /**
     * White-Box Test: getFinancingTypeLabelAttribute Branch Coverage
     * Path 1: 'bank_loan' -> 'Bank Construction Loan'
     * Path 2: 'pagibig_loan' -> 'Pag-IBIG (HDMF) Loan'
     * Path 3: 'client_equity' -> 'Client Direct Equity'
     * Path 4: 'cash_progress' -> 'Direct Progress Cash'
     * Path 5: default / other -> 'Bank / Financial Loan'
     */
    public function test_financing_type_label_attribute_branches()
    {
        $cases = [
            'bank_loan' => 'Bank Construction Loan',
            'pagibig_loan' => 'Pag-IBIG (HDMF) Loan',
            'client_equity' => 'Client Direct Equity',
            'cash_progress' => 'Direct Progress Cash',
            'in_house_financing' => 'Bank / Financial Loan',
        ];

        foreach ($cases as $type => $expectedLabel) {
            $payment = new Payment(['financing_type' => $type]);
            $this->assertEquals($expectedLabel, $payment->financing_type_label);
        }
    }

    /**
     * White-Box Test: getConstructionClearanceBadgeAttribute Branch Coverage
     * Path 1: status === 'paid' -> cleared=true
     * Path 2: payment_first_cleared === true (with status != paid) -> cleared=true
     * Path 3: construction_clearance_status === 'inspection_scheduled' -> cleared=false, inspection label
     * Path 4: default / hold -> cleared=false, hold label
     */
    public function test_construction_clearance_badge_attribute_branches()
    {
        // Path 1: status is paid
        $paidPayment = new Payment(['status' => 'paid', 'payment_first_cleared' => false]);
        $badge1 = $paidPayment->construction_clearance_badge;
        $this->assertTrue($badge1['cleared']);
        $this->assertStringContainsString('Authorized to Construct', $badge1['label']);
        $this->assertEquals('#10b981', $badge1['color']);

        // Path 2: payment_first_cleared is true
        $clearedPayment = new Payment(['status' => 'pending', 'payment_first_cleared' => true]);
        $badge2 = $clearedPayment->construction_clearance_badge;
        $this->assertTrue($badge2['cleared']);
        $this->assertStringContainsString('Authorized to Construct', $badge2['label']);

        // Path 3: inspection_scheduled
        $inspectionPayment = new Payment([
            'status' => 'pending',
            'payment_first_cleared' => false,
            'construction_clearance_status' => 'inspection_scheduled',
        ]);
        $badge3 = $inspectionPayment->construction_clearance_badge;
        $this->assertFalse($badge3['cleared']);
        $this->assertStringContainsString('Inspection Scheduled', $badge3['label']);
        $this->assertEquals('#38bdf8', $badge3['color']);

        // Path 4: pending drawdown / hold
        $holdPayment = new Payment([
            'status' => 'pending',
            'payment_first_cleared' => false,
            'construction_clearance_status' => 'pending_drawdown',
        ]);
        $badge4 = $holdPayment->construction_clearance_badge;
        $this->assertFalse($badge4['cleared']);
        $this->assertStringContainsString('Hold Site Works', $badge4['label']);
        $this->assertEquals('#ef4444', $badge4['color']);
    }
}
