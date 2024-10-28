<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mod_001_usr_status_types', function (Blueprint $table) {
            $table->id('user_status_id');
            $table->string('status_name', 50)->unique();
            $table->string('status_description', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        DB::table('mod_001_usr_status_types')->insert([
            [
                'status_name' => 'Active',
                'status_description' => 'User account is active and can authenticate',
                'is_active' => true
            ],
            [
                'status_name' => 'Suspended',
                'status_description' => 'User account is temporarily suspended',
                'is_active' => true
            ],
            [
                'status_name' => 'Locked',
                'status_description' => 'User account is locked due to multiple failed attempts',
                'is_active' => true
            ],
            [
                'status_name' => 'Inactive',
                'status_description' => 'User account is inactive',
                'is_active' => true
            ]
        ]);
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mod_001_usr_status_types');
    }
};
