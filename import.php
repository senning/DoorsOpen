<?php

require('config.php');

//Geocode
function geocode($street){
	$addr = str_replace(" ","+",$street);
	$gmaps = "http://maps.googleapis.com/maps/api/geocode/json?address=";

	$response = file_get_contents($gmaps.$addr.",+Toronto,+ON&sensor=false");
	$loc = json_decode($response,true);
	
	$return = array(
		"lat"=>$loc['results'][0]['geometry']['location']['lat'],
		"lng"=>$loc['results'][0]['geometry']['location']['lng']
		);
	return $return;

	}

/* Initialize databse */

$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_BASE);

/* Open XML file */
$file = "DOT_open_data.xml";
if(!($fp = fopen($file,"r"))){
	die("could not open XML input");
}

$xmlstr = "";
while ($data = fread($fp,4096)){$xmlstr .= $data;}
$doors = new SimpleXMLElement($xmlstr);


foreach($doors->item as $loc){
	$query = "INSERT INTO Location (name,address,latitude,longitude) VALUES (?,?,?,?)";
	
	$latlng = geocode($loc->Street_Address);
		
	if($stmt = $db->prepare($query)){
		$stmt->bind_param('ssdd',$loc->Building,$loc->Street_Address,$latlng['lat'],$latlng['lng']);
		$stmt->execute();
				
		if(!$db->error){
			$lid = $stmt->insert_id;
			echo $lid." ";
			$meta = "INSERT INTO Location_meta (location_id,meta_key,meta_value) VALUES "
				."($lid,'District',?),"
				."($lid,'Phone',?),"
				."($lid,'Saturday',?),"
				."($lid,'Sunday',?),"
				."($lid,'New',?),"
				."($lid,'Description',?),"
				."($lid,'Highlights',?),"
				."($lid,'Type',?),"
				."($lid,'Style',?),"
				."($lid,'Architect_and_Date',?),"
				."($lid,'Indoor_Photo_OK',?),"
				."($lid,'Indoor_Photo_Tripod_OK',?),"
				."($lid,'Indoor_Video_OK',?),"
				."($lid,'Indoor_Video_Tripod_OK',?),"
				."($lid,'Nearest_subway',?),"
				."($lid,'Nearest_streetcar',?),"
				."($lid,'Accessibility',?),"
				."($lid,'Washrooms',?),"
				."($lid,'Parking',?),"
				."($lid,'Green_Building',?),"
				."($lid,'Kid_Friendly_Activities',?)";
			if($stmt = $db->prepare($meta)){
				//$hours = (string)$loc->SaturdayHours." | ".(string)$loc->SundayHours;
				$stmt->bind_param('sssssssssssssssssssss',
					$loc->District,
					$loc->Phone,
					$loc->SaturdayHours,
					$loc->SundayHours,
					$loc->New,
					$loc->Description,
					$loc->Highlights,
					$loc->Type,
					$loc->Style,
					$loc->Architect_and_Date,
					$loc->Indoor_Photo_OK,
					$loc->Indoor_Photo_Tripod_OK,
					$loc->Indoor_Video_OK,
					$loc->Indoor_Video_Tripod_OK,
					$loc->Nearest_Subway,
					$loc->Nearest_Streetcar,
					$loc->Accessibility,
					$loc->Washrooms,
					$loc->Parking,
					$loc->GreenBuilding,
					$loc->kidsActivity);
				$stmt->execute();
				$stmt->close();
				if($db->error){throw new Exception($db->error);}
			}else{throw new Exception('Database error.');}
			
			$stmt->close();
		} else {throw new Exception('Database error');}
	
	}else{echo 'Database error.';}
}


/*

$latlng = geocode((string)$doors->item->Street_Address);
var_dump($latlng);


$query = "INSERT INTO Location (name,address,latitude,longitude) VALUES (?,?,?,?)";


if($stmt = $db->prepare($query)){
	$stmt->bind_param('ssdd',$doors->item->Building,$doors->item->Street_Address,$latlng['lat'],$latlng['lng']);
	$stmt->execute();
	echo $stmt->insert_id;
	if(!$this->db->error){
		$stmt->close();
	} else {throw new Exception('Database error');}
}else{throw new Exception('Database error.');}
*/

?>
