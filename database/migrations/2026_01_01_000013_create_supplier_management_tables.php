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
        // 1. Suppliers Master Table
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('category'); // 'Windows & Doors', 'Roofing', 'Structural & Masonry'
            $table->string('contact_person')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 2. Add supplier_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('role')->constrained('suppliers')->onDelete('set null');
        });

        // 3. Supplier Materials Catalog
        Schema::create('supplier_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('material_code')->unique();
            $table->string('name');
            $table->string('category'); // 'Windows & Doors', 'Roofing', 'Structural & Masonry'
            $table->string('subcategory')->nullable(); // 'Windows', 'Doors', 'Roofing sheets', etc.
            $table->text('description')->nullable();
            $table->text('specifications')->nullable();
            $table->string('unit'); // 'pcs', 'sets', 'ln.m.', 'boxes', 'bags', 'cu.m'
            $table->integer('available_quantity')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->integer('min_order_qty')->default(1);
            $table->enum('availability_status', ['available', 'low_stock', 'out_of_stock', 'unavailable'])->default('available');
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Supplier Purchase Orders (placed by Admin / Construction Firm)
        Schema::create('supplier_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique(); // e.g. ORD-2026-0001
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('ordered_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->string('delivery_location');
            $table->date('requested_delivery_date');
            $table->date('actual_delivery_date')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'ready_for_delivery',
                'delivered',
                'completed',
                'cancelled'
            ])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Supplier Purchase Order Line Items
        Schema::create('supplier_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_order_id')->constrained('supplier_orders')->onDelete('cascade');
            $table->foreignId('supplier_material_id')->nullable()->constrained('supplier_materials')->onDelete('set null');
            $table->string('material_name');
            $table->integer('quantity');
            $table->string('unit');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });

        // 6. Supplier Order Status Audit Logs
        Schema::create('supplier_order_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_order_id')->constrained('supplier_orders')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('from_status');
            $table->string('to_status');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // 7. Supplier Notifications
        Schema::create('supplier_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('type'); // 'order_created', 'order_status_updated', 'low_stock_alert'
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->string('link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_notifications');
        Schema::dropIfExists('supplier_order_logs');
        Schema::dropIfExists('supplier_order_items');
        Schema::dropIfExists('supplier_orders');
        Schema::dropIfExists('supplier_materials');

        if (Schema::hasColumn('users', 'supplier_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            });
        }

        Schema::dropIfExists('suppliers');
    }
};
