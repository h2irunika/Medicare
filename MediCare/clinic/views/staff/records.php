<h1>Medical records</h1>

<div class="grid">
    <div class="card stat"><span class="stat-no"><?= (int)$counts['records'] ?></span><span>Records</span></div>
    <div class="card stat"><span class="stat-no"><?= (int)$counts['today'] ?></span><span>Written today</span></div>
    <div class="card stat"><span class="stat-no"><?= (int)$counts['prescriptions'] ?></span><span>Prescriptions</span></div>
    <div class="card stat"><span class="stat-no"><?= (int)$counts['medicines'] ?></span><span>Medicines</span></div>
</div>

<h2>Waiting to be written up</h2>
<?php if (!$pending): ?>
    <p class="muted">Every consultation has a record.</p>
<?php else: ?>
<table class="table">
    <tr><th>Appt</th><th>Date</th><th>Patient</th><th>Doctor</th><th>Reason</th></tr>
    <?php foreach ($pending as $p): ?>
    <tr>
        <td>#<?= (int)$p['Appointment_ID'] ?></td>
        <td><?= date_label($p['Appointment_Date']) ?></td>
        <td><?= e($p['Patient_Name']) ?></td>
        <td><?= e($p['Doctor_Name']) ?></td>
        <td><?= e($p['Reason']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<p class="muted small">The doctor writes these from their own portal.</p>
<?php endif; ?>

<h2>All records</h2>
<?php if (!$records): ?>
    <p class="muted">No medical records yet.</p>
<?php else: ?>
<table class="table">
    <tr><th>ID</th><th>Date</th><th>Patient</th><th>Doctor</th><th>Diagnosis</th><th></th></tr>
    <?php foreach ($records as $r): ?>
    <tr>
        <td><?= (int)$r['Record_ID'] ?></td>
        <td><?= date_label($r['Record_Date']) ?></td>
        <td><?= e($r['Patient_Name']) ?></td>
        <td><?= e($r['Doctor_Name']) ?></td>
        <td><?= e($r['Diagnosis']) ?></td>
        <td><a class="link" href="<?= url('staff/record') ?>&id=<?= (int)$r['Record_ID'] ?>">Open</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<h2>Most prescribed medicines</h2>
<?php if (!$topMeds): ?>
    <p class="muted">No prescriptions written yet.</p>
<?php else: ?>
<table class="table">
    <tr><th>Medicine</th><th>Times prescribed</th><th>Total quantity</th></tr>
    <?php foreach ($topMeds as $m): ?>
    <tr>
        <td><?= e($m['Medicine_Name']) ?></td>
        <td><?= (int)$m['Times_Prescribed'] ?></td>
        <td><?= (int)$m['Total_Quantity'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
