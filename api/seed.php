<?php
/**
 * Database seeder for development
 * 
 * This script populates the database with test data
 */
require_once 'config.php';
require_once 'database.php';

// Create database connection
$db = new Database();
$conn = $db->getConnection();

try {
    // Begin transaction
    $conn->beginTransaction();
    
    // Clear existing data
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    $tables = ['users', 'teacher_profiles', 'student_profiles', 'subjects', 'teacher_subjects', 
               'student_subjects', 'teacher_availability', 'student_availability', 
               'student_requests', 'sessions', 'payments', 'ratings', 'notifications'];
    
    foreach ($tables as $table) {
        $conn->exec("TRUNCATE TABLE $table");
    }
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Insert subjects
    $subjects = ['Tajweed', 'Hifz', 'Tafsir', 'Arabic', 'Hadith', 'Fiqh', 'Tawheed'];
    $stmt = $conn->prepare("INSERT INTO subjects (name) VALUES (?)");
    
    foreach ($subjects as $subject) {
        $stmt->execute([$subject]);
    }
    
    // Insert users
    $users = [
        ['Admin User', 'admin@example.com', password_hash('password', PASSWORD_DEFAULT), 'admin'],
        ['Teacher User', 'teacher@example.com', password_hash('password', PASSWORD_DEFAULT), 'teacher'],
        ['Student User', 'student@example.com', password_hash('password', PASSWORD_DEFAULT), 'student']
    ];
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    
    foreach ($users as $user) {
        $stmt->execute($user);
    }
    
    // Get user IDs
    $teacherId = $conn->lastInsertId() - 1;
    $studentId = $conn->lastInsertId();
    
    // Insert teacher profile
    $stmt = $conn->prepare("INSERT INTO teacher_profiles (user_id, bio, hourly_rate, currency, payment_method, verification_status) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$teacherId, 'Experienced Quran teacher with 5+ years of experience', 25.00, 'USD', 'bank', 'verified']);
    
    $teacherProfileId = $conn->lastInsertId();
    
    // Insert student profile
    $stmt = $conn->prepare("INSERT INTO student_profiles (user_id, bio) VALUES (?, ?)");
    $stmt->execute([$studentId, 'Eager student looking to learn Quran']);
    
    $studentProfileId = $conn->lastInsertId();
    
    // Insert teacher subjects
    $stmt = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
    $stmt->execute([$teacherProfileId, 1]); // Tajweed
    $stmt->execute([$teacherProfileId, 2]); // Hifz
    
    // Insert teacher availability
    $stmt = $conn->prepare("INSERT INTO teacher_availability (teacher_id, day, time_from, time_to) VALUES (?, ?, ?, ?)");
    $stmt->execute([$teacherProfileId, 'monday', '09:00:00', '12:00:00']);
    $stmt->execute([$teacherProfileId, 'wednesday', '14:00:00', '17:00:00']);
    $stmt->execute([$teacherProfileId, 'friday', '10:00:00', '13:00:00']);
    
    // Insert student request
    $stmt = $conn->prepare("INSERT INTO student_requests (student_id, teacher_id, subject_id, message, preferred_day, 
                           preferred_time_from, preferred_time_to, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $studentProfileId, 
        $teacherProfileId, 
        1, // Tajweed
        'I would like to learn Tajweed with you.',
        'monday',
        '10:00:00',
        '11:00:00',
        'pending'
    ]);
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Database seeded successfully!'
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Database seeding failed: ' . $e->getMessage()
    ]);
}

