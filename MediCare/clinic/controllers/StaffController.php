<?php
/*
 * Reception and admin.
 * This one controller covers the staff side of three members' work:
 *   queue and appointments   - Member 2
 *   medical records, medicines - Member 4
 *   payments, reports, user accounts - Member 5
 */
class StaffController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        require_role('Receptionist');
    }

    /* ================= MEMBER 2: queue and appointments ================= */

    public function queue()
    {
        $appointment = new Appointment($this->pdo);
        $doctorModel = new Doctor($this->pdo);

        $doctors  = $doctorModel->all();
        $doctorId = $_GET['doctor_id'] ?? ($doctors ? $doctors[0]['Doctor_ID'] : 0);
        $date     = $_GET['date'] ?? date('Y-m-d');

        $queue = $appointment->queue($doctorId, $date);

        $nowServing = null;
        foreach ($queue as $row) {
            if (in_array($row['Status'], ['Confirmed', 'Pending'], true)) {
                $nowServing = $row;
                break;
            }
        }

        view('staff/queue', [
            'doctors'    => $doctors,
            'doctorId'   => $doctorId,
            'date'       => $date,
            'queue'      => $queue,
            'nowServing' => $nowServing,
            'counts'     => $appointment->counts(),
            'title'      => "Today's Queue"
        ]);
    }

    public function appointments()
    {
        $appointment = new Appointment($this->pdo);
        view('staff/appointments', [
            'appointments' => $appointment->all(),
            'title'        => 'All Appointments'
        ]);
    }

    /** Searchable patient directory for reception. */
    public function patients()
    {
        $patient = new Patient($this->pdo);
        $search = trim($_GET['q'] ?? '');
        view('staff/patients', [
            'patients' => $patient->directory($search),
            'search'   => $search,
            'title'    => 'Patient Directory'
        ]);
    }

    /** Searchable doctor directory for reception. */
    public function doctors()
    {
        $doctor = new Doctor($this->pdo);
        $search = trim($_GET['q'] ?? '');
        view('staff/doctors', [
            'doctors' => $doctor->directory($search),
            'search'  => $search,
            'title'   => 'Doctor Directory'
        ]);
    }

    /** Reception books on behalf of a patient at the desk. */
    public function book()
    {
        $appointment = new Appointment($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $patientId = $_POST['patient_id'] ?? '';
            $doctorId  = $_POST['doctor_id'] ?? '';
            $date      = $_POST['date'] ?? '';
            $slot      = $_POST['time_slot'] ?? '';

            if ($patientId === '' || $doctorId === '' || $date === '' || $slot === '') {
                set_message('Please fill every field and choose a time slot.', 'error');
                redirect('staff/book');
            }

            list($scheduleId, $timeSlot) = array_pad(explode('|', $slot), 2, null);

            $newId = $appointment->create([
                'patient_id'  => $patientId,
                'doctor_id'   => $doctorId,
                'schedule_id' => $scheduleId,
                'date'        => $date,
                'time_slot'   => $timeSlot,
                'reason'      => trim($_POST['reason'] ?? '')
            ]);

            if ($newId === false) {
                set_message('That time slot was just taken. Please choose another one.', 'error');
                redirect('staff/book');
            }

            $row = $appointment->find($newId);
            set_message('Booked. Queue number ' . $row['Queue_No'] . '.');
            redirect('staff/appointments');
        }

        $doctor  = new Doctor($this->pdo);
        $patient = new Patient($this->pdo);

        view('staff/book', [
            'doctors'  => $doctor->all(),
            'patients' => $patient->all(),
            'selectedPatient' => $_GET['patient_id'] ?? '',
            'today'    => date('Y-m-d'),
            'maxDate'  => date('Y-m-d', strtotime('+6 days')),
            'action'   => url('staff/book'),
            'title'    => 'Book for a Patient'
        ]);
    }

    /** Mark complete, no-show, or cancel. */
    public function status()
    {
        $appointment = new Appointment($this->pdo);
        $id     = $_GET['id'] ?? 0;
        $status = $_GET['status'] ?? '';
        $back   = $_GET['back'] ?? 'staff/appointments';

        if ($status === 'Cancelled') {
            $appointment->cancel($id);
            set_message('Appointment cancelled. The time slot is free again.');
        } elseif ($appointment->updateStatus($id, $status)) {
            set_message('Status updated to ' . $status . '.');
        } else {
            set_message('That status is not allowed.', 'error');
        }

        redirect($back);
    }

    /* ================= MEMBER 4: records and medicines ================= */

    public function records()
    {
        $record       = new MedicalRecord($this->pdo);
        $prescription = new Prescription($this->pdo);

        view('staff/records', [
            'counts'  => $record->counts(),
            'pending' => $record->withoutRecord(),
            'records' => $record->all(),
            'topMeds' => $prescription->mostPrescribed(5),
            'title'   => 'Medical Records'
        ]);
    }

    /** One record with its prescriptions. */
    public function record()
    {
        $record       = new MedicalRecord($this->pdo);
        $prescription = new Prescription($this->pdo);

        $row = $record->find($_GET['id'] ?? 0);

        if (!$row) {
            set_message('Medical record not found.', 'error');
            redirect('staff/records');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $record->update($row['Record_ID'], [
                'diagnosis'       => trim($_POST['diagnosis'] ?? ''),
                'symptoms'        => trim($_POST['symptoms'] ?? ''),
                'treatment_notes' => trim($_POST['treatment_notes'] ?? '')
            ]);
            set_message('Medical record updated.');
            redirect('staff/record&id=' . $row['Record_ID']);
        }

        view('staff/record', [
            'record'        => $row,
            'prescriptions' => $prescription->findByRecord($row['Record_ID']),
            'title'         => 'Record #' . $row['Record_ID']
        ]);
    }

    public function medicines()
    {
        $medicine = new Medicine($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['medicine_name'] ?? '');
            if ($name === '') {
                set_message('Enter a medicine name.', 'error');
            } else {
                $medicine->create($name, trim($_POST['description'] ?? ''));
                set_message('Medicine added.');
            }
            redirect('staff/medicines');
        }

        if (isset($_GET['delete'])) {
            if ($medicine->delete($_GET['delete'])) {
                set_message('Medicine removed.');
            } else {
                set_message('This medicine is on a prescription, so it cannot be deleted.', 'error');
            }
            redirect('staff/medicines');
        }

        view('staff/medicines', [
            'medicines' => $medicine->allWithUsage(),
            'title'     => 'Medicines'
        ]);
    }

    /* ================= MEMBER 5: payments, reports, users ================= */

    public function payments()
    {
        $payment = new Payment($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $appointmentId = $_POST['appointment_id'] ?? '';
            $amount        = $_POST['amount'] ?? '';

            if ($appointmentId === '' || !is_numeric($amount) || $amount < 0) {
                set_message('Choose a visit and enter a valid amount.', 'error');
                redirect('staff/payments');
            }

            $newId = $payment->create($appointmentId, $amount, $_POST['payment_status'] ?? 'Not Paid');

            if ($newId === false) {
                set_message('That visit already has a bill.', 'error');
                redirect('staff/payments');
            }

            set_message('Bill created.');
            redirect('staff/receipt&id=' . $newId);
        }

        if (isset($_GET['paid'])) {
            $payment->markPaid($_GET['paid']);
            set_message('Payment recorded.');
            redirect('staff/payments');
        }

        if (isset($_GET['refund'])) {
            if ($payment->refund($_GET['refund'])) {
                set_message('Payment refunded.');
            } else {
                set_message('Only a paid bill can be refunded.', 'error');
            }
            redirect('staff/payments');
        }

        view('staff/payments', [
            'counts'   => $payment->counts(),
            'unbilled' => $payment->unbilled(),
            'payments' => $payment->all(),
            'title'    => 'Payments'
        ]);
    }

    public function receipt()
    {
        $payment = new Payment($this->pdo);
        $row = $payment->find($_GET['id'] ?? 0);

        if (!$row) {
            set_message('Payment not found.', 'error');
            redirect('staff/payments');
        }

        view('staff/receipt', ['payment' => $row, 'title' => 'Receipt']);
    }

    public function reports()
    {
        $report = new Report($this->pdo);

        view('staff/reports', [
            'summary'     => $report->summary(),
            'byStatus'    => $report->appointmentsByStatus(),
            'byDoctor'    => $report->revenueByDoctor(),
            'byDay'       => $report->revenueByDay(7),
            'busiest'     => $report->busiestSlots(6),
            'topPatients' => $report->topPatients(5),
            'outstanding' => $report->outstandingBills(),
            'title'       => 'Reports'
        ]);
    }

    public function users()
    {
        $users = new UserAccount($this->pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (isset($_POST['new_password'])) {
                if (strlen($_POST['new_password']) < 6) {
                    set_message('The new password must be at least 6 characters.', 'error');
                } else {
                    $users->resetPassword($_POST['user_id'], $_POST['new_password']);
                    set_message('Password reset.');
                }
            } else {
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';

                if ($username === '' || strlen($password) < 6) {
                    set_message('Enter a username and a password of at least 6 characters.', 'error');
                } elseif ($users->create($username, $password, $_POST['user_type'] ?? '') === false) {
                    set_message('That username is taken, or the role is not valid.', 'error');
                } else {
                    set_message('Account created.');
                }
            }
            redirect('staff/users');
        }

        if (isset($_GET['delete'])) {
            if ($_GET['delete'] == current_user()['user_id']) {
                set_message('You cannot delete the account you are logged in with.', 'error');
            } elseif ($users->delete($_GET['delete'])) {
                set_message('Account deleted.');
            } else {
                set_message('That account belongs to a person, so it cannot be deleted.', 'error');
            }
            redirect('staff/users');
        }

        view('staff/users', [
            'users'  => $users->all(),
            'counts' => $users->countsByType(),
            'title'  => 'User Accounts'
        ]);
    }
}
