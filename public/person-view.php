<?php
require_once dirname(__DIR__) . '/config/config.php';

if (!Auth::isLoggedIn()) {
    header('Location: ' . url('login.php'));
    exit;
}

$organisationId = (int) Auth::getOrganisationId();
$personId       = (int) ($_GET['id'] ?? 0);

$person = $personId ? Person::findById($personId, $organisationId) : null;
if (!$person) {
    header('Location: ' . url('people.php'));
    exit;
}

// Load related data
$contacts  = PersonContact::findByPerson($personId, $organisationId);
$grouped   = PersonContact::groupByType($contacts);
$careNeeds = CareNeed::findByPerson($personId, $organisationId);
$keyworkers = Keyworker::findByPerson($personId, $organisationId);

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!CSRF::validateToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $formError = 'Invalid security token. Please try again.';
    } else {
        if ($action === 'delete_contact') {
            PersonContact::delete((int)$_POST['contact_id'], $organisationId);
            header('Location: ' . url('person-view.php?id=' . $personId . '&msg=contact_deleted'));
            exit;
        }
        if ($action === 'delete_care_need') {
            CareNeed::delete((int)$_POST['need_id'], $organisationId);
            header('Location: ' . url('person-view.php?id=' . $personId . '&msg=need_deleted'));
            exit;
        }
        if ($action === 'end_keyworker') {
            Keyworker::end((int)$_POST['kw_id'], $organisationId, date('Y-m-d'));
            header('Location: ' . url('person-view.php?id=' . $personId . '&msg=kw_ended'));
            exit;
        }
        if ($action === 'delete_keyworker') {
            Keyworker::delete((int)$_POST['kw_id'], $organisationId);
            header('Location: ' . url('person-view.php?id=' . $personId . '&msg=kw_deleted'));
            exit;
        }
    }
}

$msg = $_GET['msg'] ?? '';
$pageTitle = Person::displayName($person);
include INCLUDES_PATH . '/header.php';

