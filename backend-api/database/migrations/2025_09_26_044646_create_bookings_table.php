<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // কোন ইউজার বুক করেছে
            $table->foreignId('parking_id')->constrained()->onDelete('cascade'); // কোন পার্কিং
            $table->foreignId('slot_id')->constrained()->onDelete('cascade'); // কোন slot
            $table->integer('hours'); // কত ঘণ্টা বুক
            $table->decimal('total_price', 8, 2); // মোট দাম
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('bookings');
    }
};
