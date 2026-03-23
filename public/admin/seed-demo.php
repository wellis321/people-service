<?php
/**
 * People Service — Demo Data Seeder
 *
 * Creates 5 people supported for the Sunrise Care demo organisation,
 * with contact details and care needs.
 * Safe to run multiple times — skips if demo data already exists.
 * Super admin access only.
 */
require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();
if (!RBAC::isSuperAdmin()) {
    header('Location: ' . url('index.php'));
    exit;
}

$db      = Database::getConnection();
$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && CSRF::validatePost()) {

    try {
        $db->beginTransaction();

        // ── 1. Find (or create) Sunrise Care org ─────────────────────────────
        $orgRow = $db->prepare('SELECT id FROM organisations WHERE slug = ? LIMIT 1');
        $orgRow->execute(['sunrisecare-demo']);
        $orgId = $orgRow->fetchColumn();

        if (!$orgId) {
            $slug = 'sunrisecare-demo';
            $db->prepare('INSERT INTO organisations (name, slug, domain) VALUES (?, ?, ?)')
               ->execute(['Sunrise Care', $slug, 'sunrisecare.demo']);
            $orgId = (int) $db->lastInsertId();

            // Admin user
            $db->prepare('
                INSERT INTO users (organisation_id, email, password_hash, first_name, last_name, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
            ')->execute([
                $orgId,
                'admin@sunrisecare.demo',
                password_hash('Sunrise2024!', PASSWORD_DEFAULT),
                'Demo',
                'Admin',
            ]);
        } else {
            $orgId = (int) $orgId;
        }

        // Check if demo people already exist
        $existing = $db->prepare('SELECT COUNT(*) FROM people WHERE organisation_id = ?');
        $existing->execute([$orgId]);
        if ((int) $existing->fetchColumn() > 0) {
            $db->rollBack();
            $error = 'Demo people already exist for Sunrise Care. Delete them first if you want to re-seed.';
            goto render;
        }

        // ── 2. People we support ──────────────────────────────────────────────
        $insertPerson = $db->prepare('
            INSERT INTO people
                (organisation_id, first_name, last_name, preferred_name,
                 date_of_birth, gender, status, support_start,
                 address_line1, city, county, postcode, notes)
            VALUES (?, ?, ?, ?, ?, ?, "active", ?, ?, ?, ?, ?, ?)
        ');

        $people = [
            [
                'first'   => 'Arthur',
                'last'    => 'Henderson',
                'pref'    => 'Arthur',
                'dob'     => '1948-06-15',
                'gender'  => 'male',
                'start'   => '2021-03-01',
                'addr1'   => '14 Meadow Lane',
                'city'    => 'Bristol',
                'county'  => 'Bristol',
                'post'    => 'BS3 4NP',
                'notes'   => 'Arthur enjoys gardening and Radio 4. Prefers morning visits before 11am.',
                'contacts' => [
                    ['next_of_kin', 'Daughter', 'Margaret Henderson', '07700 900123', true],
                ],
                'needs' => [
                    ['Mobility',      'Requires a walking frame indoors and a wheelchair for longer distances.',   'medium', '2026-09-01'],
                    ['Medication',    'Twice-daily medication for hypertension — requires prompting.',             'high',   '2026-06-01'],
                    ['Personal Care', 'Needs assistance with bathing and dressing in the morning.',               'medium', '2026-09-01'],
                ],
            ],
            [
                'first'   => 'Patricia',
                'last'    => 'Moore',
                'pref'    => 'Pat',
                'dob'     => '1952-11-03',
                'gender'  => 'female',
                'start'   => '2020-07-15',
                'addr1'   => '7 Elm Close',
                'city'    => 'Bristol',
                'county'  => 'Bristol',
                'post'    => 'BS6 7RT',
                'notes'   => 'Pat is sociable and enjoys craft activities. She has mild dementia — familiar routines are important.',
                'contacts' => [
                    ['next_of_kin', 'Son',       'David Moore',       '07700 900456', true],
                    ['social_worker', 'Social Worker', 'Bristol City Council Adult Social Care — Sarah Patel', '0117 922 2000', false],
                ],
                'needs' => [
                    ['Cognitive Support', 'Mild dementia — use memory prompts and maintain consistent routines.', 'medium', '2026-06-01'],
                    ['Nutrition',         'Needs encouragement and assistance at mealtimes.',                     'low',    '2026-09-01'],
                ],
            ],
            [
                'first'   => 'William',
                'last'    => 'Okafor',
                'pref'    => 'Will',
                'dob'     => '1960-02-28',
                'gender'  => 'male',
                'start'   => '2022-01-10',
                'addr1'   => '22 St Pauls Road',
                'city'    => 'Bristol',
                'county'  => 'Bristol',
                'post'    => 'BS2 9LB',
                'notes'   => 'William has a learning disability and is largely independent. He volunteers at a local food bank on Wednesdays.',
                'contacts' => [
                    ['next_of_kin', 'Sister',    'Chioma Okafor',     '07700 900789', true],
                ],
                'needs' => [
                    ['Communication',    'Benefits from easy-read materials. Responds well to visual schedules.',  'low',    '2026-09-01'],
                    ['Community Access', 'Needs support accessing community activities and managing appointments.', 'low',    '2026-12-01'],
                ],
            ],
            [
                'first'   => 'Dorothy',
                'last'    => 'Singh',
                'pref'    => 'Dot',
                'dob'     => '1944-09-20',
                'gender'  => 'female',
                'start'   => '2019-11-01',
                'addr1'   => 'Room 4, Sunrise House',
                'city'    => 'Bristol',
                'county'  => 'Bristol',
                'post'    => 'BS1 2AB',
                'notes'   => 'Dorothy is a residential resident at Sunrise House. She loves music and reminiscence activities.',
                'contacts' => [
                    ['next_of_kin',    'Niece',      'Priya Singh',  '07700 900321', true],
                    ['gp',             'GP Surgery',  'Clifton Road Surgery — Dr Alam', '0117 987 6543', false],
                ],
                'needs' => [
                    ['Personal Care',  'Full personal care required — morning and evening routines.',              'high',   '2026-06-01'],
                    ['Medication',     'Four medications daily, administered by support staff.',                   'high',   '2026-06-01'],
                    ['Mobility',       'Hoisted for transfers — two-person assist required.',                      'high',   '2026-03-01'],
                ],
            ],
            [
                'first'   => 'George',
                'last'    => 'Fitzpatrick',
                'pref'    => 'George',
                'dob'     => '1975-04-11',
                'gender'  => 'male',
                'start'   => '2023-05-22',
                'addr1'   => '63 Coronation Street',
                'city'    => 'Bristol',
                'county'  => 'Bristol',
                'post'    => 'BS5 8JQ',
                'notes'   => 'George has autism and anxiety. He prefers the same support worker where possible and dislikes unannounced visits.',
                'contacts' => [
                    ['next_of_kin', 'Mother',    'Helen Fitzpatrick', '07700 900654', true],
                ],
                'needs' => [
                    ['Mental Health',  'Anxiety disorder — pre-visit notice essential. Follow agreed support plan.', 'medium', '2026-08-01'],
                    ['Communication',  'May need extra processing time. Avoid ambiguous language.',                  'medium', '2026-08-01'],
                    ['Community Access', 'Supported employment goal — building confidence in public spaces.',        'low',    '2026-12-01'],
                ],
            ],
        ];

        $insertContact = $db->prepare('
            INSERT INTO person_contacts
                (person_id, organisation_id, contact_type, label, value, is_primary)
            VALUES (?, ?, ?, ?, ?, ?)
        ');

        $insertNeed = $db->prepare('
            INSERT INTO care_needs
                (person_id, organisation_id, category, description, severity, review_date)
            VALUES (?, ?, ?, ?, ?, ?)
        ');

        foreach ($people as $p) {
            $insertPerson->execute([
                $orgId, $p['first'], $p['last'], $p['pref'],
                $p['dob'], $p['gender'], $p['start'],
                $p['addr1'], $p['city'], $p['county'], $p['post'], $p['notes'],
            ]);
            $personId = (int) $db->lastInsertId();

            foreach ($p['contacts'] as $c) {
                [$type, $label, $value, , $primary] = $c;
                $insertContact->execute([$personId, $orgId, $type, $label, $value, $primary ? 1 : 0]);
            }

            foreach ($p['needs'] as $n) {
                [$category, $description, $severity, $reviewDate] = $n;
                $insertNeed->execute([$personId, $orgId, $category, $description, $severity, $reviewDate]);
            }
        }

        $db->commit();
        $message = 'Demo data created for <strong>Sunrise Care</strong>: 5 people supported with contacts and care needs.';

    } catch (PDOException $e) {
        $db->rollBack();
        $error = 'Database error: ' . htmlspecialchars($e->getMessage());
    }
}

render:
$pageTitle = 'Seed Demo Data';
include INCLUDES_PATH . '/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-seedling"></i> Seed Demo Data</h1>
        <p class="text-light text-small" style="margin-top:.25rem">
            Creates 5 people supported for <strong>Sunrise Care</strong> with contacts and care needs.
        </p>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
<?php endif; ?>

<div class="card" style="max-width:640px">
    <h3 style="font-weight:600;margin-bottom:.75rem">What this will create</h3>
    <ul style="margin:.5rem 0 1.25rem 1.25rem;line-height:1.8">
        <li>Organisation: <strong>Sunrise Care</strong> (created here if not yet provisioned)</li>
        <li><strong>5 people supported:</strong> Arthur Henderson, Patricia Moore, William Okafor, Dorothy Singh, George Fitzpatrick</li>
        <li>Contact details for each (next of kin, GP, social worker where applicable)</li>
        <li>Care needs with categories, descriptions, severity, and review dates</li>
    </ul>
    <p class="text-light text-small" style="margin-bottom:1.25rem">
        Idempotent — will refuse to run if Sunrise Care already has people records.
    </p>

    <?php if (!$message): ?>
    <form method="POST">
        <?php echo CSRF::tokenField(); ?>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-seedling"></i> Create Demo Data
        </button>
        <a href="<?php echo url('admin/organisations.php'); ?>" class="btn btn-secondary" style="margin-left:.5rem">Cancel</a>
    </form>
    <?php else: ?>
    <a href="<?php echo url('people.php'); ?>" class="btn btn-primary">
        <i class="fas fa-users"></i> View People
    </a>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
