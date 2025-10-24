<?php

require_once 'db_connection.php';
session_start();

$trips = [];
$searched = false;

if ($_SERVER['REQUEST_METHOD'] == 'GET' && (
    (!empty($_GET['departure']) || !empty($_GET['arrival']) || !empty($_GET['date']))
)) {
  
    $departure = trim($_GET['departure'] ?? '');
    $arrival = trim($_GET['arrival'] ?? '');
    $date = trim($_GET['date'] ?? '');
    
    $query = "SELECT t.*, c.name as company_name 
              FROM trips t 
              JOIN companies c ON t.company_id = c.id 
              WHERE 1=1";
    $params = [];
    
    if (!empty($departure)) {
        $query .= " AND t.departure LIKE ? COLLATE NOCASE";
        $params[] = "%$departure%";
    }
    
    if (!empty($arrival)) {
        $query .= " AND t.arrival LIKE ? COLLATE NOCASE";
        $params[] = "%$arrival%";
    }
    
    if (!empty($date)) {
        $query .= " AND t.departure_date = ?";
        $params[] = $date;
    }
    
    $query .= " ORDER BY t.departure_date, t.departure_time";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $searched = true;
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['full_name'] ?? '';
$userRole = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JET BİLET</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Montserrat', sans-serif; background: #f0f4f8; }

        
        .navbar { 
            background: linear-gradient(135deg, #667eea, #5568d3);
            color: white; 
            padding: 15px 30px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .navbar h1 { font-size: 24px; font-weight: bold; }
        .navbar-links a, .navbar-links span { 
            font-weight: 600;
            color: white; 
            text-decoration: none; 
            margin-left: 15px; 
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.3s; 
            display: inline-block;
        }
        .navbar-links a:hover { 
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transform: translateY(-2px);
        }

        
        .coupon-highlight {
            background: linear-gradient(135deg, #ffcc33 0%, #ff9900 100%);
            color: white;
            font-weight: bold;
            padding: 8px 18px;
            border-radius: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .coupon-highlight:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }

        
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

        
        .search-box {
            background: linear-gradient(135deg, #ffffff, #e3f2fd);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.3s;
        }
        .search-box:hover { transform: translateY(-3px); }

        .search-form { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; align-items: end; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }

        
        button { 
            padding: 12px 30px; 
            background: linear-gradient(135deg, #667eea 0%, #5568d3 100%);
            color: white; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 16px; 
            transition: all 0.3s;
            font-family: 'Montserrat', sans-serif;
        }
        button:hover { 
            background: linear-gradient(135deg, #5568d3 0%, #4455bb 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        
        .trips-list { 
            background: white; 
            border-radius: 15px; 
            padding: 20px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
        .trip-card {
            border: 1px solid #eee;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #f0f4ff, #ffffff);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .trip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .trip-info h3 { color: #333; margin-bottom: 10px; }
        .trip-info h3::before { content: "🚌 "; margin-right: 5px; }
        .trip-info p { color: #666; margin: 5px 0; }
        .trip-price { font-size: 26px; font-weight: bold; color: #ff6b6b; }

        
        .btn-secondary { 
            background: linear-gradient(135deg, #28a745, #218838); 
            color: white; 
            border-radius: 8px;
            padding: 10px 20px;
            transition: all 0.3s;
        }
        .btn-secondary:hover { 
            background: linear-gradient(135deg, #218838, #1c6c2e); 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        
        .no-results { text-align: center; padding: 40px; color: #999; }

        
        .welcome-message { 
            background: linear-gradient(135deg, #6a11cb, #2575fc); 
            padding: 50px; 
            border-radius: 20px; 
            text-align: center; 
            color: white;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
            font-family: 'Montserrat', sans-serif;
        }
        .welcome-message h2 { font-size: 36px; margin-bottom: 15px; }
        .welcome-message p { font-size: 20px; margin-bottom: 20px; color: #f0f0f0; }
        .welcome-message a { 
            background: white; 
            color: #333; 
            padding: 12px 30px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: bold; 
            display: inline-block; 
            transition: all 0.3s;
        }
        .welcome-message a:hover { 
            background: #f0f0f0; 
            transform: translateY(-2px); 
            box-shadow: 0 5px 12px rgba(0,0,0,0.2); 
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🎫 JET BİLET</h1>
        <div class="navbar-links">
            <a href="index.php">Ana Sayfa</a>
            <a href="coupons.php" class="coupon-highlight">🎟️ Kuponlar</a>
            <?php if ($isLoggedIn): ?>
                <span>Hoşgeldin, <?= htmlspecialchars($userName) ?></span>
                <?php if ($userRole == 'user'): ?>
                    <a href="my_tickets.php">Biletlerim</a>
                <?php elseif ($userRole == 'admin'): ?>
                    <a href="admin_panel.php">Admin Panel</a>
                <?php elseif ($userRole == 'firma_admin'): ?>
                    <a href="firma_admin_panel.php">Firma Panel</a>
                <?php endif; ?>
                <a href="logout.php">Çıkış</a>
            <?php else: ?>
                <a href="login.php">Giriş Yap</a>
                <a href="register.php">Kayıt Ol</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div class="search-box">
            <h2 style="margin-bottom: 20px;">Sefer Ara</h2>
            
            <?php
            try {
                $cities_stmt = $db->query("SELECT DISTINCT departure as city FROM trips 
                                           UNION 
                                           SELECT DISTINCT arrival as city FROM trips 
                                           ORDER BY city");
                $available_cities = $cities_stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {
                $available_cities = [];
            }
            ?>
            
            <?php if (!empty($available_cities)): ?>
                <div style="background: #e3f2fd; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px;">
                    <strong>💡 Mevcut Şehirler:</strong> 
                    <?= implode(', ', array_map('htmlspecialchars', $available_cities)) ?>
                </div>
            <?php endif; ?>
            
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label>Nereden</label>
                    <input type="text" name="departure" placeholder="Kalkış şehri" value="<?= htmlspecialchars($_GET['departure'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Nereye</label>
                    <input type="text" name="arrival" placeholder="Varış şehri" value="<?= htmlspecialchars($_GET['arrival'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Tarih (Opsiyonel)</label>
                    <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
                </div>
                <button type="submit">Sefer Ara</button>
            </form>
        </div>

        <?php if ($searched): ?>
            <div class="trips-list">
                <h2 style="margin-bottom: 20px;">Bulunan Seferler (<?= count($trips) ?>)</h2>
                
                <?php if (empty($trips)): ?>
                    <div class="no-results">
                        <p style="font-size: 18px;">😕 Aradığınız kriterlere uygun sefer bulunamadı.</p>
                        <p style="margin-top: 10px;">Lütfen farklı şehir veya tarih deneyin.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($trips as $trip): ?>
                        <div class="trip-card">
                            <div class="trip-info">
                                <h3><?= htmlspecialchars($trip['company_name']) ?></h3>
                                <p><strong><?= htmlspecialchars($trip['departure']) ?></strong> → <strong><?= htmlspecialchars($trip['arrival']) ?></strong></p>
                                <p>📅 <?= date('d.m.Y', strtotime($trip['departure_date'])) ?> | 🕐 <?= substr($trip['departure_time'], 0, 5) ?></p>
                                <p>💺 Müsait Koltuk: <?= $trip['available_seats'] ?> / <?= $trip['total_seats'] ?></p>
                            </div>
                            <div style="text-align: right;">
                                <div class="trip-price"><?= number_format($trip['price'], 2) ?> ₺</div>
                                <a href="trip_details.php?id=<?= $trip['id'] ?>">
                                    <button class="btn-secondary" style="margin-top: 10px;">Detaylar</button>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="welcome-message">
                <h2>🚌 Hoş Geldiniz!</h2>
                <p>Yukarıdaki formu kullanarak sefer aramaya başlayın.</p>
                <p>Lütfen kalkış veya varış noktası girerek arama yapın.</p>
                <div style="margin-top: 30px; padding: 20px; background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); border-radius: 10px;">
                    <h3 style="color: #333; margin-bottom: 10px;">🎟️ İndirim Kuponlarımızı Keşfedin!</h3>
                    <p style="color: #555; margin-bottom: 15px;">Bilet satın alırken kullanabileceğiniz özel indirim kuponlarını görüntüleyin</p>
                    <a href="coupons.php">Kuponları Gör</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
