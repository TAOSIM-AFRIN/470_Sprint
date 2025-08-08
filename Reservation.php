<?php
// models/Reservation.php
require_once __DIR__ . '/../config.php';

class Reservation {
    private $conn;

    public function __construct() {
        $this->conn = connectDB();
    }

    public function createReservation($userId, $date, $time) {
        $stmt = $this->conn->prepare("INSERT INTO reservations (user_id, reserve_date, reserve_time) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $date, $time);
        return $stmt->execute();
    }

    public function cancelReservation($reserveId) {
        $stmt = $this->conn->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->bind_param("i", $reserveId);
        return $stmt->execute();
    }

    public function getReservationsByDate($date) {
        $stmt = $this->conn->prepare("SELECT * FROM reservations WHERE reserve_date = ?");
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $reservations = [];
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        return $reservations;
    }
}
?>
