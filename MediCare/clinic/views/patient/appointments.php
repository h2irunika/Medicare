<h1>My appointments</h1>

<?php if (!$appointments): ?>
    <p class="muted">You have no appointments yet.
        <a class="link" href="<?= url('patient/book') ?>">Book one now.</a></p>
<?php endif; ?>

<div class="grid">
<?php foreach ($appointments as $a): ?>
    <div class="card">
        <h3><?= e($a['Doctor_Name']) ?></h3>
        <p class="muted"><?= e($a['Special_Name']) ?></p>
        <p>
            <strong><?= date_label($a['Appointment_Date']) ?></strong>
            at <?= time_label($a['Time_Slot']) ?><br>
            Queue number: <strong><?= queue_label($a['Queue_No']) ?></strong><br>
            <?= $a['Reason'] ? 'Reason: ' . e($a['Reason']) : '' ?>
        </p>
        <p><span class="badge badge-<?= strtolower(str_replace('-', '', $a['Status'])) ?>">
            <?= e($a['Status']) ?></span></p>

        <?php if (in_array($a['Status'], ['Pending', 'Confirmed'], true)): ?>
            <a class="link danger" href="<?= url('patient/cancel') ?>&id=<?= (int)$a['Appointment_ID'] ?>"
               onclick="return confirm('Cancel this appointment?');">Cancel appointment</a>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>
