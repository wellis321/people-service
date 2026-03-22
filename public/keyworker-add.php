<?php
require_once dirname(__DIR__) . '/config/config.php';

if (!Auth::isLoggedIn()) {
    header('Location: ' . url('login.php'));
    exit;
}

$organisationId = (int) Auth::getOrganisationId();
$personId       = (int) ($_GET['person_id'] ?? 0);

$person = $personId ? Person::findById($personId, $organisationId) : null;
if (!$person) {
    header('Location: ' . url('people.php'));
    exit;
}

// Load staff list from PMS if connected
ob_start();
$staffList = StaffServiceClient::enabled($organisationId) ? StaffServiceClient::getStaffList($organisationId) : null;
ob_end_clean();

$errors = [];
$data   = ['staff_id' => '', 'display_name' => '', 'display_ref' => '', 'role_label' => '', 'assigned_at' => date('Y-m-d'), 'ended_at' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $data = array_merge($data, array_map('trim', $_POST));
        if (empty($data['staff_id'])) $errors[] = 'Please select or enter a staff member.';

        // If we have a staff list and the user picked from it, look up display details
        if (!empty($data['staff_id']) && is_array($staffList)) {
            foreach ($staffList as $s) {
                if ((int)$s['id'] === (int)$data['staff_id']) {
                    if (empty($data['display_name'])) {
                        $data['display_name'] = trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''));
                    }
                    if (empty($data['display_ref'])) {
                        $data['display_ref'] = $s['employee_id'] ?? $s['payroll_number'] ?? '';
                    }
                    break;
                }
            }
        }

        if (empty($errors)) {
            Keyworker::assign($personId, $organisationId, $data);
            header('Location: ' . url('person-view.php?id=' . $personId . '#section-keyworkers'));
            exit;
        }
    }
}

$pageTitle = 'Assign Key Worker';
include INCLUDES_PATH . '/header.php';
?>

<div class="page-header">
    <h1><i class="fa-solid fa-user-tie"></i> Assign Key Worker</h1>
    <a href="<?php echo url('person-view.php?id=' . $personId); ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<p class="text-light" style="margin-bottom:1.5rem">For: <strong><?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?></strong></p>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($e); ?></div>
<?php endforeach; ?>

<form method="POST" action="">
    <?php echo CSRF::tokenField(); ?>
    <div class="card" style="max-width:580px">

        <?php if (is_array($staffList) && !empty($staffList)): ?>
        <div class="form-group">
            <label for="staff_select">Select staff member</label>
            <select id="staff_select" class="form-control" onchange="fillStaff(this)">
                <option value="">— choose from Staff Service —</option>
                <?php foreach ($staffList as $s): ?>
                <option value="<?php echo $s['id']; ?>"
                        data-name="<?php echo htmlspecialchars(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))); ?>"
                        data-ref="<?php echo htmlspecialchars($s['employee_id'] ?? $s['payroll_number'] ?? ''); ?>">
                    <?php echo htmlspecialchars(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))); ?>
                    <?php if (!empty($s['job_title'])): ?> — <?php echo htmlspecialchars($s['job_title']); ?><?php endif; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <script>
        function fillStaff(sel) {
            const opt = sel.options[sel.selectedIndex];
            document.getElementById('staff_id').value    = opt.value;
            document.getElementById('display_name').value = opt.dataset.name || '';
            document.getElementById('display_ref').value  = opt.dataset.ref  || '';
        }
        </script>
        <?php else: ?>
        <div class="alert alert-info"><i class="fa-solid fa-circle-info"></i> Staff Service not connected — enter details manually.</div>
        <?php endif; ?>

        <div class="form-group">
            <label for="staff_id">Staff ID <span style="color:var(--danger)">*</span></label>
            <input type="number" id="staff_id" name="staff_id" class="form-control" required min="1"
                   value="<?php echo htmlspecialchars($data['staff_id']); ?>">
            <div class="form-hint">The numeric ID of the staff record in the Staff Service (PMS).</div>
        </div>
        <div class="form-group">
            <label for="display_name">Display name</label>
            <input type="text" id="display_name" name="display_name" class="form-control"
                   value="<?php echo htmlspecialchars($data['display_name']); ?>">
        </div>
        <div class="form-group">
            <label for="display_ref">Reference / employee number</label>
            <input type="text" id="display_ref" name="display_ref" class="form-control"
                   value="<?php echo htmlspecialchars($data['display_ref']); ?>">
        </div>
        <div class="form-group">
            <label for="role_label">Role label</label>
            <input type="text" id="role_label" name="role_label" class="form-control"
                   placeholder="e.g. Key Worker, Support Lead"
                   value="<?php echo htmlspecialchars($data['role_label']); ?>">
        </div>
        <div class="form-group">
            <label for="assigned_at">Assigned from</label>
            <input type="date" id="assigned_at" name="assigned_at" class="form-control"
                   value="<?php echo htmlspecialchars($data['assigned_at']); ?>">
        </div>
        <div class="d-flex gap-1 mt-2">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Assign</button>
            <a href="<?php echo url('person-view.php?id=' . $personId); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>

<?php include INCLUDES_PATH . '/footer.php'; ?>
