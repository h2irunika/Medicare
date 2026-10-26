<div class="page-heading"><div><span class="eyebrow">Reception workspace</span><h1>Book an appointment</h1><p class="muted">Create a patient appointment and reserve a live queue slot.</p></div><a class="btn btn-light" href="<?= url('staff/patients') ?>">← Patient directory</a></div>
<p class="muted">Booking at the desk. It follows the same rules as a patient booking online.</p>

<form class="card" method="POST" action="<?= $action ?>">

    <label for="patient_id">Patient</label>
    <select name="patient_id" id="patient_id" required>
        <option value="">-- select patient --</option>
        <?php foreach ($patients as $p): ?>
            <option value="<?= (int)$p['Patient_ID'] ?>" <?= ((string)$p['Patient_ID'] === (string)($selectedPatient ?? '')) ? 'selected' : '' ?>>
                <?= e($p['Patient_Name']) ?> (<?= e($p['NIC']) ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <label for="doctor_id">Doctor</label>
    <select name="doctor_id" id="doctor_id" required>
        <option value="">-- select doctor --</option>
        <?php foreach ($doctors as $d): ?>
            <option value="<?= (int)$d['Doctor_ID'] ?>">
                <?= e($d['Doctor_Name']) ?> &mdash; <?= e($d['Special_Name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="date">Date</label>
    <input type="date" name="date" id="date"
           min="<?= e($today) ?>" max="<?= e($maxDate) ?>" value="<?= e($today) ?>" required>

    <label>Available time slots</label>
    <div id="slots" class="slots">
        <p class="muted">Select a doctor to see free slots.</p>
    </div>

    <label for="reason">Reason for visit</label>
    <input type="text" name="reason" id="reason" maxlength="255">

    <button type="submit" class="btn">Confirm booking</button>
</form>
