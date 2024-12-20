<?php

use App\Models\User;
use App\OrderStatus;
use App\Models\Store;
use App\Models\Location;
use App\Models\ShipmentMethod;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->string('code')->unique();
            $table->decimal('total', 10, 2);
            $table->enum('status', array_column(OrderStatus::cases(), 'value'))->default(OrderStatus::INTRANSIT->value);
            $table->foreignIdFor(ShipmentMethod::class);
            $table->foreignIdFor(Location::class)->nullable();
            $table->foreignIdFor(Store::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
