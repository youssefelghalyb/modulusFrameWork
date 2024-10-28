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
        Schema::create('mod_001_email_validation', function (Blueprint $table) {
            $table->id('email_validation_status_id');
            $table->string('status_description', 50);
            $table->timestamps();
        });
        DB::table('mod_001_email_validation')->insert([
            ['status_description' => 'Pending Verification'],
            ['status_description' => 'Verified'],
            ['status_description' => 'Verification Failed'],
            ['status_description' => 'Verification Expired']
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mod_001_email_validation');
    }
};
