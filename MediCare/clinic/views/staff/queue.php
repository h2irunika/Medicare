<h1>Today's queue</h1>

<div class="grid">
    <div class="card stat"><span class="stat-no"><?= (int)$counts['today'] ?></span><span>Appointments today</span></div>
    <div class="card stat"><span class="stat-no"><?= (int)$counts['confirmed'] ?></span><span>Confirmed</span></div>
    <div class="card stat"><span class="stat-no"><?= (int)$counts['cancelled'] ?></span><span>Cancelled</span></div>
    <div class="card stat"><span class="stat-no"><?= (int)$counts['total'] ?></span><span>Total booked</span></div>
</div>

<form class="inline-form" method="GET" action="index.php" style="margin-top:22px">
    <input type="hidden" name="page" value="staff/queue">
    <div>
        <label for="doctor_id">Doctor</label>
        <select name="doctor_id" id="doctor_id">
            <?php foreach ($doctors as $d): ?>
                <option value="<?= (int)$d['Doctor_ID'] ?>" <?= $d['Doctor_ID'] == $doctorId ? 'selected' : '' ?>>
                    <?= e($d['Doctor_Name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="date">Date</label>
        <input type="date" name="date" id="date" value="<?= e($date) ?>">
    </div>
    <button class="btn btn-small" type="submit">Show queue</button>
</form>

<div class="now-serving">
    <span class="muted">Now serving</span>
    <strong><?= $nowServing ? queue_label($nowServing['Queue_No']) : '--' ?></strong>
    <span><?= $nowServing ? e($nowServing['Patient_Name']) : 'Queue is clear' ?></span>
</div>

<?php if (!$queue): ?>
    <p class="muted">No appointments for this doctor on <?= date_label($date) ?>.</p>
<?php else: ?>
<?php $back = rawurlencode('staff/queue&doctor_id=' . (int)$doctorId . '&date=' . $date); ?>
<table class="table">
    <tr><th>Queue</th><th>Time</th><th>Patient</th><th>Phone</th><th>Status</th><th>Action</th></tr>
    <?php foreach ($queue as $q): ?>
    <tr>
        <td class="queue-no"><?= queue_label($q['Queue_No']) ?></td>
        <td><?= time_label($q['Time_Slot']) ?></td>
        <td><?= e($q['Patient_Name']) ?></td>
        <td><?= e($q['Phone']) ?></td>
        <td><span class="badge badge-<?= strtolower(str_replace('-', '', $q['Status'])) ?>">
            <?= e($q['Status']) ?></span></td>
        <td class="actions">
            <?php if ($q['Status'] !== 'Completed'): ?>
                <a class="link" href="<?= url('staff/status') ?>&id=<?= (int)$q['Appointment_ID'] ?>&status=Completed&back=<?= $back ?>">Mark done</a>
                <a class="link" href="<?= url('staff/status') ?>&id=<?= (int)$q['Appointment_ID'] ?>&status=No-Show&back=<?= $back ?>">No-show</a>
            <?php else: ?>
                <span class="muted">-</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
