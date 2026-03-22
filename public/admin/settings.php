<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

if (!Auth::isLoggedIn()) {
    header('Location: ' . url('login.php'));
    exit;
}

if (!RBAC::isOrganisationAdmin() && !RBAC::isSuperAdmin()) {
    header('Location: ' . url('index.php'));
    exit;
}

$organisationId = (int) Auth::getOrganisationId();
$db = Database::getConnection();

$success    = '';
$error      = '';
$testResult = null;
$testTarget = '';

// ── Handle POST ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';

        // ── API keys ──────────────────────────────────────────────────────────
        if ($action === 'create_api_key') {
            $name = trim($_POST['key_name'] ?? '');
            if (!$name) {
                $error = 'Key name is required.';
            } else {
                $rawKey = bin2hex(random_bytes(32));
                $hash   = hash('sha256', $rawKey);
                $db->prepare('INSERT INTO api_keys (organisation_id, name, key_hash) VALUES (:org, :name, :hash)')
                   ->execute([':org' => $organisationId, ':name' => $name, ':hash' => $hash]);
                $success = 'API key created. Copy it now — it will not be shown again: <code style="background:#d1fae5;padding:.1rem .4rem;border-radius:.25rem;font-size:.85rem">' . htmlspecialchars($rawKey) . '</code>';
            }
        }

        if ($action === 'revoke_api_key') {
            $db->prepare('UPDATE api_keys SET is_active = 0 WHERE id = :id AND organisation_id = :org')
               ->execute([':id' => (int)($_POST['key_id'] ?? 0), ':org' => $organisationId]);
            $success = 'API key revoked.';
        }

        // ── Staff Service (PMS) integration ───────────────────────────────────
        if ($action === 'save_staff_service') {
            $url    = trim($_POST['staff_service_url']     ?? '');
            $apiKey = trim($_POST['staff_service_api_key'] ?? '');
            $existing = OrgSettings::get($organisationId, 'staff_service_api_key');
            if ($apiKey === '' && $existing !== '') $apiKey = $existing;
            OrgSettings::setMany($organisationId, [
                'staff_service_url'     => $url,
                'staff_service_api_key' => $apiKey,
            ]);
            $success = 'Staff Service settings saved.';
        }

        if ($action === 'test_staff_service') {
            $url    = trim($_POST['staff_service_url']     ?? '');
            $apiKey = trim($_POST['staff_service_api_key'] ?? '');
            $testTarget = 'staff';
            $testResult = ($url && $apiKey)
                ? (StaffServiceClient::testConnection($url, $apiKey) ? 'success' : 'fail')
                : 'missing';
        }

        if ($action === 'clear_staff_service') {
            OrgSettings::setMany($organisationId, ['staff_service_url' => '', 'staff_service_api_key' => '']);
            $success = 'Staff Service disconnected.';
        }
    }
}

// ── Load current settings ─────────────────────────────────────────────────────
$apiKeys = $db->prepare('SELECT * FROM api_keys WHERE organisation_id = :org ORDER BY created_at DESC');
$apiKeys->execute([':org' => $organisationId]);
$apiKeys = $apiKeys->fetchAll(PDO::FETCH_ASSOC);

$staffUrl    = OrgSettings::get($organisationId, 'staff_service_url',     getenv('STAFF_SERVICE_URL')     ?: '');
$staffKeySet = OrgSettings::get($organisationId, 'staff_service_api_key', getenv('STAFF_SERVICE_API_KEY') ?: '') !== '';

$pageTitle = 'Settings';
include INCLUDES_PATH . '/header.php';
?>

<div class="page-header">
    <h1><i class="fa-solid fa-gear"></i> Settings</h1>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($testResult === 'success'): ?>
<div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> Connection successful — <?php echo $testTarget === 'staff' ? 'Staff Service' : 'Team Service'; ?> is reachable.</div>
<?php elseif ($testResult === 'fail'): ?>
<div class="alert alert-error"><i class="fa-solid fa-times-circle"></i> Connection failed — check the URL and API key.</div>
<?php elseif ($testResult === 'missing'): ?>
<div class="alert alert-warning"><i class="fa-solid fa-exclamation-triangle"></i> Please enter both a URL and API key to test.</div>
<?php endif; ?>

