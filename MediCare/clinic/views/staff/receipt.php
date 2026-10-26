<div class="card receipt">
    <h2 style="margin-top:0"><?= e($payment['Clinic_Name']) ?></h2>
    <p class="muted"><?= e($payment['Address']) ?> &middot; <?= e($payment['Contact_No']) ?></p>

    <p>
        <strong>Receipt #<?= (int)$payment['Payment_ID'] ?></strong><br>
        Issued: <?= $payment['Payment_Date'] ? date_label($payment['Payment_Date']) : 'not paid yet' ?>
    </p>

    <table class="table">
        <tr><th>Patient</th><td><?= e($payment['Patient_Name']) ?> (<?= e($payment['NIC']) ?>)</td></tr>
        <tr><th>Doctor</th><td><?= e($payment['Doctor_Name']) ?> &middot; <?= e($payment['Special_Name']) ?></td></tr>
        <tr><th>Visit</th><td><?= date_label($payment['Appointment_Date']) ?>
            at <?= time_label($payment['Time_Slot']) ?>
            <?= $payment['Queue_No'] ? '&middot; queue ' . queue_label($payment['Queue_No']) : '' ?></td></tr>
        <tr><th>Consultation fee</th><td><?= money($payment['Amount']) ?></td></tr>
        <tr><th>Status</th><td>
            <span class="badge badge-<?= strtolower(str_replace(' ', '', $payment['Payment_Status'])) ?>">
                <?= e($payment['Payment_Status']) ?></span></td></tr>
    </table>

    <p class="total">Total: <?= money($payment['Amount']) ?></p>
    <p class="muted">Thank you. Please keep this receipt.</p>
</div>

<p class="no-print">
    <button class="btn" onclick="window.print();">Print receipt</button>
    <a class="btn btn-light" href="<?= url('staff/payments') ?>">Back to payments</a>
</p>
