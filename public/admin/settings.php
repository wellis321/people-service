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

$success = '';
$error   = '';

// ── Handle POST ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::validateToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $action = $_POST['action'] ?? '';

        // Create API key
        if ($action === 'create_api_key') {
            $name = trim($_POST['key_name'] ?? '');
            if (!$name) {
                $error = 'Key name is required.';
            } else {
                $rawKey = bin2hex(random_bytes(32));
                $hash   = hash('sha256', $rawKey);
                $stmt   = $db->prepare('INSERT INTO api_keys (organisation_id, name, key_hash) VALUES (:org, :name, :hash)');
                $stmt->execute([':org' => $organisationId, ':name' => $name, ':hash' => $hash]);
                $success = 'API key created. Copy it now — it will not be shown again: <code style="background:#d1fae5;padding:.1rem .4rem;border-radius:.25rem;font-size:.85rem">' . htmlspecialchars($rawKey) . '</code>';
            }
        }

        // Revoke API key
        if ($action === 'revoke_api_key') {
            $keyId = (int) ($_POST['key_id'] ?? 0);
            $stmt  = $db->prepare('UPDATE api_keys SET is_active = 0 WHERE id = :id AND organisation_id = :org');
            $stmt->execute([':id' => $keyId, ':org' => $organisationId]);
            $success = 'API key revoked.';
        }
    }
}

// ── Load data ─────────────────────────────────────────────────────────────────
$apiKeys = $db->prepare('SELECT * FROM api_keys WHERE organisation_id = :org ORDER BY created_at DESC');
$apiKeys->execute([':org' => $organisationId]);
$apiKeys = $apiKeys->fetchAll(PDO::FETCH_ASSOC);

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

<!-- API Keys -->
<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-key"></i> API Keys</h2>
    </div>
    <p class="text-light text-small" style="margin-bottom:1rem">
        API keys allow other services (Team Service, PMS) to query people data from this service.
    </p>

    <?php if (empty($apiKeys)): ?>
        <p class="text-light">No API keys yet.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Name</th><th>Status</th><th>Last used</th><th>Created</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($apiKeys as $k): ?>
                <tr>
                    <td><?php echo htmlspecialchars($k['name']); ?></td>
                    <td>
                        <?php if ($k['is_active']): ?>
                            <span class="badge badge-green">Active</span>
                        <?php else: ?>
                            <span class="badge badge-grey">Revoked</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-light"><?php echo $k['last_used_at'] ? date('d M Y H:i', strtotime($k['last_used_at'])) : 'Never'; ?></td>
                    <td class="text-light"><?php echo date('d M Y', strtotime($k['created_at'])); ?></td>
                    <td>
                        <?php if ($k['is_active']): ?>
                        <form method="POST" action="" style="display:inline" onsubmit="return confirm('Revoke this API key?')">
                            <?php echo CSRF::getTokenField(CSRF_TOKEN_NAME); ?>
                            <input type="hidden" name="action"  value="revoke_api_key">
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
    <form method="POST" action="" style="display:flex;gap:0.75rem;align-items:flex-end;flex-wrap:wrap">
        <?php echo CSRF::getTokenField(CSRF_TOKEN_NAME); ?>
        <input type="hidden" name="action" value="create_api_key">
        <div class="form-group" style="margin-bottom:0;flex:1;min-width:200px">
            <label for="key_name">Key name</label>
            <input type="text" id="key_name" name="key_name" class="form-control" placeholder="e.g. Team Service" required>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Create Key
        </button>
    </form>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
