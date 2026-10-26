<?php
/*
 * MEMBER 2 - Doctor schedule model.
 *
 * A row in doctor_schedule is a WORKING SESSION, for example 09:00 to 12:00.
 * We do not store every 30 minute slot as a row - that would be hundreds of
 * rows a month and impossible to edit. Instead the session is cut into slots
 * here, and slots that are already booked or already past are removed.
 */
class Schedule
{
    const SLOT_MINUTES = 30;

    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    /** Open sessions of a doctor on one date. */
    public function findByDoctorAndDate($doctorId, $date)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM doctor_schedule
             WHERE Doctor_ID = ? AND Available_Date = ? AND Is_Available = 1
             ORDER BY Start_Time"
        );
        $stmt->execute([$doctorId, $date]);
        return $stmt->fetchAll();
    }

    /** All sessions of a doctor from today on, with how many are booked. */
    public function upcoming($doctorId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT s.*,
                    (SELECT COUNT(*) FROM appointment a
                      WHERE a.Schedule_ID = s.Schedule_ID
                        AND a.Status <> 'Cancelled') AS Booked
             FROM doctor_schedule s
             WHERE s.Doctor_ID = ? AND s.Available_Date >= CURDATE()
             ORDER BY s.Available_Date, s.Start_Time"
        );
        $stmt->execute([$doctorId]);
        return $stmt->fetchAll();
    }

    /** Add a working session. False if that start time already exists. */
    public function create($doctorId, $date, $start, $end)
    {
        try {
            $this->pdo->prepare(
                "INSERT INTO doctor_schedule
                    (Doctor_ID, Available_Date, Start_Time, End_Time, Is_Available)
                 VALUES (?, ?, ?, ?, 1)"
            )->execute([$doctorId, $date, $start, $end]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') { return false; }   // UNIQUE fired
            throw $e;
        }
    }

    /** Open a closed session, or close an open one. */
    public function toggle($scheduleId, $doctorId)
    {
        return $this->pdo->prepare(
            "UPDATE doctor_schedule SET Is_Available = 1 - Is_Available
             WHERE Schedule_ID = ? AND Doctor_ID = ?"
        )->execute([$scheduleId, $doctorId]);
    }

    /**
     * Free slots for a doctor on a date.
     * Returns rows like ['schedule_id' => 4, 'time' => '09:30:00', 'label' => '09:30 AM']
     */
    public function freeSlots($doctorId, $date, array $bookedSlots)
    {
        $sessions = $this->findByDoctorAndDate($doctorId, $date);
        $isToday  = ($date === date('Y-m-d'));
        $slots    = [];

        foreach ($sessions as $session) {
            $start = strtotime($date . ' ' . $session['Start_Time']);
            $end   = strtotime($date . ' ' . $session['End_Time']);

            for ($t = $start; $t < $end; $t += self::SLOT_MINUTES * 60) {
                $time = date('H:i:s', $t);

                if (in_array($time, $bookedSlots, true)) { continue; }   // taken
                if ($isToday && $t < time())             { continue; }   // past

                $slots[] = [
                    'schedule_id' => $session['Schedule_ID'],
                    'time'        => $time,
                    'label'       => date('h:i A', $t)
                ];
            }
        }
        return $slots;
    }
}
