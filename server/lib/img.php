<?php
function ImageUpload() {
    $image = $_FILES['image'];
    if (empty($image) || $image['error'] == 4) {
        //throw new Exception('Image file is missing');
        return '';
    }

    // check INI error
    if ($image['error'] !== 0) {
        if ($image['error'] === 1) throw new Exception('Max upload size exceeded');
        throw new Exception('Image uploading error: INI Error');
    }

    // check if the file exists
    if (!file_exists($image['tmp_name'])) throw new Exception('Image file is missing in the server');
    $maxFileSize = 2 * 10e6; // in bytes
    if ($image['size'] > $maxFileSize) throw new Exception('Max size limit exceeded'); 

    // check if uploaded file is an image
    $imageData = getimagesize($image['tmp_name']);
    if (!$imageData) throw new Exception('Invalid image');

    $mimeType = $imageData['mime'];
    // validate mime type
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mimeType, $allowedMimeTypes)) throw new Exception('Only JPEG, PNG and GIFs are allowed');

    // get file extension (ex: jpg, png) not (.jpg)
    $fileExtention = strtolower(pathinfo($image['name'] ,PATHINFO_EXTENSION));

    // create random name for your image
    $fileName = round(microtime(true)) . mt_rand() . '.' . $fileExtention;

    // Create the path starting from DOCUMENT ROOT of your website
    $path = '/taskete/img/' . $fileName;

    // file path in the computer - where to save it 
    $destination = $_SERVER['DOCUMENT_ROOT'] . $path;
    if (!move_uploaded_file($image['tmp_name'], $destination)) throw new Exception('Error in moving the uploaded file');

    // create the url
    $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
    $domain = $protocol . $_SERVER['SERVER_NAME'];
    $url = $domain . $path;

    return $url;
}
?>