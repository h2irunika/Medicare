<h1>Consultation</h1>

<div class="card">
    <h3><?= e($appointment['Patient_Name']) ?></h3>
    <p class="muted">
        NIC <?= e($appointment['NIC']) ?> &middot; <?= e($appointment['Gender']) ?>
        <?php if ($appointment['DOB']): ?>
            &middot; age <?= (int)((time() - strtotime($appointment['DOB'])) / 31556952) ?>
        <?php endif; ?>
        &middot; <?= e($appointment['Phone']) ?>
    </p>
    <p>
        Visit on <?= date_label($appointment['Appointment_Date']) ?>
        at <?= time_label($appointment['Time_Slot']) ?>
        &middot; appointment #<?= (int)$appointment['Appointment_ID'] ?>
        &middot; queue <?= queue_label($appointment['Queue_No']) ?>
        <?php if ($appointment['Reason']): ?>
            <br>Reason given at booking: <?= e($appointment['Reason']) ?>
        <?php endif; ?>
    </p>
</div>

<?php if ($existing): ?>

    <div class="card">
        <h3>This visit already has a record</h3>
        <p class="muted">Record #<?= (int)$existing['Record_ID'] ?> &mdash;
            <?= e($existing['Diagnosis']) ?></p>
        <a class="btn" href="<?= url('doctor/prescription') ?>&id=<?= (int)$existing['Record_ID'] ?>">
            Open prescription</a>
        <a class="btn btn-light" href="<?= url('doctor/dashboard') ?>">Back to queue</a>
    </div>

<?php else: ?>

    <h2>Write the record</h2>

    <form class="card" method="POST" action="<?= url('doctor/consultation') ?>">
        <input type="hidden" name="appointment_id" value="<?= (int)$appointment['Appointment_ID'] ?>">

        <label for="symptoms">Symptoms</label>
        <input type="text" name="symptoms" id="symptoms" maxlength="255"
               placeholder="Fever for 3 days, sore throat" style="max-width:560px">

        <label for="diagnosis">Diagnosis</label>
        <input type="text" name="diagnosis" id="diagnosis" maxlength="255"
               placeholder="Acute viral pharyngitis" required style="max-width:560px">

        <label for="treatment">Treatment notes</label>
        <textarea name="treatment" id="treatment" rows="4" style="max-width:560px"
                  placeholder="Advice given, tests ordered, follow-up date"></textarea>

        <button type="submit" class="btn">Save record and complete the appointment</button>
    </form>

<?php endif; ?>
