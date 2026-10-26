<h1>Medical history</h1>
<p class="muted">Written by your doctor after each consultation.</p>

<?php if (!$records): ?>
    <p class="muted">No records yet. One is created after a doctor completes your visit.</p>
<?php endif; ?>

<?php foreach ($records as $r): ?>
    <div class="card">
        <h3><?= e($r['Diagnosis']) ?></h3>
        <p class="muted">
            <?= e($r['Doctor_Name']) ?> (<?= e($r['Special_Name']) ?>)
            &middot; visit on <?= date_label($r['Appointment_Date']) ?>
        </p>
        <p><strong>Symptoms:</strong> <?= $r['Symptoms'] ? e($r['Symptoms']) : '-' ?></p>
        <p><strong>Treatment notes:</strong>
           <?= $r['Treatment_Notes'] ? nl2br(e($r['Treatment_Notes'])) : '-' ?></p>
        <p class="muted small">
            <?= (int)$r['Prescription_Count'] ?> prescription<?= $r['Prescription_Count'] == 1 ? '' : 's' ?>
            &middot; <a class="link" href="<?= url('patient/prescriptions') ?>">see medicines</a>
        </p>
    </div>
<?php endforeach; ?>
