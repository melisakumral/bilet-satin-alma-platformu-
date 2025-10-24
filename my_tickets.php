<?php
require_once 'db_connection.php';
require_once 'auth_helper.php';
requireRole(['user']);


$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);


$stmt = $db->prepare("SELECT tk.*, t.departure, t.arrival, t.departure_date, t.departure_time, c.name as company_name
                      FROM tickets tk
                      JOIN trips t ON tk.trip_id = t.id
                      JOIN companies c ON t.company_id = c.id
                      WHERE tk.user_id = ?
                      ORDER BY t.departure_date DESC, t.departure_time DESC");
$stmt->execute([$_SESSION['user_id']]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletlerim</title>

    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f3f4f6; color: #333; }

        
        .navbar { 
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar h1 {
            font-size: 22px;
            letter-spacing: 1px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            padding: 8px 15px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .navbar a:first-child {
            background: #fcd34d;
            color: #333;
        }
        .navbar a:last-child {
            background: #f87171;
        }
        .navbar a:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }

        
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }

        
        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        .profile-card h2 {
            margin-bottom: 25px;
            color: #4f46e5;
            text-align: center;
        }
        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .info-item {
            background: #f9fafb;
            border-radius: 10px;
            padding: 15px;
        }
        .info-label { font-size: 13px; color: #6b7280; }
        .info-value { font-size: 17px; font-weight: 600; color: #111827; }

        
        .tickets-section h2 { 
            color: #4f46e5; 
            margin-bottom: 20px; 
            text-align: center; 
        }
        .ticket-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s ease;
        }
        .ticket-card:hover {
            transform: translateY(-3px);
        }
        .ticket-info h3 {
            color: #111827;
            margin-bottom: 8px;
            font-size: 18px;
        }
        .ticket-info p {
            color: #555;
            margin: 4px 0;
            font-size: 14px;
        }
        .ticket-info strong { color: #4f46e5; }

        .ticket-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn:hover { opacity: 0.9; }

        .status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        .no-tickets {
            text-align: center;
            padding: 50px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>👋 Hoş geldin, <?= htmlspecialchars($user['full_name']) ?>!</h1>
        <div>
            <a href="index.php">🏠 Ana Sayfa</a>
            <a href="logout.php">🚪 Çıkış</a>
        </div>
    </div>

    <div class="container">
        <div class="profile-card">
            <h2>Profil Bilgileri</h2>
            <div class="profile-info">
                <div class="info-item">
                    <div class="info-label">Ad Soyad</div>
                    <div class="info-value"><?= htmlspecialchars($user['full_name']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Kullanıcı Adı</div>
                    <div class="info-value"><?= htmlspecialchars($user['username']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">E-posta</div>
                    <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">💳 Bakiye</div>
                    <div class="info-value" style="color: #16a34a;"><?= number_format($user['credit'], 2) ?> ₺</div>
                </div>
            </div>
        </div>

        <div class="tickets-section">
            <h2>🎫 Biletlerim (<?= count($tickets) ?>)</h2>
            
            <?php if (empty($tickets)): ?>
                <div class="no-tickets">Henüz hiç bilet almadınız.</div>
            <?php else: ?>
                <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket-card">
                        <div class="ticket-info">
                            <h3>🚌 <?= htmlspecialchars($ticket['company_name']) ?></h3>
                            <p><strong><?= htmlspecialchars($ticket['departure']) ?></strong> → <strong><?= htmlspecialchars($ticket['arrival']) ?></strong></p>
                            <p>📅 <?= date('d.m.Y', strtotime($ticket['departure_date'])) ?> | 🕐 <?= substr($ticket['departure_time'], 0, 5) ?></p>
                            <p>💺 Koltuk: <?= $ticket['seat_number'] ?> | 💰 Ödenen: <?= number_format($ticket['price_paid'], 2) ?> ₺</p>
                            <?php if ($ticket['coupon_code']): ?>
                                <p>🎟️ Kupon: <?= htmlspecialchars($ticket['coupon_code']) ?></p>
                            <?php endif; ?>
                            <p style="margin-top: 10px;">
                                <span class="status status-<?= $ticket['status'] ?>">
                                    <?= $ticket['status'] == 'active' ? 'Aktif' : 'İptal Edildi' ?>
                                </span>
                            </p>
                        </div>
                        <div class="ticket-actions">
                            <?php if ($ticket['status'] == 'active'): ?>
                                <a href="download_pdf.php?ticket_id=<?= $ticket['id'] ?>" class="btn btn-primary">PDF İndir</a>
                                <a href="cancel_ticket.php?ticket_id=<?= $ticket['id'] ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Bileti iptal etmek istediğinizden emin misiniz?')">İptal Et</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
