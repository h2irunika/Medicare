<?php $current = $_GET['page'] ?? 'home'; $role = user_role(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f766e">
    <title><?= isset($title) ? e($title) . ' | ' : '' ?>MediCare Clinic</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-shell">
<header class="topbar">
    <div class="container topbar-inner">
        <a class="logo" href="<?= url('home') ?>">
            <span class="logo-mark" aria-hidden="true">✚</span>
            <span><span class="logo-name">MediCare</span><small>Clinic Management</small></span>
        </a>
        <button class="nav-toggle" type="button" aria-label="Open navigation" aria-expanded="false">☰</button>
        <nav id="mainNav">
            <a href="<?= url('home') ?>" class="<?= $current === 'home' ? 'active' : '' ?>">⌂ Home</a>
            <?php if ($role === 'Patient'): ?>
                <a href="<?= url('patient/dashboard') ?>" class="<?= $current === 'patient/dashboard' ? 'active' : '' ?>">▦ Dashboard</a>
                <a href="<?= url('patient/doctors') ?>" class="<?= $current === 'patient/doctors' ? 'active' : '' ?>">♟ Doctors</a>
                <a href="<?= url('patient/book') ?>" class="<?= $current === 'patient/book' ? 'active' : '' ?>">＋ Book</a>
                <a href="<?= url('patient/appointments') ?>" class="<?= $current === 'patient/appointments' ? 'active' : '' ?>">◷ Appointments</a>
                <a href="<?= url('patient/history') ?>" class="<?= $current === 'patient/history' ? 'active' : '' ?>">▤ History</a>
                <a href="<?= url('patient/prescriptions') ?>" class="<?= $current === 'patient/prescriptions' ? 'active' : '' ?>">Rx Prescriptions</a>
                <a href="<?= url('patient/bills') ?>" class="<?= $current === 'patient/bills' ? 'active' : '' ?>">▣ Bills</a>
                <a href="<?= url('patient/profile') ?>" class="<?= $current === 'patient/profile' ? 'active' : '' ?>">⚙ Profile</a>
            <?php elseif ($role === 'Doctor'): ?>
                <a href="<?= url('doctor/dashboard') ?>" class="<?= $current === 'doctor/dashboard' ? 'active' : '' ?>">▦ Dashboard</a>
                <a href="<?= url('doctor/appointments') ?>" class="<?= $current === 'doctor/appointments' ? 'active' : '' ?>">◷ Appointments</a>
                <a href="<?= url('doctor/schedule') ?>" class="<?= $current === 'doctor/schedule' ? 'active' : '' ?>">◫ Schedule</a>
                <a href="<?= url('doctor/records') ?>" class="<?= $current === 'doctor/records' ? 'active' : '' ?>">▤ Records</a>
                <a href="<?= url('doctor/profile') ?>" class="<?= $current === 'doctor/profile' ? 'active' : '' ?>">⚙ Profile</a>
            <?php elseif ($role === 'Receptionist'): ?>
                <a href="<?= url('staff/queue') ?>" class="<?= $current === 'staff/queue' ? 'active' : '' ?>">▣ Queue</a>
                <a href="<?= url('staff/appointments') ?>" class="<?= $current === 'staff/appointments' ? 'active' : '' ?>">◷ Appointments</a>
                <a href="<?= url('staff/patients') ?>" class="<?= $current === 'staff/patients' ? 'active' : '' ?>">♙ Patients</a>
                <a href="<?= url('staff/doctors') ?>" class="<?= $current === 'staff/doctors' ? 'active' : '' ?>">♟ Doctors</a>
                <a href="<?= url('staff/book') ?>" class="<?= $current === 'staff/book' ? 'active' : '' ?>">＋ Book</a>
                <a href="<?= url('staff/records') ?>" class="<?= strpos($current, 'staff/record') === 0 ? 'active' : '' ?>">▤ Records</a>
                <a href="<?= url('staff/medicines') ?>" class="<?= $current === 'staff/medicines' ? 'active' : '' ?>">Rx Medicines</a>
                <a href="<?= url('staff/payments') ?>" class="<?= $current === 'staff/payments' ? 'active' : '' ?>">▣ Payments</a>
                <a href="<?= url('staff/reports') ?>" class="<?= $current === 'staff/reports' ? 'active' : '' ?>">▥ Reports</a>
                <a href="<?= url('staff/users') ?>" class="<?= $current === 'staff/users' ? 'active' : '' ?>">♙ Users</a>
            <?php endif; ?>
            <span class="nav-divider"></span>
            <button class="theme-toggle" type="button" title="Toggle dark mode" aria-label="Toggle dark mode">☾</button>
            <?php if (is_logged_in()): ?>
                <span class="who"><span class="avatar-dot">●</span><?= e(user_name()) ?></span>
                <a class="logout" href="<?= url('logout') ?>">↪ Log out</a>
            <?php else: ?>
                <a href="<?= url('login&role=Patient') ?>" class="<?= $current === 'login' ? 'active' : '' ?>">Log in</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">
<?php $flash = get_message(); ?>
<?php if ($flash): ?>
    <p class="flash <?= $flash['type'] === 'error' ? 'flash-error' : 'flash-ok' ?>"><?= e($flash['text']) ?></p>
<?php endif; ?>
