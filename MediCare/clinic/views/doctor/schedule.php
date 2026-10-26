<h1>My schedule</h1>
<p class="muted">A session is a working block. Patients book 30 minute slots inside it.</p>

<form class="card" method="POST" action="<?= url('doctor/schedule') ?>">
    <div class="form-row">
        <div>
            <label for="available_date">Date</label>
            <input type="date" name="available_date" id="available_date"
                   min="<?= e($today) ?>" value="<?= e($today) ?>" required>
        </div>
        <div>
            <label for="start_time">Start time</label>
            <input type="time" name="start_time" id="start_time" value="09:00" required>
        </div>
        <div>
            <label for="end_time">End time</label>
            <input type="time" name="end_time" id="end_time" value="12:00" required>
        </div>
    </div>
    <button type="submit" class="btn">Add session</button>
</form>

<h2>Upcoming sessions</h2>
<?php if (!$schedules): ?>
    <p class="muted">No sessions yet. Add one above so patients can book.</p>
<?php else: ?>
<table class="table">
    <tr><th>Date</th><th>From</th><th>To</th><th>Booked</th><th>Status</th><th></th></tr>
    <?php foreach ($schedules as $s): ?>
    <tr>
        <td><?= date('D, d M Y', strtotime($s['Available_Date'])) ?></td>
        <td><?= time_label($s['Start_Time']) ?></td>
        <td><?= time_label($s['End_Time']) ?></td>
        <td><?= (int)$s['Booked'] ?></td>
        <td>
            <span class="badge <?= $s['Is_Available'] ? 'badge-confirmed' : 'badge-cancelled' ?>">
                <?= $s['Is_Available'] ? 'Open' : 'Closed' ?></span>
        </td>
        <td>
            <a class="link" href="<?= url('doctor/schedule') ?>&toggle=<?= (int)$s['Schedule_ID'] ?>">
                <?= $s['Is_Available'] ? 'Close' : 'Open' ?>
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
