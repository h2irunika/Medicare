<h1>My profile</h1>

<div class="card">
    <h3><?= e($doctor['Doctor_Name']) ?></h3>
    <p class="muted">
        <?= e($doctor['Special_Name']) ?> &middot; <?= e($doctor['Clinic_Name']) ?><br>
        <?= e($doctor['Address']) ?> &middot; <?= e($doctor['Contact_No']) ?>
    </p>
</div>

<h2>Update details</h2>

<form class="card" method="POST" action="<?= url('doctor/profile') ?>">
    <label for="phone">Phone</label>
    <input type="text" name="phone" id="phone" value="<?= e($doctor['Phone']) ?>" required>

    <label for="email">Email</label>
    <input type="email" name="email" id="email" value="<?= e($doctor['Email']) ?>" required>

    <label for="fee">Consultation fee (Rs.)</label>
    <input type="text" name="fee" id="fee" value="<?= e($doctor['Consultation_Fee']) ?>" required>
    <span class="muted small">Reception uses this to prefill the bill.</span>

    <button type="submit" class="btn">Save</button>
</form>
