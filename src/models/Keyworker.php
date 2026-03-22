<?php
/**
 * Keyworker model — assigned support staff (cross-service reference to PMS).
 */
class Keyworker
{
    public static function findByPerson(int $personId, int $organisationId): array
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('
            SELECT * FROM person_keyworkers
            WHERE person_id = :pid AND organisation_id = :org
            ORDER BY ended_at IS NULL DESC, assigned_at DESC
        ');
        $stmt->execute([':pid' => $personId, ':org' => $organisationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id, int $organisationId): ?array
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM person_keyworkers WHERE id = :id AND organisation_id = :org LIMIT 1');
        $stmt->execute([':id' => $id, ':org' => $organisationId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function assign(int $personId, int $organisationId, array $data): int
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('
            INSERT INTO person_keyworkers (person_id, organisation_id, staff_id, display_name, display_ref, role_label, assigned_at, ended_at)
            VALUES (:pid, :org, :staff, :name, :ref, :role, :start, :end)
        ');
        $stmt->execute([
            ':pid'   => $personId,
            ':org'   => $organisationId,
            ':staff' => $data['staff_id'],
            ':name'  => $data['display_name'] ?: null,
            ':ref'   => $data['display_ref']  ?: null,
            ':role'  => $data['role_label']   ?: null,
            ':start' => $data['assigned_at']  ?: date('Y-m-d'),
            ':end'   => $data['ended_at']     ?: null,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function end(int $id, int $organisationId, string $endedAt): bool
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('UPDATE person_keyworkers SET ended_at = :end WHERE id = :id AND organisation_id = :org');
        $stmt->execute([':end' => $endedAt, ':id' => $id, ':org' => $organisationId]);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id, int $organisationId): bool
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM person_keyworkers WHERE id = :id AND organisation_id = :org');
        $stmt->execute([':id' => $id, ':org' => $organisationId]);
        return $stmt->rowCount() > 0;
    }

    /** Refresh cached display name/ref when PMS staff record changes */
    public static function refreshDisplayName(int $staffId, string $displayName, string $displayRef): void
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('UPDATE person_keyworkers SET display_name = :name, display_ref = :ref WHERE staff_id = :sid');
        $stmt->execute([':name' => $displayName, ':ref' => $displayRef, ':sid' => $staffId]);
    }
}
