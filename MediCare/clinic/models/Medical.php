<?php
/*
 * MEMBER 4 - medical records, prescriptions and medicines.
 *
 *   APPOINTMENT 1 -- 0..1 MEDICAL_RECORD 1 -- M PRESCRIPTION
 *   PRESCRIPTION 1 -- M PRESCRIPTION_ITEM M -- 1 MEDICINE
 *
 * prescription_item is the bridge that lets one prescription hold many
 * medicines. Dosage and quantity belong to the pairing, so they live there.
 */

class MedicalRecord
{
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    public function all()
    {
        return $this->pdo->query(
            "SELECT r.*, a.Appointment_Date,
                    CONCAT(p.First_Name,' ',p.Last_Name)        AS Patient_Name,
                    CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name
             FROM medical_record r
             JOIN appointment a ON r.Appointment_ID = a.Appointment_ID
             JOIN patient p     ON a.Patient_ID = p.Patient_ID
             JOIN doctor  d     ON a.Doctor_ID  = d.Doctor_ID
             ORDER BY r.Record_Date DESC, r.Record_ID DESC"
        )->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, a.Appointment_ID, a.Appointment_Date, a.Time_Slot, a.Reason,
                    a.Doctor_ID, p.Patient_ID, p.NIC, p.DOB, p.Gender,
                    CONCAT(p.First_Name,' ',p.Last_Name)        AS Patient_Name,
                    CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name,
                    s.Special_Name
             FROM medical_record r
             JOIN appointment a ON r.Appointment_ID = a.Appointment_ID
             JOIN patient p     ON a.Patient_ID = p.Patient_ID
             JOIN doctor  d     ON a.Doctor_ID  = d.Doctor_ID
             LEFT JOIN specialization s ON d.Special_ID = s.Special_ID
             WHERE r.Record_ID = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Every record of one patient. */
    public function findByPatient($patientId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, a.Appointment_Date,
                    CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name,
                    s.Special_Name,
                    (SELECT COUNT(*) FROM prescription pr WHERE pr.Record_ID = r.Record_ID)
                        AS Prescription_Count
             FROM medical_record r
             JOIN appointment a ON r.Appointment_ID = a.Appointment_ID
             JOIN doctor d      ON a.Doctor_ID = d.Doctor_ID
             LEFT JOIN specialization s ON d.Special_ID = s.Special_ID
             WHERE a.Patient_ID = ?
             ORDER BY a.Appointment_Date DESC"
        );
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    /** Records written by one doctor, with an optional search. */
    public function findByDoctor($doctorId, $search = '')
    {
        $sql = "SELECT r.*, a.Appointment_Date,
                       CONCAT(p.First_Name,' ',p.Last_Name) AS Patient_Name, p.NIC,
                       (SELECT COUNT(*) FROM prescription pr WHERE pr.Record_ID = r.Record_ID)
                           AS Prescription_Count
                FROM medical_record r
                JOIN appointment a ON r.Appointment_ID = a.Appointment_ID
                JOIN patient p     ON a.Patient_ID = p.Patient_ID
                WHERE a.Doctor_ID = ?";
        $params = [$doctorId];

        if ($search !== '') {
            $sql .= " AND (p.First_Name LIKE ? OR p.Last_Name LIKE ?
                           OR p.NIC LIKE ? OR r.Diagnosis LIKE ?)";
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like, $like);
        }

        $sql .= " ORDER BY a.Appointment_Date DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findByAppointment($appointmentId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM medical_record WHERE Appointment_ID = ?");
        $stmt->execute([$appointmentId]);
        return $stmt->fetch();
    }

    /**
     * Consultations with no record yet.
     * LEFT JOIN plus IS NULL is how you ask "which rows on the left have
     * nothing matching on the right".
     */
    public function withoutRecord($doctorId = null)
    {
        $sql = "SELECT a.Appointment_ID, a.Appointment_Date, a.Time_Slot, a.Reason,
                       CONCAT(p.First_Name,' ',p.Last_Name)        AS Patient_Name,
                       CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name
                FROM appointment a
                JOIN patient p ON a.Patient_ID = p.Patient_ID
                JOIN doctor  d ON a.Doctor_ID  = d.Doctor_ID
                LEFT JOIN medical_record r ON r.Appointment_ID = a.Appointment_ID
                WHERE r.Record_ID IS NULL
                  AND a.Status IN ('Completed','Confirmed')";
        $params = [];

        if ($doctorId) {
            $sql .= " AND a.Doctor_ID = ?";
            $params[] = $doctorId;
        }

        $sql .= " ORDER BY a.Appointment_Date DESC, a.Time_Slot";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Write the record and complete the appointment together.
     * A saved record with the appointment still "Confirmed" would contradict
     * itself, so both happen or neither does.
     */
    public function create($data)
    {
        if ($this->findByAppointment($data['appointment_id'])) { return false; }

        try {
            $this->pdo->beginTransaction();

            $this->pdo->prepare(
                "INSERT INTO medical_record
                    (Appointment_ID, Diagnosis, Symptoms, Treatment_Notes, Record_Date)
                 VALUES (?, ?, ?, ?, CURDATE())"
            )->execute([
                $data['appointment_id'], $data['diagnosis'],
                $data['symptoms'], $data['treatment_notes']
            ]);

            $id = $this->pdo->lastInsertId();

            $this->pdo->prepare(
                "UPDATE appointment SET Status = 'Completed' WHERE Appointment_ID = ?"
            )->execute([$data['appointment_id']]);

            $this->pdo->commit();
            return $id;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            if ($e->getCode() === '23000') { return false; }
            throw $e;
        }
    }

    public function update($id, $data)
    {
        return $this->pdo->prepare(
            "UPDATE medical_record
             SET Diagnosis = ?, Symptoms = ?, Treatment_Notes = ?
             WHERE Record_ID = ?"
        )->execute([
            $data['diagnosis'], $data['symptoms'], $data['treatment_notes'], $id
        ]);
    }

    public function counts()
    {
        return $this->pdo->query(
            "SELECT
               (SELECT COUNT(*) FROM medical_record) AS records,
               (SELECT COUNT(*) FROM prescription)   AS prescriptions,
               (SELECT COUNT(*) FROM medicine)       AS medicines,
               (SELECT COUNT(*) FROM medical_record WHERE Record_Date = CURDATE()) AS today"
        )->fetch();
    }
}

