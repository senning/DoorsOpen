<?php

	class Photo_Stocker{
		private $tmp_dir		=	'uploads/tmp/';
		private $upload_dir = 'uploads/photos/';
		
		function Photo_Stocker(){
		}
		
		function get_flickr_photo($flickrID){
			//Get the list of photo sizes
			$url = "http://api.flickr.com/services/rest/?method=flickr.photos.getSizes"
				."&api_key=".FLICKR_API_KEY
				."&photo_id=".$flickrID
				."&format=json&nojsoncallback=1";
			$json = file_get_contents($url);
			$photo = json_decode($json,true);
	
			$largest = count($photo['sizes']['size']);
			$return = $photo['sizes']['size'][$largest-1];
			
			//Get the largest photo and save
			$file = $this->tmp_dir.$flickrID.".jpg";	
			
			if(!file_exists($file)){
				$fh = fopen($file,'w+');
				$photo = file_get_contents($return['source']);
				fwrite($fh,$photo);
				fclose($fh);
			}
			$return['saved'] = $file;	//add saved location
			
			//Get the photo details
			$url = "http://api.flickr.com/services/rest/?method=flickr.photos.getInfo"
				."&api_key=".FLICKR_API_KEY
				."&photo_id=".$flickrID
				."&format=json&nojsoncallback=1";
			$json = file_get_contents($url);
			$info = json_decode($json,true);
			
			$return['page'] 	= $info['photo']['urls']['url'][0]['_content'];
			$return['owner'] = $info['photo']['owner']['username'];
			switch($info['photo']['license']){
				case 0: $return['license'] = "All Rights Reserved"; break;
				case 1: $return['license'] = "CC-NC-SA"; break;
				case 2: $return['license'] = "CC-NC"; break;
				case 3: $return['license'] = "CC-NC-ND"; break;
				case 4: $return['license'] = "CC-BY"; break;
				case 5: $return['license'] = "CC-SA"; break;
				case 6: $return['license'] = "CC-ND"; break;
				case 7: $return['license'] = "Commons"; break;
			}

			return $return;
		}
		
		/*
		*	Make the photo
		*/
		
		function make_photo($opt){
			$quality = 75;
			$src = imagecreatefromjpeg($opt['tmp']);
			
			
			$new_lrg = imagecreatetruecolor(1200,300);
			
			$copied = imagecopyresampled(
				$new_lrg,$src,
				0,0,$opt['x'],$opt['y'], //new x,y ; src x,y
				1200,300,$opt['width'],$opt['height'] //new width,height ; src width, height
				);
			
			//if(!$copied) throw new Exception('Could not copy');
			
			//insert other sizes here
			
			//clean up
			imagedestroy($src);
			//delete temp file?
			
			
			//save
			$save_file = $this->upload_dir.$opt['flickrID'].".jpg";
			
			if(file_exists($save_file)){
				$save_file = $this->upload_dir.$opt['flickrID']."-".time().".jpg";
			}
			imagejpeg($new_lrg,$save_file,$quality);
			imagedestroy($new_lrg);
			
			$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_BASE);

			$meta = array(
				"file"		=>	$opt['flickrID'].".jpg",
				"credit"	=>	$opt['owner'],
				"url"			=>	$opt['url'],
				"license"	=>	$opt['license']
			);
			
			$query = "INSERT INTO Location_meta (location_id,meta_key,meta_value)"
				." VALUES (?,'photo',?)";
			$lid = $opt['lID'];
			$photo_meta = serialize($meta);
			
			if($stmt = $db->prepare($query)){
				$stmt->bind_param('ds',$lid,$photo_meta);
				$stmt->execute();
				if(!$db->error){
					$affected = $stmt->affected_rows;
					$stmt->close();
					return $affected;
				} else {throw new Exception('Database error');}
			}else{throw new Exception('Database error.');}

		}//make_photo()
	}
?>
