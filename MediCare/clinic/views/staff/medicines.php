<h1>Medicines</h1>
<p class="muted">The master list every prescription chooses from.</p>

<form class="card" method="POST" action="<?= url('staff/medicines') ?>">
    <label for="medicine_name">Medicine name</label>
    <input type="text" name="medicine_name" id="medicine_name" maxlength="100"
           placeholder="Paracetamol 500mg" required>

    <label for="description">Description</label>
    <input type="text" name="description" id="description" maxlength="255"
           placeholder="Pain and fever">

    <button type="submit" class="btn">Add medicine</button>
</form>

<table class="table">
    <tr><th>ID</th><th>Name</th><th>Description</th><th>Times used</th><th></th></tr>
    <?php foreach ($medicines as $m): ?>
    <tr>
        <td><?= (int)$m['Medicine_ID'] ?></td>
        <td><?= e($m['Medicine_Name']) ?></td>
        <td><?= e($m['Description']) ?></td>
        <td><?= (int)$m['Used_Count'] ?></td>
        <td>
            <?php if ((int)$m['Used_Count'] === 0): ?>
                <a class="link danger" href="<?= url('staff/medicines') ?>&delete=<?= (int)$m['Medicine_ID'] ?>"
                   onclick="return confirm('Delete this medicine?');">Delete</a>
            <?php else: ?>
                <span class="muted">in use</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
