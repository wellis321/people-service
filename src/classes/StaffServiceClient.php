<?php
/**
 * StaffServiceClient — HTTP client for querying the PMS (Staff Service) API.
 *
 * Connection settings are read per-organisation from `organisation_settings`,
 * falling back to .env values:
 *   STAFF_SERVICE_URL=http://localhost:8000
 *   STAFF_SERVICE_API_KEY=<key from PMS Admin → API Keys>
 */
class StaffServiceClient
{
    // ── Per-org config ────────────────────────────────────────────────────────

    private static function baseUrl(int $orgId): string
    {
        return rtrim(
            OrgSettings::get($orgId, 'staff_service_url', getenv('STAFF_SERVICE_URL') ?: ''),
            '/'
        );
    }

    private static function apiKey(int $orgId): string
    {
        return OrgSettings::get($orgId, 'staff_service_api_key', getenv('STAFF_SERVICE_API_KEY') ?: '');
    }

    public static function enabled(int $orgId): bool
    {
        return self::baseUrl($orgId) !== '' && self::apiKey($orgId) !== '';
    }

    // ── API calls ─────────────────────────────────────────────────────────────

    /**
     * Fetch the list of active staff for an organisation.
     */
    public static function getStaffList(int $orgId): ?array
    {
        $data = self::get($orgId, '/api/staff-data.php?organisation_id=' . $orgId . '&status=active&format=list');
        return is_array($data) ? $data : null;
    }

    /**
     * Fetch a single staff member's basic details.
     */
    public static function getStaff(int $orgId, int $staffId): ?array
    {
        $data = self::get($orgId, '/api/staff-data.php?id=' . $staffId);
        return is_array($data) && isset($data['id']) ? $data : null;
    }

    /**
     * Test a connection using explicit credentials (used by settings page).
     */
    public static function testConnection(string $url, string $apiKey): bool
    {
        $url = rtrim($url, '/') . '/api/staff-data.php?format=list&limit=1';
        $ctx = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'header'        => 'X-API-Key: ' . $apiKey . "\r\n" .
                                   'Accept: application/json' . "\r\n",
                'timeout'       => 5,
                'ignore_errors' => true,
            ],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        return $body !== false && json_decode($body) !== null;
    }

    // ── HTTP helpers ──────────────────────────────────────────────────────────

    private static function get(int $orgId, string $path): mixed
    {
        if (!self::enabled($orgId)) return null;

        $url = self::baseUrl($orgId) . $path;
        $ctx = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'header'        => 'X-API-Key: ' . self::apiKey($orgId) . "\r\n" .
                                   'Accept: application/json' . "\r\n",
                'timeout'       => 5,
                'ignore_errors' => true,
            ],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) return null;
        return json_decode($body, true);
    }
}
