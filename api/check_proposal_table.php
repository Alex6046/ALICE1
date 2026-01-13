<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connected successfully.<br>";

// Check if table exists
$tableName = 'proposed_events';
$checkTable = $conn->query("SHOW TABLES LIKE '$tableName'");

if ($checkTable->num_rows > 0) {
    echo "Table '$tableName' EXISTS.<br>";
    
    // Check columns
    $columns = $conn->query("SHOW COLUMNS FROM $tableName");
    echo "Columns:<br>";
    while($row = $columns->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
} else {
    echo "Table '$tableName' DOES NOT EXIST.<br>";
    echo "Attempting to create...<br>";
    
    $sql = "CREATE TABLE IF NOT EXISTS proposed_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        date DATE NOT NULL,
        time TIME NOT NULL,
        venue VARCHAR(255) NOT NULL,
        capacity INT NOT NULL,
        description TEXT NOT NULL,
        organizer_name VARCHAR(255) NOT NULL,
        organizer_email VARCHAR(255) NOT NULL,
        contact_number VARCHAR(50),
        status VARCHAR(50) DEFAULT 'pending',
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        admin_notes TEXT
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table created successfully.<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

$conn->close();
?>
