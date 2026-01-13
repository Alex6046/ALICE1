<?php
include 'db.php';

$result = $conn->query("SHOW TABLES LIKE 'proposed_events'");
if ($result && $result->num_rows > 0) {
    echo "EXISTS";
} else {
    echo "MISSING";
}
$conn->close();
?>
