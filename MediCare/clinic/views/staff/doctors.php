<div class="page-heading">
    <div>
        <span class="eyebrow">Clinical team</span>
        <h1>Doctor directory</h1>
        <p class="muted">View specializations, fees and current availability.</p>
    </div>
    <a class="btn" href="<?= url('staff/book') ?>">＋ Book for patient</a>
</div>

<form class="toolbar" method="GET" action="index.php">
    <input type="hidden" name="page" value="staff/doctors">
    <div class="search-wrap">
        <span>⌕</span>
        <input class="search" type="search" name="q" value="<?= e($search) ?>" placeholder="Search doctor, specialty or contact…">
    </div>
    <button class="btn btn-small" type="submit">Search</button>
    <?php if ($search !== ''): ?><a class="btn btn-small btn-light" href="<?= url('staff/doctors') ?>">Clear</a><?php endif; ?>
</form>

<div class="doctor-directory">
<?php foreach ($doctors as $d): ?>
    <article class="doctor-card card">
        <div class="doctor-avatar">✚</div>
        <div class="doctor-main">
            <div class="doctor-title"><h3><?= e($d['Doctor_Name']) ?></h3><span class="badge badge-confirmed">Active</span></div>
            <p class="speciality"><?= e($d['Special_Name']) ?></p>
            <div class="mini-details">
                <span>⌖ <?= e($d['Clinic_Name']) ?></span>
                <span>☎ <?= e($d['Phone']) ?></span>
                <span>Rs. <?= number_format((float)$d['Consultation_Fee'], 2) ?></span>
            </div>
        </div>
        <div class="doctor-stats">
            <strong><?= (int)$d['Sessions'] ?></strong><span>open sessions</span>
            <strong><?= (int)$d['Appointments'] ?></strong><span>appointments</span>
        </div>
    </article>
<?php endforeach; ?>
</div>
<?php if (!$doctors): ?><div class="empty-state"><div class="empty-icon">⌕</div><h3>No doctors found</h3><p class="muted">Try another search term.</p></div><?php endif; ?>
