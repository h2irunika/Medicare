<?php
/*
 * MEMBER 2 - Appointment model.
 * Every query that touches the appointment table lives here, so the patient
 * portal, the doctor portal and reception all book by the same rules.
 */
class Appointment
{
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    /** All appointments, newest first (reception list). */
    public function all()
    {
        return $this->pdo->query(
            "SELECT a.*,
                    CONCAT(p.First_Name,' ',p.Last_Name)        AS Patient_Name,
                    CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name
             FROM appointment a
             JOIN patient p ON a.Patient_ID = p.Patient_ID
             JOIN doctor  d ON a.Doctor_ID  = d.Doctor_ID
             ORDER BY a.Appointment_Date DESC, a.Time_Slot"
        )->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.*,
                    CONCAT(p.First_Name,' ',p.Last_Name) AS Patient_Name,
                    p.NIC, p.DOB, p.Gender, p.Phone,
                    CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name,
                    s.Special_Name
             FROM appointment a
             JOIN patient p ON a.Patient_ID = p.Patient_ID
             JOIN doctor  d ON a.Doctor_ID  = d.Doctor_ID
             LEFT JOIN specialization s ON d.Special_ID = s.Special_ID
             WHERE a.Appointment_ID = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** One patient's appointments. */
    public function findByPatient($patientId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.*,
                    CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name,
                    s.Special_Name
             FROM appointment a
             JOIN doctor d ON a.Doctor_ID = d.Doctor_ID
             LEFT JOIN specialization s ON d.Special_ID = s.Special_ID
             WHERE a.Patient_ID = ?
             ORDER BY a.Appointment_Date DESC, a.Time_Slot"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    /** One doctor's appointments on a date (this is the queue). */
    public function queue($doctorId, $date)
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.*,
                    CONCAT(p.First_Name,' ',p.Last_Name) AS Patient_Name,
                    p.Phone,
                    r.Record_ID
             FROM appointment a
             JOIN patient p ON a.Patient_ID = p.Patient_ID
             LEFT JOIN medical_record r ON r.Appointment_ID = a.Appointment_ID
             WHERE a.Doctor_ID = ? AND a.Appointment_Date = ?
               AND a.Status <> 'Cancelled'
             ORDER BY a.Queue_No"
        );
        $stmt->execute([$doctorId, $date]);
        return $stmt->fetchAll();
    }

