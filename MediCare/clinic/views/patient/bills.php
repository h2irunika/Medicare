<h1>My bills</h1>

<?php if (!$payments): ?>
    <p class="muted">You have no bills yet.</p>
<?php else: ?>
<table class="table">
    <tr><th>Bill</th><th>Doctor</th><th>Visit date</th><th>Amount</th><th>Paid on</th><th>Status</th></tr>
    <?php foreach ($payments as $p): ?>
    <tr>
        <td>#<?= (int)$p['Payment_ID'] ?></td>
        <td><?= e($p['Doctor_Name']) ?></td>
        <td><?= date_label($p['Appointment_Date']) ?></td>
        <td><?= money($p['Amount']) ?></td>
        <td><?= $p['Payment_Date'] ? date_label($p['Payment_Date']) : '<span class="muted">-</span>' ?></td>
        <td><span class="badge badge-<?= strtolower(str_replace(' ', '', $p['Payment_Status'])) ?>">
            <?= e($p['Payment_Status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
