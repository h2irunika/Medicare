<div class="page-heading">
    <div>
        <span class="eyebrow">Reception workspace</span>
        <h1>Patient directory</h1>
        <p class="muted">Find patient contact details and visit history quickly.</p>
    </div>
    <a class="btn" href="<?= url('staff/book') ?>">＋ Book appointment</a>
</div>

<form class="toolbar" method="GET" action="index.php">
    <input type="hidden" name="page" value="staff/patients">
    <div class="search-wrap">
        <span>⌕</span>
        <input class="search" type="search" name="q" value="<?= e($search) ?>" placeholder="Search name, NIC, phone or email…">
    </div>
    <button class="btn btn-small" type="submit">Search</button>
    <?php if ($search !== ''): ?><a class="btn btn-small btn-light" href="<?= url('staff/patients') ?>">Clear</a><?php endif; ?>
</form>

<div class="table-card">
<table class="table" id="patientDirectory">
    <thead><tr><th>Patient</th><th>NIC</th><th>Contact</th><th>Date of birth</th><th>Visits</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($patients as $p): ?>
    <tr>
        <td><strong><?= e($p['First_Name'].' '.$p['Last_Name']) ?></strong><br><span class="muted small">Patient #<?= (int)$p['Patient_ID'] ?></span></td>
        <td><?= e($p['NIC']) ?></td>
        <td><?= e($p['Phone']) ?><br><span class="muted small"><?= e($p['Email']) ?></span></td>
        <td><?= $p['DOB'] ? date_label($p['DOB']) : '—' ?></td>
        <td><span class="tag"><?= (int)$p['Visits'] ?> visit<?= $p['Visits'] == 1 ? '' : 's' ?></span></td>
        <td><a class="link" href="<?= url('staff/book') ?>&patient_id=<?= (int)$p['Patient_ID'] ?>">Book</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php if (!$patients): ?><div class="empty-state"><div class="empty-icon">⌕</div><h3>No patients found</h3><p class="muted">Try another name, NIC, phone number or email.</p></div><?php endif; ?>
</div>
