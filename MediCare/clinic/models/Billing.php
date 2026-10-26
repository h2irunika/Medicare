<?php
/*
 * MEMBER 5 - payments and reports.
 *
 *   APPOINTMENT 1 ----- 0..1 PAYMENT
 *
 * 0..1 because a visit exists before anyone pays for it. The UNIQUE on
 * payment.Appointment_ID is what turns "many" into "at most one".
 * Amounts are DECIMAL(10,2), never FLOAT, because FLOAT rounds money.
 */

class Payment
{
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    public function all()
    {
        return $this->pdo->query(
            "SELECT pay.*, a.Appointment_Date, a.Time_Slot,
                    CONCAT(p.First_Name,' ',p.Last_Name)        AS Patient_Name,
                    CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name
             FROM payment pay
             JOIN appointment a ON pay.Appointment_ID = a.Appointment_ID
             JOIN patient p     ON a.Patient_ID = p.Patient_ID
             JOIN doctor  d     ON a.Doctor_ID  = d.Doctor_ID
             ORDER BY pay.Payment_ID DESC"
        )->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT pay.*, a.Appointment_Date, a.Time_Slot, a.Queue_No,
                    CONCAT(p.First_Name,' ',p.Last_Name) AS Patient_Name,
                    p.NIC, p.Phone,
                    CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name,
                    s.Special_Name, c.Clinic_Name, c.Address, c.Contact_No
             FROM payment pay
             JOIN appointment a ON pay.Appointment_ID = a.Appointment_ID
             JOIN patient p     ON a.Patient_ID = p.Patient_ID
             JOIN doctor  d     ON a.Doctor_ID  = d.Doctor_ID
             LEFT JOIN specialization s ON d.Special_ID = s.Special_ID
             LEFT JOIN clinic c         ON d.Clinic_ID  = c.Clinic_ID
             WHERE pay.Payment_ID = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByAppointment($appointmentId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM payment WHERE Appointment_ID = ?");
        $stmt->execute([$appointmentId]);
        return $stmt->fetch();
    }

    /** One patient's bills, for their own portal. */
    public function findByPatient($patientId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT pay.*, a.Appointment_Date,
                    CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name
             FROM payment pay
             JOIN appointment a ON pay.Appointment_ID = a.Appointment_ID
             JOIN doctor d      ON a.Doctor_ID = d.Doctor_ID
             WHERE a.Patient_ID = ?
             ORDER BY pay.Payment_ID DESC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    /** Visits with no bill at all. */
    public function unbilled()
    {
        return $this->pdo->query(
            "SELECT a.Appointment_ID, a.Appointment_Date, a.Time_Slot, a.Status,
                    CONCAT(p.First_Name,' ',p.Last_Name)        AS Patient_Name,
                    CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name,
                    d.Consultation_Fee
             FROM appointment a
             JOIN patient p ON a.Patient_ID = p.Patient_ID
             JOIN doctor  d ON a.Doctor_ID  = d.Doctor_ID
             LEFT JOIN payment pay ON pay.Appointment_ID = a.Appointment_ID
             WHERE pay.Payment_ID IS NULL AND a.Status <> 'Cancelled'
             ORDER BY a.Appointment_Date DESC, a.Time_Slot"
        )->fetchAll();
    }

    public function create($appointmentId, $amount, $status)
    {
        if ($this->findByAppointment($appointmentId)) { return false; }

        if (!in_array($status, ['Paid', 'Not Paid', 'Refunded'], true)) {
            $status = 'Not Paid';
        }

        try {
            $this->pdo->prepare(
                "INSERT INTO payment (Appointment_ID, Amount, Payment_Date, Payment_Status)
                 VALUES (?, ?, ?, ?)"
            )->execute([
                $appointmentId, $amount,
                $status === 'Paid' ? date('Y-m-d') : null,
                $status
            ]);
            return $this->pdo->lastInsertId();

        } catch (PDOException $e) {
            // 23000 = no such appointment, or it already has a bill
            if ($e->getCode() === '23000') { return false; }
            throw $e;
        }
    }

    public function markPaid($id)
    {
        return $this->pdo->prepare(
            "UPDATE payment SET Payment_Status = 'Paid', Payment_Date = CURDATE()
             WHERE Payment_ID = ?"
        )->execute([$id]);
    }

    /** You cannot refund money that was never taken. */
    public function refund($id)
    {
        $stmt = $this->pdo->prepare(
            "UPDATE payment SET Payment_Status = 'Refunded'
             WHERE Payment_ID = ? AND Payment_Status = 'Paid'"
        );
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function counts()
    {
        return $this->pdo->query(
            "SELECT
               (SELECT IFNULL(SUM(Amount),0) FROM payment WHERE Payment_Status='Paid')     AS collected,
               (SELECT IFNULL(SUM(Amount),0) FROM payment WHERE Payment_Status='Not Paid') AS outstanding,
               (SELECT IFNULL(SUM(Amount),0) FROM payment
                  WHERE Payment_Status='Paid' AND Payment_Date=CURDATE())                  AS today,
               (SELECT COUNT(*) FROM payment WHERE Payment_Status='Refunded')              AS refunds"
        )->fetch();
    }
}

/** Management reports. Every number comes from SQL, not a PHP loop. */
class Report
{
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    public function summary()
    {
        return $this->pdo->query(
            "SELECT
               (SELECT COUNT(*) FROM patient)     AS patients,
               (SELECT COUNT(*) FROM doctor)      AS doctors,
               (SELECT COUNT(*) FROM appointment) AS appointments,
               (SELECT IFNULL(SUM(Amount),0) FROM payment WHERE Payment_Status='Paid')
                                                  AS revenue"
        )->fetch();
    }

    /** The sub-query in the SELECT list gives each status its share. */
    public function appointmentsByStatus()
    {
        return $this->pdo->query(
            "SELECT Status, COUNT(*) AS Total,
                    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM appointment), 1) AS Percent
             FROM appointment
             GROUP BY Status
             ORDER BY Total DESC"
        )->fetchAll();
    }

    /** LEFT JOIN keeps doctors who have earned nothing yet. */
    public function revenueByDoctor()
    {
        return $this->pdo->query(
            "SELECT CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name,
                    s.Special_Name,
                    COUNT(a.Appointment_ID) AS Appointments,
                    IFNULL(SUM(CASE WHEN pay.Payment_Status='Paid'
                                    THEN pay.Amount ELSE 0 END),0) AS Revenue
             FROM doctor d
             LEFT JOIN specialization s ON d.Special_ID = s.Special_ID
             LEFT JOIN appointment a    ON a.Doctor_ID = d.Doctor_ID
                                       AND a.Status <> 'Cancelled'
             LEFT JOIN payment pay      ON pay.Appointment_ID = a.Appointment_ID
             GROUP BY d.Doctor_ID, Doctor_Name, s.Special_Name
             ORDER BY Revenue DESC"
        )->fetchAll();
    }

    public function revenueByDay($days = 7)
    {
        return $this->pdo->query(
            "SELECT Payment_Date, COUNT(*) AS Payments, SUM(Amount) AS Total
             FROM payment
             WHERE Payment_Status = 'Paid'
               AND Payment_Date >= DATE_SUB(CURDATE(), INTERVAL " . (int)$days . " DAY)
             GROUP BY Payment_Date
             ORDER BY Payment_Date DESC"
        )->fetchAll();
    }

    public function busiestSlots($limit = 6)
    {
        return $this->pdo->query(
            "SELECT TIME_FORMAT(Time_Slot,'%h:%i %p') AS Slot, COUNT(*) AS Bookings
             FROM appointment
             WHERE Status <> 'Cancelled'
             GROUP BY Time_Slot
             ORDER BY Bookings DESC, Time_Slot
             LIMIT " . (int)$limit
        )->fetchAll();
    }

    public function topPatients($limit = 5)
    {
        return $this->pdo->query(
            "SELECT CONCAT(p.First_Name,' ',p.Last_Name) AS Patient_Name, p.NIC,
                    COUNT(a.Appointment_ID) AS Visits,
                    IFNULL(SUM(CASE WHEN pay.Payment_Status='Paid'
                                    THEN pay.Amount ELSE 0 END),0) AS Paid
             FROM patient p
             JOIN appointment a    ON a.Patient_ID = p.Patient_ID
             LEFT JOIN payment pay ON pay.Appointment_ID = a.Appointment_ID
             WHERE a.Status <> 'Cancelled'
             GROUP BY p.Patient_ID, Patient_Name, p.NIC
             ORDER BY Visits DESC, Paid DESC
             LIMIT " . (int)$limit
        )->fetchAll();
    }

    /** DATEDIFF tells us how long a bill has been unpaid. */
    public function outstandingBills()
    {
        return $this->pdo->query(
            "SELECT pay.Payment_ID, pay.Amount, a.Appointment_Date,
                    CONCAT(p.First_Name,' ',p.Last_Name) AS Patient_Name, p.Phone,
                    DATEDIFF(CURDATE(), a.Appointment_Date) AS Days_Old
             FROM payment pay
             JOIN appointment a ON pay.Appointment_ID = a.Appointment_ID
             JOIN patient p     ON a.Patient_ID = p.Patient_ID
             WHERE pay.Payment_Status = 'Not Paid'
             ORDER BY Days_Old DESC"
        )->fetchAll();
    }
}
