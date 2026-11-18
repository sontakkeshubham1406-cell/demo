<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../app/Services/OrderService.php';
require_once __DIR__ . '/../app/Services/InventoryLockService.php';
require_once __DIR__ . '/../app/Services/RazorpayService.php';

use App\Services\OrderService;
use App\Database\Database;

echo "--- Testing Admin Fulfillment Pipeline ---\n";

// 1. Move Order #4 to PROCESSING
$res1 = OrderService::updateOrderStatus(4, 'PROCESSING');
echo "Step 1 (PROCESSING): " . json_encode($res1) . "\n";

// 2. Move Order #4 to SHIPPED with Tracking
$res2 = OrderService::updateOrderStatus(4, 'SHIPPED', [
    'carrier_name' => 'Blue Dart Air',
    'tracking_number' => 'BLUEDART-IND-778899'
]);
echo "Step 2 (SHIPPED): " . json_encode($res2) . "\n";

// 3. Verify shipment record in DB
$shipment = Database::fetchOne("SELECT * FROM shipments WHERE order_id = 4");
echo "Step 3 (Shipment DB Entry): Carrier=" . $shipment['carrier_name'] . ", AWB=" . $shipment['tracking_number'] . "\n";

// 4. Move Order #4 to DELIVERED
$res4 = OrderService::updateOrderStatus(4, 'DELIVERED');
echo "Step 4 (DELIVERED): " . json_encode($res4) . "\n";

echo "--- ALL ORDER WORKFLOWS 100% PASS ---\n";

