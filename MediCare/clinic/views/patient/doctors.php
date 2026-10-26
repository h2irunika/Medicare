<h1>Our doctors</h1>
<p class="muted">Pick a doctor to see their free time slots.</p>

<div class="grid">
    <?php foreach ($doctors as $d): ?>
        <div class="card">
            <div class="icon">&#129658;</div>
            <h3><?= e($d['Doctor_Name']) ?></h3>
            <p class="muted">
                <?= e($d['Special_Name']) ?><br>
                <?= e($d['Clinic_Name']) ?><br>
                Consultation fee <?= money($d['Consultation_Fee']) ?><br>
                <?= (int)$d['Sessions'] ?> upcoming session<?= $d['Sessions'] == 1 ? '' : 's' ?>
            </p>
            <a class="btn btn-small" href="<?= url('patient/book') ?>&doctor_id=<?= (int)$d['Doctor_ID'] ?>">Book</a>
        </div>
    <?php endforeach; ?>
</div>
