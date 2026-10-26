<div class="page-heading"><div><span class="eyebrow">Reception workspace</span><h1>Appointments</h1><p class="muted">Search, review and manage every clinic appointment.</p></div><a class="btn" href="<?= url('staff/book') ?>">＋ New appointment</a></div>
<p class="muted">Search, update status, or cancel a booking.</p>

<input type="text" id="tableSearch" class="search"
       placeholder="Search by patient, doctor or status...">

<table class="table" id="searchTable">
    <thead>
    <tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th>
        <th>Queue</th><th>Status</th><th>Action</th></tr>
    </thead>
    <tbody>
    <?php foreach ($appointments as $a): ?>
    <tr>
        <td><?= (int)$a['Appointment_ID'] ?></td>
        <td><?= e($a['Patient_Name']) ?></td>
        <td><?= e($a['Doctor_Name']) ?></td>
        <td><?= date_label($a['Appointment_Date']) ?></td>
        <td><?= time_label($a['Time_Slot']) ?></td>
        <td><?= queue_label($a['Queue_No']) ?></td>
        <td><span class="badge badge-<?= strtolower(str_replace('-', '', $a['Status'])) ?>">
            <?= e($a['Status']) ?></span></td>
        <td class="actions">
            <?php if (!in_array($a['Status'], ['Cancelled', 'Completed'], true)): ?>
                <a class="link" href="<?= url('staff/status') ?>&id=<?= (int)$a['Appointment_ID'] ?>&status=Completed&back=staff/appointments">Complete</a>
                <a class="link danger" href="<?= url('staff/status') ?>&id=<?= (int)$a['Appointment_ID'] ?>&status=Cancelled&back=staff/appointments"
                   onclick="return confirm('Cancel appointment #<?= (int)$a['Appointment_ID'] ?>?');">Cancel</a>
            <?php else: ?>
                <span class="muted">-</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
