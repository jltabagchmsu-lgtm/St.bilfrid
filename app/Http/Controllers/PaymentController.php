<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $selectedProjectId = $request->query('project_id');
        $selectedStatus = $request->query('status');
        $selectedMethod = $request->query('payment_method');

        $query = Payment::with('project')->orderBy('payment_date', 'desc');

        if ($selectedProjectId) {
            $query->where('project_id', $selectedProjectId);
        }

        if ($selectedStatus) {
            $query->where('status', $selectedStatus);
        }

        if ($selectedMethod) {
            $query->where('payment_method', $selectedMethod);
        }

        $payments = $query->get();
        $projects = Project::orderBy('title')->get();

        $totalPaid = Payment::where('status', 'paid')->sum('amount');
        $totalPending = Payment::where('status', 'pending')->sum('amount');
        $totalOverdue = Payment::where('status', 'overdue')->sum('amount');
        $totalInvoiced = Payment::sum('amount');
        $collectionRate = $totalInvoiced > 0 ? round(($totalPaid / $totalInvoiced) * 100, 1) : 0;

        return view('payments.index', compact(
            'payments',
            'projects',
            'totalPaid',
            'totalPending',
            'totalOverdue',
            'totalInvoiced',
            'collectionRate',
            'selectedProjectId',
            'selectedStatus',
            'selectedMethod'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_stage' => 'required|string|max:255',
            'payment_method' => 'required|string|max:100',
            'status' => 'required|string|in:paid,pending,overdue',
            'invoice_no' => 'nullable|string|max:100',
            'official_receipt_no' => 'nullable|string|max:100',
            'payer_name' => 'nullable|string|max:255',
            'bank_reference' => 'nullable|string|max:100',
            'received_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:10240',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        if (empty($validated['invoice_no'])) {
            $basePrefix = 'INV-' . date('Ym') . '-';
            do {
                $candidateInvoice = $basePrefix . strtoupper(substr(uniqid(), -4)) . rand(10, 99);
            } while (Payment::where('invoice_no', $candidateInvoice)->exists());
            $validated['invoice_no'] = $candidateInvoice;
        } else {
            $originalInvoice = trim($validated['invoice_no']);
            $candidateInvoice = $originalInvoice;
            $counter = 1;
            while (Payment::where('invoice_no', $candidateInvoice)->exists()) {
                $candidateInvoice = $originalInvoice . '-' . $counter;
                $counter++;
            }
            $validated['invoice_no'] = $candidateInvoice;
        }

        if (empty($validated['official_receipt_no'])) {
            $baseOrPrefix = 'OR-' . date('Ym') . '-';
            do {
                $candidateOr = $baseOrPrefix . strtoupper(substr(uniqid(), -4)) . rand(10, 99);
            } while (Payment::where('official_receipt_no', $candidateOr)->exists());
            $validated['official_receipt_no'] = $candidateOr;
        }

        if (empty($validated['payer_name'])) {
            $validated['payer_name'] = $project->client_name;
        }

        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            $filename = 'rcpt_' . time() . '_' . rand(100, 999) . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/receipts');

            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }

            $file->move($destinationPath, $filename);
            $validated['receipt_file'] = $filename;
        }

        $payment = Payment::create($validated);

        $redirectUrl = $request->input('redirect_to');
        if ($redirectUrl) {
            return redirect($redirectUrl)->with('success', 'Official payment record ' . $payment->effective_or_number . ' logged successfully!');
        }

        return redirect()->back()->with('success', 'Payment record ' . $payment->invoice_no . ' (' . $payment->effective_or_number . ') recorded successfully!');
    }

    public function updateStatus(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string|in:paid,pending,overdue',
        ]);

        $payment->update($validated);

        return redirect()->back()->with('success', 'Payment status for ' . $payment->invoice_no . ' updated to ' . strtoupper($validated['status']) . '.');
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $orNo = $payment->effective_or_number;

        if ($payment->receipt_file) {
            $filePath = public_path('uploads/receipts/' . $payment->receipt_file);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }

        $payment->delete();

        return redirect()->back()->with('success', 'Payment record ' . $orNo . ' was deleted from the financial ledger.');
    }

    // Official Printable Payment Receipt Voucher
    public function printReceipt($id)
    {
        $payment = Payment::with('project')->findOrFail($id);

        return view('payments.receipt_voucher', compact('payment'));
    }
}
