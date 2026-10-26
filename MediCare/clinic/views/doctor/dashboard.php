<div class="dashboard-welcome">
    <div><span class="eyebrow">Doctor portal</span><h1><?= e(user_name()) ?> 👋</h1><p class="muted"><?= e($doctor['Special_Name']) ?> · <?= e($doctor['Clinic_Name']) ?> · Consultation fee <?= money($doctor['Consultation_Fee']) ?></p></div>
    <a class="btn" href="<?= url('doctor/appointments') ?>">View appointments →</a>
</div>
<div class="stats-grid">
    <div class="card stat-card"><span class="stat-icon">◷</span><div><span class="stat-no"><?= (int)$counts['today'] ?></span><span>Today</span></div></div>
    <div class="card stat-card"><span class="stat-icon">↗</span><div><span class="stat-no"><?= (int)$counts['upcoming'] ?></span><span>Upcoming</span></div></div>
    <div class="card stat-card"><span class="stat-icon">✓</span><div><span class="stat-no"><?= (int)$counts['completed'] ?></span><span>Completed</span></div></div>
    <div class="card stat-card"><span class="stat-icon">▤</span><div><span class="stat-no"><?= (int)$counts['records'] ?></span><span>Records written</span></div></div>
</div>
<h2>Today's queue</h2>
<div class="now-serving"><div><span class="eyebrow">Now serving</span><strong><?= $nowServing ? queue_label($nowServing['Queue_No']) : '--' ?></strong></div><span><?= $nowServing ? e($nowServing['Patient_Name']) : 'Queue is clear' ?></span></div>
<?php if (!$queue): ?><div class="empty-state"><div class="empty-icon">✓</div><h3>No appointments today</h3><p class="muted">Your queue is clear.</p></div><?php else: ?>
<div class="table-card"><table class="table"><thead><tr><th>Queue</th><th>Time</th><th>Patient</th><th>Reason</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($queue as $q): ?><tr><td class="queue-no"><?= queue_label($q['Queue_No']) ?></td><td><?= time_label($q['Time_Slot']) ?></td><td><strong><?= e($q['Patient_Name']) ?></strong></td><td><?= e($q['Reason']) ?></td><td><span class="badge badge-<?= strtolower(str_replace('-', '', $q['Status'])) ?>"><?= e($q['Status']) ?></span></td><td><?php if ($q['Record_ID']): ?><a class="btn btn-small btn-light" href="<?= url('doctor/prescription') ?>&id=<?= (int)$q['Record_ID'] ?>">Prescription</a><?php else: ?><a class="btn btn-small" href="<?= url('doctor/consultation') ?>&id=<?= (int)$q['Appointment_ID'] ?>">Start consultation</a><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php endif; ?>
