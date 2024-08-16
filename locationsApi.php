<?php
require_once ('cors.php');

$db = require_once ("connection.php");

$method = $_SERVER["REQUEST_METHOD"];
$service = $_SERVER["HTTP_SERVICE"];

if ($method === 'GET' && $service == 'getLocations') {


    $result = $db->query("SELECT * FROM locations");

    $locations = $result->fetch_all(MYSQLI_ASSOC);

    if ($result->num_rows > 0) {
        echo json_encode(['success' => true, 'locations' => $locations, 'service' => $service]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    return;
}