<?php
/*
 * MEMBER 3 - the doctor portal.
 * Every query carries AND Doctor_ID = the ID from the session, so one doctor
 * cannot open another doctor's queue or records by editing the URL.
 */
class DoctorController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        require_role('Doctor');
    }

    public function dashboard()
    {
        $appointment = new Appointment($this->pdo);
        $doctor      = new Doctor($this->pdo);

        $queue = $appointment->queue(ref_id(), date('Y-m-d'));

        // the first patient in queue order who is not finished
        $nowServing = null;
        foreach ($queue as $row) {
            if (in_array($row['Status'], ['Confirmed', 'Pending'], true)) {
                $nowServing = $row;
                break;
            }
        }

        view('doctor/dashboard', [
            'doctor'     => $doctor->find(ref_id()),
            'counts'     => $appointment->doctorCounts(ref_id()),
            'queue'      => $queue,
            'nowServing' => $nowServing,
            'title'      => 'Doctor Dashboard'
        ]);
    }

    public function appointments()
    {
        $appointment = new Appointment($this->pdo);
        $date = $_GET['date'] ?? date('Y-m-d');

        view('doctor/appointments', [
            'date'     => $date,
            'today'    => $appointment->queue(ref_id(), $date),
            'upcoming' => $appointment->upcomingForDoctor(ref_id()),
            'title'    => 'My Appointments'
        ]);
    }

    /** Working sessions. Member 2's booking page turns these into slots. */
    public function schedule()
    {
        $schedule = new Schedule($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $date  = $_POST['available_date'] ?? '';
            $start = $_POST['start_time'] ?? '';
            $end   = $_POST['end_time'] ?? '';

            if ($date < date('Y-m-d')) {
                set_message('You cannot add a session in the past.', 'error');
            } elseif ($start >= $end) {
                set_message('The end time must be after the start time.', 'error');
            } elseif ($schedule->create(ref_id(), $date, $start, $end) === false) {
                set_message('You already have a session starting at that time on that date.', 'error');
            } else {
                set_message('Session added.');
            }
            redirect('doctor/schedule');
        }

        if (isset($_GET['toggle'])) {
            $schedule->toggle($_GET['toggle'], ref_id());
            set_message('Session updated.');
            redirect('doctor/schedule');
        }

        view('doctor/schedule', [
            'schedules' => $schedule->upcoming(ref_id()),
            'today'     => date('Y-m-d'),
            'title'     => 'My Schedule'
        ]);
    }

    /** Write the medical record for one appointment. */
    public function consultation()
    {
        $appointment = new Appointment($this->pdo);
        $record      = new MedicalRecord($this->pdo);

        $appointmentId = $_GET['id'] ?? ($_POST['appointment_id'] ?? '');
        $row = $appointmentId !== '' ? $appointment->find($appointmentId) : null;

        if (!$row || $row['Doctor_ID'] != ref_id()) {
            set_message('That appointment was not found, or it is not yours.', 'error');
            redirect('doctor/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $diagnosis = trim($_POST['diagnosis'] ?? '');

            if ($diagnosis === '') {
                set_message('A diagnosis is required.', 'error');
                redirect('doctor/consultation&id=' . $appointmentId);
            }

            $newId = $record->create([
                'appointment_id'  => $appointmentId,
                'diagnosis'       => $diagnosis,
                'symptoms'        => trim($_POST['symptoms'] ?? ''),
                'treatment_notes' => trim($_POST['treatment'] ?? '')
            ]);

            if ($newId === false) {
                set_message('This appointment already has a medical record.', 'error');
                redirect('doctor/dashboard');
            }

            set_message('Record saved and the appointment is complete. Add a prescription below.');
            redirect('doctor/prescription&id=' . $newId);
        }

        view('doctor/consultation', [
            'appointment' => $row,
            'existing'    => $record->findByAppointment($appointmentId),
            'title'       => 'Consultation'
        ]);
    }

    /** Prescribe medicines for a record. Uses Member 4's model to save. */
    public function prescription()
    {
        $record       = new MedicalRecord($this->pdo);
        $prescription = new Prescription($this->pdo);
        $medicine     = new Medicine($this->pdo);

        $recordId = $_GET['id'] ?? ($_POST['record_id'] ?? '');
        $row = $recordId !== '' ? $record->find($recordId) : null;

        if (!$row || $row['Doctor_ID'] != ref_id()) {
            set_message('That record was not found, or it is not yours.', 'error');
            redirect('doctor/records');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // the form posts parallel arrays: medicine_id[], dosage[], ...
            $items = [];
            foreach (($_POST['medicine_id'] ?? []) as $i => $medicineId) {
                $items[] = [
                    'medicine_id'  => $medicineId,
                    'dosage'       => $_POST['dosage'][$i]       ?? '',
                    'frequency'    => $_POST['frequency'][$i]    ?? '',
                    'duration'     => $_POST['duration'][$i]     ?? '',
                    'quantity'     => $_POST['quantity'][$i]     ?? 0,
                    'instructions' => $_POST['instructions'][$i] ?? ''
                ];
            }

            if ($prescription->create($recordId, $items) === false) {
                set_message('Add at least one medicine to the prescription.', 'error');
            } else {
                set_message('Prescription saved.');
            }
            redirect('doctor/prescription&id=' . $recordId);
        }

        view('doctor/prescription', [
            'record'        => $row,
            'prescriptions' => $prescription->findByRecord($recordId),
            'medicines'     => $medicine->all(),
            'title'         => 'Prescription'
        ]);
    }

    public function records()
    {
        $record = new MedicalRecord($this->pdo);
        $search = trim($_GET['q'] ?? '');

        view('doctor/records', [
            'records' => $record->findByDoctor(ref_id(), $search),
            'pending' => $record->withoutRecord(ref_id()),
            'search'  => $search,
            'title'   => 'Medical Records'
        ]);
    }

    public function profile()
    {
        $doctor = new Doctor($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $doctor->updateProfile(
                ref_id(),
                trim($_POST['phone'] ?? ''),
                trim($_POST['email'] ?? ''),
                is_numeric($_POST['fee'] ?? '') ? $_POST['fee'] : 0
            );
            set_message('Profile updated.');
            redirect('doctor/profile');
        }

        view('doctor/profile', [
            'doctor' => $doctor->find(ref_id()),
            'title'  => 'My Profile'
        ]);
    }
}
