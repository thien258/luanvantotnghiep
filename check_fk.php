<?php
// Script tạm để kiểm tra FK của bảng purchase_orders
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$fks = DB::select("
    SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'purchase_orders'
      AND REFERENCED_TABLE_NAME IS NOT NULL
");

echo "=== Foreign Keys của purchase_orders ===\n";
foreach ($fks as $fk) {
    echo $fk->COLUMN_NAME . ' -> ' . $fk->REFERENCED_TABLE_NAME . '.' . $fk->REFERENCED_COLUMN_NAME . "\n";
}

$cols = DB::select("DESCRIBE purchase_orders");
echo "\n=== Cấu trúc cột ===\n";
foreach ($cols as $col) {
    echo $col->Field . ' | ' . $col->Type . ' | Key: ' . $col->Key . "\n";
}
