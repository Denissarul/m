-- Select your existing Avaris database before importing. Existing accounts stay intact.
CREATE TABLE IF NOT EXISTS server_setups (
 user_id BIGINT UNSIGNED PRIMARY KEY,
 server_name VARCHAR(80) NOT NULL,
 framework VARCHAR(20) NOT NULL,
 screenshots TINYINT(1) NOT NULL DEFAULT 0,
 completed_steps TEXT NOT NULL,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;
