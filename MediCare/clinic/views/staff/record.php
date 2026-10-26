<h1>Medical record #<?= (int)$record['Record_ID'] ?></h1>

<div class="card">
    <h3><?= e($record['Patient_Name']) ?></h3>
    <p class="muted">
        NIC <?= e($record['NIC']) ?> &middot; <?= e($record['Gender']) ?><br>
        Seen by <?= e($record['Doctor_Name']) ?> (<?= e($record['Special_Name']) ?>)
        on <?= date_label($record['Appointment_Date']) ?>
        at <?= time_label($record['Time_Slot']) ?>
    </p>
</div>

<form class="card" method="POST" action="<?= url('staff/record') ?>&id=<?= (int)$record['Record_ID'] ?>">
    <label for="symptoms">Symptoms</label>
    <input type="text" name="symptoms" id="symptoms" maxlength="255"
           value="<?= e($record['Symptoms']) ?>" style="max-width:560px">

    <label for="diagnosis">Diagnosis</label>
    <input type="text" name="diagnosis" id="diagnosis" maxlength="255"
           value="<?= e($record['Diagnosis']) ?>" required style="max-width:560px">

    <label for="treatment_notes">Treatment notes</label>
    <textarea name="treatment_notes" id="treatment_notes" rows="4"
              style="max-width:560px"><?= e($record['Treatment_Notes']) ?></textarea>

    <button type="submit" class="btn">Update record</button>
</form>

<h2>Prescriptions</h2>
<?php if (!$prescriptions): ?>
    <p class="muted">No prescription on this record.</p>
<?php endif; ?>

<?php foreach ($prescriptions as $p): ?>
<div class="card">
    <h3>Prescription #<?= (int)$p['Prescription_ID'] ?>
        <span class="tag"><?= date_label($p['Prescription_Date']) ?></span></h3>
    <table class="table">
        <tr><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Qty</th><th>Instructions</th></tr>
        <?php foreach ($p['Items'] as $i): ?>
        <tr>
            <td><?= e($i['Medicine_Name']) ?></td>
            <td><?= e($i['Dosage']) ?></td>
            <td><?= e($i['Frequency']) ?></td>
            <td><?= e($i['Duration']) ?></td>
            <td><?= (int)$i['Quantity'] ?></td>
            <td><?= e($i['Instructions']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endforeach; ?>

<p><a class="link" href="<?= url('staff/records') ?>">Back to all records</a></p>
