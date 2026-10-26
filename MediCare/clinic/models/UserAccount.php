<?php
/*
 * MEMBER 5 - logins for all three roles.
 *
 * One user_account table serves patients, doctors and receptionists.
 * User_Type decides what the person may open, and patient.User_ID,
 * doctor.User_ID and receptionist.User_ID point back at the account.
 * Password is VARCHAR(255) because a bcrypt hash is 60 characters.
 */
class UserAccount
{
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    /** Every account with the name of the person behind it. */
    public function all()
    {
        return $this->pdo->query(
            "SELECT u.User_ID, u.Username, u.User_Type, u.Created_At,
                    COALESCE(
                        CONCAT(p.First_Name,' ',p.Last_Name),
                        CONCAT(d.First_Name,' ',d.Last_Name),
                        CONCAT(r.First_Name,' ',r.Last_Name)
                    ) AS Person_Name
             FROM user_account u
             LEFT JOIN patient p      ON p.User_ID = u.User_ID
             LEFT JOIN doctor d       ON d.User_ID = u.User_ID
             LEFT JOIN receptionist r ON r.User_ID = u.User_ID
             ORDER BY u.User_Type, u.Username"
        )->fetchAll();
    }

    public function usernameTaken($username)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM user_account WHERE Username = ?");
        $stmt->execute([$username]);
        return $stmt->fetchColumn() > 0;
    }

    public function create($username, $password, $type)
    {
        if ($this->usernameTaken($username)) { return false; }
        if (!in_array($type, ['Patient', 'Doctor', 'Receptionist'], true)) { return false; }

        $this->pdo->prepare(
            "INSERT INTO user_account (Username, Password, User_Type) VALUES (?, ?, ?)"
        )->execute([$username, password_hash($password, PASSWORD_DEFAULT), $type]);

        return $this->pdo->lastInsertId();
    }

    public function resetPassword($id, $newPassword)
    {
        return $this->pdo->prepare("UPDATE user_account SET Password = ? WHERE User_ID = ?")
                         ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    }

    /** An account attached to a person cannot be deleted. */
    public function delete($id)
    {
        // one placeholder per table: MySQL's native prepared statements do not
        // allow the same named placeholder to be used more than once
        $stmt = $this->pdo->prepare(
            "SELECT (SELECT COUNT(*) FROM patient      WHERE User_ID = ?) +
                    (SELECT COUNT(*) FROM doctor       WHERE User_ID = ?) +
                    (SELECT COUNT(*) FROM receptionist WHERE User_ID = ?) AS linked"
        );
        $stmt->execute([$id, $id, $id]);
        if ($stmt->fetchColumn() > 0) { return false; }

        return $this->pdo->prepare("DELETE FROM user_account WHERE User_ID = ?")
                         ->execute([$id]);
    }

    /**
     * Check a login and return what the session needs, or false.
     *
     * The sample data ships plain text passwords so the group can log in on
     * day one. A plain password is compared directly and then replaced with a
     * hash, so after the first login only hashes remain.
     */
    public function attempt($username, $password)
    {
        $stmt = $this->pdo->prepare(
            "SELECT u.User_ID, u.Username, u.Password, u.User_Type,
                    COALESCE(p.Patient_ID, d.Doctor_ID, r.Receptionist_ID) AS Ref_ID,
                    COALESCE(
                        CONCAT(p.First_Name,' ',p.Last_Name),
                        CONCAT('Dr. ',d.First_Name,' ',d.Last_Name),
                        CONCAT(r.First_Name,' ',r.Last_Name),
                        u.Username
                    ) AS Person_Name
             FROM user_account u
             LEFT JOIN patient p      ON p.User_ID = u.User_ID
             LEFT JOIN doctor d       ON d.User_ID = u.User_ID
             LEFT JOIN receptionist r ON r.User_ID = u.User_ID
             WHERE u.Username = ?"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) { return false; }

        $stored = $user['Password'];
        $isHash = strlen($stored) > 50 && $stored[0] === '$';

        if ($isHash) {
            if (!password_verify($password, $stored)) { return false; }
        } else {
            if (!hash_equals($stored, $password)) { return false; }
            $this->resetPassword($user['User_ID'], $password);   // upgrade
        }

        return $user;
    }

    public function countsByType()
    {
        return $this->pdo->query(
            "SELECT User_Type, COUNT(*) AS Total FROM user_account GROUP BY User_Type"
        )->fetchAll();
    }
}
