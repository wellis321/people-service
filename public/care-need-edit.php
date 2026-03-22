<?php
require_once dirname(__DIR__) . '/config/config.php';

if (!Auth::isLoggedIn()) {
    header('Location: ' . url('login.php'));
    exit;
}

$organisationId = (int) Auth::getOrganisationId();
$needId         = (int) ($_GET['id']        ?? 0);
$personId       = (int) ($_GET['person_id'] ?? 0);

$need   = $needId   ? CareNeed::findById($needId, $organisationId) : null;
$person = $personId ? Person::findById($personId, $organisationId) : null;
if (!$need || !$person) {
    header('Location: ' . url('people.php'));
    exit;
}

$errors = [];
$data   = $need;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $data = array_merge($data, array_map('trim', $_POST));
        if (empty($data['category']))    $errors[] = 'Category is required.';
        if (empty($data['description'])) $errors[] = 'Description is required.';

        if (empty($errors)) {
            CareNeed::update($needId, $organisationId, $data);
            header('Location: ' . url('person-view.php?id=' . $personId . '#section-care-needs'));
            exit;
        }
    }
}

$pageTitle = 'Edit Care Need';
include INCLUDES_PATH . '/header.php';
?>

<div class="page-header">
    <h1><i class="fa-solid fa-hand-holding-medical"></i> Edit Care Need</h1>
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
            <label for="category">Category <span style="color:var(--danger)">*</span></label>
            <input type="text" id="category" name="category" class="form-control" required
                   value="<?php echo htmlspecialchars($data['category']); ?>">
        </div>
        <div class="form-group">
            <label for="description">Description <span style="color:var(--danger)">*</span></label>
            <textarea id="description" name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($data['description']); ?></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 1.25rem">
            <div class="form-group">
                <label for="severity">Severity</label>
                <select id="severity" name="severity" class="form-control">
                    <option value="">— not set —</option>
                    <option value="low"    <?php echo ($data['severity'] ?? '') === 'low'    ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo ($data['severity'] ?? '') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="high"   <?php echo ($data['severity'] ?? '') === 'high'   ? 'selected' : ''; ?>>High</option>
                </select>
            </div>
            <div class="form-group">
                <label for="review_date">Review date</label>
                <input type="date" id="review_date" name="review_date" class="form-control"
                       value="<?php echo htmlspecialchars($data['review_date'] ?? ''); ?>">
            </div>
        </div>
        <div class="d-flex gap-1 mt-2">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Changes</button>
            <a href="<?php echo url('person-view.php?id=' . $personId); ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>

<?php include INCLUDES_PATH . '/footer.php'; ?>
