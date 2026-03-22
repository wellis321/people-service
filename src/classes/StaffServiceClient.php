<?php
/**
 * StaffServiceClient — HTTP client for querying the PMS (Staff Service) API.
 *
 * Used to look up staff names when assigning key workers, and to push
 * display-name refreshes when a person's details change.
 */
class StaffServiceClient
{
    private static function baseUrl(): string
    {
        return rtrim(getenv('STAFF_SERVICE_URL') ?: '', '/');
    }

    private static function apiKey(): string
    {
        return getenv('STAFF_SERVICE_API_KEY') ?: '';
    }

    public static function enabled(): bool
    {
        return self::baseUrl() !== '' && self::apiKey() !== '';
    }

    // ── API calls ─────────────────────────────────────────────────────────────

    /**
     * Fetch the list of active staff for an organisation.
     * Returns array of ['id', 'first_name', 'last_name', 'job_title', ...] or null on error.
     */
    public static function getStaffList(int $orgId): ?array
    {
        $data = self::get('/api/staff-data.php?organisation_id=' . $orgId . '&status=active&format=list');
        return is_array($data) ? $data : null;
    }

    /**
     * Fetch a single staff member's basic details.
     */
    public static function getStaff(int $staffId): ?array
    {
        $data = self::get('/api/staff-data.php?id=' . $staffId);
        return is_array($data) && isset($data['id']) ? $data : null;
    }

    // ── HTTP helpers ──────────────────────────────────────────────────────────

    private static function get(string $path): mixed
    {
        if (!self::enabled()) return null;

        $url = self::baseUrl() . $path;
        $ctx = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'header'  => 'X-API-Key: ' . self::apiKey() . "\r\n" .
                             'Accept: application/json' . "\r\n",
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) return null;
        return json_decode($body, true);
    }

    private static function post(string $path, array $payload): mixed
    {
        if (!self::enabled()) return null;

        $url  = self::baseUrl() . $path;
        $json = json_encode($payload);
        $ctx  = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => 'X-API-Key: ' . self::apiKey() . "\r\n" .
                             'Content-Type: application/json' . "\r\n" .
                             'Content-Length: ' . strlen($json) . "\r\n" .
                             'Accept: application/json' . "\r\n",
                'content' => $json,
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) return null;
        return json_decode($body, true);
    }
}
