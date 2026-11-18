<?php
namespace App\Services;

use App\Database\Database;

class AuditLogger
{
    public static function log(string $action, string $entityType, string $entityId, ?array $before = null, ?array $after = null): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI/System';

        Database::insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => (string) $entityId,
            'ip_address' => $ip,
            'user_agent' => substr($ua, 0, 255),
            'payload_before' => $before ? json_encode($before) : null,
            'payload_after' => $after ? json_encode($after) : null
        ]);
    }
}

