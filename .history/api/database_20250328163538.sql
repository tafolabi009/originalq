
-- Create table for teacher verification requests
CREATE TABLE IF NOT EXISTS teacher_verification_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    status ENUM('pending', 'scheduled', 'completed', 'rejected') NOT NULL DEFAULT 'pending',
    scheduled_date DATETIME NULL,
    meeting_link VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teacher_profiles(id) ON DELETE CASCADE
);

-- Create table for teacher subjects
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Insert default subjects
INSERT INTO subjects (name) VALUES 
('Tajweed'),
('Hifz'),
('Tafsir'),
('Arabic'),
('Hadith'),
('Fiqh'),
('Tawheed');

-- Create table for teacher subjects
CREATE TABLE IF NOT EXISTS teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teacher_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY (teacher_id, subject_id)
);

-- Create table for student subjects
CREATE TABLE IF NOT EXISTS student_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY (student_id, subject_id)
);

-- Create table for teacher availability
CREATE TABLE IF NOT EXISTS teacher_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    day ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
    time_from TIME NOT NULL,
    time_to TIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teacher_profiles(id) ON DELETE CASCADE
);

-- Create table for student availability
CREATE TABLE IF NOT EXISTS student_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    day ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
    time_from TIME NOT NULL,
    time_to TIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE
);

-- Create table for student requests
CREATE TABLE IF NOT EXISTS student_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    message TEXT NULL,
    preferred_day ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NULL,
    preferred_time_from TIME NULL,
    preferred_time_to TIME NULL,
    status ENUM('pending', 'accepted', 'declined') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teacher_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Create table for sessions
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    meeting_link VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teacher_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Create table for payments
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NULL,
    teacher_id INT NOT NULL,
    student_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'USD',
    status ENUM('pending', 'completed', 'refunded', 'failed') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50) NULL,
    transaction_id VARCHAR(100) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL,
    FOREIGN KEY (teacher_id) REFERENCES teacher_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE
);

-- Create table for ratings and reviews
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NULL,
    teacher_id INT NOT NULL,
    student_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE SET NULL,
    FOREIGN KEY (teacher_id) REFERENCES teacher_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student_profiles(id) ON DELETE CASCADE
);

