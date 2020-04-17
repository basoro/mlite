<?php
include_once ('../../../config.php');
reset($_FILES);
$temp = current($_FILES);

if (is_uploaded_file($temp['tmp_name'])) {
    if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
        header("HTTP/1.1 400 Invalid file name,Bad request");
        return;
    }

    // Validating File extensions
    if (! in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array(
        "gif",
        "jpg",
        "png"
    ))) {
        header("HTTP/1.1 400 Not an Image");
        return;
    }

    $fileName = "../content/uploads/" . $temp['name'];
    move_uploaded_file($temp['tmp_name'], $fileName);

    // Return JSON response with the uploaded file path.
    echo json_encode(array(
        'file_path' => $fileName
    ));
}
?>
