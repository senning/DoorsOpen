<?php

require('config.php');

//Geocode
function geocode($street){
	$addr = str_replace(" ","+",$street);
	$gmaps = "http://maps.googleapis.com/maps/api/geocode/json?address=";

	$response = file_get_contents($gmaps.$addr.",+Toronto,+ON&sensor=false");
	$loc = json_decode($response,true);
	
	//$loc['results'][0]['address_components'][i]['types']['neighbourhood']; <- if neigh in array
	//$loc['results'][0]['address_components'][i]['long_name'];
	
	return $loc;

	}

$addr = "601 Christie Street";
echo $addr;
$geo = geocode($addr);
var_dump($geo);

?>
