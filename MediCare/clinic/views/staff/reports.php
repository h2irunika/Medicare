<div class="page-heading">
    <div><span class="eyebrow">Management insights</span><h1>Reports &amp; analytics</h1><p class="muted">A live snapshot of clinic activity, revenue and workload.</p></div>
    <button class="btn btn-light no-print" type="button" onclick="window.print()">⎙ Print report</button>
</div>

<div class="stats-grid">
    <div class="card stat-card"><span class="stat-icon">♙</span><div><span class="stat-no"><?= (int)$summary['patients'] ?></span><span>Patients</span></div></div>
    <div class="card stat-card"><span class="stat-icon">♟</span><div><span class="stat-no"><?= (int)$summary['doctors'] ?></span><span>Doctors</span></div></div>
    <div class="card stat-card"><span class="stat-icon">◷</span><div><span class="stat-no"><?= (int)$summary['appointments'] ?></span><span>Appointments</span></div></div>
    <div class="card stat-card"><span class="stat-icon">Rs</span><div><span class="stat-no money-stat"><?= money($summary['revenue']) ?></span><span>Collected revenue</span></div></div>
</div>

<div class="report-grid">
<section class="report-panel"><h2>Appointments by status</h2>
<table class="table"><thead><tr><th>Status</th><th>Total</th><th>Share</th></tr></thead><tbody>
<?php foreach ($byStatus as $s): ?><tr><td><span class="badge badge-<?= strtolower(str_replace('-', '', $s['Status'])) ?>"><?= e($s['Status']) ?></span></td><td><strong><?= (int)$s['Total'] ?></strong></td><td><div class="bar"><span class="bar-fill" style="width: <?= min(100, (float)$s['Percent']) ?>%"></span></div><span class="small muted"><?= (float)$s['Percent'] ?>%</span></td></tr><?php endforeach; ?>
</tbody></table></section>

<section class="report-panel"><h2>Revenue by doctor</h2>
<table class="table"><thead><tr><th>Doctor</th><th>Appointments</th><th>Revenue</th></tr></thead><tbody>
<?php foreach ($byDoctor as $d): ?><tr><td><strong><?= e($d['Doctor_Name']) ?></strong><br><span class="muted small"><?= e($d['Special_Name']) ?></span></td><td><?= (int)$d['Appointments'] ?></td><td class="total"><?= money($d['Revenue']) ?></td></tr><?php endforeach; ?>
</tbody></table></section>
</div>

<div class="report-grid">
<section class="report-panel"><h2>Money taken — last 7 days</h2>
<?php if (!$byDay): ?><div class="empty-mini">No payments in the last 7 days.</div><?php else: ?>
<div class="chart-list"><?php $maxDay = 0; foreach ($byDay as $d) { $maxDay = max($maxDay, (float)$d['Total']); } foreach ($byDay as $d): $pct = $maxDay > 0 ? ((float)$d['Total'] / $maxDay) * 100 : 0; ?><div class="chart-row"><div><strong><?= date('D', strtotime($d['Payment_Date'])) ?></strong><span><?= date('d M', strtotime($d['Payment_Date'])) ?></span></div><div class="chart-track"><span style="width: <?= $pct ?>%"></span></div><strong><?= money($d['Total']) ?></strong></div><?php endforeach; ?></div>
<?php endif; ?></section>

<section class="report-panel"><h2>Busiest time slots</h2>
<?php if (!$busiest): ?><div class="empty-mini">No appointment data yet.</div><?php else: ?>
<table class="table"><thead><tr><th>Time</th><th>Bookings</th></tr></thead><tbody><?php foreach ($busiest as $s): ?><tr><td><?= e($s['Slot']) ?></td><td><div class="inline-meter"><span style="width: <?= min(100, ((int)$s['Bookings'] / max(1, (int)$busiest[0]['Bookings'])) * 100) ?>%"></span></div> <?= (int)$s['Bookings'] ?></td></tr><?php endforeach; ?></tbody></table>
<?php endif; ?></section>
</div>

<section class="report-panel full-panel"><h2>Patients who visit most</h2>
<table class="table"><thead><tr><th>Patient</th><th>NIC</th><th>Visits</th><th>Paid</th></tr></thead><tbody>
<?php foreach ($topPatients as $p): ?><tr><td><strong><?= e($p['Patient_Name']) ?></strong></td><td><?= e($p['NIC']) ?></td><td><span class="tag"><?= (int)$p['Visits'] ?> visits</span></td><td><?= money($p['Paid']) ?></td></tr><?php endforeach; ?>
</tbody></table></section>

<section class="report-panel full-panel"><h2>Bills still unpaid</h2>
<?php if (!$outstanding): ?><div class="empty-mini success-mini">✓ Nothing outstanding.</div><?php else: ?>
<table class="table"><thead><tr><th>Bill</th><th>Patient</th><th>Visit date</th><th>Days old</th><th>Amount</th><th></th></tr></thead><tbody>
<?php foreach ($outstanding as $o): ?><tr><td>#<?= (int)$o['Payment_ID'] ?></td><td><strong><?= e($o['Patient_Name']) ?></strong><br><span class="muted small"><?= e($o['Phone']) ?></span></td><td><?= date_label($o['Appointment_Date']) ?></td><td><?= (int)$o['Days_Old'] ?></td><td class="total"><?= money($o['Amount']) ?></td><td><a class="btn btn-small" href="<?= url('staff/payments') ?>&paid=<?= (int)$o['Payment_ID'] ?>">Mark paid</a></td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?></section>
