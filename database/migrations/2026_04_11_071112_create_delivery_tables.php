<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Customer Profiles
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('avatar')->nullable();
            $table->integer('loyalty_pts')->default(0);
            $table->timestamps();
        });

        // Hotel Profiles
        Schema::create('hotel_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('hotel_name');
            $table->text('description')->nullable();
            $table->string('address');
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('long', 11, 8)->nullable();
            $table->time('opens_at')->default('08:00:00');
            $table->time('closes_at')->default('22:00:00');
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // Delivery Profiles
        Schema::create('delivery_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');

            $table->string('vehicle_type')->nullable(); // Bike, Motorcycle, Car
            $table->string('vehicle_plate')->nullable();

            $table->decimal('current_lat', 10, 8)->nullable();
            $table->decimal('current_long', 11, 8)->nullable();

            $table->boolean('is_online')->default(false);
            $table->boolean('is_available')->default(true);

            $table->timestamps();
        });

        // Admin Profiles
        Schema::create('admin_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('employee_id')->nullable();
            $table->string('department')->default('General');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_profiles');
        Schema::dropIfExists('delivery_profiles');
        Schema::dropIfExists('hotel_profiles');
        Schema::dropIfExists('customer_profiles');
    }
};