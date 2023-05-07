<?php
mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=utf-8');
// Set the text
$text = "บัตรประจำตัวประชาชน Thai National ID Card\nเลขประจำตัวประชาชน\n1 5399 00920 17 5\nIdentification Number\nชื่อตัวและชื่อสกุล นาย ภูผาภูมิ ผาทอง\nม\nName Mr. Phuphaphoom\nTast name Phatong\nเกิดวันที่ 28 ก.ย. 2548\nDate of Birth 28 Sep. 2005\nศาสนา พุทธ\nที่อยู่ 63 หมู่ที่ 5 ต.บ้านเสี้ยว อ.ฟากท่า\nจ.อุตรดิตถ์\n17 8.A. 2564\nวันออกบัตร\n37 Aug. 2021\nDate of issue\n(นายธนาคม จงจิระ\nเจ้าพนักงานออกบัตร\n27 n.8. 2572\nวันบัตรหมดอายุ\n27 Sep. 2029\nDate of Expiry\n180\n170\n160.\n150.\n5305-02-08170919\nบัตร\nประจำ\nตัว\nประชาชน\nThai\nNational\nID\nCard\nเลข\nประจำ\nตัว\nประชาชน\n1\n5399\n00920\n17\n5\nIdentification\nNumber\nชื่อ\nตัว\nและ\nชื่อสกุล\nนาย\nภูผา\nภูมิ\nผา\nทอง\nม\nName\nMr.\nPhuphaphoom\nTast\nname\nPhatong\nเกิด\nวัน\nที่\n28\nก.ย.\n2548\nDate\nof\nBirth\n28\nSep.\n2005\nศาสนา\nพุทธ\nที่\nอยู่\n63\nหมู่\nที่\n5\nต\n.\nบ้าน\nเสี้ยว\nอ\n.\nฟากท่า\nจ\n.\nอุตรดิตถ์\n17\n8.A.\n2564\nวัน\nออก\nบัตร\n37\nAug.\n2021\nDate\nof\nissue\n(\nนาย\nธนาคม\nจง\nจิ\nระ\nเจ้าพนักงาน\nออก\nบัตร\n27\nn.8\n.\n2572\nวัน\nบัตร\nหมดอายุ\n27\nSep.\n2029\nDate\nof\nExpiry\n180\n170\n160\n.\n150\n.\n5305-02-08170919";

// Extract the data
preg_match('/(\d{1}[-\s]*\d{4}[-\s]*\d{5}[-\s]*\d{2}[-\s]*\d{1})/', $text, $idNumberMatches);
$idNumber = str_replace([' ', '-'], '', $idNumberMatches[1]);

preg_match('/ชื่อตัวและชื่อสกุล\s*([^\n]+)/', $text, $nameMatches);
$fullname = trim($nameMatches[1]);
$parts = explode(" ", $fullname);

$prefix = $parts[0];
$nameThai = $parts[1];
$surnameThai = array_slice($parts, 2);
$surnameThai = implode(" ", $surnameThai);

preg_match('/Name\s*([^\n]+)/', $text, $firstNameMatches);
$fullnameEng = trim($firstNameMatches[1]);
$parts = explode(" ", $fullnameEng);
$prefixEng = $parts[0];
$nameEng = $parts[1];

preg_match('/ast name\s*([^\n]+)/', $text, $lastNameMatches);
$lastName = trim($lastNameMatches[1]);

preg_match('/เกิดวันที่\s*([^\n]+)/', $text, $dobThaiMatches);
$dobThai = trim($dobThaiMatches[1]);

preg_match('/of Birth\s*([^\n]+)/', $text, $dobMatches);
$dob = trim($dobMatches[1]);

preg_match('/ที่อยู่(.*)\n(.*)/', $text, $addressMatches);
$address = trim($addressMatches[1]." ".$addressMatches[2]);

preg_match('/([^\n]+)\nวันออกบัตร/', $text, $issueDateThaiMatches);
$issueDateThai = trim($issueDateThaiMatches[1]);

preg_match('/([^\n]+)\nDate of/', $text, $issueDateMatches);
$issueDate = trim($issueDateMatches[1]);

preg_match('/([^\n]+)\nวันบัตรหมดอายุ/', $text, $expiryDateThaiMatches);
$expiryDateThai = trim($expiryDateThaiMatches[1]);

preg_match('/([^\n]+)\nDate of Expiry/', $text, $expiryDateMatches);
$expiryDate = trim($expiryDateMatches[1]);

// Create the data array
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
    'expiryDate' => $expiryDate
];

// Encode the data as JSON
$jsonData = json_encode($data);

echo $jsonData;
// Save the JSON data to a file in the ./output folder
file_put_contents('./output/data.json', $jsonData);

?>