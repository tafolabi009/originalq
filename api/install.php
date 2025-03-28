<?php
// Database installation script
require_once 'config.php';

try {
    // Create database connection
    $pdo = new PDO(
        'mysql:host=' . DB_HOST,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);
    
    // Import schema from database.sql
    $sql = file_get_contents('database.sql');
    $pdo->exec($sql);
    
    // Create sample data
    createSampleData($pdo);
    
    echo "Database installation completed successfully!";
} catch (PDOException $e) {
    die("Database installation failed: " . $e->getMessage());
}

function createSampleData($pdo) {
    // Create sample users
    $users = [
        [
            'name' => 'Abdullah Ahmed',
            'email' => 'teacher@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'teacher',
        ],
        [
            'name' => 'Zainab Ali',
            'email' => 'student@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'student',
        ],
    ];
    
    foreach ($users as $user) {
        $stmt = $pdo->prepare('
            INSERT INTO users (name, email, password, role, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ');
        
        $stmt->execute([
            $user['name'],
            $user['email'],
            $user['password'],
            $user['role'],
        ]);
        
        $userId = $pdo->lastInsertId();
        
        // Create profile based on role
        if ($user['role'] === 'teacher') {
            $stmt = $pdo->prepare('
                INSERT INTO teacher_profiles (
                    user_id, bio, education, experience, specialization,
                    hourly_rate, languages, country, timezone, is_verified,
                    created_at, updated_at
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ');
            
            $stmt->execute([
                $userId,
                'I am a certified Quran teacher with 5 years of experience teaching Tajweed and Hifz to students of all ages.',
                'Bachelor\'s in Islamic Studies, Al-Azhar University',
                '5+',
                'Tajweed, Hifz',
                25.00,
                'English, Arabic, Urdu',
                'Nigeria',
                'GMT+01:00',
                true,
            ]);
            
            // Add availability
            $availabilities = [
                ['monday', '09:00', '12:00'],
                ['wednesday', '14:00', '17:00'],
                ['friday', '10:00', '13:00'],
            ];
            
            $stmt = $pdo->prepare('
                INSERT INTO teacher_availability (teacher_id, day_of_week, start_time, end_time)
                VALUES (?, ?, ?, ?)
            ');
            
            foreach ($availabilities as $availability) {
                $stmt->execute([
                    $userId,
                    $availability[0],
                    $availability[1],
                    $availability[2],
                ]);
            }
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO student_profiles (
                    user_id, grade_level, learning_goals, country, timezone,
                    created_at, updated_at
                )
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ');
            
            $stmt->execute([
                $userId,
                'Intermediate',
                'I want to improve my Tajweed and memorize more of the Quran.',
                'United States',
                'GMT-05:00',
            ]);
        }
    }
    
    // Create sample student requests
    $stmt = $pdo->prepare('
        INSERT INTO student_requests (
            student_id, teacher_id, subject, message, preferred_time,
            status, created_at, updated_at
        )
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ');
    
    $stmt->execute([
        2, // Student ID
        1, // Teacher ID
        'Tajweed',
        'I\'m looking for a teacher who can help me improve my Tajweed. I have basic knowledge but need to work on my pronunciation.',
        '3:00 PM - 4:00 PM',
        'pending',
    ]);
    
    // Create sample conversation
    $stmt = $pdo->prepare('
        INSERT INTO conversations (user1_id, user2_id, created_at, updated_at)
        VALUES (?, ?, NOW(), NOW())
    ');
    
    $stmt->execute([1, 2]);
    $conversationId = $pdo->lastInsertId();
    
    // Create sample messages
    $messages = [
        [1, 'Assalamu alaikum, welcome to IqraPath! How can I help you with your Quran learning journey?'],
        [2, 'Wa alaikum assalam, thank you! I\'m interested in improving my Tajweed. When can we start?'],
        [1, 'We can start this week. How about Wednesday at 3 PM?'],
        [2, 'That works for me. JazakAllah khair!'],
    ];
    
    $stmt = $pdo->prepare('
        INSERT INTO messages (conversation_id, user_id, message, is_read, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ');
    
    foreach ($messages as $message) {
        $stmt->execute([
            $conversationId,
            $message[0],
            $message[1],
            1,
        ]);
    }
}
