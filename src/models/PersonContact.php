<?php
/**
 * PersonContact model — phone numbers, emails, next of kin etc.
 */
class PersonContact
{
    public static function findByPerson(int $personId, int $organisationId): array
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('
            SELECT * FROM person_contacts
            WHERE person_id = :pid AND organisation_id = :org
            ORDER BY contact_type, is_primary DESC, id
        ');
        $stmt->execute([':pid' => $personId, ':org' => $organisationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id, int $organisationId): ?array
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM person_contacts WHERE id = :id AND organisation_id = :org LIMIT 1');
        $stmt->execute([':id' => $id, ':org' => $organisationId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(int $personId, int $organisationId, array $data): int
    {
        $db   = Database::getConnection();

        // If this is being set as primary, clear existing primary for same type
        if (!empty($data['is_primary'])) {
            $db->prepare('
                UPDATE person_contacts SET is_primary = 0
                WHERE person_id = :pid AND organisation_id = :org AND contact_type = :type
            ')->execute([':pid' => $personId, ':org' => $organisationId, ':type' => $data['contact_type']]);
        }

        $stmt = $db->prepare('
            INSERT INTO person_contacts (person_id, organisation_id, contact_type, label, value, is_primary, notes)
            VALUES (:pid, :org, :type, :label, :value, :primary, :notes)
        ');
        $stmt->execute([
            ':pid'     => $personId,
            ':org'     => $organisationId,
            ':type'    => $data['contact_type'],
            ':label'   => $data['label']      ?: null,
            ':value'   => $data['value'],
            ':primary' => empty($data['is_primary']) ? 0 : 1,
            ':notes'   => $data['notes']      ?: null,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, int $organisationId, array $data): bool
    {
        $db   = Database::getConnection();

        // Clear primary for the same type if setting this one as primary
        if (!empty($data['is_primary'])) {
            $existing = self::findById($id, $organisationId);
            if ($existing) {
                $db->prepare('
                    UPDATE person_contacts SET is_primary = 0
                    WHERE person_id = :pid AND organisation_id = :org AND contact_type = :type AND id != :id
                ')->execute([
                    ':pid'  => $existing['person_id'],
                    ':org'  => $organisationId,
                    ':type' => $data['contact_type'] ?? $existing['contact_type'],
                    ':id'   => $id,
                ]);
            }
        }

        $stmt = $db->prepare('
            UPDATE person_contacts SET
                contact_type = :type,
                label        = :label,
                value        = :value,
                is_primary   = :primary,
                notes        = :notes
            WHERE id = :id AND organisation_id = :org
        ');
        $stmt->execute([
            ':type'    => $data['contact_type'],
            ':label'   => $data['label']      ?: null,
            ':value'   => $data['value'],
            ':primary' => empty($data['is_primary']) ? 0 : 1,
            ':notes'   => $data['notes']      ?: null,
            ':id'      => $id,
            ':org'     => $organisationId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id, int $organisationId): bool
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM person_contacts WHERE id = :id AND organisation_id = :org');
        $stmt->execute([':id' => $id, ':org' => $organisationId]);
        return $stmt->rowCount() > 0;
    }

    /** Group contacts by type for display */
    public static function groupByType(array $contacts): array
    {
        $grouped = [];
        foreach ($contacts as $c) {
            $grouped[$c['contact_type']][] = $c;
        }
        return $grouped;
    }
}
