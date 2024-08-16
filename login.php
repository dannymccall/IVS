<?php
$db = require_once ('./connection.php');
require_once ('cors.php');

// The file path

// $username = 'rnyonator';
// $stmt =$db->prepare("SELECT * FROM users WHERE username=?");
// $stmt->bind_param("s", $username);
// $stmt->execute();
// $result = $stmt->get_result();    


// $user = [];
// while ($row = $result->fetch_assoc()) {
//     $user[] = $row;
// }

// $dbUserName = '';
// foreach($user as $u){
//     $dbUserName = $u['username'];
// }

// echo($dbUserName);
//echo var_dump($user);

//echo json_encode(['user' => $user]);

$method = $_SERVER["REQUEST_METHOD"];
$service = $_SERVER["HTTP_SERVICE"];
if ($method === 'POST' && $service === 'login') {

    $form_data = json_decode(file_get_contents('php://input'), true);

    if (isset($form_data['username']) && isset($form_data['password'])) {
        $username = $form_data['username'];
        $password = $form_data['password'];

        if (strlen(trim($username)) === 0 || strlen(trim($password)) === 0) {
            echo json_encode(['error' => 'Username and Password required']);
            return;
        }


        $stmt = $db->prepare("SELECT * FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = [];
        while ($row = $result->fetch_assoc()) {
            $user[] = $row;
        }

        $dbUserName = '';
        foreach ($user as $u)
            $dbUserName = $u['password'];

        // header('Content-Type: application/json');
        if (count($user) == 0 || $dbUserName !== $password) {
            echo json_encode(['success' => false, 'message' => 'Username or Password incorrect']);
            return;
        } else {
            echo json_encode(['success' => true, 'message' => 'Login Success', 'user' => $user]);
            return;
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Username and Password required']);
        return;
    }
} else {
    // http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
}
