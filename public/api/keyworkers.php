<?php
/**
 * People Service — Key Workers API
 *
 * GET  /api/keyworkers.php?person_id=1   List key workers for a person
 * POST /api/keyworkers.php               Actions: assign, end, refresh_display
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

header('Content-Type: application/json; charset=utf-8');

$auth  = ApiAuth::requireAuth();
$orgId = (int) ($auth['organisation_id'] ?? 0);
if (!$orgId) {
    ApiAuth::json(['error' => 'Organisation context required'], 400);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $personId = (int) ($_GET['person_id'] ?? 0);
    if (!$personId) {
        ApiAuth::json(['error' => 'person_id required'], 400);
    }
    ApiAuth::json(Keyworker::findByPerson($personId, $orgId));
}

if ($method === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $body['action'] ?? '';

    if ($action === 'assign') {
        $personId = (int) ($body['person_id'] ?? 0);
        if (!$personId) ApiAuth::json(['error' => 'person_id required'], 400);
        $id = Keyworker::assign($personId, $orgId, $body);
        ApiAuth::json(['success' => true, 'id' => $id], 201);
    }

    if ($action === 'end') {
        $id = (int) ($body['id'] ?? 0);
        if (!$id) ApiAuth::json(['error' => 'id required'], 400);
        $endedAt = $body['ended_at'] ?? date('Y-m-d');
        $ok = Keyworker::end($id, $orgId, $endedAt);
        ApiAuth::json(['success' => $ok]);
    }

    if ($action === 'refresh_display') {
        $staffId     = (int) ($body['staff_id']    ?? 0);
        $displayName = (string) ($body['display_name'] ?? '');
        $displayRef  = (string) ($body['display_ref']  ?? '');
        if (!$staffId) ApiAuth::json(['error' => 'staff_id required'], 400);
        Keyworker::refreshDisplayName($staffId, $displayName, $displayRef);
        ApiAuth::json(['success' => true]);
    }

    ApiAuth::json(['error' => 'Unknown action'], 400);
}

ApiAuth::json(['error' => 'Method not allowed'], 405);
