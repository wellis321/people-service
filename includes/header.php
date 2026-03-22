<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'People Service'); ?> — <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:      #7c3aed;
            --primary-dark: #6d28d9;
            --success:      #10b981;
            --warning:      #f59e0b;
            --danger:       #ef4444;
            --text:         #111827;
            --text-light:   #6b7280;
            --border:       #e5e7eb;
            --bg:           #f9fafb;
            --bg-white:     #ffffff;
            --nav-bg:       #1e1b2e;
            --nav-text:     #c4b5fd;
            --nav-hover:    #2d2547;
            --nav-active:   #7c3aed;
            --radius:       0.5rem;
            --shadow:       0 1px 3px rgba(0,0,0,.1);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Nav ──────────────────────────────────────────────────────────── */
        nav {
            background: var(--nav-bg);
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            gap: 0;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,.2);
        }

        .nav-brand {
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-right: 2rem;
            white-space: nowrap;
        }

        .nav-brand i { color: var(--nav-active); }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            flex: 1;
        }

        .nav-links a {
            color: var(--nav-text);
            text-decoration: none;
            padding: 0.5rem 0.875rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            transition: background 0.15s, color 0.15s;
        }

        .nav-links a:hover   { background: var(--nav-hover); color: #fff; }
        .nav-links a.active  { background: var(--nav-active); color: #fff; }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: auto;
        }

        .nav-user {
            color: var(--nav-text);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .nav-right a {
            color: var(--nav-text);
            text-decoration: none;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: background 0.15s;
        }

        .nav-right a:hover { background: var(--nav-hover); color: #fff; }

        /* ── Main layout ──────────────────────────────────────────────────── */
        main {
            flex: 1;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* ── Page header ──────────────────────────────────────────────────── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* ── Cards ────────────────────────────────────────────────────────── */
        .card {
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .card + .card { margin-top: 1.5rem; }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border);
        }

        .card-header h2 {
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* ── Buttons ──────────────────────────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: background 0.15s, border-color 0.15s, color 0.15s;
            white-space: nowrap;
        }

        .btn-primary   { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: #fff; color: var(--text); border-color: var(--border); }
        .btn-secondary:hover { background: var(--bg); }
        .btn-danger    { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-success   { background: var(--success); color: #fff; }
        .btn-sm        { padding: 0.3rem 0.65rem; font-size: 0.8rem; }

        /* ── Alerts ───────────────────────────────────────────────────────── */
        .alert {
            padding: 0.875rem 1rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert-info    { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

        /* ── Forms ────────────────────────────────────────────────────────── */
        .form-group { margin-bottom: 1rem; }
        .form-group label {
            display: block;
            font-weight: 500;
            font-size: 0.875rem;
            margin-bottom: 0.375rem;
            color: var(--text);
        }
        .form-control {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.375rem;
            font-size: 0.875rem;
            color: var(--text);
            background: #fff;
            transition: border-color 0.15s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124,58,237,.12);
        }
        .form-hint {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }

        /* ── Grid helper ──────────────────────────────────────────────────── */
        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1.25rem; }
        .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0 1.25rem; }

        @media (max-width: 640px) {
            .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
        }

        /* ── Badges ───────────────────────────────────────────────────────── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge-blue    { background: #ede9fe; color: #5b21b6; }
        .badge-green   { background: #d1fae5; color: #065f46; }
        .badge-amber   { background: #fef3c7; color: #92400e; }
        .badge-red     { background: #fee2e2; color: #991b1b; }
        .badge-grey    { background: #f3f4f6; color: #374151; }

        /* ── Tables ───────────────────────────────────────────────────────── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        th {
            text-align: left;
            padding: 0.625rem 0.75rem;
            border-bottom: 2px solid var(--border);
            color: var(--text-light);
            font-weight: 600;
            white-space: nowrap;
        }
        td {
            padding: 0.625rem 0.75rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--bg); }

        /* ── Stat cards ───────────────────────────────────────────────────── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
        }
        .stat-card .stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* ── Person avatar ────────────────────────────────────────────────── */
        .person-avatar {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        /* ── Misc utilities ───────────────────────────────────────────────── */
        .text-light  { color: var(--text-light); }
        .text-small  { font-size: 0.8rem; }
        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 1.5rem; }
        .d-flex { display: flex; }
        .align-center { align-items: center; }
        .gap-1 { gap: 0.5rem; }
        .gap-2 { gap: 1rem; }

        @media (max-width: 640px) {
            main { padding: 1rem; }
            .nav-links a span { display: none; }
        }
    </style>
</head>
<body>

<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
$isLoggedIn  = Auth::isLoggedIn();
$isAdmin     = $isLoggedIn && (RBAC::isOrganisationAdmin() || RBAC::isSuperAdmin());
?>

<nav>
    <a href="<?php echo url('index.php'); ?>" class="nav-brand">
        <i class="fa-solid fa-heart-pulse"></i>
        <?php echo APP_NAME; ?>
    </a>

    <?php if ($isLoggedIn): ?>
    <div class="nav-links">
        <a href="<?php echo url('index.php'); ?>"
           class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-gauge"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?php echo url('people.php'); ?>"
           class="<?php echo in_array($currentPage, ['people.php','person-view.php','person-edit.php','person-create.php']) ? 'active' : ''; ?>">
            <i class="fa-solid fa-users"></i>
            <span>People</span>
        </a>
        <?php if (RBAC::isSuperAdmin()): ?>
        <a href="<?php echo url('admin/organisations.php'); ?>"
           class="<?php echo $currentPage === 'organisations.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-building"></i>
            <span>Organisations</span>
        </a>
        <?php endif; ?>
        <?php if ($isAdmin): ?>
        <a href="<?php echo url('admin/settings.php'); ?>"
           class="<?php echo $currentDir === 'admin' ? 'active' : ''; ?>">
            <i class="fa-solid fa-gear"></i>
            <span>Settings</span>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="nav-right">
        <?php if ($isLoggedIn): ?>
            <span class="nav-user">
                <i class="fa-solid fa-circle-user"></i>
                <?php $u = Auth::getUser(); echo htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')); ?>
            </span>
            <a href="<?php echo url('logout.php'); ?>">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign out
            </a>
        <?php else: ?>
            <a href="<?php echo url('login.php'); ?>">Sign in</a>
        <?php endif; ?>
    </div>
</nav>

<main>
