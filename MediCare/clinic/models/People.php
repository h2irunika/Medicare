<?php
/*
 * MEMBERS 1 and 3 - patients, doctors, clinics and specializations.
 * Two small classes kept in one file because they are both simple lookups.
 */

/** MEMBER 1 - the patient table. */
class Patient
{
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    public function all()
    {
        return $this->pdo->query(
            "SELECT Patient_ID, NIC, Phone,
                    CONCAT(First_Name,' ',Last_Name) AS Patient_Name
             FROM patient ORDER BY First_Name"
        )->fetchAll();
    }

    public function directory($search = '')
    {
        $sql = "SELECT p.Patient_ID, p.NIC, p.First_Name, p.Last_Name, p.DOB, p.Gender,
                       p.Phone, p.Email, p.Address, p.Registered_Date,
                       (SELECT COUNT(*) FROM appointment a WHERE a.Patient_ID = p.Patient_ID) AS Visits
                FROM patient p";
        $params = [];
        if ($search !== '') {
            $sql .= " WHERE CONCAT_WS(' ', p.First_Name, p.Last_Name, p.NIC, p.Phone, p.Email) LIKE ?";
            $params[] = '%' . $search . '%';
        }
        $sql .= " ORDER BY p.First_Name, p.Last_Name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM patient WHERE Patient_ID = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function nicTaken($nic)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM patient WHERE NIC = ?");
        $stmt->execute([$nic]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Register a patient: the login row and the person row must both exist,
     * so they are written inside one transaction.
     * Returns the new Patient_ID, or false.
     */
    public function register($data)
    {
        try {
            $this->pdo->beginTransaction();

            $this->pdo->prepare(
                "INSERT INTO user_account (Username, Password, User_Type)
                 VALUES (?, ?, 'Patient')"
            )->execute([
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT)
            ]);

            $userId = $this->pdo->lastInsertId();

            $this->pdo->prepare(
                "INSERT INTO patient
                    (NIC, First_Name, Last_Name, DOB, Gender, Phone, Email,
                     Address, Registered_Date, User_ID)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)"
            )->execute([
                $data['nic'], $data['first_name'], $data['last_name'], $data['dob'],
                $data['gender'], $data['phone'], $data['email'], $data['address'], $userId
            ]);

            $id = $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $id;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            if ($e->getCode() === '23000') { return false; }
            throw $e;
        }
    }

    public function updateContact($id, $phone, $email, $address)
    {
        return $this->pdo->prepare(
            "UPDATE patient SET Phone = ?, Email = ?, Address = ? WHERE Patient_ID = ?"
        )->execute([$phone, $email, $address, $id]);
    }
}

/** MEMBER 3 - the doctor table, plus clinic and specialization lookups. */
class Doctor
{
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    public function all()
    {
        return $this->pdo->query(
            "SELECT d.Doctor_ID,
                    CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name,
                    d.Phone, d.Email, d.Consultation_Fee,
                    s.Special_Name, c.Clinic_Name,
                    (SELECT COUNT(*) FROM doctor_schedule ds
                      WHERE ds.Doctor_ID = d.Doctor_ID
                        AND ds.Available_Date >= CURDATE()
                        AND ds.Is_Available = 1) AS Sessions
             FROM doctor d
             LEFT JOIN specialization s ON d.Special_ID = s.Special_ID
             LEFT JOIN clinic c         ON d.Clinic_ID  = c.Clinic_ID
             ORDER BY s.Special_Name, d.First_Name"
        )->fetchAll();
    }

    public function directory($search = '')
    {
        $sql = "SELECT d.Doctor_ID, CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name,
                       d.Phone, d.Email, d.Consultation_Fee, s.Special_Name, c.Clinic_Name,
                       (SELECT COUNT(*) FROM appointment a WHERE a.Doctor_ID = d.Doctor_ID
                        AND a.Status <> 'Cancelled') AS Appointments,
                       (SELECT COUNT(*) FROM doctor_schedule ds WHERE ds.Doctor_ID = d.Doctor_ID
                        AND ds.Available_Date >= CURDATE() AND ds.Is_Available = 1) AS Sessions
                FROM doctor d
                LEFT JOIN specialization s ON d.Special_ID = s.Special_ID
                LEFT JOIN clinic c ON d.Clinic_ID = c.Clinic_ID";
        $params = [];
        if ($search !== '') {
            $sql .= " WHERE CONCAT_WS(' ', d.First_Name, d.Last_Name, d.Phone, d.Email, s.Special_Name) LIKE ?";
            $params[] = '%' . $search . '%';
        }
        $sql .= " ORDER BY s.Special_Name, d.First_Name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT d.*,
                    CONCAT('Dr. ',d.First_Name,' ',d.Last_Name) AS Doctor_Name,
                    s.Special_Name, c.Clinic_Name, c.Address, c.Contact_No
             FROM doctor d
             LEFT JOIN specialization s ON d.Special_ID = s.Special_ID
             LEFT JOIN clinic c         ON d.Clinic_ID  = c.Clinic_ID
             WHERE d.Doctor_ID = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function specializations()
    {
        return $this->pdo->query(
            "SELECT * FROM specialization ORDER BY Special_Name"
        )->fetchAll();
    }

    public function clinics()
    {
        return $this->pdo->query("SELECT * FROM clinic ORDER BY Clinic_Name")->fetchAll();
    }

    /**
     * Register a doctor - login row and doctor row in one transaction,
     * exactly like patient registration.
     */
    public function register($data)
    {
        try {
            $this->pdo->beginTransaction();

            $this->pdo->prepare(
                "INSERT INTO user_account (Username, Password, User_Type)
                 VALUES (?, ?, 'Doctor')"
            )->execute([
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT)
            ]);

            $userId = $this->pdo->lastInsertId();

            $this->pdo->prepare(
                "INSERT INTO doctor
                    (First_Name, Last_Name, Phone, Email, Special_ID, Clinic_ID,
                     User_ID, Consultation_Fee)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            )->execute([
                $data['first_name'], $data['last_name'], $data['phone'], $data['email'],
                $data['special_id'], $data['clinic_id'], $userId, $data['fee']
            ]);

            $id = $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $id;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            if ($e->getCode() === '23000') { return false; }
            throw $e;
        }
    }

    public function updateProfile($id, $phone, $email, $fee)
    {
        return $this->pdo->prepare(
            "UPDATE doctor SET Phone = ?, Email = ?, Consultation_Fee = ?
             WHERE Doctor_ID = ?"
        )->execute([$phone, $email, $fee, $id]);
    }
}
