<div class="dashboard-welcome welcome-patient">
    <div><span class="eyebrow">Patient portal</span><h1>Good day, <?= e(user_name()) ?> 👋</h1><p class="muted">Manage your appointments, medical history, prescriptions and bills from one place.</p></div>
    <a class="btn" href="<?= url('patient/book') ?>">＋ Book appointment</a>
</div>

<?php if ($next): ?>
<div class="next-appointment">
    <div class="next-icon">◷</div><div><span class="eyebrow">Next appointment</span><strong><?= e($next['Doctor_Name']) ?></strong><span><?= date_label($next['Appointment_Date']) ?> · <?= time_label($next['Time_Slot']) ?> · Queue <?= queue_label($next['Queue_No']) ?></span></div><a class="btn btn-small btn-light" href="<?= url('patient/appointments') ?>">View details</a>
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="card stat-card"><span class="stat-icon">◷</span><div><span class="stat-no"><?= (int)$counts['upcoming'] ?></span><span>Upcoming</span></div></div>
    <div class="card stat-card"><span class="stat-icon">✓</span><div><span class="stat-no"><?= (int)$counts['completed'] ?></span><span>Completed visits</span></div></div>
    <div class="card stat-card"><span class="stat-icon">▤</span><div><span class="stat-no"><?= (int)$counts['records'] ?></span><span>Medical records</span></div></div>
</div>

<h2>Quick actions</h2>
<div class="action-grid">
    <a class="card action-card" href="<?= url('patient/book') ?>"><span class="icon">＋</span><div><h3>Book an appointment</h3><p class="muted">Choose a doctor, date and available time.</p></div><span class="arrow">→</span></a>
    <a class="card action-card" href="<?= url('patient/doctors') ?>"><span class="icon">♟</span><div><h3>Find a doctor</h3><p class="muted">Browse specializations, fees and sessions.</p></div><span class="arrow">→</span></a>
    <a class="card action-card" href="<?= url('patient/history') ?>"><span class="icon">▤</span><div><h3>Medical history</h3><p class="muted">Review diagnoses and treatment notes.</p></div><span class="arrow">→</span></a>
    <a class="card action-card" href="<?= url('patient/prescriptions') ?>"><span class="icon">Rx</span><div><h3>Prescriptions</h3><p class="muted">See medicines, dosage and instructions.</p></div><span class="arrow">→</span></a>
    <a class="card action-card" href="<?= url('patient/bills') ?>"><span class="icon">▣</span><div><h3>My bills</h3><p class="muted">Check paid and outstanding payments.</p></div><span class="arrow">→</span></a>
    <a class="card action-card" href="<?= url('patient/profile') ?>"><span class="icon">⚙</span><div><h3>Profile settings</h3><p class="muted">Keep your contact details up to date.</p></div><span class="arrow">→</span></a>
</div>
