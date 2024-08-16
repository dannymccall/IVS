<?php
require_once ('cors.php');

$db = require_once ("connection.php");



$method = $_SERVER["REQUEST_METHOD"];
$service = $_SERVER["HTTP_SERVICE"];

if ($method == 'POST' && $service == 'addAsset') {
    $form_data = json_decode(file_get_contents("php://input"), true);

    $assetName = '';
    $assetType = '';
    $assetCategory = '';
    $manufacturer = '';
    $model = '';
    $purchaseDate = '';
    $purchaseCost = '';
    $condition = '';
    $qty = '';

    if (
        isset($form_data['assetName']) && isset($form_data['assetType']) && isset($form_data['assetCategory']) && isset($form_data['manufacturer'])
        && isset($form_data['model']) && isset($form_data['purchaseDate']) && isset($form_data['purchaseCost']) && isset($form_data['condition']) && isset($form_data['qty'])
    ) {
        $assetName = $form_data['assetName'];
        $assetType = $form_data['assetType'];
        $assetCategory = $form_data['assetCategory'];
        $manufacturer = $form_data['manufacturer'];
        $model = $form_data['model'];
        $purchaseDate = $form_data['purchaseDate'];
        $purchaseCost = $form_data['purchaseCost'];
        $condition = $form_data['condition'];
        $qty = $form_data['qty'];
        if (
            strlen(trim($assetName)) !== 0 || strlen(trim($assetType)) !== 0 || strlen(trim($assetCategory)) !== 0
            || strlen(trim($manufacturer)) !== 0 || strlen(trim($model)) !== 0 || strlen(trim($purchaseDate)) !== 0 || strlen(trim($purchaseCost)) !== 0 || strlen(trim($condition)) !== 0 || strlen(trim($qty)) !== 0
        ) {
            $assetName = strtolower(trim($assetName));
            $model = strtolower(trim($model));
            $sql = $db->prepare("SELECT * FROM assets WHERE assetName = ? AND model = ?");
            $sql->bind_param("ss", $assetName, $model);

            if ($sql->execute()) {
                $result = $sql->get_result(); // Fetch the result set
                $assets = $result->fetch_all(MYSQLI_ASSOC); //
            }

            if (!empty($assets)) {
                $quatity = 0;

              foreach($assets as $u){
                $quantity = $u['qty'];
              }
              $quantity += $qty; 
                $sql2 = $db->prepare("UPDATE assets SET qty = ? WHERE assetName = ? AND model = ?");
                $sql2->bind_param('iss', $quantity, $assetName, $model);
                $sql2->execute();
                echo json_encode(['success' => true, 'message' => 'Data inserted successfully']);
                return;
            }

            $stmt = $db->prepare("INSERT INTO assets(assetName, assetType, assetCategory, manufacturer, model, purchaseDate, purchaseCost, assetCondition, qty) values(?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssisi", $assetName, $assetType, $assetCategory, $manufacturer, $model, $purchaseDate, $purchaseCost, $condition, $qty);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Data inserted successfull']);
                return;
            }

        } else {
            echo json_encode(['success' => false, 'message' => 'All fields required']);
            return;

        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Please we need all details']);
        return;
    }

} else if ($method === 'GET' && $service == 'getAssets') {


    $result = $db->query("SELECT * FROM assets");

    $assets = $result->fetch_all(MYSQLI_ASSOC);

    if ($result->num_rows > 0) {
        echo json_encode(['success' => true, 'assets' => $assets, 'service' => $service]);
        return;
    }else{
        echo json_encode(['success' => true, 'assets' => $assets]);
        return;
    }
} else if ($method === "DELETE" && $service === 'deleteAsset') {
    $id = '';
    if (isset($_GET['id']))
        $id = $_GET['id'];

    $sql = "DELETE FROM assets WHERE id=?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Asset deleted successfully"]);
    }

} else if ($method === "PUT" && $service === 'updateAsset') {
    $form_data = json_decode(file_get_contents("php://input"), true);
    $stmt = $db->prepare("UPDATE assets SET assetName = ?, assetType = ?, assetCategory = ?, manufacturer = ?, model = ?, 
                         purchaseDate = ?, purchaseCost = ?, assetCondition = ?, qty = ? WHERE id = ?");

    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => $db->error]);
        return;
    }

    $stmt->bind_param(
        "ssssssisii",
        $form_data['assetName'],
        $form_data['assetType'],
        $form_data['assetCategory'],
        $form_data['manufacturer'],
        $form_data['model'],
        $form_data['purchaseDate'],
        $form_data['purchaseCost'],
        $form_data['assetCondition'],
        $form_data['qty'],
        $form_data['id']
    );

    $query = $stmt->execute();
    if ($query) {
        echo json_encode(['success' => true, 'message' => 'Asset updated successfully']);
        return;
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
        return;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    return;
}

