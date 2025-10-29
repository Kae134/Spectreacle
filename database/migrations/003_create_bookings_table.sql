CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    show_id INT NOT NULL,
    booking_date DATETIME NOT NULL,
    number_of_seats INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (show_id) REFERENCES shows(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_show_id (show_id),
    INDEX idx_booking_date (booking_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;