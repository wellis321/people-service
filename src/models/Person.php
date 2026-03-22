<?php
/**
 * Person model — core people record.
 */
class Person
{
    // ── Finders ─────────────────────────────────────────────────────────────

    public static function findAll(int $organisationId, array $filters = []): array
    {
        $db   = Database::getConnection();
        $sql  = 'SELECT * FROM people WHERE organisation_id = :org';
        $params = [':org' => $organisationId];

        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND (first_name LIKE :s OR last_name LIKE :s OR preferred_name LIKE :s OR nhs_number LIKE :s)';
            $params[':s'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['unit_id'])) {
            $sql .= ' AND id IN (SELECT person_id FROM person_organisational_units WHERE unit_id = :unit)';
            $params[':unit'] = (int) $filters['unit_id'];
        }

        $sql .= ' ORDER BY last_name, first_name';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id, int $organisationId): ?array
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM people WHERE id = :id AND organisation_id = :org LIMIT 1');
        $stmt->execute([':id' => $id, ':org' => $organisationId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ── Create / Update ──────────────────────────────────────────────────────

    public static function create(int $organisationId, array $data): int
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('
            INSERT INTO people
                (organisation_id, first_name, last_name, preferred_name, date_of_birth,
                 gender, pronouns, status, support_start, support_end,
                 nhs_number, local_authority_ref,
                 address_line1, address_line2, city, county, postcode, notes)
            VALUES
                (:org, :first_name, :last_name, :preferred_name, :dob,
                 :gender, :pronouns, :status, :support_start, :support_end,
                 :nhs_number, :la_ref,
                 :addr1, :addr2, :city, :county, :postcode, :notes)
        ');
        $stmt->execute([
            ':org'           => $organisationId,
            ':first_name'    => $data['first_name']          ?? '',
            ':last_name'     => $data['last_name']           ?? '',
            ':preferred_name'=> $data['preferred_name']      ?: null,
            ':dob'           => $data['date_of_birth']       ?: null,
            ':gender'        => $data['gender']              ?: null,
            ':pronouns'      => $data['pronouns']            ?: null,
            ':status'        => $data['status']              ?? 'active',
            ':support_start' => $data['support_start']       ?: null,
            ':support_end'   => $data['support_end']         ?: null,
            ':nhs_number'    => $data['nhs_number']          ?: null,
            ':la_ref'        => $data['local_authority_ref'] ?: null,
            ':addr1'         => $data['address_line1']       ?: null,
            ':addr2'         => $data['address_line2']       ?: null,
            ':city'          => $data['city']                ?: null,
            ':county'        => $data['county']              ?: null,
            ':postcode'      => $data['postcode']            ?: null,
            ':notes'         => $data['notes']               ?: null,
        ]);
        return (int) $db->lastInsertId();
    }

    public static function update(int $id, int $organisationId, array $data): bool
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('
            UPDATE people SET
                first_name          = :first_name,
                last_name           = :last_name,
                preferred_name      = :preferred_name,
                date_of_birth       = :dob,
                gender              = :gender,
                pronouns            = :pronouns,
                status              = :status,
                support_start       = :support_start,
                support_end         = :support_end,
                nhs_number          = :nhs_number,
                local_authority_ref = :la_ref,
                address_line1       = :addr1,
                address_line2       = :addr2,
                city                = :city,
                county              = :county,
                postcode            = :postcode,
                notes               = :notes
            WHERE id = :id AND organisation_id = :org
        ');
        $stmt->execute([
            ':first_name'    => $data['first_name']          ?? '',
            ':last_name'     => $data['last_name']           ?? '',
            ':preferred_name'=> $data['preferred_name']      ?: null,
            ':dob'           => $data['date_of_birth']       ?: null,
            ':gender'        => $data['gender']              ?: null,
            ':pronouns'      => $data['pronouns']            ?: null,
            ':status'        => $data['status']              ?? 'active',
            ':support_start' => $data['support_start']       ?: null,
            ':support_end'   => $data['support_end']         ?: null,
            ':nhs_number'    => $data['nhs_number']          ?: null,
            ':la_ref'        => $data['local_authority_ref'] ?: null,
            ':addr1'         => $data['address_line1']       ?: null,
            ':addr2'         => $data['address_line2']       ?: null,
            ':city'          => $data['city']                ?: null,
            ':county'        => $data['county']              ?: null,
            ':postcode'      => $data['postcode']            ?: null,
            ':notes'         => $data['notes']               ?: null,
            ':id'            => $id,
            ':org'           => $organisationId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id, int $organisationId): bool
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('DELETE FROM people WHERE id = :id AND organisation_id = :org');
        $stmt->execute([':id' => $id, ':org' => $organisationId]);
        return $stmt->rowCount() > 0;
    }

    // ── Counts ───────────────────────────────────────────────────────────────

    public static function countByStatus(int $organisationId): array
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('SELECT status, COUNT(*) as cnt FROM people WHERE organisation_id = :org GROUP BY status');
        $stmt->execute([':org' => $organisationId]);
        $result = ['active' => 0, 'inactive' => 0, 'archived' => 0];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['status']] = (int) $row['cnt'];
        }
        return $result;
    }

    // ── Organisational units ─────────────────────────────────────────────────

    public static function getOrganisationalUnits(int $personId): array
    {
        $db   = Database::getConnection();
        $stmt = $db->prepare('
            SELECT ou.* FROM organisational_units ou
            JOIN person_organisational_units pou ON pou.unit_id = ou.id
            WHERE pou.person_id = :id
            ORDER BY ou.name
        ');
        $stmt->execute([':id' => $personId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function setOrganisationalUnits(int $personId, array $unitIds): void
    {
        $db = Database::getConnection();
        $db->prepare('DELETE FROM person_organisational_units WHERE person_id = :id')->execute([':id' => $personId]);
        if (empty($unitIds)) return;
        $stmt = $db->prepare('INSERT IGNORE INTO person_organisational_units (person_id, unit_id) VALUES (:pid, :uid)');
        foreach ($unitIds as $uid) {
            $stmt->execute([':pid' => $personId, ':uid' => (int) $uid]);
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public static function displayName(array $person): string
    {
        $name = trim($person['first_name'] . ' ' . $person['last_name']);
        if (!empty($person['preferred_name'])) {
            $name .= ' (' . $person['preferred_name'] . ')';
        }
        return $name;
    }
}
