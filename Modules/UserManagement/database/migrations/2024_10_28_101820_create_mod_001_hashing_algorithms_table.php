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
        Schema::create('mod_001_hashing_algorithms', function (Blueprint $table) {
            $table->id('hash_algorithm_id');
            $table->string('algorithm_name', 50)->unique();
            $table->json('parameters')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        DB::table('mod_001_hashing_algorithms')->insert([
            [
                'algorithm_name' => 'Argon2id',
                'parameters' => json_encode([
                    'memory' => 65536,
                    'time' => 4,
                    'threads' => 1
                ]),
                'is_active' => true
            ],
            [
                'algorithm_name' => 'Bcrypt',
                'parameters' => json_encode([
                    'rounds' => 12
                ]),
                'is_active' => true
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mod_001_hashing_algorithms');
    }
};