    /** A doctor's future appointments. */
    public function upcomingForDoctor($doctorId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.*, CONCAT(p.First_Name,' ',p.Last_Name) AS Patient_Name, p.Phone
             FROM appointment a
             JOIN patient p ON a.Patient_ID = p.Patient_ID
             WHERE a.Doctor_ID = ? AND a.Appointment_Date > CURDATE()
               AND a.Status <> 'Cancelled'
             ORDER BY a.Appointment_Date, a.Time_Slot"
        );
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }

    /** Times already taken for a doctor on a date. */
    public function bookedSlots($doctorId, $date)
    {
        $stmt = $this->pdo->prepare(
            "SELECT Time_Slot FROM appointment
             WHERE Doctor_ID = ? AND Appointment_Date = ? AND Status <> 'Cancelled'"
        );
        $stmt->execute([$doctorId, $date]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Next queue number for that doctor on that day.
     * IFNULL turns the NULL from MAX() on an empty day into 0, so the first
     * patient of the day gets 1.
     */
    public function nextQueueNo($doctorId, $date)
    {
        $stmt = $this->pdo->prepare(
            "SELECT IFNULL(MAX(Queue_No),0) + 1
             FROM appointment
             WHERE Doctor_ID = ? AND Appointment_Date = ? AND Status <> 'Cancelled'"
        );
        $stmt->execute([$doctorId, $date]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Create a booking and give it a queue number, inside one transaction.
     * Returns the new Appointment_ID, or false if the slot was already taken.
     */
    public function create($data)
    {
        try {
            $this->pdo->beginTransaction();

            $queueNo = $this->nextQueueNo($data['doctor_id'], $data['date']);

            $stmt = $this->pdo->prepare(
                "INSERT INTO appointment
                    (Patient_ID, Doctor_ID, Schedule_ID, Appointment_Date,
                     Time_Slot, Queue_No, Status, Reason)
                 VALUES (?, ?, ?, ?, ?, ?, 'Confirmed', ?)"
            );
            $stmt->execute([
                $data['patient_id'], $data['doctor_id'], $data['schedule_id'],
                $data['date'], $data['time_slot'], $queueNo, $data['reason']
            ]);

            $id = $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $id;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            // 23000 = the UNIQUE (Doctor_ID, Appointment_Date, Slot_Key) fired,
            // or the doctor/patient does not exist
            if ($e->getCode() === '23000') { return false; }
            throw $e;
        }
    }

    public function updateStatus($id, $status)
    {
        $allowed = ['Pending', 'Confirmed', 'Completed', 'Cancelled', 'No-Show'];
        if (!in_array($status, $allowed, true)) { return false; }

        return $this->pdo->prepare(
            "UPDATE appointment SET Status = ? WHERE Appointment_ID = ?"
        )->execute([$status, $id]);
    }

    /**
     * Cancel. The row stays for history, but Slot_Key in the database becomes
     * NULL, which frees the time slot for another patient.
     */
    public function cancel($id)
    {
        return $this->pdo->prepare(
            "UPDATE appointment SET Status = 'Cancelled', Queue_No = NULL
             WHERE Appointment_ID = ?"
        )->execute([$id]);
    }

    /** Does this appointment belong to this patient? */
    public function belongsToPatient($id, $patientId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM appointment WHERE Appointment_ID = ? AND Patient_ID = ?"
        );
        $stmt->execute([$id, $patientId]);
        return $stmt->fetchColumn() > 0;
    }

    public function counts()
    {
        return $this->pdo->query(
            "SELECT
               (SELECT COUNT(*) FROM appointment) AS total,
               (SELECT COUNT(*) FROM appointment
                  WHERE Appointment_Date = CURDATE() AND Status <> 'Cancelled') AS today,
               (SELECT COUNT(*) FROM appointment WHERE Status = 'Confirmed')  AS confirmed,
               (SELECT COUNT(*) FROM appointment WHERE Status = 'Cancelled')  AS cancelled"
        )->fetch();
    }

    /** Counters for one patient's dashboard. */
    public function patientCounts($patientId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT
               (SELECT COUNT(*) FROM appointment
                  WHERE Patient_ID = :a AND Status IN ('Pending','Confirmed')) AS upcoming,
               (SELECT COUNT(*) FROM appointment
                  WHERE Patient_ID = :b AND Status = 'Completed')              AS completed,
               (SELECT COUNT(*) FROM medical_record r
                  JOIN appointment a ON r.Appointment_ID = a.Appointment_ID
                  WHERE a.Patient_ID = :c)                                     AS records"
        );
        $stmt->execute(['a' => $patientId, 'b' => $patientId, 'c' => $patientId]);
        return $stmt->fetch();
    }

    /** Counters for one doctor's dashboard. */
    public function doctorCounts($doctorId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT
               (SELECT COUNT(*) FROM appointment
                  WHERE Doctor_ID = :a AND Appointment_Date = CURDATE()
                    AND Status <> 'Cancelled')                       AS today,
               (SELECT COUNT(*) FROM appointment
                  WHERE Doctor_ID = :b AND Appointment_Date > CURDATE()
                    AND Status <> 'Cancelled')                       AS upcoming,
               (SELECT COUNT(*) FROM appointment
                  WHERE Doctor_ID = :c AND Status = 'Completed')     AS completed,
               (SELECT COUNT(*) FROM medical_record r
                  JOIN appointment a ON r.Appointment_ID = a.Appointment_ID
                  WHERE a.Doctor_ID = :d)                            AS records"
        );
        $stmt->execute(['a' => $doctorId, 'b' => $doctorId, 'c' => $doctorId, 'd' => $doctorId]);
        return $stmt->fetch();
    }
}
