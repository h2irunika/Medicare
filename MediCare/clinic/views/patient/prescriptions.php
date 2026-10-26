<h1>My prescriptions</h1>

<?php if (!$prescriptions): ?>
    <p class="muted">You have no prescriptions yet.</p>
<?php endif; ?>

<?php foreach ($prescriptions as $p): ?>
    <div class="card">
        <h3>Prescription #<?= (int)$p['Prescription_ID'] ?>
            <span class="tag"><?= date_label($p['Prescription_Date']) ?></span></h3>
        <p class="muted"><?= e($p['Doctor_Name']) ?> &middot; <?= e($p['Diagnosis']) ?></p>

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
