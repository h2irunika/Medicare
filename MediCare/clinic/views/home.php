<section class="hero">
    <img src="images/doctor_bg.jpg" alt="MediCare Clinic Doctors Background">

    <div class="hero-copy">
        <span class="hero-kicker">✚ Trusted care • Easy appointments</span>
        <h1>Better healthcare, made simple.</h1>
        <p>Connect with our doctors, book appointments, manage medical records and keep track of your clinic visits in one secure place.</p>
        <div class="hero-actions">
            <a class="btn" href="<?= url('login&role=Patient') ?>">Book an appointment</a>
            <a class="btn btn-light" href="<?= url('register&role=Patient') ?>">Create patient account</a>
        </div>
    </div>

    <div class="stat-strip">
        <div><strong>20+</strong><span>Years of experience</span></div>
        <div><strong>95%</strong><span>Patient satisfaction</span></div>
        <div><strong>5000+</strong><span>Patients served a year</span></div>
        <div><strong><?= count($doctors) ?></strong><span>Doctors on staff</span></div>
    </div>
</section>

<h2>Choose how you want to sign in</h2>

<div class="grid">

    <a class="card" href="<?= url('login&role=Patient') ?>">
        <div class="icon">&#128100;</div>
        <img src="images/patient.jpg" alt="Patient">
        <h3>Patient</h3>
        <p class="muted">Find a doctor, book a free time slot, and read your
           medical history, prescriptions and bills.</p>

        <span class="btn btn-small">Patient login</span>
    </a>

    <a class="card" href="<?= url('login&role=Doctor') ?>">
        <div class="icon">&#129658;</div>
        <img src="images/doctor.jpg" alt="Doctor">

        <h3>Doctor</h3>
        <p class="muted">See today's queue, set your working sessions, write
           consultation records and prescribe medicines.</p>
        <span class="btn btn-small">Doctor login</span>
    </a>

    <a class="card" href="<?= url('login&role=Receptionist') ?>">
        <div class="icon">&#127973;</div>
        <img src="images/reception.jpg" alt="Receptionist">
        <h3>Reception</h3>
        <p class="muted">Run the live queue, book at the desk, handle billing,
           user accounts and reports.</p>
        <span class="btn btn-small">Reception login</span>
    </a>

</div>

<p class="muted" style="margin-top:14px">
    New here?
    <a class="link" href="<?= url('register&role=Patient') ?>">Register as a patient</a>
    or
    <a class="link" href="<?= url('register&role=Doctor') ?>">register as a doctor</a>.
</p>



<section class="home-features"><div><span class="eyebrow">Everything in one place</span><h2>Built for a smoother clinic day</h2><p class="muted">From the first appointment to the final receipt, MediCare keeps every step organized.</p></div><div class="feature-pills"><span>✓ Smart appointments</span><span>✓ Patient records</span><span>✓ Digital prescriptions</span><span>✓ Billing &amp; receipts</span><span>✓ Live queue</span><span>✓ Reports &amp; analytics</span></div></section>
