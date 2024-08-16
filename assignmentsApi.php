<?php
$db = require_once ("connection.php");

require_once ("cors.php");

$method = $_SERVER["REQUEST_METHOD"];
$service = $_SERVER["HTTP_SERVICE"];


if ($method === "POST" && $service === "assignAsset") {
    $form_data = json_decode(file_get_contents("php://input"), true);

    $locationBlock = '';
    $locationRoom = '';
    $assetName = '';
    $assetModel = '';
    $quantity = 0;

    if (
        isset($form_data['locationBlock']) && isset($form_data['locationRoom']) && isset($form_data['assetName']) && isset($form_data['assetModel'])
    ) {
        $locationBlock = $form_data['locationBlock'];
        $locationRoom = $form_data['locationRoom'];
        $assetName = $form_data['assetName'];
        $assetModel = $form_data['assetModel'];
        $quantity = $form_data['quantity'];

        $assetName = strtolower($assetName);
        $assetModel = strtolower($assetModel);
        $result = '';

        $sql = $db->prepare("SELECT * FROM assets WHERE assetName = ? AND model = ?");
        $sql->bind_param("ss", $assetName, $assetModel);

        $assetId = '';
        $oldAssetName = '';
        $oldAssetModel = '';
        $oldQty = 0;
        $newAssetQty = 0;

        if ($sql->execute()) {
            $result = $sql->get_result();
            $asset = $result->fetch_all(MYSQLI_ASSOC);

            foreach ($asset as $u) {
                $assetId = $u['id'];
                $oldAssetName = $u['assetName'];
                $oldAssetModel = $u['model'];
                $oldQty = $u['qty'];
            }

            $sql2 = $db->prepare('SELECT * FROM assignments WHERE assetId = ? ');
            $sql2->bind_param('i', $assetId);

            if ($sql2->execute()) {
                $result2 = $sql2->get_result();
                $asset2 = $result2->fetch_all(MYSQLI_ASSOC);

                if (!empty($asset2)) {
                    foreach ($asset2 as $u) {
                        $newAssetQty = $u['qty'] + $quantity;
                    }

                    if($oldQty > $quantity || $oldQty == $quantity) {
                        $sql4 = $db->prepare("UPDATE assets SET qty = ? WHERE model = ?");
                        $assetSub = 0;
                        $assetSub = $oldQty - $quantity;
                        $sql4->bind_param("is", $assetSub, $assetModel);
                        $sql4->execute();

                        $sql3 = $db->prepare('UPDATE assignments SET qty = ? WHERE assetId = ?');
                        $sql3->bind_param("ii", $newAssetQty, $assetId);
                        $sql3->execute();
    
                        echo json_encode(['success' => true, 'message' => 'Data inserted successfully']);
                        return;
                    }else{
                        echo json_encode(['success' => false, 'message' => 'Please your quantity is more than the asset quantity, try reducing it']);
                        return;
                    }
                } else {
                    $sql3 = $db->prepare("INSERT INTO assignments (assetName, assetId, locationblock, locationroom, qty, assetModel) VALUES(?,?,?,?,?,?)");
                    $sql3->bind_param("sissis", $assetName, $assetId, $locationBlock, $locationRoom, $quantity, $assetModel);
                    if ($sql3->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Data inserted successfully']);
                        return;
                    }
                }
            }
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'All fields required']);
        return;
    }

} else if ($method === 'GET' && $service === 'getAssignments') {
    $sql = $db->prepare("SELECT * FROM assignments");

    if ($sql->execute()) {
        $result = $sql->get_result();
        $assignments = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode(['success' => true, 'assignments' => $assignments]);
    }
}