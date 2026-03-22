<?php
/**
 * People Service — API Authentication
 *
 * Validates requests from other services using SHA-256-hashed API keys
 * stored in the local api_keys table.
 *
 * Also falls back to session-based auth for same-origin browser requests.
 */
class ApiAuth
{
    /**
     * Authenticate the current API request.
     *
     * Returns an array with at minimum:
     *   ['organisation_id' => int|null, 'source' => 'api_key'|'session']
     *
     * Returns false if unauthenticated.
     *
     * @return array|false
     */
    public static function authenticate()
    {
        $key = self::extractKey();
        if ($key) {
            $keyData = self::validateKey($key);
            if ($keyData) {
                return $keyData;
            }
        }

        // Fall back to browser session
        if (Auth::isLoggedIn()) {
            return [
                'organisation_id' => Auth::getOrganisationId(),
                'source'          => 'session',
            ];
        }

        return false;
    }

    /**
     * Authenticate and return auth context, or send 401 and exit.
     */
    public static function requireAuth(): array
    {
        $auth = self::authenticate();
        if (!$auth) {
            self::json(['error' => 'Unauthorised'], 401);
        }
        return $auth;
    }

    /**
     * Validate a raw API key against the api_keys table.
     *
     * @return array|false
     */
    public static function validateKey(string $rawKey)
    {
        $hash = hash('sha256', $rawKey);
        $db   = Database::getConnection();
        $stmt = $db->prepare('
            SELECT id, organisation_id, name, permissions
            FROM   api_keys
            WHERE  key_hash = ?
              AND  is_active = 1
        ');
        $stmt->execute([$hash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        // Record last use
        $db->prepare('UPDATE api_keys SET last_used_at = NOW() WHERE id = ?')
           ->execute([$row['id']]);

        return [
            'api_key_id'      => (int) $row['id'],
            'organisation_id' => $row['organisation_id'] !== null ? (int) $row['organisation_id'] : null,
            'permissions'     => $row['permissions'] ? json_decode($row['permissions'], true) : [],
            'source'          => 'api_key',
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function extractKey(): ?string
    {
        $headers = function_exists('getallheaders') ? getallheaders() : self::buildHeaders();

        // Authorization: Bearer <key>
        if (!empty($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.+)$/i', $headers['Authorization'], $m)) {
                return $m[1];
            }
        }

        // X-API-Key: <key>
        if (!empty($headers['X-Api-Key'])) {
            return $headers['X-Api-Key'];
        }
        if (!empty($headers['X-API-Key'])) {
            return $headers['X-API-Key'];
        }

        return null;
    }

    private static function buildHeaders(): array
    {
        $out = [];
        foreach ($_SERVER as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $name       = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($k, 5)))));
                $out[$name] = $v;
            }
        }
        return $out;
    }

    /**
     * Send a JSON response and exit.
     */
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
