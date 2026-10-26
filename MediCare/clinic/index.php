<?php
/*
 * ============================================================
 *  Clinic Appointment & Patient Record Management System
 *
 *  ONE entry point. Every page in the system - patient, doctor and
 *  reception - is requested as index.php?page=something, and this file
 *  decides which controller handles it.
 *
 *  Example:  index.php?page=patient/book
 * ============================================================
 */

session_start();

require_once __DIR__ . '/config/database.php';   // gives us $pdo
require_once __DIR__ . '/config/helpers.php';

/* models - one file per member area */
require_once __DIR__ . '/models/Appointment.php';   // Member 2
require_once __DIR__ . '/models/Schedule.php';      // Member 2
require_once __DIR__ . '/models/People.php';        // Members 1 and 3
require_once __DIR__ . '/models/Medical.php';       // Member 4
require_once __DIR__ . '/models/Billing.php';       // Member 5
require_once __DIR__ . '/models/UserAccount.php';   // Member 5

/* controllers */
require_once __DIR__ . '/controllers/HomeController.php';
require_once __DIR__ . '/controllers/PatientController.php';
require_once __DIR__ . '/controllers/DoctorController.php';
require_once __DIR__ . '/controllers/StaffController.php';

/*
 * The route table: 'page value' => ['ControllerName', 'method'].
 * Adding a page means adding one line here.
 */
$routes = [

    /* --- open to everyone --- */
    'home'                  => ['HomeController', 'home'],
    'login'                 => ['HomeController', 'loginForm'],
    'login/check'           => ['HomeController', 'login'],
    'register'              => ['HomeController', 'registerForm'],
    'register/patient'      => ['HomeController', 'registerPatient'],
    'register/doctor'       => ['HomeController', 'registerDoctor'],
    'logout'                => ['HomeController', 'logout'],
    'api/slots'             => ['HomeController', 'slots'],

    /* --- patient portal (Member 1) --- */
    'patient/dashboard'     => ['PatientController', 'dashboard'],
    'patient/doctors'       => ['PatientController', 'doctors'],
    'patient/book'          => ['PatientController', 'book'],
    'patient/appointments'  => ['PatientController', 'appointments'],
    'patient/cancel'        => ['PatientController', 'cancel'],
    'patient/history'       => ['PatientController', 'history'],
    'patient/prescriptions' => ['PatientController', 'prescriptions'],
    'patient/bills'         => ['PatientController', 'bills'],
    'patient/profile'       => ['PatientController', 'profile'],

    /* --- doctor portal (Member 3) --- */
    'doctor/dashboard'      => ['DoctorController', 'dashboard'],
    'doctor/appointments'   => ['DoctorController', 'appointments'],
    'doctor/schedule'       => ['DoctorController', 'schedule'],
    'doctor/consultation'   => ['DoctorController', 'consultation'],
    'doctor/prescription'   => ['DoctorController', 'prescription'],
    'doctor/records'        => ['DoctorController', 'records'],
    'doctor/profile'        => ['DoctorController', 'profile'],

    /* --- reception and admin (Members 2, 4 and 5) --- */
    'staff/queue'           => ['StaffController', 'queue'],
    'staff/appointments'    => ['StaffController', 'appointments'],
    'staff/book'            => ['StaffController', 'book'],
    'staff/status'          => ['StaffController', 'status'],
    'staff/records'         => ['StaffController', 'records'],
    'staff/record'          => ['StaffController', 'record'],
    'staff/medicines'       => ['StaffController', 'medicines'],
    'staff/payments'        => ['StaffController', 'payments'],
    'staff/receipt'         => ['StaffController', 'receipt'],
    'staff/reports'         => ['StaffController', 'reports'],
    'staff/users'           => ['StaffController', 'users'],
    'staff/patients'        => ['StaffController', 'patients'],
    'staff/doctors'         => ['StaffController', 'doctors'],
];

$page = $_GET['page'] ?? 'home';

if (!isset($routes[$page])) {
    http_response_code(404);
    view('notfound', ['title' => 'Page not found']);
    exit;
}

list($controllerName, $method) = $routes[$page];

$controller = new $controllerName($pdo);
$controller->$method();
