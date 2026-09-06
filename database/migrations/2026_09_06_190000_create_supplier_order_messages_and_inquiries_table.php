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
        // 1. Interactive Order Messages (Discussions between Admin and Supplier on specific POs)
        if (!Schema::hasTable('supplier_order_messages')) {
            Schema::create('supplier_order_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_order_id')->constrained('supplier_orders')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->enum('sender_role', ['admin', 'supplier'])->default('admin');
                $table->text('message');
                $table->string('attachment_url')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }

        // 2. Material Pre-Order Inquiries & Quotation Requests
        if (!Schema::hasTable('supplier_inquiries')) {
            Schema::create('supplier_inquiries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->foreignId('supplier_material_id')->nullable()->constrained('supplier_materials')->onDelete('set null');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('subject');
                $table->text('message');
                $table->integer('requested_quantity')->nullable();
                $table->enum('status', ['open', 'quoted', 'closed'])->default('open');
                $table->text('supplier_response')->nullable();
                $table->decimal('quoted_unit_price', 12, 2)->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_inquiries');
        Schema::dropIfExists('supplier_order_messages');
    }
};
