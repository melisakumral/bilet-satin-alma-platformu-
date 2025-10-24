<?php

require_once 'db_connection.php';
require_once 'auth_helper.php';


requireRole(['user', 'firma_admin']);

$currentUser = getCurrentUser();
$userId = $currentUser['id'];
$userRole = $currentUser['role'];
$companyId = $currentUser['company_id'] ?? null;

$messages = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel' && isset($_POST['ticket_id'])) {
    $ticketId = (int)$_POST['ticket_id'];

    try {
        
        $stmt = $db->prepare("
            SELECT t.id as ticket_id, t.trip_id, t.user_id, t.price_paid, t.coupon_code, t.status,
                   tr.departure_date, tr.departure_time, tr.company_id, tr.available_seats
            FROM tickets t
            JOIN trips tr ON t.trip_id = tr.id
            WHERE t.id = :ticket_id
        ");
        $stmt->execute([':ticket_id' => $ticketId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            $messages[] = ['type' => 'error', 'text' => 'Bilet bulunamadı.'];
        } elseif ($ticket['status'] !== 'active') {
            $messages[] = ['type' => 'error', 'text' => 'Bu bilet zaten iptal edilmiş veya geçersiz.'];
        } else {
           
            if ($userRole === 'user' && $ticket['user_id'] != $userId) {
                $messages[] = ['type' => 'error', 'text' => 'Bu bileti iptal etme yetkiniz yok.'];
            } elseif ($userRole === 'firma_admin' && $ticket['company_id'] != $companyId) {
                $messages[] = ['type' => 'error', 'text' => 'Bu bileti iptal etme yetkiniz yok (firma uyuşmuyor).'];
            } else {
                
                $departureTs = strtotime($ticket['departure_date'] . ' ' . $ticket['departure_time']);
                $now = time();
                if (($departureTs - $now) < 3600) {
                    $messages[] = ['type' => 'error', 'text' => 'Kalkışa 1 saatten az kaldığı için bilet iptali mümkün değil.'];
                } else {
                    
                    $db->beginTransaction();
                    
                    $upd = $db->prepare("UPDATE tickets SET status = 'cancelled' WHERE id = :id");
                    $upd->execute([':id' => $ticketId]);

                    
                    $refundStmt = $db->prepare("UPDATE users SET credit = credit + :amount WHERE id = :uid");
                    $refundStmt->execute([':amount' => $ticket['price_paid'], ':uid' => $ticket['user_id']]);

                    
                    $updateSeats = $db->prepare("UPDATE trips SET available_seats = available_seats + 1 WHERE id = :trip_id");
                    $updateSeats->execute([':trip_id' => $ticket['trip_id']]);

                  
                    if (!empty($ticket['coupon_code'])) {
                        $dec = $db->prepare("UPDATE coupons SET used_count = CASE WHEN used_count > 0 THEN used_count - 1 ELSE 0 END WHERE code = :code");
                        $dec->execute([':code' => $ticket['coupon_code']]);
                    }

                    $db->commit();
                    $messages[] = ['type' => 'success', 'text' => 'Bilet iptali başarılı. Ücret hesabınıza iade edildi.'];
                    
                    header('Location: biletlerim.php?msg=cancelled');
                    exit;
                }
            }
        }
    } catch (PDOException $e) {
        if ($db->inTransaction()) $db->rollBack();
        $messages[] = ['type' => 'error', 'text' => 'İptal sırasında hata: ' . $e->getMessage()];
    }
}


try {
    if ($userRole === 'user') {
        $stmt = $db->prepare("
            SELECT t.id as ticket_id, t.trip_id, t.seat_number, t.price_paid, t.coupon_code, t.status, t.purchased_at,
                   tr.departure, tr.arrival, tr.departure_date, tr.departure_time, c.name as company_name
            FROM tickets t
            JOIN trips tr ON t.trip_id = tr.id
            JOIN companies c ON tr.company_id = c.id
            WHERE t.user_id = :uid
            ORDER BY tr.departure_date DESC, tr.departure_time DESC, t.purchased_at DESC
        ");
        $stmt->execute([':uid' => $userId]);
    } else { 
        $stmt = $db->prepare("
            SELECT t.id as ticket_id, t.trip_id, t.seat_number, t.price_paid, t.coupon_code, t.status, t.purchased_at, t.user_id,
                   tr.departure, tr.arrival, tr.departure_date, tr.departure_time, c.name as company_name, u.full_name as buyer_name
            FROM tickets t
            JOIN trips tr ON t.trip_id = tr.id
            JOIN companies c ON tr.company_id = c.id
            LEFT JOIN users u ON t.user_id = u.id
            WHERE tr.company_id = :company_id
            ORDER BY tr.departure_date DESC, tr.departure_time DESC, t.purchased_at DESC
        ");
        $stmt->execute([':company_id' => $companyId]);
    }

    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Biletler alınırken hata: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Biletlerim</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background:#f5f5f5; margin:0; padding:0; }
        .navbar { background:#667eea; color:#fff; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; }
        .container { max-width:1100px; margin:30px auto; padding:0 20px; }
        .card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:20px; }
        h1 { margin-bottom:10px; }
        table { width:100%; border-collapse:collapse; margin-top:10px; }
        th, td { text-align:left; padding:10px; border-bottom:1px solid #eee; vertical-align:middle; }
        th { background:#fafafa; }
        .btn { padding:8px 12px; border-radius:6px; text-decoration:none; display:inline-block; font-weight:600; }
        .btn-danger { background:#dc3545; color:#fff; border:none; cursor:pointer; }
        .btn-primary { background:#007bff; color:#fff; }
        .muted { color:#666; font-size:13px; }
        .msg { padding:10px; border-radius:6px; margin-bottom:12px; }
        .msg.success { background:#e6ffed; color:#056a19; }
        .msg.error { background:#ffe6e6; color:#a00; }
        .small { font-size:13px; color:#555; }
    </style>
</head>
<body>
    <div class="navbar">
        <div><strong>🎫 Bilet Platformu</strong></div>
        <div>
            Hoşgeldin, <?= htmlspecialchars($currentUser['full_name'] ?? $currentUser['username']) ?> |
            <a href="index.php" style="color:#fff; text-decoration:underline; margin-left:10px;">Anasayfa</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h1>Biletlerim</h1>

            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $m): ?>
                    <div class="msg <?= $m['type'] === 'success' ? 'success' : 'error' ?>">
                        <?= htmlspecialchars($m['text']) ?>
                    </div>
                <?php endforeach; ?>
            <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'cancelled'): ?>
                <div class="msg success">Bilet iptali başarılı. Ücret iade edildi.</div>
            <?php endif; ?>

            <?php if (empty($tickets)): ?>
                <p class="muted">Görüntülenecek bilet yok.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Firma</th>
                            <th>Kalkış → Varış</th>
                            <th>Tarih</th>
                            <th>Saat</th>
                            <th>Koltuk</th>
                            <th>Ücret</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): 
                            $departureTs = strtotime($t['departure_date'] . ' ' . $t['departure_time']);
                            $canCancel = ($t['status'] === 'active') && (($departureTs - time()) >= 3600);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($t['company_name']) ?></td>
                            <td><?= htmlspecialchars($t['departure']) ?> → <?= htmlspecialchars($t['arrival']) ?></td>
                            <td><?= htmlspecialchars(date('d.m.Y', strtotime($t['departure_date']))) ?></td>
                            <td><?= htmlspecialchars(substr($t['departure_time'], 0, 5)) ?></td>
                            <td><?= htmlspecialchars($t['seat_number'] ?? $t['seat_number'] ?? '-') ?></td>
                            <td><?= number_format($t['price_paid'], 2) ?> ₺</td>
                            <td>
                                <?= htmlspecialchars(ucfirst($t['status'])) ?>
                                <?php if ($t['status'] === 'active'): ?>
                                    <div class="small">Alınma: <?= htmlspecialchars(date('d.m.Y H:i', strtotime($t['purchased_at']))) ?></div>
                                <?php else: ?>
                                    <div class="small">Satın alma: <?= htmlspecialchars(date('d.m.Y H:i', strtotime($t['purchased_at'] ?? 'now'))) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($userRole === 'firma_admin' && isset($t['buyer_name'])): ?>
                                    <div class="small">Yolcu: <?= htmlspecialchars($t['buyer_name']) ?></div>
                                <?php endif; ?>

                               
                                <a class="btn btn-primary" href="ticket_pdf.php?id=<?= urlencode($t['ticket_id']) ?>" target="_blank">PDF İndir</a>

                               
                                <?php if ($canCancel): ?>
                                    <form method="POST" style="display:inline-block; margin-left:8px;" onsubmit="return confirm('Bileti iptal etmek istediğinize emin misiniz?');">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($t['ticket_id']) ?>">
                                        <button type="submit" class="btn btn-danger">İptal Et</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn" disabled style="margin-left:8px; background:#eee; color:#888; border:1px solid #ddd;">
                                        İptal Yok
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
