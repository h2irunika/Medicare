<h1>User accounts</h1>
<form class="toolbar" onsubmit="return false"><div class="search-wrap"><span>⌕</span><input id="tableSearch" class="search" type="search" placeholder="Search accounts…"></div></form>
<p class="muted">One login table serves patients, doctors and reception.</p>

<div class="grid">
    <?php foreach ($counts as $c): ?>
        <div class="card stat">
            <span class="stat-no"><?= (int)$c['Total'] ?></span>
            <span><?= e($c['User_Type']) ?>s</span>
        </div>
    <?php endforeach; ?>
</div>

<h2>Add an account</h2>
<form class="card" method="POST" action="<?= url('staff/users') ?>">
    <label for="username">Username</label>
    <input type="text" name="username" id="username" maxlength="50" required>

    <label for="password">Password</label>
    <input type="password" name="password" id="password" minlength="6" required>

    <label for="user_type">Role</label>
    <select name="user_type" id="user_type">
        <option value="Patient">Patient</option>
        <option value="Doctor">Doctor</option>
        <option value="Receptionist">Receptionist</option>
    </select>

    <button type="submit" class="btn">Create account</button>
</form>

<h2>All accounts</h2>
<table class="table" id="searchTable">
    <tr><th>ID</th><th>Username</th><th>Role</th><th>Belongs to</th><th>Created</th><th></th></tr>
    <?php foreach ($users as $u): ?>
    <tr>
        <td><?= (int)$u['User_ID'] ?></td>
        <td><?= e($u['Username']) ?></td>
        <td><span class="tag"><?= e($u['User_Type']) ?></span></td>
        <td><?= $u['Person_Name'] ? e($u['Person_Name']) : '<span class="muted">not linked</span>' ?></td>
        <td><?= $u['Created_At'] ? date_label($u['Created_At']) : '-' ?></td>
        <td>
            <?php if ($u['Person_Name']): ?>
                <span class="muted">linked</span>
            <?php else: ?>
                <a class="link danger" href="<?= url('staff/users') ?>&delete=<?= (int)$u['User_ID'] ?>"
                   onclick="return confirm('Delete this account?');">Delete</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>Reset a password</h2>
<form class="card" method="POST" action="<?= url('staff/users') ?>">
    <label for="user_id">Account</label>
    <select name="user_id" id="user_id">
        <?php foreach ($users as $u): ?>
            <option value="<?= (int)$u['User_ID'] ?>">
                <?= e($u['Username']) ?> (<?= e($u['User_Type']) ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <label for="new_password">New password</label>
    <input type="password" name="new_password" id="new_password" minlength="6" required>

    <button type="submit" class="btn">Reset password</button>
</form>
