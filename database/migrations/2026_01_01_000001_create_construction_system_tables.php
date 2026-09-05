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
        // 1. Projects Table
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->string('title');
            $table->string('client_name');
            $table->string('location')->nullable();
            $table->enum('project_type', ['Commercial Construction', 'Residential Build', 'Industrial Complex', 'Renovation & Overhaul'])->default('Commercial Construction');
            $table->decimal('land_area_sqm', 10, 2)->comment('Land area in square meters');
            $table->decimal('floor_area_sqm', 10, 2)->comment('Floor area in square meters');
            $table->enum('status', ['pending_approval', 'approved', 'in_progress', 'completed', 'on_hold'])->default('in_progress');
            $table->decimal('contract_budget', 12, 2)->default(0.00);
            $table->decimal('spent_budget', 12, 2)->default(0.00);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('actual_completion_date')->nullable();
            
            // Trade Work Progression (%)
            $table->integer('structural_progress')->default(0);
            $table->integer('electrical_progress')->default(0);
            $table->integer('piping_progress')->default(0);
            $table->integer('overall_progress')->default(0);
            
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Engineers & Architects Personnel Table
        Schema::create('personnel', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->comment('Lead Architect, Site Engineer, Electrical Engineer, Plumbing Engineer, etc.');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('license_no')->nullable();
            $table->string('specialization')->nullable();
            $table->timestamps();
        });

        // 3. Project Personnel Pivot Table
        Schema::create('project_personnel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('personnel_id')->constrained('personnel')->onDelete('cascade');
            $table->string('assignment_role')->nullable()->comment('Project Lead, Lead Architect, Site Manager, etc.');
            $table->timestamps();
        });

        // 4. Materials Master Catalog Table
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('material_code')->unique();
            $table->string('name');
            $table->string('category')->comment('Structural, Electrical, Piping/Plumbing, Finishing, General');
            $table->string('unit')->comment('bags, pcs, tons, meters, kg, sheets');
            $table->decimal('unit_cost', 10, 2)->default(0.00);
            $table->integer('stock_quantity')->default(0);
            $table->timestamps();
        });

        // 5. Project Bill of Materials (BOM) Table
        Schema::create('project_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
            $table->integer('allocated_qty')->default(0);
            $table->integer('used_qty')->default(0);
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 6. Project Tasks & Scheduling Table
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('task_name');
            $table->string('category')->default('General')->comment('Structural, Electrical, Piping, General');
            $table->foreignId('assigned_personnel_id')->nullable()->constrained('personnel')->onDelete('set null');
            $table->date('start_date');
            $table->date('due_date');
            $table->decimal('allocated_budget', 10, 2)->default(0.00);
            $table->decimal('actual_cost', 10, 2)->default(0.00);
            $table->integer('progress')->default(0)->comment('0 to 100%');
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->timestamps();
        });

        // 7. Service Requests & Cost Estimation Table
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone')->nullable();
            $table->string('service_type');
            $table->decimal('land_area_sqm', 10, 2);
            $table->decimal('floor_area_sqm', 10, 2);
            $table->decimal('estimated_cost', 12, 2);
            $table->date('requested_start_date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 8. Financial Payments & Billing Table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('invoice_no')->unique();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('payment_stage')->comment('Downpayment, 30% Milestone, 60% Structural, Final Turnover');
            $table->string('payment_method')->default('Bank Transfer');
            $table->enum('status', ['paid', 'pending', 'overdue'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('project_materials');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('project_personnel');
        Schema::dropIfExists('personnel');
        Schema::dropIfExists('projects');
    }
};