// CSS block immediately after header for sidebar layout
?>
<style>
    .profile-layout {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 1.5rem;
        align-items: start;
    }
    .profile-sidebar { display: flex; flex-direction: column; gap: 1rem; }
    .profile-avatar {
        width: 80px; height: 80px; border-radius: 50%;
        background: var(--primary); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.75rem; font-weight: 700;
        margin: 0 auto 0.75rem;
    }
    .profile-name { text-align: center; font-weight: 700; font-size: 1rem; }
    .profile-sub  { text-align: center; font-size: 0.8rem; color: var(--text-light); margin-top: 0.2rem; }
    .sidebar-nav  { display: flex; flex-direction: column; gap: 0.25rem; }
    .sidebar-nav a {
        display: flex; align-items: center; gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        text-decoration: none;
        color: var(--text);
        transition: background 0.15s;
    }
    .sidebar-nav a:hover { background: var(--bg); }
    .sidebar-nav a.active { background: #ede9fe; color: var(--primary); font-weight: 500; }
    .section { margin-bottom: 2rem; }
    .section-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1rem; padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--border);
    }
    .section-header h2 { font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 0.4rem; }
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem 1.5rem; }
    .detail-item label { font-size: 0.75rem; font-weight: 600; color: var(--text-light); text-transform: uppercase; letter-spacing: .04em; display: block; margin-bottom: 0.15rem; }
    .detail-item span  { font-size: 0.9rem; }
    .badge-severity-high   { background: #fee2e2; color: #991b1b; }
    .badge-severity-medium { background: #fef3c7; color: #92400e; }
    .badge-severity-low    { background: #d1fae5; color: #065f46; }
    @media (max-width: 768px) {
        .profile-layout { grid-template-columns: 1fr; }
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>

<?php if ($msg): ?>
<div class="alert alert-success"><i class="fa-solid fa-check-circle"></i>
    <?php echo match($msg) {
        'saved'           => 'Changes saved successfully.',
        'contact_deleted' => 'Contact removed.',
        'need_deleted'    => 'Care need removed.',
        'kw_ended'        => 'Key worker assignment ended.',
        'kw_deleted'      => 'Key worker record deleted.',
        default           => 'Done.'
    }; ?>
</div>
<?php endif; ?>

<?php if (!empty($formError)): ?>
<div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($formError); ?></div>
<?php endif; ?>

<div class="page-header">
    <h1>
        <i class="fa-solid fa-user"></i>
        <?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?>
    </h1>
    <div class="d-flex gap-1">
        <a href="<?php echo url('person-edit.php?id=' . $personId); ?>" class="btn btn-secondary">
            <i class="fa-solid fa-pen"></i> Edit
        </a>
        <a href="<?php echo url('people.php'); ?>" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="profile-layout">

    <!-- Sidebar -->
    <div class="profile-sidebar">
        <div class="card" style="padding:1.25rem">
            <div class="profile-avatar">
                <?php echo htmlspecialchars(strtoupper(substr($person['first_name'],0,1) . substr($person['last_name'],0,1))); ?>
            </div>
            <div class="profile-name"><?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?></div>
            <?php if ($person['preferred_name']): ?>
            <div class="profile-sub">Known as: <?php echo htmlspecialchars($person['preferred_name']); ?></div>
            <?php endif; ?>
            <div style="text-align:center;margin-top:.75rem">
                <?php
                $badge = match($person['status']) { 'active' => 'badge-green', 'inactive' => 'badge-amber', default => 'badge-grey' };
                echo '<span class="badge ' . $badge . '">' . htmlspecialchars(ucfirst($person['status'])) . '</span>';
                ?>
            </div>
        </div>
        <div class="card" style="padding:0.75rem">
            <nav class="sidebar-nav">
                <a href="#section-details" class="active"><i class="fa-solid fa-id-card"></i> Details</a>
                <a href="#section-contacts"><i class="fa-solid fa-address-book"></i> Contacts</a>
                <a href="#section-care-needs"><i class="fa-solid fa-hand-holding-medical"></i> Care Needs</a>
                <a href="#section-keyworkers"><i class="fa-solid fa-user-tie"></i> Key Workers</a>
            </nav>
        </div>
    </div>

    <!-- Main content -->
    <div>

        <!-- Details -->
        <div id="section-details" class="card section">
            <div class="section-header">
                <h2><i class="fa-solid fa-id-card"></i> Personal Details</h2>
                <a href="<?php echo url('person-edit.php?id=' . $personId . '#section-details'); ?>" class="btn btn-secondary btn-sm">Edit</a>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>Date of birth</label>
                    <span><?php echo $person['date_of_birth'] ? date('d M Y', strtotime($person['date_of_birth'])) : '—'; ?></span>
                </div>
                <div class="detail-item">
                    <label>Gender</label>
                    <span><?php echo $person['gender'] ? htmlspecialchars(ucfirst(str_replace('_', ' ', $person['gender']))) : '—'; ?></span>
                </div>
                <?php if ($person['pronouns']): ?>
                <div class="detail-item">
                    <label>Pronouns</label>
                    <span><?php echo htmlspecialchars($person['pronouns']); ?></span>
                </div>
                <?php endif; ?>
                <div class="detail-item">
                    <label>NHS Number</label>
                    <span><?php echo $person['nhs_number'] ? htmlspecialchars($person['nhs_number']) : '—'; ?></span>
                </div>
                <div class="detail-item">
                    <label>Local Authority Ref</label>
                    <span><?php echo $person['local_authority_ref'] ? htmlspecialchars($person['local_authority_ref']) : '—'; ?></span>
                </div>
                <div class="detail-item">
                    <label>Support start</label>
                    <span><?php echo $person['support_start'] ? date('d M Y', strtotime($person['support_start'])) : '—'; ?></span>
                </div>
                <div class="detail-item">
                    <label>Support end</label>
                    <span><?php echo $person['support_end'] ? date('d M Y', strtotime($person['support_end'])) : '—'; ?></span>
                </div>
            </div>
            <?php if ($person['address_line1'] || $person['city'] || $person['postcode']): ?>
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border)">
                <label style="font-size:.75rem;font-weight:600;color:var(--text-light);text-transform:uppercase;letter-spacing:.04em">Address</label>
                <address style="font-style:normal;margin-top:.25rem;line-height:1.6;font-size:.9rem">
                    <?php
                    $addr = array_filter([$person['address_line1'], $person['address_line2'], $person['city'], $person['county'], $person['postcode']]);
                    echo htmlspecialchars(implode(', ', $addr));
                    ?>
                </address>
            </div>
            <?php endif; ?>
            <?php if ($person['notes']): ?>
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border)">
                <label style="font-size:.75rem;font-weight:600;color:var(--text-light);text-transform:uppercase;letter-spacing:.04em">Notes</label>
                <p style="margin-top:.25rem;font-size:.9rem;white-space:pre-wrap"><?php echo htmlspecialchars($person['notes']); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Contacts -->
        <div id="section-contacts" class="card section">
            <div class="section-header">
                <h2><i class="fa-solid fa-address-book"></i> Contacts</h2>
                <a href="<?php echo url('contact-add.php?person_id=' . $personId); ?>" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-plus"></i> Add
                </a>
            </div>
            <?php if (empty($contacts)): ?>
                <p class="text-light">No contacts on record.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Type</th><th>Label</th><th>Contact</th><th>Primary</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contacts as $c): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $c['contact_type']))); ?></td>
                                <td class="text-light"><?php echo $c['label'] ? htmlspecialchars($c['label']) : '—'; ?></td>
                                <td><?php echo htmlspecialchars($c['value']); ?></td>
                                <td><?php echo $c['is_primary'] ? '<i class="fa-solid fa-star" style="color:var(--warning)"></i>' : ''; ?></td>
                                <td>
                                    <form method="POST" action="" style="display:inline" onsubmit="return confirm('Remove this contact?')">
                                        <?php echo CSRF::getTokenField(CSRF_TOKEN_NAME); ?>
                                        <input type="hidden" name="action" value="delete_contact">
                                        <input type="hidden" name="contact_id" value="<?php echo $c['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Care Needs -->
        <div id="section-care-needs" class="card section">
            <div class="section-header">
                <h2><i class="fa-solid fa-hand-holding-medical"></i> Care Needs</h2>
                <a href="<?php echo url('care-need-add.php?person_id=' . $personId); ?>" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-plus"></i> Add
                </a>
            </div>
            <?php if (empty($careNeeds)): ?>
                <p class="text-light">No care needs recorded.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Category</th><th>Description</th><th>Severity</th><th>Review date</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($careNeeds as $n): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($n['category']); ?></td>
                                <td><?php echo htmlspecialchars($n['description']); ?></td>
                                <td>
                                    <?php if ($n['severity']): ?>
                                    <span class="badge badge-severity-<?php echo $n['severity']; ?>">
                                        <?php echo CareNeed::severityLabel($n['severity']); ?>
                                    </span>
                                    <?php else: echo '—'; endif; ?>
                                </td>
                                <td class="text-light"><?php echo $n['review_date'] ? date('d M Y', strtotime($n['review_date'])) : '—'; ?></td>
                                <td>
                                    <a href="<?php echo url('care-need-edit.php?id=' . $n['id'] . '&person_id=' . $personId); ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <form method="POST" action="" style="display:inline" onsubmit="return confirm('Delete this care need?')">
                                        <?php echo CSRF::getTokenField(CSRF_TOKEN_NAME); ?>
                                        <input type="hidden" name="action" value="delete_care_need">
                                        <input type="hidden" name="need_id" value="<?php echo $n['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Key Workers -->
        <div id="section-keyworkers" class="card section">
            <div class="section-header">
                <h2><i class="fa-solid fa-user-tie"></i> Key Workers</h2>
                <a href="<?php echo url('keyworker-add.php?person_id=' . $personId); ?>" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-plus"></i> Assign
                </a>
            </div>
            <?php if (empty($keyworkers)): ?>
                <p class="text-light">No key workers assigned.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Name</th><th>Role</th><th>From</th><th>To</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($keyworkers as $kw): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($kw['display_name'] ?: 'Staff #' . $kw['staff_id']); ?>
                                    <?php if ($kw['display_ref']): ?>
                                        <div class="text-small text-light"><?php echo htmlspecialchars($kw['display_ref']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-light"><?php echo $kw['role_label'] ? htmlspecialchars($kw['role_label']) : '—'; ?></td>
                                <td class="text-light"><?php echo $kw['assigned_at'] ? date('d M Y', strtotime($kw['assigned_at'])) : '—'; ?></td>
                                <td>
                                    <?php if ($kw['ended_at']): ?>
                                        <span class="text-light"><?php echo date('d M Y', strtotime($kw['ended_at'])); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-green">Current</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$kw['ended_at']): ?>
                                    <form method="POST" action="" style="display:inline" onsubmit="return confirm('End this key worker assignment today?')">
                                        <?php echo CSRF::getTokenField(CSRF_TOKEN_NAME); ?>
                                        <input type="hidden" name="action" value="end_keyworker">
                                        <input type="hidden" name="kw_id" value="<?php echo $kw['id']; ?>">
                                        <button type="submit" class="btn btn-secondary btn-sm">End</button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" action="" style="display:inline" onsubmit="return confirm('Delete this record?')">
                                        <?php echo CSRF::getTokenField(CSRF_TOKEN_NAME); ?>
                                        <input type="hidden" name="action" value="delete_keyworker">
                                        <input type="hidden" name="kw_id" value="<?php echo $kw['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div><!-- /main content -->
</div><!-- /profile-layout -->

<?php include INCLUDES_PATH . '/footer.php'; ?>
