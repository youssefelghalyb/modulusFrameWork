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
        Schema::create('mod_001_users', function (Blueprint $table) {
            $table->id();
            $table->string('login_name', 100)->unique();
            $table->string('password_hash');
            $table->string('password_salt', 32);
            $table->foreignId('hash_algorithm_id')
                ->constrained('mod_001_hashing_algorithms', 'hash_algorithm_id')
                ->onUpdate('cascade');
            $table->string('email_address')->unique();
            $table->string('confirmation_token', 100)->nullable();
            $table->timestamp('token_generation_time')->nullable();
            $table->foreignId('email_validation_status_id')
                ->constrained('mod_001_email_validation', 'email_validation_status_id')
                ->onUpdate('cascade');
            $table->string('password_recovery_token', 100)->nullable();
            $table->timestamp('recovery_token_time')->nullable();
            $table->unsignedInteger('no_failed_attempts')->default(0);
            $table->foreignId('user_status_id')
                ->constrained('mod_001_usr_status_types', 'user_status_id')
                ->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mod_001_users');
    }
};
