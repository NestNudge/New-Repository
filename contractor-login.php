<?php
session_start();

/*
|--------------------------------------------------------------------------
| NestNudge Contractor Login
|--------------------------------------------------------------------------
| Update these database settings to match your environment.
| This file expects a contractors table with columns similar to:
| id, company, email, password_hash, plan, active
|--------------------------------------------------------------------------
*/

$dbHost = "localhost";
$dbName = "leadbizo_db";
$dbUser = "root";
$dbPass = "";
$dashboardPath = "/partner-dashboard.php";

$error = "";
$success = "";
$email = "";

if (isset($_GET["logged_out"]) && $_GET["logged_out"] === "1") {
    $success = "You have been logged out successfully.";
}

if (isset($_SESSION["contractor"])) {
    header("Location: " . $dashboardPath);
    exit;
}

function safe_value($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($email === "" || $password === "") {
        $error = "Please enter both your email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $sql = "
                SELECT id, company, email, password_hash, plan, active
                FROM contractors
                WHERE email = :email
                LIMIT 1
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(["email" => $email]);
            $contractor = $stmt->fetch();

            if (!$contractor) {
                $error = "We could not find an account with that email.";
            } elseif ((int)($contractor["active"] ?? 1) !== 1) {
                $error = "Your account is not active yet. Please contact support if you believe this is a mistake.";
            } elseif (!password_verify($password, $contractor["password_hash"])) {
                $error = "Your password is incorrect. Please try again.";
            } else {
                session_regenerate_id(true);

                $_SESSION["contractor"] = [
                    "id" => $contractor["id"],
                    "company" => $contractor["company"],
                    "email" => $contractor["email"],
                    "plan" => $contractor["plan"] ?: "Starter"
                ];

                header("Location: " . $dashboardPath);
                exit;
            }
        } catch (Throwable $e) {
            $error = "There was a problem signing you in. Please verify your database connection and contractor table settings.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="/nestnudge.ico">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Partner Login | NestNudge</title>
  <meta name="description" content="Log in to your NestNudge partner dashboard to review leads, manage your plan, and claim opportunities.">
  <meta name="robots" content="noindex,follow">
  <link rel="stylesheet" href="style.css">

  <style>
    :root {
      --nn-green: #2e7d32;
      --nn-green-dark: #1f6b3c;
      --nn-ink: #111827;
      --nn-text: #4b5563;
      --nn-muted: #6b7280;
      --nn-border: #dfe6df;
      --nn-bg: #f5f7f6;
      --nn-card: #ffffff;
      --nn-shadow: 0 18px 42px rgba(15, 23, 42, 0.10);
      --nn-radius-lg: 24px;
      --nn-radius-md: 18px;
      --nn-radius-sm: 12px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      background: var(--nn-bg);
      color: var(--nn-ink);
      font-family: Arial, sans-serif;
    }

    .login-shell {
      max-width: 1220px;
      margin: 0 auto;
      padding: 34px 20px 60px;
    }

    .login-wrap {
      display: grid;
      grid-template-columns: 1.05fr 0.95fr;
      gap: 28px;
      align-items: stretch;
    }

    .login-brand {
      background: linear-gradient(135deg, var(--nn-green-dark), var(--nn-green));
      color: #fff;
      border-radius: var(--nn-radius-lg);
      padding: 38px 34px;
      box-shadow: var(--nn-shadow);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 620px;
    }

    .login-brand h1 {
      margin: 0 0 14px;
      font-size: 2.6rem;
      line-height: 1.08;
    }

    .login-brand p {
      margin: 0 0 16px;
      line-height: 1.7;
      color: rgba(255,255,255,0.94);
      max-width: 700px;
    }

    .login-brand ul {
      margin: 0;
      padding-left: 18px;
      line-height: 1.9;
    }

    .login-brand li {
      color: rgba(255,255,255,0.96);
    }

    .brand-stats {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 14px;
      margin-top: 28px;
    }

    .brand-stat {
      background: rgba(255,255,255,0.12);
      border: 1px solid rgba(255,255,255,0.18);
      border-radius: 16px;
      padding: 16px 14px;
    }

    .brand-stat strong {
      display: block;
      font-size: 1.3rem;
      margin-bottom: 4px;
    }

    .brand-stat span {
      display: block;
      color: rgba(255,255,255,0.88);
      line-height: 1.45;
      font-size: 0.92rem;
    }

    .login-panel {
      background: var(--nn-card);
      border-radius: var(--nn-radius-lg);
      box-shadow: var(--nn-shadow);
      padding: 34px 30px;
      border: 1px solid #eef2ef;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-kicker {
      display: inline-block;
      font-size: 0.82rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--nn-green);
      margin-bottom: 10px;
    }

    .login-panel h2 {
      margin: 0 0 10px;
      font-size: 2rem;
      line-height: 1.12;
    }

    .login-panel p.lead {
      margin: 0 0 24px;
      color: var(--nn-text);
      line-height: 1.65;
    }

    .status-message,
    .error-message {
      border-radius: 12px;
      padding: 13px 14px;
      margin-bottom: 16px;
      font-weight: 600;
      line-height: 1.5;
    }

    .status-message {
      background: #edf8ee;
      border: 1px solid #cfe7d2;
      color: #1f6b3c;
    }

    .error-message {
      background: #fff3f2;
      border: 1px solid #f3d0cc;
      color: #9f2d20;
    }

    .login-form {
      display: grid;
      gap: 14px;
    }

    .form-group {
      display: grid;
      gap: 7px;
    }

    .form-group label {
      font-size: 0.94rem;
      font-weight: 700;
      color: var(--nn-ink);
    }

    .form-group input {
      width: 100%;
      border: 1px solid var(--nn-border);
      border-radius: 12px;
      padding: 14px 14px;
      font-size: 0.98rem;
      color: var(--nn-ink);
      background: #fff;
      outline: none;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-group input:focus {
      border-color: var(--nn-green);
      box-shadow: 0 0 0 4px rgba(46, 125, 50, 0.10);
    }

    .login-actions {
      display: grid;
      gap: 12px;
      margin-top: 8px;
    }

    .btn-primary,
    .btn-secondary {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 50px;
      padding: 0 18px;
      border: none;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 800;
      text-decoration: none;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    }

    .btn-primary {
      background: var(--nn-green);
      color: #fff;
      box-shadow: 0 10px 24px rgba(46, 125, 50, 0.18);
    }

    .btn-primary:hover,
    .btn-secondary:hover {
      transform: translateY(-2px);
    }

    .btn-secondary {
      background: #edf2f7;
      color: #111827;
    }

    .login-help {
      margin-top: 20px;
      padding-top: 18px;
      border-top: 1px solid #eef2ef;
      color: var(--nn-muted);
      line-height: 1.65;
      font-size: 0.94rem;
    }

    .login-help a {
      color: var(--nn-green);
      font-weight: 700;
      text-decoration: none;
    }

    .micro-note {
      margin-top: 14px;
      color: var(--nn-muted);
      font-size: 0.9rem;
      line-height: 1.55;
    }

    @media (max-width: 980px) {
      .login-wrap {
        grid-template-columns: 1fr;
      }

      .login-brand {
        min-height: auto;
      }

      .brand-stats {
        grid-template-columns: 1fr;
      }

      .login-brand h1 {
        font-size: 2.15rem;
      }

      .login-panel h2 {
        font-size: 1.75rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <nav class="main-nav">
      <a href="/">Home</a>
      <a href="/partners.html">For Partners</a>
      <a href="/roof-quotes-miami-fort-lauderdale.html">Roof Quotes</a>
      <a href="/offers.html">Offers</a>
      <a class="cta-button" href="/contractor-signup.php">Apply Now</a>
    </nav>
  </header>

  <div class="login-shell">
    <div class="login-wrap">
      <section class="login-brand">
        <div>
          <h1>Welcome back to your NestNudge partner account</h1>
          <p>
            Log in to access your partner dashboard, review matched homeowner opportunities, and manage the next step in your growth with NestNudge.
          </p>
          <ul>
            <li>Review matched leads faster</li>
            <li>Claim exclusive opportunities</li>
            <li>Track your current plan and account access</li>
            <li>Stay focused on the ZIP-based opportunities that fit your business</li>
          </ul>
        </div>

        <div class="brand-stats">
          <div class="brand-stat">
            <strong>$29</strong>
            <span>Starter test entry for low-risk onboarding</span>
          </div>
          <div class="brand-stat">
            <strong>$99/mo</strong>
            <span>Monthly Growth for steady opportunity flow</span>
          </div>
          <div class="brand-stat">
            <strong>$299 / $499</strong>
            <span>Quarterly and Yearly value for longer-term growth</span>
          </div>
        </div>
      </section>

      <section class="login-panel">
        <span class="login-kicker">Partner Login</span>
        <h2>Sign in to your dashboard</h2>
        <p class="lead">
          Enter the email and password tied to your partner account. Once signed in, you will be taken directly to your NestNudge partner dashboard.
        </p>

        <?php if ($success !== ""): ?>
          <div class="status-message"><?php echo safe_value($success); ?></div>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
          <div class="error-message"><?php echo safe_value($error); ?></div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="">
          <div class="form-group">
            <label for="email">Email Address</label>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="you@company.com"
              value="<?php echo safe_value($email); ?>"
              required
            >
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Enter your password"
              required
            >
          </div>

          <div class="login-actions">
            <button type="submit" class="btn-primary">Log In</button>
            <a href="/contractor-signup.php" class="btn-secondary">Apply to Become a Partner</a>
          </div>
        </form>

        <p class="micro-note">
          After login, approved partners are redirected to <strong>/partner-dashboard.php</strong>.
        </p>

        <div class="login-help">
          Need help getting access? Visit the <a href="/partners.html">partner overview page</a> or start a new application through <a href="/contractor-signup.php">Apply Now</a>.
        </div>
      </section>
    </div>
  </div>
</body>
</html>
