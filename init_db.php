<?php


require_once 'db_connection.php';

try {
    
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        full_name TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'user',
        credit REAL DEFAULT 1000.0,
        company_id INTEGER DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id)
    )");

    
    $db->exec("CREATE TABLE IF NOT EXISTS companies (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    
    $db->exec("CREATE TABLE IF NOT EXISTS trips (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        company_id INTEGER NOT NULL,
        departure TEXT NOT NULL,
        arrival TEXT NOT NULL,
        departure_date DATE NOT NULL,
        departure_time TIME NOT NULL,
        price REAL NOT NULL,
        total_seats INTEGER NOT NULL,
        available_seats INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id)
    )");

    
    $db->exec("CREATE TABLE IF NOT EXISTS tickets (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        trip_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        seat_number INTEGER NOT NULL,
        price_paid REAL NOT NULL,
        coupon_code TEXT DEFAULT NULL,
        status TEXT DEFAULT 'active',
        purchased_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trip_id) REFERENCES trips(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");

    
    $db->exec("CREATE TABLE IF NOT EXISTS coupons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT UNIQUE NOT NULL,
        discount_rate REAL NOT NULL,
        usage_limit INTEGER NOT NULL,
        used_count INTEGER DEFAULT 0,
        expiry_date DATE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    echo "<h2>📊 Veritabanı Kurulumu Başladı...</h2>";

    
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT OR IGNORE INTO users (username, email, password, full_name, role, credit) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@bilet.com', $password, 'Sistem Yöneticisi', 'admin', 10000]);
    echo "✅ Admin kullanıcısı eklendi (admin / admin123)<br>";

    
    $companies = [
        'Metro Turizm',
        'Pamukkale Turizm', 
        'Kamil Koç',
        'Ulusoy'
    ];

    $companyIds = [];
    foreach ($companies as $company) {
        $stmt = $db->prepare("INSERT OR IGNORE INTO companies (name) VALUES (?)");
        $stmt->execute([$company]);
        
        $stmt = $db->prepare("SELECT id FROM companies WHERE name = ?");
        $stmt->execute([$company]);
        $companyIds[$company] = $stmt->fetchColumn();
        echo "✅ Firma eklendi: $company<br>";
    }


    $firmaAdmins = [
        ['username' => 'metro_admin', 'email' => 'metro@bilet.com', 'name' => 'Metro Yöneticisi', 'company' => 'Metro Turizm'],
        ['username' => 'pamukkale_admin', 'email' => 'pamukkale@bilet.com', 'name' => 'Pamukkale Yöneticisi', 'company' => 'Pamukkale Turizm'],
        ['username' => 'kamilkoc_admin', 'email' => 'kamilkoc@bilet.com', 'name' => 'Kamil Koç Yöneticisi', 'company' => 'Kamil Koç'],
        ['username' => 'ulusoy_admin', 'email' => 'ulusoy@bilet.com', 'name' => 'Ulusoy Yöneticisi', 'company' => 'Ulusoy']
    ];

    foreach ($firmaAdmins as $admin) {
        $password = password_hash('firma123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT OR IGNORE INTO users (username, email, password, full_name, role, credit, company_id) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $admin['username'], 
            $admin['email'], 
            $password, 
            $admin['name'], 
            'firma_admin', 
            5000, 
            $companyIds[$admin['company']]
        ]);
        echo "✅ Firma Admin eklendi: {$admin['username']} / firma123<br>";
    }

    
    $users = [
        ['username' => 'ahmet_yilmaz', 'email' => 'ahmet@mail.com', 'name' => 'Ahmet Yılmaz', 'credit' => 2000],
        ['username' => 'ayse_kaya', 'email' => 'ayse@mail.com', 'name' => 'Ayşe Kaya', 'credit' => 1500],
        ['username' => 'mehmet_demir', 'email' => 'mehmet@mail.com', 'name' => 'Mehmet Demir', 'credit' => 3000],
        ['username' => 'fatma_celik', 'email' => 'fatma@mail.com', 'name' => 'Fatma Çelik', 'credit' => 1000],
        ['username' => 'ali_ozturk', 'email' => 'ali@mail.com', 'name' => 'Ali Öztürk', 'credit' => 2500]
    ];

    foreach ($users as $user) {
        $password = password_hash('12345678', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT OR IGNORE INTO users (username, email, password, full_name, role, credit) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user['username'], 
            $user['email'], 
            $password, 
            $user['name'], 
            'user', 
            $user['credit']
        ]);
        echo "✅ Kullanıcı eklendi: {$user['username']} / 12345678<br>";
    }

    
    $trips = [
        
        ['company' => 'Metro Turizm', 'from' => 'İstanbul', 'to' => 'Ankara', 'date' => '2025-10-25', 'time' => '09:00', 'price' => 250, 'seats' => 45],
        ['company' => 'Metro Turizm', 'from' => 'Ankara', 'to' => 'İzmir', 'date' => '2025-10-26', 'time' => '14:00', 'price' => 280, 'seats' => 45],
        ['company' => 'Metro Turizm', 'from' => 'İstanbul', 'to' => 'Antalya', 'date' => '2025-10-27', 'time' => '20:00', 'price' => 350, 'seats' => 50],
        
        
        ['company' => 'Pamukkale Turizm', 'from' => 'İstanbul', 'to' => 'Ankara', 'date' => '2025-10-25', 'time' => '10:30', 'price' => 240, 'seats' => 45],
        ['company' => 'Pamukkale Turizm', 'from' => 'İzmir', 'to' => 'Antalya', 'date' => '2025-10-28', 'time' => '15:00', 'price' => 300, 'seats' => 40],
        ['company' => 'Pamukkale Turizm', 'from' => 'Bursa', 'to' => 'Ankara', 'date' => '2025-10-26', 'time' => '08:00', 'price' => 180, 'seats' => 45],
        
        
        ['company' => 'Kamil Koç', 'from' => 'İstanbul', 'to' => 'İzmir', 'date' => '2025-10-25', 'time' => '11:00', 'price' => 220, 'seats' => 45],
        ['company' => 'Kamil Koç', 'from' => 'Ankara', 'to' => 'Antalya', 'date' => '2025-10-27', 'time' => '16:30', 'price' => 320, 'seats' => 45],
        ['company' => 'Kamil Koç', 'from' => 'İstanbul', 'to' => 'Trabzon', 'date' => '2025-10-29', 'time' => '19:00', 'price' => 400, 'seats' => 40],
        
        
        ['company' => 'Ulusoy', 'from' => 'İstanbul', 'to' => 'Ankara', 'date' => '2025-10-25', 'time' => '13:00', 'price' => 260, 'seats' => 45],
        ['company' => 'Ulusoy', 'from' => 'Ankara', 'to' => 'İstanbul', 'date' => '2025-10-26', 'time' => '09:30', 'price' => 260, 'seats' => 45],
        ['company' => 'Ulusoy', 'from' => 'İzmir', 'to' => 'Ankara', 'date' => '2025-10-27', 'time' => '12:00', 'price' => 270, 'seats' => 45]
    ];

    foreach ($trips as $trip) {
        $stmt = $db->prepare("INSERT OR IGNORE INTO trips (company_id, departure, arrival, departure_date, departure_time, price, total_seats, available_seats) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $companyIds[$trip['company']], 
            $trip['from'], 
            $trip['to'], 
            $trip['date'], 
            $trip['time'], 
            $trip['price'], 
            $trip['seats'], 
            $trip['seats']
        ]);
        echo "✅ Sefer eklendi: {$trip['company']} - {$trip['from']} → {$trip['to']}<br>";
    }

    
    $coupons = [
        ['code' => 'INDIRIM20', 'rate' => 20, 'limit' => 100, 'date' => '2025-12-31'],
        ['code' => 'YENIYIL25', 'rate' => 25, 'limit' => 50, 'date' => '2026-01-31'],
        ['code' => 'ERKEN15', 'rate' => 15, 'limit' => 200, 'date' => '2025-11-30']
    ];

    foreach ($coupons as $coupon) {
        $stmt = $db->prepare("INSERT OR IGNORE INTO coupons (code, discount_rate, usage_limit, expiry_date) 
                              VALUES (?, ?, ?, ?)");
        $stmt->execute([$coupon['code'], $coupon['rate'], $coupon['limit'], $coupon['date']]);
        echo "✅ Kupon eklendi: {$coupon['code']} - %{$coupon['rate']} indirim<br>";
    }

    echo "<br><h2>🎉 Veritabanı başarıyla kuruldu!</h2>";
    echo "<h3>📋 Giriş Bilgileri:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin / admin123</li>";
    echo "<li><strong>Firma Adminler:</strong> metro_admin, pamukkale_admin, kamilkoc_admin, ulusoy_admin / firma123</li>";
    echo "<li><strong>Kullanıcılar:</strong> ahmet_yilmaz, ayse_kaya, mehmet_demir, fatma_celik, ali_ozturk / 12345678</li>";
    echo "</ul>";
    echo "<br><a href='index.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Ana Sayfaya Git</a>";

} catch (PDOException $e) {
    die("❌ Hata: " . $e->getMessage());
}
?>