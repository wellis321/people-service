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

$errors = [];
$data   = ['contact_type' => 'phone', 'label' => '', 'value' => '', 'is_primary' => 0, 'notes' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $data = array_merge($data, array_map('trim', $_POST));
        $data['is_primary'] = isset($_POST['is_primary']) ? 1 : 0;
        if (empty($data['contact_type'])) $errors[] = 'Contact type is required.';
        if (empty($data['value']))        $errors[] = 'Contact value is required.';

        if (empty($errors)) {
            PersonContact::create($personId, $organisationId, $data);
            header('Location: ' . url('person-view.php?id=' . $personId . '#section-contacts'));
            exit;
        }
    }
}

$pageTitle = 'Add Contact — ' . $person['first_name'] . ' ' . $person['last_name'];
include INCLUDES_PATH . '/header.php';
?>

<div class="page-header">
    <h1><i class="fa-solid fa-address-book"></i> Add Contact</h1>
    <a href="<?php echo url('person-view.php?id=' . $personId); ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<p class="text-light" style="margin-bottom:1.5rem">For: <strong><?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?></strong></p>

<?php foreach ($errors as $e): ?>
    <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($e); ?></div>
<?php endforeach; ?>

<form method="POST" action="">
    <?php echo CSRF::tokenField(); ?>
    <div class="card" style="max-width:580px">
        <div class="form-group">
            <label for="contact_type">Type <span style="color:var(--danger)">*</span></label>
            <select id="contact_type" name="contact_type" class="form-control" required>
                <?php
                $types = ['phone','email','address','next_of_kin','legal_guardian','social_worker','gp','other'];
                foreach ($types as $t):
                ?>
                <option value="<?php echo $t; ?>" <?php echo $data['contact_type'] === $t ? 'selected' : ''; ?>>
                    <?php echo ucfirst(str_replace('_', ' ', $t)); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="label">Label</label>
            <input type="text" id="label" name="label" class="form-control" placeholder="e.g. Mobile, Mother, GP Surgery"
                   value="<?php echo htmlspecialchars($data['label']); ?>">
        </div>
        <div class="form-group">
            <label for="value">Contact details <span style="color:var(--danger)">*</span></label>
            <input type="text" id="value" name="value" class="form-control" required
                   value="<?php echo htmlspecialchars($data['value']); ?>">
        </div>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                <input type="checkbox" name="is_primary" value="1" <?php echo $data['is_primary'] ? 'checked' : ''; ?>>
                Primary contact for this type
            </label>
        </div>
        <div class="form-group">
            <label for="notes">Notes</label>
            <input type="text" id="notes" name="notes" class="form-control"
                   value="<?php echo htmlspecialchars($data['notes']); ?>">
        </div>
        <div class="d-flex gap-1 mt-2">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Contact</button>
            <a href="<?php echo url('person-view.php?id=' . $personId); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>

<?php include INCLUDES_PATH . '/footer.php'; ?>
