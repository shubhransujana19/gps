<?php  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); try { $con = new mysqli('localhost',  'admin', '', 'employee_tracking'); $con->set_charset('utf8mb4');} catch(Exception $e) { error_log($e->getMessage());}  ?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract and validate the received data (latitude, longitude, timestamp, address, mapping status, client name)
    $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null;
    $timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : null;
    $address = isset($_POST['address']) ? $_POST['address'] : null;
    $mappingStatus = isset($_POST['mappingStatus']) ? $_POST['mappingStatus'] : null;
    $clientName = isset($_POST['clientName']) ? $_POST['clientName'] : null;

    // Get the current date in the format YYYY-MM-DD
    $currentDate = date('Y-m-d');

    // Connect to the database (adjust database credentials)
    $connection = new mysqli("localhost", "admin", "", "employee_tracking");

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Prepare the SQL statement
    $sql = "INSERT INTO tracking_data (latitude, longitude, timestamp, address, mapping_status, client_name, date)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            latitude = VALUES(latitude),
            longitude = VALUES(longitude),
            timestamp = VALUES(timestamp),
            address = VALUES(address),
            mapping_status = VALUES(mapping_status)";

    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ddsssss", $latitude, $longitude, $timestamp, $address, $mappingStatus, $clientName, $currentDate);

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
