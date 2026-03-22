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

$errors = [];
$data   = $person;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $data = array_merge($data, array_map('trim', $_POST));
        if (empty($data['first_name'])) $errors[] = 'First name is required.';
        if (empty($data['last_name']))  $errors[] = 'Last name is required.';

        if (empty($errors)) {
            Person::update($personId, $organisationId, $data);
            header('Location: ' . url('person-view.php?id=' . $personId . '&msg=saved'));
            exit;
        }
    }
}

$pageTitle = 'Edit — ' . $person['first_name'] . ' ' . $person['last_name'];
include INCLUDES_PATH . '/header.php';
?>
<style>
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1.25rem; }
    .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0 1.25rem; }
    @media (max-width: 640px) { .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; } }
</style>

<div class="page-header">
    <h1><i class="fa-solid fa-pen"></i> Edit — <?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?></h1>
    <a href="<?php echo url('person-view.php?id=' . $personId); ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($e); ?></div>
<?php endforeach; ?>

<form method="POST" action="">
    <?php echo CSRF::tokenField(); ?>

    <div class="card">
        <div class="section-header" style="margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">
            <h2 style="font-size:1rem;font-weight:600"><i class="fa-solid fa-id-card"></i> Personal Details</h2>
        </div>
        <div class="form-grid-2">
            <div class="form-group">
                <label for="first_name">First name <span style="color:var(--danger)">*</span></label>
                <input type="text" id="first_name" name="first_name" class="form-control" required
                       value="<?php echo htmlspecialchars($data['first_name']); ?>">
            </div>
            <div class="form-group">
                <label for="last_name">Last name <span style="color:var(--danger)">*</span></label>
                <input type="text" id="last_name" name="last_name" class="form-control" required
                       value="<?php echo htmlspecialchars($data['last_name']); ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="preferred_name">Preferred name / known as</label>
            <input type="text" id="preferred_name" name="preferred_name" class="form-control"
                   value="<?php echo htmlspecialchars($data['preferred_name'] ?? ''); ?>">
        </div>
        <div class="form-grid-3">
            <div class="form-group">
                <label for="date_of_birth">Date of birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control"
                       value="<?php echo htmlspecialchars($data['date_of_birth'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" class="form-control">
                    <option value="">— select —</option>
                    <?php foreach (['male','female','non_binary','other','prefer_not_to_say'] as $g): ?>
                    <option value="<?php echo $g; ?>" <?php echo ($data['gender'] ?? '') === $g ? 'selected' : ''; ?>>
                        <?php echo ucfirst(str_replace('_', ' ', $g)); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="pronouns">Pronouns</label>
                <input type="text" id="pronouns" name="pronouns" class="form-control" placeholder="e.g. she/her"
                       value="<?php echo htmlspecialchars($data['pronouns'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <div class="card mt-2">
        <div class="section-header" style="margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">
            <h2 style="font-size:1rem;font-weight:600"><i class="fa-solid fa-calendar"></i> Support Details</h2>
        </div>
        <div class="form-grid-3">
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="active"   <?php echo ($data['status'] ?? '') === 'active'   ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($data['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="archived" <?php echo ($data['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                </select>
            </div>
            <div class="form-group">
                <label for="support_start">Support start date</label>
                <input type="date" id="support_start" name="support_start" class="form-control"
                       value="<?php echo htmlspecialchars($data['support_start'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="support_end">Support end date</label>
                <input type="date" id="support_end" name="support_end" class="form-control"
                       value="<?php echo htmlspecialchars($data['support_end'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-grid-2">
            <div class="form-group">
                <label for="nhs_number">NHS Number</label>
                <input type="text" id="nhs_number" name="nhs_number" class="form-control"
                       value="<?php echo htmlspecialchars($data['nhs_number'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="local_authority_ref">Local Authority Reference</label>
                <input type="text" id="local_authority_ref" name="local_authority_ref" class="form-control"
                       value="<?php echo htmlspecialchars($data['local_authority_ref'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <div class="card mt-2">
        <div class="section-header" style="margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid var(--border)">
            <h2 style="font-size:1rem;font-weight:600"><i class="fa-solid fa-location-dot"></i> Address</h2>
        </div>
        <div class="form-group">
            <label for="address_line1">Address line 1</label>
            <input type="text" id="address_line1" name="address_line1" class="form-control"
                   value="<?php echo htmlspecialchars($data['address_line1'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="address_line2">Address line 2</label>
            <input type="text" id="address_line2" name="address_line2" class="form-control"
                   value="<?php echo htmlspecialchars($data['address_line2'] ?? ''); ?>">
        </div>
        <div class="form-grid-3">
            <div class="form-group">
                <label for="city">Town / City</label>
                <input type="text" id="city" name="city" class="form-control"
                       value="<?php echo htmlspecialchars($data['city'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="county">County</label>
                <input type="text" id="county" name="county" class="form-control"
                       value="<?php echo htmlspecialchars($data['county'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="postcode">Postcode</label>
                <input type="text" id="postcode" name="postcode" class="form-control"
                       value="<?php echo htmlspecialchars($data['postcode'] ?? ''); ?>">
            </div>
        </div>
    </div>

    <div class="card mt-2">
        <div class="form-group" style="margin-bottom:0">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" class="form-control" rows="4"><?php echo htmlspecialchars($data['notes'] ?? ''); ?></textarea>
        </div>
    </div>

    <div class="d-flex gap-1 mt-2" style="padding-bottom:2rem">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Changes</button>
        <a href="<?php echo url('person-view.php?id=' . $personId); ?>" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php include INCLUDES_PATH . '/footer.php'; ?>
