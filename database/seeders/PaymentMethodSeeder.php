<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'ادفع لي',
                'description' => 'من مصرف التجارة والتنمية',
                'image' => 'https://mir-s3-cdn-cf.behance.net/projects/404/43dff882406425.Y3JvcCwxNDAwLDEwOTUsMCw0Mjk.jpg',
            ],
            [
                'name' => 'سداد',
                'description' => 'من المدار الجديد',
                'image' => 'https://play-lh.googleusercontent.com/s53UMzbOEhDTHCnT9Jn5qMMG4BmDLMNKwDsz6S1ob2G_sA3PpAJbDo4hErVwJJmw-A',
            ],
            [
                'name' => 'بطاقة',
                'description' => 'البطاقات المصرفية المحلية',
                'image' => 'https://pngimg.com/uploads/credit_card/credit_card_PNG144.png',
            ],
        ];
    }
}
