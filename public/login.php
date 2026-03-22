<?php
require_once dirname(__DIR__) . '/config/config.php';

// Redirect if already logged in
if (Auth::isLoggedIn()) {
    header('Location: ' . url('index.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $result = Auth::login($email, $password);
        if ($result === true) {
            header('Location: ' . url('index.php'));
            exit;
        }
        $error = 'Invalid email address or password.';
    } else {
        $error = 'Please enter your email address and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #7c3aed; --primary-dark: #6d28d9;
            --border: #e5e7eb; --text: #111827; --bg: #f9fafb;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-box {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
        }
        .login-brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-brand i {
            font-size: 2.5rem;
            color: var(--primary);
        }
        .login-brand h1 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-top: 0.5rem;
            color: var(--text);
        }
        .login-brand p {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        .form-group { margin-bottom: 1rem; }
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.375rem;
        }
        .form-control {
            width: 100%;
            padding: 0.625rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124,58,237,.12);
        }
        .btn {
            width: 100%;
            padding: 0.625rem 1rem;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: background 0.15s;
        }
        .btn:hover { background: var(--primary-dark); }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            border-radius: 0.375rem;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-brand">
            <i class="fa-solid fa-heart-pulse"></i>
            <h1><?php echo APP_NAME; ?></h1>
            <p>Sign in to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       required autofocus autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       required autocomplete="current-password">
            </div>
            <button type="submit" class="btn">
                <i class="fa-solid fa-right-to-bracket"></i> Sign in
            </button>
        </form>
    </div>
</body>
</html>
