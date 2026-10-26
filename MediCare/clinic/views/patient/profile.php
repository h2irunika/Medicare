<h1>My profile</h1>

<div class="card">
    <h3><?= e($patient['First_Name'] . ' ' . $patient['Last_Name']) ?></h3>
    <p class="muted">
        Patient #<?= (int)$patient['Patient_ID'] ?> &middot; NIC <?= e($patient['NIC']) ?>
        &middot; <?= e($patient['Gender']) ?>
        &middot; born <?= $patient['DOB'] ? date_label($patient['DOB']) : '-' ?><br>
        Registered <?= $patient['Registered_Date'] ? date_label($patient['Registered_Date']) : '-' ?>
    </p>
</div>

<h2>Update contact details</h2>

<form class="card" method="POST" action="<?= url('patient/profile') ?>">
    <label for="phone">Phone number</label>
    <input type="text" name="phone" id="phone" value="<?= e($patient['Phone']) ?>" required>

    <label for="email">Email address</label>
    <input type="email" name="email" id="email" value="<?= e($patient['Email']) ?>" required>

    <label for="address">Address</label>
    <textarea name="address" id="address" rows="2" required><?= e($patient['Address']) ?></textarea>

    <button type="submit" class="btn">Save changes</button>
</form>
