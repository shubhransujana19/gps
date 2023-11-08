<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract and validate the received data (latitude, longitude, timestamp, mapping status, client name)
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $timestamp = $_POST['timestamp'];
    $address = $_POST['address'];
    $mappingStatus = $_POST['mappingStatus'];
    $clientName = $_POST['clientName'];

    // Get the current date in the format YYYY-MM-DD
    $currentDate = date('Y-m-d');

    // Connect to the database (adjust database credentials)
    $connection = new mysqli("localhost", "admin", "", "employee_tracking");

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Check if there is an existing record for the client and date
    $existingRecord = $connection->query("SELECT * FROM tracking_data WHERE client_name = '$clientName' AND date = '$currentDate'");

    if ($existingRecord->num_rows > 0) {
        // Update the existing record
        $sql = "UPDATE tracking_data SET latitude = ?, longitude = ?, timestamp = ?, address = ?, mapping_status = ? WHERE client_name = ? AND date = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ddssss", $latitude, $longitude, $timestamp, $address, $mappingStatus, $clientName, $currentDate);
    } else {
        // Insert a new record
        $sql = "INSERT INTO tracking_data (latitude, longitude, timestamp, address, mapping_status, client_name, date) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ddsssss", $latitude, $longitude, $timestamp, $address, $mappingStatus, $clientName, $currentDate);
    }

    if ($stmt->execute()) {
        echo "Data received and stored successfully.";
    } else {
        echo "Error storing data: " . $connection->error;
    }

    // Close the database connection
    $stmt->close();
    $connection->close();
} else {
    echo "Invalid request method.";
}
?>
