<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing 'user' role to 'staff'
        DB::table('users')->where('role', 'user')->update(['role' => 'staff']);

        // Alter table to change enum values
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'staff') DEFAULT 'staff'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'staff') DEFAULT 'user'");

        // Update 'staff' back to 'user' if needed
        DB::table('users')->where('role', 'staff')->update(['role' => 'user']);
    }
};
