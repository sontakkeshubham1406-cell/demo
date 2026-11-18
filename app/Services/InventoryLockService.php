<?php
namespace App\Services;

use App\Database\Database;
use Exception;

class InventoryLockService
{
    /**
     * Attempts to soft-reserve stock for 15 minutes during checkout.
     * Throws an exception or returns false if any item has insufficient stock.
     */
    public static function reserveCartStock(array $cartItems): array
    {
        Database::beginTransaction();

        try {
            foreach ($cartItems as $item) {
                $variantId = $item['variant_id'];
                $qty = (int)$item['quantity'];

                $inv = Database::fetchOne("
                    SELECT stock_quantity, reserved_quantity 
                    FROM inventory 
                    WHERE variant_id = ?
                ", [$variantId]);

                if (!$inv) {
                    Database::rollBack();
                    return ['success' => false, 'message' => "Inventory record for SKU {$item['sku']} not found."];
                }

                $available = (int)$inv['stock_quantity'] - (int)$inv['reserved_quantity'];
                if ($available < $qty) {
                    Database::rollBack();
                    return [
                        'success' => false, 
                        'message' => "Item '{$item['product_name']} ({$item['size_name']}/{$item['color_name']})' has only {$available} left in stock."
                    ];
                }

                // Increment reserved quantity
                Database::execute("
                    UPDATE inventory 
                    SET reserved_quantity = reserved_quantity + ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE variant_id = ?
                ", [$qty, $variantId]);
            }

            Database::commit();
            return ['success' => true, 'message' => 'Stock reserved for 15 minutes.'];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Failed to reserve inventory: ' . $e->getMessage()];
        }
    }

    /**
     * Executes hard stock deduction upon validated payment confirmation.
     */
    public static function commitOrderStock(int $orderId): bool
    {
        Database::beginTransaction();

        try {
            $items = Database::query("SELECT * FROM order_items WHERE order_id = ?", [$orderId]);

            foreach ($items as $item) {
                $variantId = $item['variant_id'];
                $qty = (int)$item['quantity'];

                if (!$variantId) continue;

                // Decrement stock_quantity and decrement reserved_quantity
                Database::execute("
                    UPDATE inventory 
                    SET stock_quantity = MAX(0, stock_quantity - ?),
                        reserved_quantity = MAX(0, reserved_quantity - ?),
                        updated_at = CURRENT_TIMESTAMP
                    WHERE variant_id = ?
                ", [$qty, $qty, $variantId]);

                // Record immutable audit entry
                Database::insert('inventory_movements', [
                    'variant_id' => $variantId,
                    'user_id' => $_SESSION['user_id'] ?? null,
                    'change_type' => 'ORDER_DEDUCTION',
                    'quantity' => -$qty,
                    'reference_id' => "ORDER-{$orderId}",
                    'remarks' => "Order #{$orderId} payment confirmed"
                ]);
            }

            Database::commit();
            return true;
        } catch (Exception $e) {
            Database::rollBack();
            return false;
        }
    }

    /**
     * Releases soft-reserved stock if checkout is cancelled or times out.
     */
    public static function releaseCartStock(array $cartItems): void
    {
        foreach ($cartItems as $item) {
            $variantId = $item['variant_id'];
            $qty = (int)$item['quantity'];

            Database::execute("
                UPDATE inventory 
                SET reserved_quantity = MAX(0, reserved_quantity - ?),
                    updated_at = CURRENT_TIMESTAMP
                WHERE variant_id = ?
            ", [$qty, $variantId]);
        }
    }

    /**
     * Restores stock when an order is cancelled or a returned item is accepted back.
     */
    public static function restockVariant(int $variantId, int $quantity, string $reason, string $refId): bool
    {
        Database::beginTransaction();
        try {
            Database::execute("
                UPDATE inventory 
                SET stock_quantity = stock_quantity + ?, updated_at = CURRENT_TIMESTAMP 
                WHERE variant_id = ?
            ", [$quantity, $variantId]);

            Database::insert('inventory_movements', [
                'variant_id' => $variantId,
                'user_id' => $_SESSION['user_id'] ?? null,
                'change_type' => 'RETURN_RESTOCK',
                'quantity' => $quantity,
                'reference_id' => $refId,
                'remarks' => $reason
            ]);

            Database::commit();
            return true;
        } catch (Exception $e) {
            Database::rollBack();
            return false;
        }
    }
}

