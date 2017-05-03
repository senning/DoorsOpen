<?php

session_start('monifest');

require_once 'config.php';
require 'manifest.php';
require_once 'event.php';
$event = new Doto;
require 'Slim/Slim.php';
require_once 'stocker-handler.php';
$stocker = new Photo_Stocker;


$app = new Slim(array(
	'templates.path'		=> 'templates/'
));


$app->get('/loc/:id','get_building');
$app->post('/login','login');
$app->post('/logout','logout');
$app->get('/list/','get_lists');
$app->get('/list/all','get_all_buildings');
$app->get('/list/:listID','get_list');
$app->post('/profile/','save_profile');
$app->post('/list/:listID/(:bID)','add_to_list');
$app->delete('/list/:listID/:bID','remove_from_list');

$app->get('/photo/:flickrID','get_photo');
$app->post('/photo/','process_photo');

$app->get('/thank-you','thanks');
$app->get('/donation-cancelled','cancelled');
$app->get('/','make_home');

$app->hook('slim.before','setup');

/*
*		Rendering
*/

function make_home(){
	global $app;
	
	$track = $app->getCookie('track');
	
	$app->render(
			'home.php',
			array(
				'track'			=>	$track
			)
		);
}

/*
*		AJAX Responders
*/

function get_building($id){
	global $app,$event;
	
	if(is_numeric($id)){
		try{
			$loc = $event->get_building($id);	
			echo json_encode($loc);
		}catch (Exception $e){
			var_dump($e);
		}
	}//not numeric				
}
	
function login(){
	global $app;
	
	$method = $app->request()->post('method');
	
	switch($method){
		case "browserID":
			$audience = BROWSERID_AUDIENCE;
			$assertion = $app->request()->post('assertion');
			$postdata = "assertion=".urlencode($assertion)."&audience=".urlencode($audience);
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://browserid.org/verify");
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			$json = curl_exec($ch);
			curl_close($ch);
						
			$res = json_decode($json,true);
			if($res['status'] === "okay"){
				$event = new Doto;
				$return = $event->get_user(array("by"=>"email","email"=>$res['email']));
				$return['expires'] = $res['expires'];
				setcookie("track",$return['uid'],time() + 21600); //change to tracker string
			} else {
				$return = $res;
			}
			
			echo json_encode($return);
			break;
		case "passphrase":
			//hash passphrase and check in DB
			//return status, userlist to script
			break;
	}
}
	
function logout(){
	setcookie("track","",-1);
}

function get_all_buildings(){
	global $app,$event;
	
	try{
		$list = $event->get_buildings(array("basic"=>true));
		echo json_encode(array("list"=>$list));
	}catch(Exception $e){
	
	}
}

function get_list($listID){
	global $app,$event;
	$username=$user=null;
	
	$track = $app->getCookie('track');
	
	try{
		if(!is_numeric($listID)){
			if($user = $event->is_username($listID)){
				$lists = $event->get_user_lists($user);
				$username = $listID;
								
				if(count($lists) > 0){
					$keys = array_keys($lists);
					$listID = array_shift($keys);
					$list = array_shift($lists);
				}else{return false;} 
			}else{
				$app->response()->status(404); return false;
			}
		}else{
			$list = $event->get_buildings_by_list($listID);
			if(!$list){
				$app->response()->status(204); return false;
			}else{
				$user = $event->get_user_by_list($listID);
				$username = $user['name'];
			}
		}
		//If current user cannot read the list, do not respond
		if(!$event->user_can('read_list',$track,$listID)){
			$app->response()->status(401); return false;
		}
			
		if($app->request()->isAjax()){
			$ret	= array("user"=>$user,"list"=>$list);
			echo json_encode($ret);
		} else{
			
			$app->render(
				'home.php',
				array(
					'track'	=>	$app->getCookie('track'),
					'lid'		=>	$listID,
					'name'	=>	$username					
				)
			);
		}
	}	catch(Exception $e){
		$app->response()->status(501);
		echo $e->getMessage();
	}
}

function get_lists(){
	global $app,$event;
	
	$offset = $app->request()->get('offset');
	$max 		= $app->request()->get('max');

	try{
		$opt = array();
		if($offset) $opt['offset'] = $offset;
		if($max)	$opt['max']	= $max;
		
		$res = $event->get_public_lists($opt);
		//$res = array(1,2,3);
		if(count($res) > 0){echo json_encode($res);
		}else{$app->response()->status(404);}
		
	}catch(Exception $e){
		$app->response()->status(501); return false;
	}
}

function add_to_list($listID,$bID=false){
	global $app,$event;
	
	$user = $app->getCookie('track');
	if(!$bID) $bID = $app->request()->post('newBIDs');
	try{
		$res = $event->add_to_list($user,$listID,$bID);
		if($res > 0){$app->response()->status(200);
		}	else{$app->response()->status(304);}
	}catch(Exception $e){
		$app->response()->status(501);
		//TODO: error reporting;
	}
}

function remove_from_list($listID,$bID){
	global $app,$event;
	
	$user = $app->request()->post('uid');
	try{
		$res = $event->remove_from_list($user,$listID,$bID);
	}catch(Exception $e){
		$app->response()->status(501);
		//TODO: error reporting;
	}
}

function get_photo($flickrID){
	global $app,$stocker;
	
	
	try{
		
		$res = $stocker->get_flickr_photo($flickrID);
		if($res){
			echo json_encode($res);
			$app->response()->status(200);
		}else{
			$app->response()->status(204); //Probably the wrong status code
		}
	}catch(Exception $e){
		$app->response()->status(400);
		echo json_encode(array("status"=>"error","error"=>$e->getMessage()));
	}
	
}

function process_photo(){
	global $app,$stocker;
	
	
	try{
		$info = array(
			"flickrID"	=>	$app->request()->post('flickrID'),
			"height"		=>	$app->request()->post('height'),
			"width"			=>	$app->request()->post('width'),
			"x"					=>	$app->request()->post('x'),
			"y"					=>	$app->request()->post('y'),
			"license"		=>	$app->request()->post('license'),
			"lID"				=>	$app->request()->post('location'),
			"owner"			=>	$app->request()->post('owner'),
			"tmp"				=>	$app->request()->post('tmp_file'),
			"url"				=>	$app->request()->post('url')
		);
		
		if($res = $stocker->make_photo($info)){
			$app->response()->status(201);
		}else{
			$app->response()->status(503);
		}
	}catch(Exception $e){
		$app->response()->status(400);
		echo $e->getLine().": ".$e->getMessage();
	}
	
}

function save_profile(){
	global $app,$event;
		
	$user = $app->request()->post('uid');
	$name = $app->request()->post('name');
	$priv = $app->request()->post('privacy');

	$valid	= $event->is_valid_username($name);
	$taken	= $event->is_username($name);
	
	if($taken && $taken != $user){
		$app->response()->status(406);
		echo json_encode(array("status"=>"error","error"=>"Username taken"));
	}elseif(!$valid){
		$app->response()->status(403);
		echo json_encode(array("status"=>"error","error"=>"Username contains spaces or only numbers"));
	}else{
		try{
			$res = $event->save_profile($user,$name,$priv);
			echo json_encode(array("status"=>"saved"));
		}catch(Exception $e){
			$app->response()->status(304);
			//echo $e->getLine().": ".$e->getMessage();
		}
	}
}

function thanks(){
	global $app;
	$app->render('thanks.php');
}

function cancelled(){
	global $app;
	$app->render('cancelled.php');
}

function setup(){
	require_once 'config.php';
}

/*
*	Always last:
*/

$app->run();
?>
