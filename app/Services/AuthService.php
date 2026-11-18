<?php
namespace App\Services;

use App\Database\Database;

class AuthService
{
    public static function getCurrentUser(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        return Database::fetchOne("
            SELECT u.*, r.slug as role_slug, r.name as role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ? AND u.is_active = 1
        ", [$_SESSION['user_id']]);
    }

    public static function check(): bool
    {
        return self::getCurrentUser() !== null;
    }

    public static function isAdmin(): bool
    {
        $user = self::getCurrentUser();
        return $user && !empty($user['role_id']);
    }

    public static function hasPermission(string $permissionSlug): bool
    {
        $user = self::getCurrentUser();
        if (!$user || empty($user['role_id'])) {
            return false;
        }

        // Super Admin bypasses all checks
        if ($user['role_slug'] === 'super-admin') {
            return true;
        }

        $perm = Database::fetchOne("
            SELECT p.id 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ? AND p.slug = ?
        ", [$user['role_id'], $permissionSlug]);

        return $perm !== null;
    }

    public static function attempt(string $email, string $password, bool $adminOnly = false): bool
    {
        $user = Database::fetchOne("
            SELECT u.*, r.slug as role_slug 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE u.email = ? AND u.is_active = 1
        ", [$email]);

        if (!$user) {
            return false;
        }

        if ($adminOnly && empty($user['role_id'])) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role_id'] = $user['role_id'];

        return true;
    }

    public static function register(array $data): int
    {
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $userId = Database::insert('users', [
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'phone' => trim($data['phone'] ?? ''),
            'password_hash' => $hash,
            'is_active' => 1
        ]);

        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $data['name'];
        $_SESSION['user_email'] = $data['email'];
        $_SESSION['role_id'] = null;

        return $userId;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}

