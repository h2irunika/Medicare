<div class="auth wide">

    <h1>Create an account</h1>

    <div class="auth-tabs">
        <a href="<?= url('register&role=Patient') ?>" class="<?= $role === 'Patient' ? 'active' : '' ?>">Patient</a>
        <a href="<?= url('register&role=Doctor') ?>"  class="<?= $role === 'Doctor' ? 'active' : '' ?>">Doctor</a>
    </div>

    <?php if ($role === 'Patient'): ?>

    <form class="card" method="POST" action="<?= url('register/patient') ?>">

        <div class="form-row">
            <div>
                <label for="first_name">First name</label>
                <input type="text" name="first_name" id="first_name" required>
            </div>
            <div>
                <label for="last_name">Last name</label>
                <input type="text" name="last_name" id="last_name" required>
            </div>
        </div>

        <label for="nic">NIC number</label>
        <input type="text" name="nic" id="nic" required>

        <div class="form-row">
            <div>
                <label for="dob">Date of birth</label>
                <input type="date" name="dob" id="dob" max="<?= date('Y-m-d') ?>" required>
            </div>
            <div>
                <label for="gender">Gender</label>
                <select name="gender" id="gender" required>
                    <option value="">Select</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>

        <label for="phone">Phone number</label>
        <input type="text" name="phone" id="phone" required>

        <label for="email">Email address <span class="muted">(this is your username)</span></label>
        <input type="email" name="email" id="email" required>

        <label for="address">Address</label>
        <textarea name="address" id="address" rows="2" required></textarea>

        <label for="password">Password <span class="muted">(at least 6 characters)</span></label>
        <input type="password" name="password" id="password" minlength="6" required>

        <button type="submit" class="btn">Create patient account</button>
    </form>

    <?php else: ?>

    <form class="card" method="POST" action="<?= url('register/doctor') ?>">

        <div class="form-row">
            <div>
                <label for="first_name">First name</label>
                <input type="text" name="first_name" id="first_name" required>
            </div>
            <div>
                <label for="last_name">Last name</label>
                <input type="text" name="last_name" id="last_name" required>
            </div>
        </div>

        <label for="username">Username</label>
        <input type="text" name="username" id="username" placeholder="dr.name" required>

        <div class="form-row">
            <div>
                <label for="special_id">Specialization</label>
                <select name="special_id" id="special_id" required>
                    <option value="">Select</option>
                    <?php foreach ($specializations as $s): ?>
                        <option value="<?= (int)$s['Special_ID'] ?>"><?= e($s['Special_Name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="clinic_id">Clinic</label>
                <select name="clinic_id" id="clinic_id" required>
                    <option value="">Select</option>
                    <?php foreach ($clinics as $c): ?>
                        <option value="<?= (int)$c['Clinic_ID'] ?>"><?= e($c['Clinic_Name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label for="phone">Phone number</label>
                <input type="text" name="phone" id="phone" required>
            </div>
            <div>
                <label for="fee">Consultation fee (Rs.)</label>
                <input type="text" name="fee" id="fee" value="2500" required>
            </div>
        </div>

        <label for="email">Email address</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Password <span class="muted">(at least 6 characters)</span></label>
        <input type="password" name="password" id="password" minlength="6" required>

        <button type="submit" class="btn">Create doctor account</button>
    </form>

    <p class="muted small">
        In a real clinic an administrator would approve a new doctor before the
        account goes live. We allow direct registration to keep the demo simple.
    </p>

    <?php endif; ?>

    <p class="muted">Already registered?
        <a class="link" href="<?= url('login&role=' . $role) ?>">Log in</a></p>

</div>
