<?php

require_once 'db_connection.php';
require_once 'auth_helper.php';
requireRole(['user']);

$ticketId = $_GET['ticket_id'] ?? 0;


$stmt = $db->prepare("SELECT tk.*, t.departure_date, t.departure_time 
                      FROM tickets tk
                      JOIN trips t ON tk.trip_id = t.id
                      WHERE tk.id = ? AND tk.user_id = ? AND tk.status = 'active'");
$stmt->execute([$ticketId, $_SESSION['user_id']]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    die('Bilet bulunamadı veya zaten iptal edilmiş!');
}


$departureDateTime = new DateTime($ticket['departure_date'] . ' ' . $ticket['departure_time']);
$now = new DateTime();
$diff = $now->diff($departureDateTime);
$hoursDiff = ($diff->days * 24) + $diff->h;

if ($hoursDiff < 1 && $now < $departureDateTime) {
    die('Sefer kalkışına 1 saatten az süre kaldığı için bilet iptal edilemez!');
}

if ($now >= $departureDateTime) {
    die('Sefer kalkışı gerçekleştiği için bilet iptal edilemez!');
}

try {
    $db->beginTransaction();
    
    
    $stmt = $db->prepare("UPDATE tickets SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$ticketId]);
    
    
    $stmt = $db->prepare("UPDATE users SET credit = credit + ? WHERE id = ?");
    $stmt->execute([$ticket['price_paid'], $_SESSION['user_id']]);
    
    
    $stmt = $db->prepare("UPDATE trips SET available_seats = available_seats + 1 WHERE id = ?");
    $stmt->execute([$ticket['trip_id']]);
    
    $db->commit();
    
    header('Location: my_tickets.php?success=cancel');
    exit;
    
} catch (PDOException $e) {
    $db->rollBack();
    die('Bilet iptali sırasında hata oluştu!');
}
?>