<!-- ── Staff Service Integration ─────────────────────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-users"></i> Staff Service (PMS) Integration</h2>
        <?php if ($staffUrl && $staffKeySet): ?>
            <span class="badge badge-green">Connected</span>
        <?php else: ?>
            <span class="badge badge-grey">Not configured</span>
        <?php endif; ?>
    </div>
    <p class="text-light text-small" style="margin-bottom:1rem">
        Connect to the Staff Service (PMS) so you can look up and assign staff as key workers.
        Settings are stored per organisation.
    </p>
    <form method="POST" action="">
        <?php echo CSRF::tokenField(); ?>
        <input type="hidden" name="action" value="save_staff_service">
        <div class="form-group">
            <label for="staff_service_url">Staff Service URL</label>
            <input type="url" id="staff_service_url" name="staff_service_url" class="form-control"
                   placeholder="https://your-pms.hostingersite.com"
                   value="<?php echo htmlspecialchars($staffUrl); ?>">
        </div>
        <div class="form-group">
            <label for="staff_service_api_key">API Key</label>
            <input type="password" id="staff_service_api_key" name="staff_service_api_key" class="form-control"
                   placeholder="<?php echo $staffKeySet ? '(key saved — leave blank to keep)' : 'Paste API key from PMS → Admin → API Keys'; ?>">
            <div class="form-hint">Generated in <strong>PMS → Admin → API Keys</strong>. <?php if ($staffKeySet): ?>Leave blank to keep the existing key.<?php endif; ?></div>
        </div>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1.25rem">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save</button>
            <button type="submit" form="test-staff-form" class="btn btn-secondary"><i class="fa-solid fa-plug"></i> Test Connection</button>
            <?php if ($staffUrl || $staffKeySet): ?>
            <button type="submit" form="clear-staff-form" class="btn btn-danger"
                    onclick="return confirm('Disconnect the Staff Service?')">
                <i class="fa-solid fa-unlink"></i> Disconnect
            </button>
            <?php endif; ?>
        </div>
    </form>
    <form id="test-staff-form" method="POST" action="" style="display:none">
        <?php echo CSRF::tokenField(); ?>
        <input type="hidden" name="action" value="test_staff_service">
        <input type="hidden" name="staff_service_url" id="test_staff_url" value="<?php echo htmlspecialchars($staffUrl); ?>">
        <input type="hidden" name="staff_service_api_key" id="test_staff_key" value="">
    </form>
    <form id="clear-staff-form" method="POST" action="" style="display:none">
        <?php echo CSRF::tokenField(); ?>
        <input type="hidden" name="action" value="clear_staff_service">
    </form>
</div>

<script>
document.querySelector('[form="test-staff-form"]').addEventListener('click', function() {
    document.getElementById('test_staff_url').value = document.getElementById('staff_service_url').value;
    document.getElementById('test_staff_key').value = document.getElementById('staff_service_api_key').value;
});
</script>

<!-- ── API Keys (incoming) ───────────────────────────────────────────────── -->
<div class="card mt-3">
    <div class="card-header">
        <h2><i class="fa-solid fa-key"></i> API Keys</h2>
    </div>
    <p class="text-light text-small" style="margin-bottom:1rem">
        API keys allow other services to query people data from this service.
    </p>
    <?php if (empty($apiKeys)): ?>
        <p class="text-light">No API keys yet.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Status</th><th>Last used</th><th>Created</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($apiKeys as $k): ?>
                <tr>
                    <td><?php echo htmlspecialchars($k['name']); ?></td>
                    <td><?php echo $k['is_active'] ? '<span class="badge badge-green">Active</span>' : '<span class="badge badge-grey">Revoked</span>'; ?></td>
                    <td class="text-light"><?php echo $k['last_used_at'] ? date('d M Y H:i', strtotime($k['last_used_at'])) : 'Never'; ?></td>
                    <td class="text-light"><?php echo date('d M Y', strtotime($k['created_at'])); ?></td>
                    <td>
                        <?php if ($k['is_active']): ?>
                        <form method="POST" action="" style="display:inline" onsubmit="return confirm('Revoke this key?')">
                            <?php echo CSRF::tokenField(); ?>
                            <input type="hidden" name="action" value="revoke_api_key">
                            <input type="hidden" name="key_id" value="<?php echo $k['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Revoke</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    <hr style="margin:1.5rem 0;border-color:var(--border)">
    <h3 style="font-size:.9rem;font-weight:600;margin-bottom:1rem">Create New API Key</h3>
    <form method="POST" action="" style="display:flex;gap:.75rem;align-items:flex-end;flex-wrap:wrap">
        <?php echo CSRF::tokenField(); ?>
        <input type="hidden" name="action" value="create_api_key">
        <div class="form-group" style="margin-bottom:0;flex:1;min-width:200px">
            <label for="key_name">Key name</label>
            <input type="text" id="key_name" name="key_name" class="form-control" placeholder="e.g. Team Service" required>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create Key</button>
    </form>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
