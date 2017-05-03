<?php
	/*
		Event app
	*/
	require_once('config.php');
	
	class Doto{
		public $manifest = false;
	
		function __construct(){
			//init db
			$this->db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_BASE);
		}
		
		function get_buildings($opt = array()){
			
			$default = array(
				"listID"				=>	false,
				"offset"				=>	0,
				"sort"					=>	"id",
				"limit"					=>	false,
				"not_in_list"		=>	false,
				"basic"					=>	false
			);
			$opt = array_merge($default,$opt);
			
			$query=$from=$sort=$limit="";
			$where = " WHERE 1=1";
			
			//Get the locations we want
			if($opt['listID']){
				//TODO: check listID is a legit list & user can access it
				$query	= "SELECT L.id,L.name,L.address,L.latitude,L.longitude";
				$from		=	" FROM vals2locs J"
					." LEFT JOIN  Location L ON J.loc_id = L.id";
				$where .= " AND J.val_id=".$opt['listID'];
				
			}else{
				$query 	= "SELECT L.id,L.name,L.address,L.latitude,L.longitude";
				$from 	= " FROM Location L";
				}
			
			
			if($opt['not_in_list']){
				$where .= " AND L.id NOT IN(SELECT loc_id FROM vals2locs WHERE val_id=".$opt['not_in_list'].")";
			}
			
			switch($opt['sort']){
				case "adds": 
					$query 	.=	",COUNT(val_id) as adds";
					if(!$opt['listID']) $from 	.= 	" LEFT JOIN vals2locs J ON J.loc_id = L.id";
					$sort 	= 	" GROUP BY J.loc_id ORDER BY adds DESC";
					break;
			}	
			if($opt['limit']){$limit = " LIMIT ".$opt['offset'].','.$opt['limit'];}
						
			$loc = array();		
			if($res = $this->db->query($query.$from.$where.$sort.$limit)): while($r = $res->fetch_assoc()):
				$loc[$r['id']] = array(
					"name"				=>	$r['name'],
					"address"			=>	$r['address'],
					"lat"					=>	$r['latitude'],
					"lng"					=>	$r['longitude']
				);
			endwhile; endif;
			
			//Get the metadata of the locations
			$lids = join(',',array_keys($loc));
			$query = "SELECT location_id,meta_key,meta_value FROM Location_meta";
			$where =" WHERE location_id IN (".$lids.")";
			if($opt['basic']) $where .= " AND meta_key IN ('Description','Neighbourhood')";
			
			if($res = $this->db->query($query.$where)): while($r = $res->fetch_assoc()):
				
				if($r['meta_key'] == 'photo'){
					//pack photos into a container list
					$loc[$r['location_id']]['photo'][] = unserialize($r['meta_value']);
				}elseif($r['meta_key'] == 'Description' && $opt['basic']){
					//shorten description
					$loc[$r['location_id']]['Description'] = substr($r['meta_value'],0,200);
				}else{$loc[$r['location_id']][$r['meta_key']] = $r['meta_value'];}
				
	
			endwhile; endif;
			
			
			return $loc;
		} //get_buildings()
		
		function get_top_buildings(){
			$return = $this->get_buildings(array(
				"limit"		=>	5,
				"sort"		=>	"adds"
			));
			return $return;
		}
		
		function get_buildings_by_list($lid){
			return $this->get_buildings(array(
				"listID"		=>	$lid
			));
		}
		
		function get_building($id){
		
			$loc = array();

			$query = "SELECT id,name,address,latitude,longitude FROM Location WHERE id=?";			
			//Get location summary
			if($stmt = $this->db->prepare($query)){
				$stmt->bind_param('d',$id);
				$stmt->execute();
				if(!$this->db->error){
					$stmt->bind_result($lid,$name,$address,$lat,$lng);
					$stmt->fetch();
					$loc = array(
						"id"				=>	$lid,
						"name"			=>	$name,
						"address"		=>	$address,
						"lat"				=>	$lat,
						"lng"				=>	$lng
					);
					$stmt->close();
					
				} else {throw new Exception($this->db->error);}
			}else{throw new Exception($this->db->error);}
		
		//Get location details
		$query = "SELECT meta_key,meta_value FROM Location_meta WHERE location_id=?";
		if($stmt = $this->db->prepare($query)){
			$stmt->bind_param('d',$id);
			$stmt->execute();
			if(!$this->db->error){
				$stmt->bind_result($key,$val);
				while($stmt->fetch()){
					if($key == "photo"){
						$loc['photo'][]  = unserialize($val);
					}else{$loc[$key] = $val;}
				}
				$stmt->close();
			} else {throw new Exception('GB2:'.$this->db->error);}
		}else{throw new Exception('GB1:'.$this->db->error);}	
		
		return $loc;
		
		} //get_building()
		
		/*
		*	list management
		*/
		
		function get_list($lid){
			$query = "SELECT loc_id FROM vals2locs WHERE val_id=? ORDER BY priority";
			$return = array();
			if($stmt = $this->db->prepare($query)){
				$stmt->bind_param('d',$lid);
				$stmt->execute();
				if(!$this->db->error){
					$stmt->bind_result($loc);
					$i = 0;
					while($stmt->fetch()){
						$return[$i] = $loc;
						$i++;
					}
					$stmt->close();
				} else {throw new Exception('GL2:'.$this->db->error);}
			}else{throw new Exception('GL1:'.$this->db->error);}
			return $return;
		}
		
		function get_user_lists($uid){
			$query = "SELECT V.val_id,V.loc_id FROM user_vals U"
				." LEFT JOIN vals2locs V ON V.val_id=U.vid"
				." WHERE U.uid = ? AND U.ukey='list'";
				
			$return = array();
			if($stmt = $this->db->prepare($query)){
				$stmt->bind_param('d',$uid);
				$stmt->execute();
				if(!$this->db->error){
					$stmt->bind_result($listID,$locID);
					while($stmt->fetch()){
						$return[$listID][]	=	$locID;
					}
					$stmt->close();
				} else {throw new Exception('GL2:'.$this->db->error);}
			}else{throw new Exception('GL1:'.$this->db->error);}
			return $return;
			/*
			if($res = $this->db->query($query)): while($r = $res->fetch_assoc()):
				$return[$r['val_id']][] = $r['loc_id'];
			endwhile; endif;
			return $return;
			*/
		}
		
		function add_to_list($uid,$lid,$bid){
			if($this->is_user($uid) && $this->user_can('add_to_list',$uid,$lid)){
				$query = "INSERT INTO vals2locs (val_id,loc_id) VALUES (?,?)";
				
				if($stmt = $this->db->prepare($query)){
					if(!is_array($bid)){
						$stmt->bind_param('dd',$lid,$bid);
						$stmt->execute();
						if(!$this->db->error){
							$return =$stmt->affected_rows;
							$stmt->close();
							return $return;
						} else {throw new Exception('Database error');}
					}else{
						$return = 0;
						foreach($bid as $id){
							$stmt->bind_param('dd',$lid,$id);
							$stmt->execute();
							if(!$this->db->error){
								$return += $stmt->affected_rows;
							}else{throw new Exception('Database error');}
						}
						$stmt->close();
						return $return;
					}
				}else{throw new Exception('Query error');}//prepare fail
			}
		}
		
		function remove_from_list($uid,$lid,$bid){
			if($this->is_user($uid) && $this->user_can('remove_from_list',$uid,$lid)){
				$query = "DELETE FROM vals2locs WHERE val_id=? AND loc_id=?";
				
				if($stmt = $this->db->prepare($query)){
					$stmt->bind_param('dd',$lid,$bid);
					$stmt->execute();
					if(!$this->db->error){
						return $stmt->affected_rows;
						$stmt->close();
					} else {throw new Exception('Database error');}
				}else{throw new Exception('Database error.');}
			}		
		}
		
		/*
		*	User functions
		*/
		
		function get_user($config){
			switch($config['by']){
				case "email":
						try{
							$return = $this->get_user_by_email($config['email']);
							$return['status'] = "okay";
						} catch (Exception $e){
							$return = array(
								"status"	=> "fail",
								"reason"	=> $e->getMessage(),
								"line"		=> $e->getLine()
							);
						}
						return $return;
					break;
				default:
					return false;
					break;
			
			}
		}
		
		private function get_user_by_email($address){
			$query = "SELECT uid FROM user_vals WHERE ukey='email' AND val=?";
			$users = array();
			$return = array();
			
			if($stmt = $this->db->prepare($query)){
				$stmt->bind_param('s',$address);
				$stmt->execute();
				if(!$this->db->error){
					$stmt->bind_result($uid);
					while($stmt->fetch()){
						$users[] = $uid;
					}
					$stmt->close();
				} else {throw new Exception('GUBE2:'.$this->db->error);}
			}else{throw new Exception('GUBE1:'.$this->db->error);}
			
			//no user by that email; create new
			if(count($users) == 0){
				$ret = $this->create_account($address);
			}else{
				//elseif (count($users) > 1) log error
				$return['new'] = false;
				$uid = $users[0];
				$ret = $this->get_account($uid);
			}

			$return = array_merge($return,$ret);			
			return $return;
		}
	
	function get_user_by_list($listID){
		
		$query = "SELECT U.id,U.name FROM user_vals V"
			." LEFT JOIN users U ON U.id=V.uid"
			." WHERE V.vid=? LIMIT 1";
		
		$return = false;
		if($stmt = $this->db->prepare($query)){
			$stmt->bind_param('d',$listID);
			$stmt->execute();
			if(!$this->db->error){
				$stmt->bind_result($id,$name);
				$stmt->fetch();
				$return = array(
					"id"	=>	$id,
					"name"=>	$name
				);
				$stmt->close();
			} else {throw new Exception('GUBL2: Database error');}
		}else{throw new Exception('GUBL1: Database error.');}
		return $return;
	}
	
	private function create_account($address){
		$return = array('new' => true);
				
		$query = "INSERT INTO users () VALUES ()";
		$this->db->query($query);
		if($this->db->error) throw new Exception('CA0:'.$this->db->error);
		else $user = $this->db->insert_id;
		$return['uid'] = $user;
				
		//add email to db, create a default list ID
		$def_list = 'Scratchpad';
		$query = "INSERT INTO user_vals(uid,ukey,val) VALUES (?,'email',?)"
			.",(?,'list',?)";
		if($stmt = $this->db->prepare($query)){
			$stmt->bind_param('dsds',$user,$address,$user,$def_list);
			$stmt->execute();
			if(!$this->db->error){
				$query = "SELECT vid FROM user_vals WHERE ukey='list' AND uid =".$user;
				if($res = $this->db->query($query)): while($r = $res->fetch_assoc()):
					$return['listID'] = $r['vid'];
				endwhile; endif;
				$stmt->close();
			} else {throw new Exception('CA2: Database error');}
		}else{throw new Exception('CA1: Database error.');}
		
		return $return;	
	}
	
	private function get_account($uid){
		$return = array("uid"	=>	$uid);
		
		$query = "SELECT name FROM users WHERE id=? LIMIT 0,1";
		if($stmt = $this->db->prepare($query)){
			$stmt->bind_param('d',$uid);
			$stmt->execute();
			if(!$this->db->error){
				$stmt->bind_result($name);
				$stmt->fetch();
				$return['name'] = $name;
				$stmt->close();
			} else {throw new Exception('Database error');}
		}else{throw new Exception('Database error.');}
		
		$query = "SELECT vid,val FROM user_vals V WHERE uid=? AND ukey='list'";
		if($stmt = $this->db->prepare($query)){
			$stmt->bind_param('d',$uid);
			$stmt->execute();
			if(!$this->db->error){
				$stmt->bind_result($listID,$list);
				$i = 0;
				while($stmt->fetch()){
					$return['list'][0] = array("id"=>$listID,"name"=>$list);
				}
				$stmt->close();
			} else {throw new Exception('GA2:'.$this->db->error);}
		}else{throw new Exception('GA1:'.$this->db->error);}
		$return['list'][0]['locations'] = $this->get_list($return['list'][0]['id']);
		return $return;
	}
	
	function save_profile($uid,$name,$priv){		
		$query = "UPDATE users SET name=? WHERE id=?";
		if($stmt = $this->db->prepare($query)){
			$stmt->bind_param('sd',$name,$uid);
			$stmt->execute();
			if(!$this->db->error){
				$stmt->close();
			} else {throw new Exception('Database error');}
		}else{throw new Exception('Database error.');}
				
		$query = "SELECT ukey,val FROM user_vals WHERE uid=?";
		$profile = array();
		if($stmt = $this->db->prepare($query)){
			$stmt->bind_param('d',$uid);
			$stmt->execute();
			if(!$this->db->error){
				$stmt->bind_result($k,$v);
				while($stmt->fetch()){
					$profile[$k] = $v;
				}
				$stmt->close();
			} else {throw new Exception('Database error');}
		}else{throw new Exception('Database error.');}
		
		//if user doesn't have a privacy setting yet, set it
		if(!isset($profile['lists_public'])){
			$query = "INSERT INTO user_vals (uid,ukey,val) VALUES (?,'lists_public',?)";
			if($stmt = $this->db->prepare($query)){
				$stmt->bind_param('ds',$uid,$priv);
				$stmt->execute();
				if(!$this->db->error){
					if($stmt->affected_rows == 0) throw new Exception('Database error');
					$stmt->close();
				} else {throw new Exception('Database error');}
			}else{throw new Exception('Database error.');}
		//otherwise, if user has a privacy setting and it's not what was just set, update
		}elseif($priv != $profile['lists_public']){
			$query = "UPDATE user_vals SET val=? WHERE ukey='lists_public' AND uid=?";
			if($stmt = $this->db->prepare($query)){
				$stmt->bind_param('sd',$priv,$uid);
				$stmt->execute();
				if(!$this->db->error){
					if($stmt->affected_rows == 0){
						$query = "INSERT INTO user_vals (uid,ukey,val) VALUES (?,'lists_public',?)";
						if($stmt = $this->db->prepare($query)){
							$stmt->bind_param('ds',$uid,$priv);
							$stmt->execute();
							if(!$this->db->error){
								if($stmt->affected_rows == 0) throw new Exception('Database error');
								
								$stmt->close();
							} else {throw new Exception('Database error');}
						}else{throw new Exception('Database error.');}
					}
				} else {throw new Exception('Database error');}
			}else{throw new Exception('Database error.');}
		}
	}
	
	function get_public_lists($opt){
		$default = array(
			"max"			=>	LIST_MAX_LENGTH,
			"offset"	=>	0
		);
		$opt = array_merge($default,$opt);
				
		$query = "SELECT V.uid,U.name,V.vid,val,COUNT(DISTINCT J.loc_id)"
			." FROM user_vals V"
			." LEFT JOIN users U ON U.id=V.uid"
			." LEFT JOIN vals2locs J ON J.val_id=V.vid"
			." WHERE ukey='list'"
			." AND uid IN(SELECT uid FROM user_vals WHERE ukey='lists_public' AND val='true')"
			." GROUP BY J.val_id"
			." LIMIT ?,?";
		
		$return = array();
		if($stmt = $this->db->prepare($query)){
			$stmt->bind_param('dd',$opt['offset'],$opt['max']);
			$stmt->execute();
			if(!$this->db->error){
				$stmt->bind_result($uid,$user,$listID,$listName,$numLocs);
				while($stmt->fetch()){
					$return[] = array(
						"listID"	=>	$listID,
						"listName"=>	$listName,
						"UserID"	=>	$uid,
						"UserName"=>	$user,
						"NumLocs"	=>	$numLocs
					);
				}
				$stmt->close();
			} else {throw new Exception('Database error');}
		}else{throw new Exception('Database error.');}
		return $return;
	}
	
	function is_user($id){
		return true;
	}
	
	function is_username($name){
		$query = "SELECT id FROM users WHERE name=? LIMIT 1";
		if($stmt = $this->db->prepare($query)){
			$stmt->bind_param('s',$name);
			$stmt->execute();
			if(!$this->db->error){
				$stmt->bind_result($id);
				$stmt->fetch();
				return $id;
				$stmt->close();
			} else {throw new Exception('Database error');}
		}else{throw new Exception('Database error.');}
	}
	
	function user_can($task,$track,$assetID=null){
		
		$uid = $track; //TODO: when track becomes a tracking cookie, translate to uid
		switch($task){
			case "read_list": return $this->user_can_read_list($uid,$assetID); break;
			default: break;
		}
		return true;
	}
	
	function is_valid_username($name){
		$return = true;	
		if(preg_match("|[^0-9 ]|",$name) === 0) $return = false;
		if(preg_match("/ |[?]|^all$/",$name) !== 0) $return = false;
		return $return;
	}
	
	function user_can_read_list($uid,$listID){
		$query = "SELECT uid FROM user_vals WHERE vid=?";
		if($stmt = $this->db->prepare($query)){
			$stmt->bind_param('d',$listID);
			$stmt->execute();
			if(!$this->db->error){
				$stmt->bind_result($user);
				$stmt->fetch();
				if($user == $uid) return true;
				$stmt->close();
			} else {throw new Exception('Database error');}
		}else{throw new Exception('Database error.');}
	
		$query = "SELECT val FROM user_vals WHERE uid=".$user." AND ukey='lists_public'";
		if($res = $this->db->query($query)): while($r = $res->fetch_assoc()):
			return ($r['val'] == 'true')? true : false;
		endwhile; endif;
	}	
}//Doto
?>
