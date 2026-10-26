<?php
/*
 * MEMBER 1 - the patient portal.
 * Every method starts with require_role('Patient'), and every query filters
 * by the Patient_ID stored in the session at login - never by a value sent
 * from the browser, so one patient cannot read another's data.
 */
class PatientController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        require_role('Patient');
    }

    public function dashboard()
    {
        $appointment = new Appointment($this->pdo);

        $next = null;
        foreach ($appointment->findByPatient(ref_id()) as $a) {
            if ($a['Appointment_Date'] >= date('Y-m-d') &&
                in_array($a['Status'], ['Pending', 'Confirmed'], true)) {
                $next = $a;   // the list is newest first, so keep the last match
            }
        }

        view('patient/dashboard', [
            'counts' => $appointment->patientCounts(ref_id()),
            'next'   => $next,
            'title'  => 'My Dashboard'
        ]);
    }

    public function doctors()
    {
        $doctor = new Doctor($this->pdo);
        view('patient/doctors', ['doctors' => $doctor->all(), 'title' => 'Our Doctors']);
    }

    /** Booking uses Member 2's models, so patients and reception book alike. */
    public function book()
    {
        $appointment = new Appointment($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $doctorId = $_POST['doctor_id'] ?? '';
            $date     = $_POST['date'] ?? '';
            $slot     = $_POST['time_slot'] ?? '';        // scheduleID|HH:MM:SS

            if ($doctorId === '' || $date === '' || $slot === '') {
                set_message('Please choose a doctor, a date and a time slot.', 'error');
                redirect('patient/book');
            }
            if ($date < date('Y-m-d')) {
                set_message('You cannot book a date in the past.', 'error');
                redirect('patient/book');
            }

            list($scheduleId, $timeSlot) = array_pad(explode('|', $slot), 2, null);

            $newId = $appointment->create([
                'patient_id'  => ref_id(),
                'doctor_id'   => $doctorId,
                'schedule_id' => $scheduleId,
                'date'        => $date,
                'time_slot'   => $timeSlot,
                'reason'      => trim($_POST['reason'] ?? '')
            ]);

            if ($newId === false) {
                set_message('That time slot was just taken. Please choose another one.', 'error');
                redirect('patient/book');
            }

            $row = $appointment->find($newId);
            set_message('Appointment confirmed. Your queue number is ' . $row['Queue_No'] . '.');
            redirect('patient/appointments');
        }

        $doctor = new Doctor($this->pdo);

        view('patient/book', [
            'doctors'  => $doctor->all(),
            'selected' => $_GET['doctor_id'] ?? '',
            'today'    => date('Y-m-d'),
            'maxDate'  => date('Y-m-d', strtotime('+6 days')),
            'action'   => url('patient/book'),
            'title'    => 'Book an Appointment'
        ]);
    }

    public function appointments()
    {
        $appointment = new Appointment($this->pdo);
        view('patient/appointments', [
            'appointments' => $appointment->findByPatient(ref_id()),
            'title'        => 'My Appointments'
        ]);
    }

    public function cancel()
    {
        $appointment = new Appointment($this->pdo);
        $id = $_GET['id'] ?? 0;

        // the appointment must really be this patient's
        if (!$appointment->belongsToPatient($id, ref_id())) {
            set_message('That appointment is not yours to cancel.', 'error');
            redirect('patient/appointments');
        }

        $appointment->cancel($id);
        set_message('Appointment cancelled. The time slot is free again.');
        redirect('patient/appointments');
    }

    public function history()
    {
        $record = new MedicalRecord($this->pdo);
        view('patient/history', [
            'records' => $record->findByPatient(ref_id()),
            'title'   => 'Medical History'
        ]);
    }

    public function prescriptions()
    {
        $prescription = new Prescription($this->pdo);
        view('patient/prescriptions', [
            'prescriptions' => $prescription->findByPatient(ref_id()),
            'title'         => 'My Prescriptions'
        ]);
    }

    public function bills()
    {
        $payment = new Payment($this->pdo);
        view('patient/bills', [
            'payments' => $payment->findByPatient(ref_id()),
            'title'    => 'My Bills'
        ]);
    }

    public function profile()
    {
        $patient = new Patient($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $patient->updateContact(
                ref_id(),
                trim($_POST['phone'] ?? ''),
                trim($_POST['email'] ?? ''),
                trim($_POST['address'] ?? '')
            );
            set_message('Your details have been updated.');
            redirect('patient/profile');
        }

        view('patient/profile', [
            'patient' => $patient->find(ref_id()),
            'title'   => 'My Profile'
        ]);
    }
}
