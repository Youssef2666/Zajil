<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use App\Notifications\DiscountExpiredNotification;

class CleanupExpiredDiscounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discounts:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired discounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredDiscounts = Product::whereNotNull('discount_end')
            ->where('discount_end', '<', now())
            ->get();

        if ($expiredDiscounts->isEmpty()) {
            $this->info('No expired discounts found.');
            return 0;
        }

        foreach ($expiredDiscounts as $product) {
            $storeOwner = $product->store->user;
            if ($storeOwner) {
                $storeOwner->notify(new DiscountExpiredNotification($product));
            }

            $product->update([
                'discount_value' => null,
                'discount_percentage' => null,
                'discount_start' => null,
                'discount_end' => null,
            ]);

            $this->info("Discount expired for product: {$product->name}. Notification sent to store owner.");
        }

        $this->info('All expired discounts have been cleaned up.');
        return 0;
    }
}
