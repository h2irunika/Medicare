<h1>Payments</h1>
<p class="muted">One bill per visit. A visit can exist before it is paid.</p>

<div class="grid">
    <div class="card stat"><span class="stat-no"><?= money($counts['collected']) ?></span><span>Collected</span></div>
    <div class="card stat"><span class="stat-no"><?= money($counts['today']) ?></span><span>Taken today</span></div>
    <div class="card stat"><span class="stat-no"><?= money($counts['outstanding']) ?></span><span>Outstanding</span></div>
    <div class="card stat"><span class="stat-no"><?= (int)$counts['refunds'] ?></span><span>Refunds</span></div>
</div>

<h2>Create a bill</h2>
<?php if (!$unbilled): ?>
    <p class="muted">Every visit has been billed.</p>
<?php else: ?>
<form class="card" method="POST" action="<?= url('staff/payments') ?>">

    <label for="appointment_id">Visit</label>
    <select name="appointment_id" id="appointment_id" required style="max-width:520px">
        <option value="">-- select visit --</option>
        <?php foreach ($unbilled as $u): ?>
            <option value="<?= (int)$u['Appointment_ID'] ?>"
                    data-fee="<?= (float)$u['Consultation_Fee'] ?>">
                #<?= (int)$u['Appointment_ID'] ?> &middot;
                <?= date_label($u['Appointment_Date']) ?> &middot;
                <?= e($u['Patient_Name']) ?> with <?= e($u['Doctor_Name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="amount">Amount (Rs.)</label>
    <input type="text" name="amount" id="amount" required>
    <span class="muted small">Filled in from the doctor's consultation fee. You can change it.</span>

    <label for="payment_status">Status</label>
    <select name="payment_status" id="payment_status">
        <option value="Not Paid">Not paid (patient will pay later)</option>
        <option value="Paid">Paid now</option>
    </select>

    <button type="submit" class="btn">Create bill</button>
</form>

<script>
/* prefill the amount with the doctor's consultation fee */
document.addEventListener('DOMContentLoaded', function () {
    var visit  = document.getElementById('appointment_id');
    var amount = document.getElementById('amount');
    if (!visit || !amount) { return; }

    function useFee() {
        var opt = visit.options[visit.selectedIndex];
        var fee = opt ? opt.getAttribute('data-fee') : null;
        amount.value = fee ? fee : '';
    }
    visit.addEventListener('change', useFee);
    useFee();
});
</script>
<?php endif; ?>

<h2>All bills</h2>
<?php if (!$payments): ?>
    <p class="muted">No bills yet.</p>
<?php else: ?>
<table class="table">
    <tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Visit</th><th>Amount</th>
        <th>Paid on</th><th>Status</th><th>Action</th></tr>
    <?php foreach ($payments as $p): ?>
    <tr>
        <td><?= (int)$p['Payment_ID'] ?></td>
        <td><?= e($p['Patient_Name']) ?></td>
        <td><?= e($p['Doctor_Name']) ?></td>
        <td><?= date_label($p['Appointment_Date']) ?></td>
        <td><?= money($p['Amount']) ?></td>
        <td><?= $p['Payment_Date'] ? date_label($p['Payment_Date']) : '<span class="muted">-</span>' ?></td>
        <td><span class="badge badge-<?= strtolower(str_replace(' ', '', $p['Payment_Status'])) ?>">
            <?= e($p['Payment_Status']) ?></span></td>
        <td class="actions">
            <a class="link" href="<?= url('staff/receipt') ?>&id=<?= (int)$p['Payment_ID'] ?>">Receipt</a>
            <?php if ($p['Payment_Status'] === 'Not Paid'): ?>
                <a class="link" href="<?= url('staff/payments') ?>&paid=<?= (int)$p['Payment_ID'] ?>">Mark paid</a>
            <?php elseif ($p['Payment_Status'] === 'Paid'): ?>
                <a class="link danger" href="<?= url('staff/payments') ?>&refund=<?= (int)$p['Payment_ID'] ?>"
                   onclick="return confirm('Refund this payment?');">Refund</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
