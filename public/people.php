<?php
require_once dirname(__DIR__) . '/config/config.php';

if (!Auth::isLoggedIn()) {
    header('Location: ' . url('login.php'));
    exit;
}

$organisationId = (int) Auth::getOrganisationId();

$filters = [
    'status' => $_GET['status'] ?? '',
    'search' => trim($_GET['search'] ?? ''),
];

$people = Person::findAll($organisationId, $filters);
$counts = Person::countByStatus($organisationId);

$pageTitle = 'People';
include INCLUDES_PATH . '/header.php';
?>

<div class="page-header">
    <h1><i class="fa-solid fa-users"></i> People</h1>
    <a href="<?php echo url('person-create.php'); ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add Person
    </a>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:1.5rem">
    <form method="GET" action="" class="d-flex gap-2 align-center" style="flex-wrap:wrap">
        <input type="text" name="search" class="form-control" style="max-width:260px"
               placeholder="Search by name or NHS number…"
               value="<?php echo htmlspecialchars($filters['search']); ?>">

        <select name="status" class="form-control" style="max-width:160px">
            <option value="">All statuses (<?php echo array_sum($counts); ?>)</option>
            <option value="active"   <?php echo $filters['status'] === 'active'   ? 'selected' : ''; ?>>Active (<?php echo $counts['active']; ?>)</option>
            <option value="inactive" <?php echo $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive (<?php echo $counts['inactive']; ?>)</option>
            <option value="archived" <?php echo $filters['status'] === 'archived' ? 'selected' : ''; ?>>Archived (<?php echo $counts['archived']; ?>)</option>
        </select>

        <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-filter"></i> Filter</button>
        <?php if ($filters['search'] || $filters['status']): ?>
            <a href="<?php echo url('people.php'); ?>" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Results -->
<div class="card">
    <?php if (empty($people)): ?>
        <p class="text-light" style="text-align:center;padding:2rem">
            <?php if ($filters['search'] || $filters['status']): ?>
                No people match those filters.
            <?php else: ?>
                No people have been added yet. <a href="<?php echo url('person-create.php'); ?>">Add the first person.</a>
            <?php endif; ?>
        </p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>NHS No.</th>
                    <th>Status</th>
                    <th>Support start</th>
                    <th>Location</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($people as $p): ?>
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
                    <td class="text-light"><?php echo $p['nhs_number'] ? htmlspecialchars($p['nhs_number']) : '—'; ?></td>
                    <td>
                        <?php
                        $badge = match($p['status']) { 'active' => 'badge-green', 'inactive' => 'badge-amber', default => 'badge-grey' };
                        echo '<span class="badge ' . $badge . '">' . htmlspecialchars(ucfirst($p['status'])) . '</span>';
                        ?>
                    </td>
                    <td class="text-light"><?php echo $p['support_start'] ? date('d M Y', strtotime($p['support_start'])) : '—'; ?></td>
                    <td class="text-light"><?php echo $p['city'] ? htmlspecialchars($p['city']) : '—'; ?></td>
                    <td>
                        <a href="<?php echo url('person-view.php?id=' . $p['id']); ?>" class="btn btn-secondary btn-sm">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p class="text-small text-light mt-2"><?php echo count($people); ?> result<?php echo count($people) !== 1 ? 's' : ''; ?></p>
    <?php endif; ?>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
