<?php

return [
    'default_commission_rate' => (float) env('MARKETPLACE_COMMISSION_RATE', 10),
    'min_withdrawal_amount' => (float) env('MARKETPLACE_MIN_WITHDRAWAL', 100),
    'default_store_name' => 'Default Store',
    'default_store_slug' => 'default-store',
];
