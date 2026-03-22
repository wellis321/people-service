<?php
require_once dirname(__DIR__) . '/config/config.php';

if (!Auth::isLoggedIn()) {
    header('Location: ' . url('login.php'));
    exit;
}

$organisationId = (int) Auth::getOrganisationId();
$counts = Person::countByStatus($organisationId);
$total  = array_sum($counts);

// Recent additions
$db     = Database::getConnection();
$stmt   = $db->prepare('SELECT * FROM people WHERE organisation_id = :org ORDER BY created_at DESC LIMIT 5');
$stmt->execute([':org' => $organisationId]);
$recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Dashboard';
include INCLUDES_PATH . '/header.php';
?>

<div class="page-header">
    <h1><i class="fa-solid fa-gauge"></i> Dashboard</h1>
    <a href="<?php echo url('person-create.php'); ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add Person
    </a>
</div>

<!-- Stats -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value"><?php echo $total; ?></div>
        <div class="stat-label">Total people</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:var(--success)"><?php echo $counts['active']; ?></div>
        <div class="stat-label">Active</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:var(--warning)"><?php echo $counts['inactive']; ?></div>
        <div class="stat-label">Inactive</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:var(--text-light)"><?php echo $counts['archived']; ?></div>
        <div class="stat-label">Archived</div>
    </div>
</div>

<!-- Recently added -->
<div class="card">
    <div class="card-header">
        <h2><i class="fa-solid fa-clock-rotate-left"></i> Recently Added</h2>
        <a href="<?php echo url('people.php'); ?>" class="btn btn-secondary btn-sm">View all</a>
    </div>

    <?php if (empty($recent)): ?>
        <p class="text-light">No people have been added yet. <a href="<?php echo url('person-create.php'); ?>">Add the first person.</a></p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Support start</th>
                    <th>Added</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $p): ?>
                <tr>
                    <td>
                        <div class="d-flex align-center gap-1">
                            <span class="person-avatar" style="width:2rem;height:2rem;font-size:.8rem">
                                <?php echo htmlspecialchars(strtoupper(substr($p['first_name'],0,1) . substr($p['last_name'],0,1))); ?>
                            </span>
                            <div>
                                <a href="<?php echo url('person-view.php?id=' . $p['id']); ?>" style="font-weight:500;text-decoration:none;color:inherit">
                                    <?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?>
                                </a>
                                <?php if ($p['preferred_name']): ?>
                                    <div class="text-small text-light"><?php echo htmlspecialchars($p['preferred_name']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php
                        $badge = match($p['status']) { 'active' => 'badge-green', 'inactive' => 'badge-amber', default => 'badge-grey' };
                        echo '<span class="badge ' . $badge . '">' . htmlspecialchars(ucfirst($p['status'])) . '</span>';
                        ?>
                    </td>
                    <td class="text-light"><?php echo $p['support_start'] ? date('d M Y', strtotime($p['support_start'])) : '—'; ?></td>
                    <td class="text-light text-small"><?php echo date('d M Y', strtotime($p['created_at'])); ?></td>
                    <td><a href="<?php echo url('person-view.php?id=' . $p['id']); ?>" class="btn btn-secondary btn-sm">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
