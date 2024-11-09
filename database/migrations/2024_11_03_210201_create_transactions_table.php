<?php

use App\Models\User;
use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['credit', 'debit','trasfer', 'withdraw', 'refund']);
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            $table->foreignIdFor(Order::class)->nullable()->constrained()->onDelete('cascade');
            $table->foreignIdFor(User::class, 'receiver_user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignIdFor(PaymentMethod::class, 'payment_method_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
