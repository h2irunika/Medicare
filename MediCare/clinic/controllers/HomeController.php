<?php
/*
 * Home page, login, registration and logout for ALL THREE roles.
 * One login form serves patients, doctors and reception - the User_Type
 * column on the account decides where the person lands.
 */
class HomeController
{
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    public function home()
    {
        $doctor = new Doctor($this->pdo);

        view('home', [
            'doctors' => $doctor->all(),
            'title'   => 'Home'
        ]);
    }

    /* ---------------- login ---------------- */

    public function loginForm()
    {
        if (is_logged_in()) {
            redirect(home_for_role(user_role()));
        }

        $role = $_GET['role'] ?? 'Patient';
        if (!in_array($role, ['Patient', 'Doctor', 'Receptionist'], true)) {
            $role = 'Patient';
        }

        view('login', ['role' => $role, 'title' => 'Log in']);
    }

    public function login()
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'Patient';

        $users = new UserAccount($this->pdo);
        $user  = $users->attempt($username, $password);

        // one message for both cases, so the form cannot be used to find out
        // which usernames exist
        if (!$user) {
            set_message('Wrong username or password.', 'error');
            redirect('login&role=' . $role);
        }

        session_regenerate_id(true);          // stops session fixation
        $_SESSION['user'] = [
            'user_id' => $user['User_ID'],
            'name'    => $user['Person_Name'],
            'role'    => $user['User_Type'],
            'ref_id'  => $user['Ref_ID']
        ];

        set_message('Welcome back, ' . $user['Person_Name'] . '.');
        redirect(home_for_role($user['User_Type']));
    }

    public function logout()
    {
        $_SESSION = [];
        session_destroy();
        session_start();
        set_message('You have been logged out.');
        redirect('home');
    }

    /* ---------------- registration ---------------- */

    public function registerForm()
    {
        $role = $_GET['role'] ?? 'Patient';
        if (!in_array($role, ['Patient', 'Doctor'], true)) { $role = 'Patient'; }

        $doctor = new Doctor($this->pdo);

        view('register', [
            'role'            => $role,
            'specializations' => $doctor->specializations(),
            'clinics'         => $doctor->clinics(),
            'title'           => 'Create an account'
        ]);
    }

    public function registerPatient()
    {
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name'  => trim($_POST['last_name'] ?? ''),
            'nic'        => trim($_POST['nic'] ?? ''),
            'dob'        => $_POST['dob'] ?? '',
            'gender'     => $_POST['gender'] ?? '',
            'phone'      => trim($_POST['phone'] ?? ''),
            'email'      => trim($_POST['email'] ?? ''),
            'address'    => trim($_POST['address'] ?? ''),
            'password'   => $_POST['password'] ?? ''
        ];

        if ($data['first_name'] === '' || $data['nic'] === '' || $data['email'] === '') {
            set_message('Please fill every field.', 'error');
            redirect('register&role=Patient');
        }
        if (strlen($data['password']) < 6) {
            set_message('Your password must be at least 6 characters.', 'error');
            redirect('register&role=Patient');
        }

        $patient = new Patient($this->pdo);
        $users   = new UserAccount($this->pdo);

        if ($patient->nicTaken($data['nic'])) {
            set_message('That NIC is already registered.', 'error');
            redirect('register&role=Patient');
        }
        if ($users->usernameTaken($data['email'])) {
            set_message('That email is already registered.', 'error');
            redirect('register&role=Patient');
        }

        if ($patient->register($data) === false) {
            set_message('Registration failed. Please try again.', 'error');
            redirect('register&role=Patient');
        }

        set_message('Account created. You can log in now.');
        redirect('login&role=Patient');
    }

    public function registerDoctor()
    {
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name'  => trim($_POST['last_name'] ?? ''),
            'username'   => trim($_POST['username'] ?? ''),
            'phone'      => trim($_POST['phone'] ?? ''),
            'email'      => trim($_POST['email'] ?? ''),
            'special_id' => $_POST['special_id'] ?? '',
            'clinic_id'  => $_POST['clinic_id'] ?? '',
            'fee'        => is_numeric($_POST['fee'] ?? '') ? $_POST['fee'] : 0,
            'password'   => $_POST['password'] ?? ''
        ];

        if ($data['first_name'] === '' || $data['username'] === '' ||
            $data['special_id'] === '' || $data['clinic_id'] === '') {
            set_message('Please fill every field.', 'error');
            redirect('register&role=Doctor');
        }
        if (strlen($data['password']) < 6) {
            set_message('The password must be at least 6 characters.', 'error');
            redirect('register&role=Doctor');
        }

        $users = new UserAccount($this->pdo);
        if ($users->usernameTaken($data['username'])) {
            set_message('That username is already taken.', 'error');
            redirect('register&role=Doctor');
        }

        $doctor = new Doctor($this->pdo);
        if ($doctor->register($data) === false) {
            set_message('Registration failed. Please try again.', 'error');
            redirect('register&role=Doctor');
        }

        set_message('Doctor account created. You can log in now.');
        redirect('login&role=Doctor');
    }

    /* ---------------- shared JSON endpoint (Member 2) ----------------
     * The booking pages call this when the doctor or the date changes, so
     * free slots load without reloading the page. The patient portal and the
     * reception booking page both use it.
     */
    public function slots()
    {
        header('Content-Type: application/json');

        $doctorId = $_GET['doctor_id'] ?? '';
        $date     = $_GET['date'] ?? '';

        if ($doctorId === '' || $date === '') {
            echo json_encode(['slots' => [], 'message' => 'Choose a doctor and a date.']);
            return;
        }

        $appointment = new Appointment($this->pdo);
        $schedule    = new Schedule($this->pdo);

        $slots = $schedule->freeSlots(
            $doctorId, $date, $appointment->bookedSlots($doctorId, $date)
        );

        echo json_encode([
            'slots'   => $slots,
            'message' => $slots ? '' : 'No free slots for this doctor on this date.'
        ]);
    }
}
