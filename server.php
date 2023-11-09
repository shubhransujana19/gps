<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $con = new mysqli('localhost', 'admin', '', 'employee_tracking');
    $con->set_charset('utf8mb4');
} catch (Exception $e) {
    error_log($e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $input = file_get_contents("php://input");

    // Decode the JSON data
    $data = json_decode($input, true);

    // Extract and validate the received data
    $latitude = isset($data['latitude']) ? $data['latitude'] : null;
    $longitude = isset($data['longitude']) ? $data['longitude'] : null;
    $timestamp = isset($data['timestamp']) ? $data['timestamp'] : null;
    $mappingStatus = isset($data['mappingStatus']) ? $data['mappingStatus'] : null;
    $clientName = isset($data['clientName']) ? $data['clientName'] : null;
    $address = isset($data['address']) ? $data['address'] : null;
    $totalDistance = isset($data['totalDistance']) ? $data['totalDistance'] : 0;

    // Get the current date in the format YYYY-MM-DD
    $currentDate = date('Y-m-d');

    // Connect to the database (adjust database credentials)
    $connection = new mysqli("localhost", "admin", "", "employee_tracking");

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Check if record with client name and date already exists
    $checkQuery = "SELECT * FROM tracking_data WHERE client_name = ? AND date = ?";
    $checkStmt = $connection->prepare($checkQuery);
    $checkStmt->bind_param("ss", $clientName, $currentDate);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Record exists, perform update
        $updateQuery = "UPDATE tracking_data 
                        SET latitude = ?, longitude = ?, timestamp = ?, mapping_status = ?, address = ?, total_distance = ?
                        WHERE client_name = ? AND date = ?";
        $updateStmt = $connection->prepare($updateQuery);
        $updateStmt->bind_param("ddsssdss", $latitude, $longitude, $timestamp, $mappingStatus, $address, $totalDistance, $clientName, $currentDate);
        $updateResult = $updateStmt->execute();

        if ($updateResult) {
            echo "Data updated successfully.";
        } else {
            echo "Error updating data: " . $updateStmt->error;
        }

        $updateStmt->close();
    } else {
        // Record does not exist, perform insert
        $insertQuery = "INSERT INTO tracking_data (latitude, longitude, initial_latitude, initial_longitude, timestamp, mapping_status, client_name, date, starting_address, total_distance)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $connection->prepare($insertQuery);
        $insertStmt->bind_param("ddddsssssd", $latitude, $longitude, $latitude, $longitude, $timestamp, $mappingStatus, $clientName, $currentDate, $address, $totalDistance);
        $insertResult = $insertStmt->execute();

        if ($insertResult) {
            echo "Data inserted successfully.";
        } else {
            echo "Error inserting data: " . $insertStmt->error;
        }

        $insertStmt->close();
    }

    // Close the database connection
    $checkStmt->close();
    $connection->close();
} else {
    echo "Invalid request method.";
}
?>
