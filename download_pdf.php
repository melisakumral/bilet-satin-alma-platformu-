<?php

require_once 'db_connection.php';
require_once 'auth_helper.php';
requireRole(['user']);

$ticketId = $_GET['ticket_id'] ?? 0;


$stmt = $db->prepare("SELECT tk.*, t.departure, t.arrival, t.departure_date, t.departure_time, 
                      c.name as company_name, u.full_name, u.email
                      FROM tickets tk
                      JOIN trips t ON tk.trip_id = t.id
                      JOIN companies c ON t.company_id = c.id
                      JOIN users u ON tk.user_id = u.id
                      WHERE tk.id = ? AND tk.user_id = ?");
$stmt->execute([$ticketId, $_SESSION['user_id']]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    die('Bilet bulunamadı!');
}


header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bilet <?= $ticketId ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; }
        .ticket { border: 3px solid #667eea; padding: 30px; border-radius: 15px; max-width: 600px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px dashed #667eea; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #667eea; margin: 0; }
        .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; color: #555; }
        .value { color: #333; }
        .barcode { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px dashed #667eea; }
        .barcode-text { font-family: 'Courier New', monospace; font-size: 24px; letter-spacing: 3px; }
        @media print { body { padding: 20px; } }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h1>🎫 OTOBÜS BİLETİ</h1>
            <p><?= htmlspecialchars($ticket['company_name']) ?></p>
        </div>
        
        <div class="info-row">
            <span class="label">Yolcu Adı:</span>
            <span class="value"><?= htmlspecialchars($ticket['full_name']) ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Kalkış:</span>
            <span class="value"><?= htmlspecialchars($ticket['departure']) ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Varış:</span>
            <span class="value"><?= htmlspecialchars($ticket['arrival']) ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Tarih:</span>
            <span class="value"><?= date('d.m.Y', strtotime($ticket['departure_date'])) ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Saat:</span>
            <span class="value"><?= substr($ticket['departure_time'], 0, 5) ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Koltuk No:</span>
            <span class="value"><?= $ticket['seat_number'] ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Bilet Fiyatı:</span>
            <span class="value"><?= number_format($ticket['price_paid'], 2) ?> ₺</span>
        </div>
        
        <?php if ($ticket['coupon_code']): ?>
        <div class="info-row">
            <span class="label">Kullanılan Kupon:</span>
            <span class="value"><?= htmlspecialchars($ticket['coupon_code']) ?></span>
        </div>
        <?php endif; ?>
        
        <div class="info-row">
            <span class="label">Satın Alma Tarihi:</span>
            <span class="value"><?= date('d.m.Y H:i', strtotime($ticket['purchased_at'])) ?></span>
        </div>
        
        <div class="barcode">
            <p><strong>Bilet No:</strong></p>
            <p class="barcode-text">*<?= str_pad($ticketId, 8, '0', STR_PAD_LEFT) ?>*</p>
        </div>
        
        <p style="text-align: center; margin-top: 20px; color: #999; font-size: 12px;">
            İyi yolculuklar dileriz! Bu sayfayı PDF olarak kaydetmek için tarayıcınızdan yazdır seçeneğini kullanın.
        </p>
    </div>
    
    <script>
        
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>