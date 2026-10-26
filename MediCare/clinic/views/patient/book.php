<h1>Book an appointment</h1>
<p class="muted">Choose a doctor and a date. Free time slots load below by themselves.</p>

<form class="card" method="POST" action="<?= $action ?>">

    <label for="doctor_id">Doctor</label>
    <select name="doctor_id" id="doctor_id" required>
        <option value="">-- select doctor --</option>
        <?php foreach ($doctors as $d): ?>
            <option value="<?= (int)$d['Doctor_ID'] ?>"
                <?= (string)$d['Doctor_ID'] === (string)$selected ? 'selected' : '' ?>>
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
    <input type="text" name="reason" id="reason" maxlength="255"
           placeholder="Fever, follow-up, checkup...">

    <button type="submit" class="btn">Confirm booking</button>
</form>
