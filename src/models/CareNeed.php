<?php
/**
 * CareNeed model — support/care requirements for a person.
 */
class CareNeed
{
    public static function findByPerson(int $personId, int $organisationId): array
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('
            SELECT * FROM care_needs
            WHERE person_id = :pid AND organisation_id = :org
            ORDER BY category, id
        ');
        $stmt->execute([':pid' => $personId, ':org' => $organisationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id, int $organisationId): ?array
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM care_needs WHERE id = :id AND organisation_id = :org LIMIT 1');
        $stmt->execute([':id' => $id, ':org' => $organisationId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function create(int $personId, int $organisationId, int $createdBy, array $data): int
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('
            INSERT INTO care_needs (person_id, organisation_id, category, description, severity, review_date, created_by)
            VALUES (:pid, :org, :category, :desc, :severity, :review, :by)
        ');
        $stmt->execute([
            ':pid'      => $personId,
            ':org'      => $organisationId,
            ':category' => $data['category'],
            ':desc'     => $data['description'],
            ':severity' => $data['severity']    ?: null,
            ':review'   => $data['review_date'] ?: null,
            ':by'       => $createdBy,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, int $organisationId, array $data): bool
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('
            UPDATE care_needs SET
                category    = :category,
                description = :desc,
                severity    = :severity,
                review_date = :review
            WHERE id = :id AND organisation_id = :org
        ');
        $stmt->execute([
            ':category' => $data['category'],
            ':desc'     => $data['description'],
            ':severity' => $data['severity']    ?: null,
            ':review'   => $data['review_date'] ?: null,
            ':id'       => $id,
            ':org'      => $organisationId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id, int $organisationId): bool
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM care_needs WHERE id = :id AND organisation_id = :org');
        $stmt->execute([':id' => $id, ':org' => $organisationId]);
        return $stmt->rowCount() > 0;
    }

    /** Return care needs grouped by category */
    public static function groupByCategory(array $needs): array
    {
        $grouped = [];
        foreach ($needs as $n) {
            $grouped[$n['category']][] = $n;
        }
        return $grouped;
    }

    public static function severityLabel(string $severity): string
    {
        return match ($severity) {
            'high'   => 'High',
            'medium' => 'Medium',
            'low'    => 'Low',
            default  => 'Unknown',
        };
    }
}
