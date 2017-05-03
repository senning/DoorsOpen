<?php
	require_once 'event.php';
	$event = new Doto;
	require_once 'config.php';

	function the_locations($opt=array()){
		global $event;
		
		$list = $event->get_buildings($opt);
		render_list($list);
	}
	
	function the_top_locations(){
		global $event;
	
		$list = $event->get_top_buildings();
		render_featured($list);		
	}
	
	function list_locations($lid,$opt = array()){
		global $event;
		
		$default = array(
			"listID"=>$lid
			);
		
		$opt = array_merge($default,$opt);
		
		$list = $event->get_buildings($opt);
		render_list($list);
	}
	
	function list_top_locations($lid){
		global $event;
		$list = $event->get_buildings(array(
			"listID"	=>	$lid,
			"sort"		=>	"adds"
		));
		render_featured($list);
	}
	
	function render_list($list){
		foreach($list as $key=>$location){
			if(!isset($location['Neighbourhood'])) $location['Neighbourhood'] = '';
			
			printf('<li data-id="%s" data-lat="%s" data-lng="%s"><h3>%s</h3><p class="place"><span class="neighbourhood">%s</span><span class="addr">%s</span></p><p class="desc">%s</p></li>',
			$key,
			$location['lat'],
			$location['lng'],
			$location['name'],
			$location['Neighbourhood'],
			$location['address'],
			substr($location['Description'],0,200)
		);
		}
	}
	
	function render_featured($list){
		$base = get_info('base_url');
		foreach($list as $key=>$location){
			if(isset($location['photo'][0]['file'])){
				$photo = $location['photo'][0]['file'];
			}else{
				$photo = "_.gif";
			}
			if(!isset($location['Neighbourhood'])) $location['Neighbourhood'] = '';
			
			printf('<div data-id="%s" data-lat="%s" data-lng="%s" style="background-image:url(\''.$base.'uploads/photos/%s\')"><div><div class="name"><h3>%s</h3><p class="place"><span class="neighbourhood">%s</span><span class="addr">%s</span></p></div><div><button class="detailBuilding"><img src="'.$base.'style/_.gif" alt="" class="icon detail" /></button><button class="addBuilding building-%s verbose" data-id="%s"><img src="'.$base.'style/_.gif" alt="" class="icon add" /><label>Add to itinerary</label></button></div></div></div>',
				$key,
				$location['lat'],
				$location['lng'],
				$photo,
				$location['name'],
				$location['Neighbourhood'],
				$location['address'],
				$key,$key
			);
		}
	}
	
	function get_info($attr){
		switch($attr){
			case 'base_url': return BASE_URL; break;
		}
	}
	
	function info($attr){
		echo get_info($attr);
	}

?>
