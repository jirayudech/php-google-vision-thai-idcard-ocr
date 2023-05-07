<?php
putenv('GOOGLE_APPLICATION_CREDENTIALS=/Applications/MAMP/bin/php/vision-api-sample.json');

mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=utf-8');

require 'vendor/autoload.php';

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

# The name of the image file to annotate
$fileName = './input/3.png';

if (file_exists($fileName)) {
    # Prepare the image to be annotated
    $image = file_get_contents($fileName);

    $imageAnnotator = new ImageAnnotatorClient();
    # Performs label detection on the image file
    $response = $imageAnnotator->documentTextDetection($image);
    $texts = $response->getTextAnnotations();

    # Extract relevant information from OCR results
    $idNumber = '';
    $name = '';
    $surname = '';
    $birthday = '';
    $address = '';
    $ocrText = '';

    foreach ($texts as $text) {
        $description = $text->getDescription();
        $ocrText .= $description . "\n";
    } 

    // Extract the data
    preg_match('/(\d{1}[-\s]*\d{4}[-\s]*\d{5}[-\s]*\d{2}[-\s]*\d{1})/', $ocrText, $idNumberMatches);
    $idNumber =  str_replace([' ', '-'], '', $idNumberMatches[1]);

    preg_match('/ชื่อตัวและชื่อสกุล\s*([^\n]+)/', $ocrText, $nameMatches);
    $fullname = trim($nameMatches[1]);
    $parts = explode(" ", $fullname);

    $prefix = $parts[0];
    $nameThai = $parts[1];
    $surnameThai = $parts[2];

    preg_match('/Name\s*([^\n]+)/', $ocrText, $firstNameMatches);
    $fullnameEng = trim($firstNameMatches[1]);
    $parts = explode(" ", $fullnameEng);
    $prefixEng = $parts[0];
    $nameEng = $parts[1];

    preg_match('/ast name\s*([^\n]+)/', $ocrText, $lastNameMatches);
    $lastName = trim($lastNameMatches[1]);

    preg_match('/เกิดวันที่\s*([^\n]+)/', $ocrText, $dobThaiMatches);
    $dobThai = trim($dobThaiMatches[1]);

    preg_match('/of Birth\s*([^\n]+)/', $ocrText, $dobMatches);
    $dob = trim($dobMatches[1]);

    preg_match('/ที่อยู่(.*)\n(.*)/', $ocrText, $addressMatches);
    $address = trim($addressMatches[1]." ".$addressMatches[2]);

    preg_match('/([^\n]+)\nวันออกบัตร/', $ocrText, $issueDateThaiMatches);
    $issueDateThai = trim($issueDateThaiMatches[1]);

    preg_match('/([^\n]+)\nDate of is/', $ocrText, $issueDateMatches);
    $issueDate = trim($issueDateMatches[1]);

    preg_match('/([^\n]+)\nวันบัตรหมดอายุ/', $ocrText, $expiryDateThaiMatches);
    $expiryDateThai = trim($expiryDateThaiMatches[1]);

    preg_match('/([^\n]+)\nDate of Expiry/', $ocrText, $expiryDateMatches);
    $expiryDate = trim($expiryDateMatches[1]);

    # Set the margin for the cropped area
    $margin = 60;
    $left_margin = 15;
    # Crop the ID card from the image and save it to a file
    $vertices = $texts[0]->getBoundingPoly()->getVertices();
    $x1 = min($vertices[0]->getX(), $vertices[3]->getX()) - $margin - $left_margin;;
    $y1 = min($vertices[0]->getY(), $vertices[1]->getY()) - $margin;
    $x2 = max($vertices[1]->getX(), $vertices[2]->getX()) + $margin;
    $y2 = max($vertices[2]->getY(), $vertices[3]->getY()) + $margin;

    switch (exif_imagetype($fileName)) {
        case IMAGETYPE_JPEG:
            $imageResource = imagecreatefromjpeg($fileName);
            break;
        case IMAGETYPE_PNG:
            $imageResource = imagecreatefrompng($fileName);
            break;
        default:
            throw new Exception('Unsupported image type');
    }


    # Save the cropped image to the ./output folder
    if (!file_exists('./output')) {
        mkdir('./output');
    }

    if (($x2 - max(0, $x1)) < imagesx($imageResource) && ($y2 - max(0, $y1)) < imagesy($imageResource)) {
        $croppedImage = imagecrop($imageResource, ['x' => max(0, $x1), 'y' => max(0, $y1), 'width' => ($x2 - max(0, $x1)), 'height' => ($y2 - max(0, $y1))]);
        imagejpeg($croppedImage, './output/'.basename($fileName));
    }else{
        imagejpeg($imageResource, './output/'.basename($fileName));
    }


    # Return data in JSON format
    $data = [
        'idNumber' => $idNumber,
        'prefix' => $prefix,
        'nameThai' => $nameThai,
        'surnameThai' => $surnameThai,
        'prefixEng' => $prefixEng,
        'nameEng' => $nameEng,
        'surnameEng' => $lastName,
        'dobThai' => $dobThai,
        'dob' => $dob,
        'address' => $address,
        'issueDateThai' => $issueDateThai,
        'issueDate' => $issueDate,
        'expiryDateThai' => $expiryDateThai,
        'expiryDate' => $expiryDate,
        'ocrText' => trim($ocrText),
    ];
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);

    # Save the JSON data to a file in the ./output folder
    $jsonFileName = './output/' . basename($fileName) . '.json';
    file_put_contents($jsonFileName, $jsonData);

    echo $jsonData;

    //echo "JSON data saved to: " . realpath($jsonFileName) . "\n";

    $imageAnnotator->close();

} else {
    echo "Error: The file $fileName does not exist.";
}

?>