<div class="auth">

    <h1>Log in</h1>
    <p class="muted">One login form for all three roles. Your account type
       decides which pages you see.</p>

    <div class="auth-tabs">
        <a href="<?= url('login&role=Patient') ?>"      class="<?= $role === 'Patient' ? 'active' : '' ?>">Patient</a>
        <a href="<?= url('login&role=Doctor') ?>"       class="<?= $role === 'Doctor' ? 'active' : '' ?>">Doctor</a>
        <a href="<?= url('login&role=Receptionist') ?>" class="<?= $role === 'Receptionist' ? 'active' : '' ?>">Reception</a>
    </div>

    <form class="card" method="POST" action="<?= url('login/check') ?>">
        <input type="hidden" name="role" value="<?= e($role) ?>">

        <label for="username">
            <?= $role === 'Patient' ? 'Email address' : 'Username' ?>
        </label>
        <input type="text" name="username" id="username" autocomplete="username" required
               style="max-width:none">

        <label for="password">Password</label>
        <input type="password" name="password" id="password" autocomplete="current-password"
               required style="max-width:none">

        <button type="submit" class="btn" style="width:100%">Log in</button>
    </form>

    <?php if ($role !== 'Receptionist'): ?>
        <p class="muted">
            No account yet?
            <a class="link" href="<?= url('register&role=' . $role) ?>">Register as a <?= strtolower(e($role)) ?></a>
        </p>
    <?php else: ?>
        <p class="muted">Reception accounts are created by an administrator.</p>
    <?php endif; ?>

    <p class="muted small">
        Test account:
        <?php if ($role === 'Patient'): ?>kamal@mail.com / patient123
        <?php elseif ($role === 'Doctor'): ?>dr.perera / doctor123
        <?php else: ?>reception / admin123<?php endif; ?>
    </p>

</div>
