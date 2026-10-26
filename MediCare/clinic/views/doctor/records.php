<h1>Medical records</h1>

<?php if ($pending): ?>
    <h2>Waiting to be written up</h2>
    <table class="table">
        <tr><th>Appt</th><th>Date</th><th>Patient</th><th>Reason</th><th></th></tr>
        <?php foreach ($pending as $p): ?>
        <tr>
            <td>#<?= (int)$p['Appointment_ID'] ?></td>
            <td><?= date_label($p['Appointment_Date']) ?></td>
            <td><?= e($p['Patient_Name']) ?></td>
            <td><?= e($p['Reason']) ?></td>
            <td><a class="link" href="<?= url('doctor/consultation') ?>&id=<?= (int)$p['Appointment_ID'] ?>">Write record</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<h2>My records</h2>

<form class="inline-form" method="GET" action="index.php">
    <input type="hidden" name="page" value="doctor/records">
    <div>
        <label for="q">Search by patient, NIC or diagnosis</label>
        <input type="text" name="q" id="q" value="<?= e($search) ?>" style="width:280px">
    </div>
    <button class="btn btn-small" type="submit">Search</button>
    <?php if ($search !== ''): ?>
        <a class="btn btn-small btn-light" href="<?= url('doctor/records') ?>">Clear</a>
    <?php endif; ?>
</form>

<?php if (!$records): ?>
    <p class="muted">No records found.</p>
<?php else: ?>
<table class="table">
    <tr><th>ID</th><th>Visit date</th><th>Patient</th><th>Diagnosis</th><th>Prescriptions</th><th></th></tr>
    <?php foreach ($records as $r): ?>
    <tr>
        <td><?= (int)$r['Record_ID'] ?></td>
        <td><?= date_label($r['Appointment_Date']) ?></td>
        <td><?= e($r['Patient_Name']) ?><br><span class="muted small"><?= e($r['NIC']) ?></span></td>
        <td><?= e($r['Diagnosis']) ?><br><span class="muted small"><?= e($r['Symptoms']) ?></span></td>
        <td><?= (int)$r['Prescription_Count'] ?></td>
        <td><a class="link" href="<?= url('doctor/prescription') ?>&id=<?= (int)$r['Record_ID'] ?>">Prescriptions</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
