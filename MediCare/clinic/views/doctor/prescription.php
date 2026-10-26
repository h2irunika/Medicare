<h1>Prescription</h1>

<div class="card">
    <h3><?= e($record['Patient_Name']) ?></h3>
    <p class="muted">
        Record #<?= (int)$record['Record_ID'] ?> &middot; <?= e($record['Diagnosis']) ?>
        &middot; visit on <?= date_label($record['Appointment_Date']) ?>
    </p>
</div>

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

<h2>Add a prescription</h2>
<p class="muted">One prescription can hold several medicines. Add a row for each.</p>

<form class="card" method="POST" action="<?= url('doctor/prescription') ?>">
    <input type="hidden" name="record_id" value="<?= (int)$record['Record_ID'] ?>">

    <table class="table" id="itemTable">
        <thead>
        <tr><th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th><th>Qty</th><th>Instructions</th><th></th></tr>
        </thead>
        <tbody>
        <tr class="item-row">
            <td>
                <select name="medicine_id[]">
                    <option value="">-- medicine --</option>
                    <?php foreach ($medicines as $m): ?>
                        <option value="<?= (int)$m['Medicine_ID'] ?>"><?= e($m['Medicine_Name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="text" name="dosage[]" placeholder="500mg"></td>
            <td><input type="text" name="frequency[]" placeholder="3 times a day"></td>
            <td><input type="text" name="duration[]" placeholder="5 days"></td>
            <td><input type="text" name="quantity[]" placeholder="15"></td>
            <td><input type="text" name="instructions[]" placeholder="After meals"></td>
            <td><a href="#" class="link danger remove-row">Remove</a></td>
        </tr>
        </tbody>
    </table>

    <a href="#" class="link" id="addItemRow">+ Add another medicine</a><br>
    <button type="submit" class="btn">Save prescription</button>
</form>
