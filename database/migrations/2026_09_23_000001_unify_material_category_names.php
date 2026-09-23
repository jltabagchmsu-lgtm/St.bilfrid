<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Unify duplicate/variant material category names into canonical engineering names
        DB::table('materials')->where('category', 'Roofing')->update(['category' => 'Roofing & Metal Sheets']);
        DB::table('materials')->where('category', 'Electrical')->update(['category' => 'Electrical Works']);
        DB::table('materials')->where('category', 'Piping/Plumbing')->update(['category' => 'Plumbing & Sanitary']);
        DB::table('materials')->where('category', 'Structural')->update(['category' => 'Structural & Masonry']);
        DB::table('materials')->where('category', 'Finishing')->update(['category' => 'Architectural & Finishes']);
        DB::table('materials')->where('category', 'General')->update(['category' => 'General Building Materials']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed
    }
};
