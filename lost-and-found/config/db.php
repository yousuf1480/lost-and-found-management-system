<?php
// ─────────────────────────────────────────────
//  config/db.php  —  Database Connection
// ─────────────────────────────────────────────

define('DB_HOST', '127.0.0.1'); // Changed from 'localhost' to '127.0.0.1'
define('DB_USER', 'root');
define('DB_PASS', '');       
define('DB_NAME', 'lost_found');
define('DB_PORT', 3307);        // Added the specific port

function getDB() {
    // Added DB_PORT as the 5th parameter in the mysqli connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
?>