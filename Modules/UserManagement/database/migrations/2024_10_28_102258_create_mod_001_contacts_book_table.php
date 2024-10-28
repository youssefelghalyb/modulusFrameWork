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
        Schema::create('mod_001_contacts_book', function (Blueprint $table) {
            $table->id('profile_id');
            $table->foreignId('user_id')
                ->constrained('mod_001_users', 'id')
                ->onDelete('cascade');
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('avatar')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('country', 20)->nullable();
            $table->string('city', 20)->nullable();
            $table->string('location', 20)->nullable();



            $table->json('meta_data')->nullable();
            $table->timestamp('last_updated')->useCurrent();
            $table->softDeletes();
            $table->timestamps();
        });


        Schema::create('mod_001_user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('mod_001_users', 'id')
                ->onDelete('cascade');
            $table->string('device_id')->unique();
            $table->string('device_type');
            $table->string('browser');
            $table->string('browser_version');
            $table->string('operating_system');
            $table->string('ip_address');
            $table->timestamp('last_login_at');
            $table->boolean('is_trusted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mod_001_contacts_book');
        Schema::dropIfExists('mod_001_user_devices');
    }
};