class Prescription
{
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    /** Prescriptions of one record, each with its medicine lines. */
    public function findByRecord($recordId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM prescription WHERE Record_ID = ? ORDER BY Prescription_ID DESC"
        );
        $stmt->execute([$recordId]);
        $list = $stmt->fetchAll();

        foreach ($list as $i => $row) {
            $list[$i]['Items'] = $this->items($row['Prescription_ID']);
        }
        return $list;
    }

    public function items($prescriptionId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT i.*, m.Medicine_Name
             FROM prescription_item i
             JOIN medicine m ON i.Medicine_ID = m.Medicine_ID
             WHERE i.Prescription_ID = ?
             ORDER BY i.Item_ID"
        );
        $stmt->execute([$prescriptionId]);
        return $stmt->fetchAll();
    }

    /** Every prescription of one patient, newest first. */
    public function findByPatient($patientId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT pr.Prescription_ID, pr.Prescription_Date, r.Diagnosis,
                    CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name
             FROM prescription pr
             JOIN medical_record r ON pr.Record_ID = r.Record_ID
             JOIN appointment a    ON r.Appointment_ID = a.Appointment_ID
             JOIN doctor d         ON a.Doctor_ID = d.Doctor_ID
             WHERE a.Patient_ID = ?
             ORDER BY pr.Prescription_Date DESC, pr.Prescription_ID DESC"
        );
        $stmt->execute([$patientId]);
        $list = $stmt->fetchAll();

        foreach ($list as $i => $row) {
            $list[$i]['Items'] = $this->items($row['Prescription_ID']);
        }
        return $list;
    }

    /**
     * Save a prescription with all of its medicine lines as one unit.
     * If no medicine was chosen the whole thing is rolled back, so the
     * database never holds an empty prescription.
     */
    public function create($recordId, array $items)
    {
        try {
            $this->pdo->beginTransaction();

            $this->pdo->prepare(
                "INSERT INTO prescription (Record_ID, Prescription_Date)
                 VALUES (?, CURDATE())"
            )->execute([$recordId]);

            $prescriptionId = $this->pdo->lastInsertId();

            $itemStmt = $this->pdo->prepare(
                "INSERT INTO prescription_item
                    (Prescription_ID, Medicine_ID, Dosage, Frequency, Duration,
                     Quantity, Instructions)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            $saved = 0;
            foreach ($items as $item) {
                if (empty($item['medicine_id'])) { continue; }
                $itemStmt->execute([
                    $prescriptionId, $item['medicine_id'],
                    $item['dosage'] ?? '', $item['frequency'] ?? '',
                    $item['duration'] ?? '', (int)($item['quantity'] ?? 0),
                    $item['instructions'] ?? ''
                ]);
                $saved++;
            }

            if ($saved === 0) {
                $this->pdo->rollBack();
                return false;
            }

            $this->pdo->commit();
            return $prescriptionId;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /** Delete the item rows first, then the parent - the foreign key demands it. */
    public function delete($id)
    {
        try {
            $this->pdo->beginTransaction();
            $this->pdo->prepare("DELETE FROM prescription_item WHERE Prescription_ID = ?")
                      ->execute([$id]);
            $this->pdo->prepare("DELETE FROM prescription WHERE Prescription_ID = ?")
                      ->execute([$id]);
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /** Report: which medicines are prescribed most often. */
    public function mostPrescribed($limit = 5)
    {
        return $this->pdo->query(
            "SELECT m.Medicine_Name,
                    COUNT(i.Item_ID) AS Times_Prescribed,
                    SUM(i.Quantity)  AS Total_Quantity
             FROM prescription_item i
             JOIN medicine m ON i.Medicine_ID = m.Medicine_ID
             GROUP BY m.Medicine_ID, m.Medicine_Name
             ORDER BY Times_Prescribed DESC
             LIMIT " . (int)$limit
        )->fetchAll();
    }
}

class Medicine
{
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    public function all()
    {
        return $this->pdo->query("SELECT * FROM medicine ORDER BY Medicine_Name")->fetchAll();
    }

    public function allWithUsage()
    {
        return $this->pdo->query(
            "SELECT m.*, COUNT(i.Item_ID) AS Used_Count
             FROM medicine m
             LEFT JOIN prescription_item i ON m.Medicine_ID = i.Medicine_ID
             GROUP BY m.Medicine_ID
             ORDER BY m.Medicine_Name"
        )->fetchAll();
    }

    public function create($name, $description)
    {
        return $this->pdo->prepare(
            "INSERT INTO medicine (Medicine_Name, Description) VALUES (?, ?)"
        )->execute([$name, $description]);
    }

    /** Only if it has never been prescribed - otherwise the foreign key breaks. */
    public function delete($id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM prescription_item WHERE Medicine_ID = ?"
        );
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) { return false; }

        return $this->pdo->prepare("DELETE FROM medicine WHERE Medicine_ID = ?")
                         ->execute([$id]);
    }
}
