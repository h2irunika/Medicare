<h1>My appointments</h1>

<form class="inline-form" method="GET" action="index.php">
    <input type="hidden" name="page" value="doctor/appointments">
    <div>
        <label for="date">Show date</label>
        <input type="date" name="date" id="date" value="<?= e($date) ?>">
    </div>
    <button class="btn btn-small" type="submit">Show</button>
</form>

<h2><?= date_label($date) ?></h2>
<?php if (!$today): ?>
    <p class="muted">Nothing booked on this date.</p>
<?php else: ?>
<table class="table">
    <tr><th>Queue</th><th>Time</th><th>Patient</th><th>Phone</th><th>Status</th><th></th></tr>
    <?php foreach ($today as $a): ?>
    <tr>
        <td class="queue-no"><?= queue_label($a['Queue_No']) ?></td>
        <td><?= time_label($a['Time_Slot']) ?></td>
        <td><?= e($a['Patient_Name']) ?></td>
        <td><?= e($a['Phone']) ?></td>
        <td><span class="badge badge-<?= strtolower(str_replace('-', '', $a['Status'])) ?>">
            <?= e($a['Status']) ?></span></td>
        <td>
            <?php if ($a['Record_ID']): ?>
                <a class="link" href="<?= url('doctor/prescription') ?>&id=<?= (int)$a['Record_ID'] ?>">Prescription</a>
            <?php else: ?>
                <a class="link" href="<?= url('doctor/consultation') ?>&id=<?= (int)$a['Appointment_ID'] ?>">Consultation</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<h2>Upcoming</h2>
<?php if (!$upcoming): ?>
    <p class="muted">No upcoming appointments.</p>
<?php else: ?>
<table class="table">
    <tr><th>Date</th><th>Time</th><th>Patient</th><th>Phone</th><th>Status</th></tr>
    <?php foreach ($upcoming as $a): ?>
    <tr>
        <td><?= date_label($a['Appointment_Date']) ?></td>
        <td><?= time_label($a['Time_Slot']) ?></td>
        <td><?= e($a['Patient_Name']) ?></td>
        <td><?= e($a['Phone']) ?></td>
        <td><span class="badge badge-<?= strtolower(str_replace('-', '', $a['Status'])) ?>">
            <?= e($a['Status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
