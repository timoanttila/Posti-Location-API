<?php
function head($i = 2, $data = ""){
	$status = ["200 OK", "201 No content", "400 Bad Request"];
	header("Content-Type: application/json");
	header("HTTP/1.1 " . $status[$i]);
	if (!empty($data)) echo json_encode($data, JSON_UNESCAPED_SLASHES);
	die();
}

// Postal code is a required field
if(empty($_GET) || empty($_GET["postal"]) head();

// Request to the Posti's location API
$items = file_get_contents("https://locationservice.posti.com/location?locationZipCode=". preg_replace("/[^0-9]/", "", $_GET["postal"]) ."&top=10&types=POSTOFFICE&types=PICKUPPOINT&types=SMARTPOST&types=BUSINESSSERVICE");

// Error message if empty answer
if(empty($items)) head(1, [
	"status" => 201,
	"fi" => "Ei toimipisteitä saatavilla.",
	"en" => "No post offices available."
]);

// Convert data to object
$items = json_decode($items, true);

// Checking the language, default fi
if(!empty($_GET["lang"])){
	$lang = preg_replace("/[^a-z]/", "", $_GET["lang"]);
	if(!is_array($lang,["fi","en","sv"]) $lang = "fi";
}
else $lang = "fi";

// Creating a response from locations
foreach($items["locations"] as $item){
	$data[] = [
		"id" => (int)$item["id"],
		"name" => $item["publicName"][$lang],
		"street" => $item["address"][$lang]["streetName"] ." ". $item["address"]["fi"]["streetNumber"],
		"postal" => $item["address"][$lang]["postalCode"],
		"area" => $item["address"][$lang]["postalCodeName"]
	];
}

// Returns the response in json format
head(0, $data);
