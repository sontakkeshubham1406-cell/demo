<?php
namespace App\Services;

use App\Database\Database;

class CartService
{
    private static function getSessionToken(): string
    {
        if (empty($_SESSION['cart_session_token'])) {
            $_SESSION['cart_session_token'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['cart_session_token'];
    }

    public static function getOrCreateCart(): array
    {
        $userId = $_SESSION['user_id'] ?? null;
        $sessionToken = self::getSessionToken();

        if ($userId) {
            $cart = Database::fetchOne("SELECT * FROM carts WHERE user_id = ?", [$userId]);
            if (!$cart) {
                // Check if there was an anonymous session cart to merge
                $anonCart = Database::fetchOne("SELECT * FROM carts WHERE session_token = ? AND user_id IS NULL", [$sessionToken]);
                if ($anonCart) {
                    Database::update('carts', ['user_id' => $userId, 'session_token' => null], 'id = :id', ['id' => $anonCart['id']]);
                    $cart = Database::fetchOne("SELECT * FROM carts WHERE id = ?", [$anonCart['id']]);
                } else {
                    $cartId = Database::insert('carts', ['user_id' => $userId, 'session_token' => null]);
                    $cart = Database::fetchOne("SELECT * FROM carts WHERE id = ?", [$cartId]);
                }
            }
        } else {
            $cart = Database::fetchOne("SELECT * FROM carts WHERE session_token = ?", [$sessionToken]);
            if (!$cart) {
                $cartId = Database::insert('carts', ['user_id' => null, 'session_token' => $sessionToken]);
                $cart = Database::fetchOne("SELECT * FROM carts WHERE id = ?", [$cartId]);
            }
        }

        return $cart;
    }

    public static function getCartItems(): array
    {
        $cart = self::getOrCreateCart();
        $items = Database::query("
            SELECT 
                ci.id as cart_item_id,
                ci.cart_id,
                ci.variant_id,
                ci.quantity,
                pv.sku,
                pv.price_override,
                pv.product_id,
                p.name as product_name,
                p.slug as product_slug,
                p.base_price,
                p.discount_price,
                COALESCE(pv.price_override, p.discount_price, p.base_price) as unit_price,
                s.name as size_name,
                s.id as size_id,
                c.name as color_name,
                c.id as color_id,
                c.hex_code as color_hex,
                COALESCE(img.image_url, (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1)) as item_image,
                (COALESCE(inv.stock_quantity, 0) - COALESCE(inv.reserved_quantity, 0)) as available_to_sell
            FROM cart_items ci
            JOIN product_variants pv ON ci.variant_id = pv.id
            JOIN products p ON pv.product_id = p.id
            JOIN sizes s ON pv.size_id = s.id
            JOIN colors c ON pv.color_id = c.id
            LEFT JOIN product_images img ON (img.product_id = p.id AND img.color_id = c.id)
            LEFT JOIN inventory inv ON pv.id = inv.variant_id
            WHERE ci.cart_id = ?
            ORDER BY ci.created_at DESC
        ", [$cart['id']]);

        return $items;
    }

    public static function addItem(int $variantId, int $quantity = 1): array
    {
        if ($quantity <= 0) {
            return ['success' => false, 'message' => 'Quantity must be at least 1.'];
        }

        // Check stock availability
        $inv = Database::fetchOne("
            SELECT (stock_quantity - reserved_quantity) as available 
            FROM inventory 
            WHERE variant_id = ?
        ", [$variantId]);

        $available = $inv ? (int)$inv['available'] : 0;
        if ($available < $quantity) {
            return ['success' => false, 'message' => 'Requested quantity exceeds available stock (' . $available . ' available).'];
        }

        $cart = self::getOrCreateCart();

        // Check if item already exists in cart
        $existing = Database::fetchOne("
            SELECT id, quantity 
            FROM cart_items 
            WHERE cart_id = ? AND variant_id = ?
        ", [$cart['id'], $variantId]);

        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            if ($newQty > 5) {
                return ['success' => false, 'message' => 'Maximum limit of 5 units per item reached.'];
            }
            if ($newQty > $available) {
                return ['success' => false, 'message' => 'Cannot add more. Only ' . $available . ' units in stock.'];
            }

            Database::update('cart_items', ['quantity' => $newQty], 'id = :id', ['id' => $existing['id']]);
        } else {
            Database::insert('cart_items', [
                'cart_id' => $cart['id'],
                'variant_id' => $variantId,
                'quantity' => min($quantity, 5)
            ]);
        }

        return ['success' => true, 'message' => 'Added to bag successfully!'];
    }

    public static function updateItem(int $cartItemId, int $quantity): array
    {
        if ($quantity <= 0) {
            return self::removeItem($cartItemId);
        }

        $item = Database::fetchOne("SELECT variant_id FROM cart_items WHERE id = ?", [$cartItemId]);
        if (!$item) {
            return ['success' => false, 'message' => 'Item not found.'];
        }

        $inv = Database::fetchOne("
            SELECT (stock_quantity - reserved_quantity) as available 
            FROM inventory 
            WHERE variant_id = ?
        ", [$item['variant_id']]);

        $available = $inv ? (int)$inv['available'] : 0;
        if ($quantity > $available) {
            return ['success' => false, 'message' => 'Only ' . $available . ' units in stock.'];
        }

        if ($quantity > 5) {
            return ['success' => false, 'message' => 'Maximum limit is 5 units per item.'];
        }

        Database::update('cart_items', ['quantity' => $quantity], 'id = :id', ['id' => $cartItemId]);
        return ['success' => true, 'message' => 'Cart updated.'];
    }

    public static function removeItem(int $cartItemId): array
    {
        $cart = self::getOrCreateCart();
        Database::execute("DELETE FROM cart_items WHERE id = ? AND cart_id = ?", [$cartItemId, $cart['id']]);
        return ['success' => true, 'message' => 'Item removed from bag.'];
    }

    public static function clearCart(): void
    {
        $cart = self::getOrCreateCart();
        Database::execute("DELETE FROM cart_items WHERE cart_id = ?", [$cart['id']]);
        Database::update('carts', ['coupon_id' => null], 'id = :id', ['id' => $cart['id']]);
    }
}

