<?php
/**
 * People Service — People Data API
 *
 * GET  /api/people-data.php                     List people (auth required)
 * GET  /api/people-data.php?id=123              Single person record
 * GET  /api/people-data.php?staff_id=456        All people linked to a key-worker
 * GET  /api/people-data.php?status=active       Filter by status
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

header('Content-Type: application/json; charset=utf-8');

$auth = ApiAuth::requireAuth();
// App-scoped keys (organisation_id = null) must supply organisation_id as a query param
$orgId = (int) ($auth['organisation_id'] ?? $_GET['organisation_id'] ?? 0);
if (!$orgId) {
    ApiAuth::json(['error' => 'Organisation context required — pass organisation_id as a query parameter'], 400);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {

    // Single person by ID
    if (!empty($_GET['id'])) {
        $person = Person::findById((int)$_GET['id'], $orgId);
        if (!$person) {
            ApiAuth::json(['error' => 'Not found'], 404);
        }
        $person['contacts']    = PersonContact::findByPerson((int)$_GET['id'], $orgId);
        $person['care_needs']  = CareNeed::findByPerson((int)$_GET['id'], $orgId);
        $person['key_workers'] = Keyworker::findByPerson((int)$_GET['id'], $orgId);
        ApiAuth::json($person);
    }

    // People for a key-worker (staff_id)
    if (!empty($_GET['staff_id'])) {
        $db   = Database::getConnection();
        $stmt = $db->prepare('
            SELECT p.* FROM people p
            JOIN person_keyworkers kw ON kw.person_id = p.id
            WHERE kw.staff_id = :sid AND p.organisation_id = :org AND kw.ended_at IS NULL
            ORDER BY p.last_name, p.first_name
        ');
        $stmt->execute([':sid' => (int)$_GET['staff_id'], ':org' => $orgId]);
        ApiAuth::json($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // List
    $filters = [
        'status' => $_GET['status'] ?? '',
        'search' => $_GET['search'] ?? '',
    ];
    ApiAuth::json(Person::findAll($orgId, $filters));
}

ApiAuth::json(['error' => 'Method not allowed'], 405);
