var $window = $(window);
var $list = $("#big-list");
var $allLists = $("#location-list");
var $slidePane = $("#itinerary");
var $itinerary = $("#itinerary ul");
var $slideButton = $("#slideButton");
var $featured = $("#featured");
var $listLists = $("#list-list ul");
var $map = $('<div class="eight columns" id="navigator"></div>');
var gmap;
var markerArray = [];
var locArray = [];
var user = false;
var username = false;


function itinerarify(){
	$list.on("click","li",function(){		
		detailify($(this));
		return false;
	});
	$window.on("click",".detailBuilding",function(){
		detailify($(this).closest(".orbit-slide"));
		return false;
	});

	//browserID
	$(".browserID").click(function(){
		navigator.id.get(gotAssertion);
		return false;
		});
	
	$("#logout").click(function(){
		logout();
	});
	
	$list.on("mouseenter","li",function(){
		var building = $(this);
		//only include an Add button if building hasn't already been added
		if(!building.hasClass("added")){
			var addButton = $('<button class="addBuilding building-'+building.attr('data-id')+' tip-top" data-id="'+building.attr('data-id')+'" title="Add to itinerary"><img src="'+baseURL+'style/_.gif" class="icon add" alt="" /></button>');
			building.append(addButton);
			}
	}).on("mouseleave","li",function(){
		$(this).children("button.addBuilding").remove();
		});
		
	$list.on("click","button.addBuilding",function(){
		//on click, add building
		var bID = $(this).attr("data-id");
		addLocation(bID);
		return false;
		});
	
	$list.on("click","button.removeBuilding",
		function(){
			removeLocation($(this).attr("data-id"));
			return false;
		});

	$featured.on("click","button.addBuilding",function(){
		//on click, add building
		var bID = $(this).attr("data-id");
		addLocation(bID);
		return false;
		});
		
	$map.on("click","button.addBuilding",function(){
		var bID = $(this).attr("data-id");
		addLocation(bID);
		return false;
	})
	
	$featured.on("click","button.removeBuilding",
		function(){
			removeLocation($(this).attr("data-id"));
			return false;
		});


	$itinerary.on("click","button.removeBuilding",
		function(){
			removeLocation($(this).attr("data-id"));
			return false;
			});

	$map.on("click","button.removeBuilding",
		function(){
			removeLocation($(this).attr("data-id"));
			return false;
		});
	
	$list.on("click","button.closePane",function(){
		var pane = $(this).parent();
		pane.remove();
		$(".detailed").removeClass("detailed");
		});
		
	$slideButton.on("click",function(){paneSlidify();});
	
	$("#link-main").on("click",function(){
		listURL(null);
		return false;	});
	$("#link-thelists").on("click",function(){
		if($listLists.children().length < 1){getLists();
		}else{$listLists.slideToggle();}
		return false;
	});
	$listLists.on("click","li",function(){
		$listLists.slideToggle();
		listURL($(this).attr('data-listid'));
		return false;
	});
	
	$("#filter").change(function(){
		var n = $(this).val();
		
		if(n && n.length > 1){
			$list
				.find("li::Contains("+n+")")
				.show();
			$list
				.find("li:not(:Contains("+n+"))")
				.hide();
		}else if(!n){
			$list.children().show();
		}
	}).keyup(function(){$(this).change()});
}

function detailify(el){
	var deetbox = $("#details");
	var deeted = $(".detailed");
	var bID = el.attr("data-id");
	
	deetbox.remove();	
	//if this location isn't already detailed, get details
	if(!$("#big-list").children("li[data-id="+bID+"]").hasClass("detailed")){
		getBuilding(bID);
	}
	deeted.removeClass("detailed");
}

function appmenufy(){
	$(".nav-bar").on("click","li",function(){$(".nav-bar").slideToggle();});
	$(".showMap").on("click",function(){
		paneSlidify();
		if($(this).hasClass("mapped")){
			$(this)
				.removeClass("mapped")
				.text("Map");
		}else{
			$(this)
				.addClass("mapped")
				.text("List");
		
		}
		});
	$("#appmenu-button").on("click",function(){$(".nav-bar").slideToggle()});
}

function addLocation(bID,sync){
	sync = typeof sync !== 'undefined' ? sync : true;
	
	//set item in list to added
	$list.children("li[data-id="+bID+"]").addClass("added");
	replaceButton(bID,'remove');
		
	//manage button	
	var loc = $list.children('li[data-id='+bID+']');
	if(loc.children("button").size() < 1)
		loc.append('<button class="removeBuilding building-'+bID+'" data-id="'+bID+'" tip-top title="Remove from itinerary"><img src="'+baseURL+'style/_.gif" class="icon minus" alt="" /></button>');
		
	if(sync){
		$(".map-button")
			.animate({backgroundColor:"#2E9CED"},500,"swing")
			.animate({backgroundColor:"#0157A7"},750,"swing");
		$itinerary.append(loc.clone());
		}	
	
	activateMarker(loc.attr("data-id"),loc.attr("data-lat"),loc.attr("data-lng"));	
		
	if(sync && $itinerary.attr("data-listid")){
		//if triggered by user, add to itinerary, add the location to GMaps, send change to server

		var lid = $itinerary.attr('data-listid');
		$.ajax({
			type: "POST",
			url: baseURL+"list/"+lid+"/"+bID,
			dataType: "json",
			data: {uid:user}
		}).done(function(data){})
			.fail(function(data){sync_failed()});
	}
}

function removeLocation(bID,sync){
	sync = typeof sync !== 'undefined' ? sync : true;
	
	$list.children("li[data-id="+bID+"]")
		.removeClass("added")
		.children("button")
		.remove();
	$itinerary.children("[data-id="+bID+"]").remove();
	deactivateMarker(bID);
	replaceButton(bID,'add');
	
	if(sync && $itinerary.attr("data-listid")){
		var lid = $itinerary.attr('data-listid');
		$.ajax({
			type: "POST",
			url: baseURL+"list/"+lid+"/"+bID,
			dataType: "json",
			data: {uid:user,_METHOD:"DELETE"}
		}).done(function(data){})
			.fail(function(data){sync_failed()});
		}
	}

function replaceButton(bID,newButton){
	switch(newButton){
		case "add":
			$(".building-"+bID+":not(.verbose)").replaceWith('<button class="addBuilding building-'+bID+'" data-id="'+bID+'" title="Add to itinerary"><img src="'+baseURL+'style/_.gif" class="icon add" alt="Add to itinerary" /></button>');
			$(".building-"+bID+".verbose").replaceWith('<button class="addBuilding verbose building-'+bID+'" data-id="'+bID+'" title="Add to itinerary"><img src="'+baseURL+'style/_.gif" class="icon add" alt="Add to itinerary" /><label>Add to itinerary</label></button>');
			break;
			
		case "remove":
			$(".building-"+bID+":not(.verbose)").replaceWith('<button class="removeBuilding building-'+bID+'" data-id="'+bID+'" title="Remove from itinerary"><img src="'+baseURL+'style/_.gif" alt="Remove from itinerary" class="icon minus" /></button>');		
			$(".building-"+bID+".verbose").replaceWith('<button class="removeBuilding verbose building-'+bID+'" data-id="'+bID+'" title="Remove from itinerary"><img src="'+baseURL+'style/_.gif" alt="-" class="icon minus" /><label>Remove from itinerary</label></button>');
			break;
		}
	}

function getBuilding(bid){
	//get building details from storage or server
	if(details = localStorage.getItem("building"+bid)){
		//building found in Storage
		if(typeof JSON !== 'undefined') makeBuilding(bid,JSON.parse(details));
		else makeBuilding(bid,json_parse(details));
		
	} else{
		$.getJSON(
			baseURL+"loc/"+bid,{},
			function(data){
				makeBuilding(bid,data);
				if(localStorage)localStorage.setItem("building"+bid,JSON.stringify(data));
			}
		);	
	}
	$list.children('li[data-id="'+bid+'"]').addClass("detailed");
}

function makeBuilding(bID,deets){
	//use passed info to build a pane
	var details = $('<div id="details" data-id="'+bID+'" class="columns"></div>');
	//var features = $('<div class="features"></div>');
	var loc = $list.children("li[data-id="+bID+"]");
	
	if(loc.hasClass("added")){
		var itinButton = $('<button class="removeBuilding building-'+bID+' verbose" data-id="'+bID+'"><img src="'+baseURL+'style/_.gif" alt="" class="icon minus" /><label>Remove from itinerary</label></button>');
	}else{
		var itinButton = $('<button class="addBuilding building-'+bID+' verbose" data-id="'+bID+'"><img src="'+baseURL+'style/_.gif" alt="" class="icon add" /><label>Add to itinerary</label></button>');
		}
	var closeButton = $('<button class="closePane" tip-top title="Close details"><img src="'+baseURL+'style/_.gif" class="icon close" alt="Close details" /></button>');
	
	//features
	var icons = "";
	switch(deets.Accessibility){
		case "Yes":icons = icons + '<img src="'+baseURL+'style/_.gif" title="Accessible" class="icon Acc" />'; break;
		case "Partial":icons = icons + '<img src="'+baseURL+'style/_.gif" title="Partially accessible" class="icon Acc-part" />'; break;
		default:icons = icons + '<img src="'+baseURL+'style/_.gif" title="Not accessible" class="icon NAcc" />'; break;
		} 
	if(deets.Green_Building == 'Yes') icons = icons + '<img src="'+baseURL+'style/_.gif" title="Green building" class="icon GB" />';
	if(deets.Indoor_Photo_OK == 'Yes' && details.Indoor_Photo_Tripod_OK == 'No') icons = icons + '<img src="'+baseURL+'style/_.gif" title="Indoor photography allowed" class="icon Ph" />';
	else if(deets.Indoor_Photo_Tripod_OK) icons = icons + '<img src="'+baseURL+'style/_.gif" title="Indoor photography with tripod allowed" class="icon PhT" />';
	if(deets.Indoor_Video_OK == 'Yes' && deets.Indoor_Video_Tripod_OK == 'No') icons = icons + '<img src="'+baseURL+'style/_.gif" title="Indoor video allowed" class="icon Vi" />';
	else if(deets.Indoor_Video_Tripod_OK) icons = icons + '<img src="'+baseURL+'style/_.gif" title="Indoor video with tripod allowed" class="icon ViT" />';
	if(deets.Kid_Friendly_Activities == 'Yes') icons = icons + '<img src="'+baseURL+'style/_.gif" title="Kid friendly activities" class="icon KF" />';
	if(deets.New == 'Yes') icons = icons + '<img src="'+baseURL+'style/_.gif" title="New to Doors Open" class="icon New" />';
	if(deets.Parking == 'Yes') icons = icons + '<img src="'+baseURL+'style/_.gif" title="Parking" class="icon Park" />';
	if(deets.Washrooms == 'Yes') icons = icons + '<img src="'+baseURL+'style/_.gif" title="Washrooms" class="icon Wash" />';
	var features = $('<div class="icons">'+icons+'</div>');
	
	//summary
	var highlights = $('<div class="highlights">'+deets.Highlights+'</div>');
	var summary = $('<div class="summary"><dl class="list"><dt>Architect</dt><dd>'+deets.Architect_and_Date+'</dd><dt>Saturday</dt><dd>'+deets.Saturday+'</dd><dt>Sunday</dt><dd>'+deets.Sunday+'</dd><dt>Phone</dt><dd>'+deets.Phone+'</dd><dt>Style</dd><dd>'+deets.Style+'</dd></dd><dt>Address</dt><dd>'+deets.address+'</dd><dt>Nearest Streetcar</dt><dd>'+deets.Nearest_streetcar+'</dd><dt>Nearest Subway</dt><dd>'+deets.Nearest_subway+'</dd></div>').prepend(features).append(itinButton);
	var desc = $('<div class="desc">'+deets.Description+'</div>');
	
	var header = $('<header><div id="name"><h1>'+deets.name+'</h1><span>'+deets.address+'</span></header></div>');
	var photos = $('<div id="photos"></div>').prependTo(header);
	
	//or stuff the images as backgrounds for divs
	if(deets.photo){
		for(var i = 0; i < deets.photo.length; i++){
			photos.append('<div style="background-image:url(\'uploads/photos/'+deets.photo[i].file+'\')"><cite>'+deets.photo[i].license+' <a href="'+deets.photo[i].url+'">'+deets.photo[i].credit+'</a></cite></div>');
		}
	}
	
	var pointer = $('<img src="'+baseURL+'style/_.gif" class="pointer" alt="" />');
	
	details
		.append(closeButton,header,summary,highlights,desc,
			closeButton);
	if($window.width() > 767){
		var n = $list.children('li').index(loc);
		if(n < 0){/* return an error */
		}else if(n % 2 == 1){//index starts at 0
			pointer.addClass("right");
			details.append(pointer);
			loc.after(details);
		}else{
			pointer.addClass("left");
			details.append(pointer);
			loc.next().after(details);
			}
	}else{
		pointer.addClass("left");
		details.append(pointer);
		loc.after(details);
		}
	details.slideDown();
	
	//scroll to the detail box
	var position = details.position();
	$.scrollTo({top:position.top - 10,left:0},500);
	
	if(typeof deets.photo != 'undefined' && deets.photo.length > 1){
		photos.orbit({
			timer:true,
			animation: 'fade',
			advanceSpeed:4000,
			pauseOnHover: true
		});
	}
	//push down, scroll clicked to the top
	//create overlay blocking other list items; close detail pane if clicked
}

function makeList(data){
	var itemList = '';
	for (i in data){
		var l = data[i];
		if(typeof l.Neighbourhood == 'undefined') l.Neighbourhood = "";
		itemList += '<li data-id="'+i+'" data-lat="'+l.lat+'" data-lng="'+l.lng+'"><h3>'+l.name+'</h3><p class="place"><span class="neighbourhood">'+l.Neighbourhood+'</span><span class="addr">'+l.address+'</span></p><p class="desc">'+l.Description+'</p></li>';
		//add map marker
		addMarker(i,l.lat,l.lng);
	}
	return itemList;
}

function getList(lID){
	var list = lID ? lID : 'all';
	$list.empty().addClass("loading");
	$.ajax({
		url:baseURL+'list/'+list,
		type: 'GET',
		dataType: 'json'
	}).done(function(data){
		$list.append(makeList(data.list));
		if(typeof data.user != 'undefined')
			$list.prepend('<div class="identifier">'+data.user.name+' thinks these could be great</div>');
		$list.removeClass("loading");
		
		//update url on list change if browser is History capable
		//if(History.pushState()) listURL(list);
		
	}).fail(function(data){
		showMessage("error","Could not load the list. Please try again.");
		$list.removeClass("loading");
	});
}

function getLists(offset){
	if(typeof offset === 'undefined') offset = 0;
	$listLists.addClass("loading");
	$.ajax({
		url: baseURL+'list/',
		type:	'GET',
		dataType:	'json',
		data:{offset:offset}
	}).done(function(data){
		var items = '';
		for(var i=0; i<data.length; i++){
			var l = data[i];
			items +='<li class="userlist" data-listid="'+l.listID+'"><h3>'+l.UserName+'</h3><p>'+l.NumLocs+' locations</p></li>';
		}
		$listLists.append(items).removeClass("loading");
	});
}

/*
identity */
//jquery('#qrcode').qrcode({width: 64,height: 64,text: "size doesn't matter"});
//generates a URL with current itinerary OR uses the list URL for a saved list

function gotAssertion(assertion){
	$itinerary.addClass("loading");
	if(assertion !== null){
		$.ajax({
			type: "POST",
			url: baseURL+"login",
			dataType: "json",
			data: {method:"browserID","assertion":assertion}
		})
			.done(function(data){
				if(data === null){showMessage("error","Could not log you in. Please try again.");}
				else if(data['status'] == 'failure'){
					//"reason": "expired cert in chain"
					logout();
					}
				else{login(data)}
				})
			.fail(function(data){authFail(data)});
	}else{
	logout();
	}
}	

function login(data){
	user = data['uid'];
	
	//add user list(s)
	$("#signin").hide();
	$("#sync").show();
	
	if(data.list){
		//get first list
		firstID = data.list[0].id;
		//firstName = data.list[0].name;
	}else{
		firstID = data.listID;
	}
	$("#name").val(data.name);
	
	username = data.name;
	//TODO:if(data.new) request_introduction();
	if(data.new != true){
		makeItinerary(firstID);
	}else{
		$itinerary.removeClass("loading");
		$itinerary.attr('data-listid',firstID);
	}
}

function authFail(data){
	showMessage("error","Authorization failed.");
}

function logout(){
	$itinerary.removeClass("loading");
	$.ajax({type:"POST",url:baseURL+"logout"})
		.done(function(){
		})
		.fail(function(){showMessage("error","Error while signing you out. Reload and contact us if the problem persists.")});
}

function makeItinerary(listID){
	$itinerary.attr("data-listid",listID);
	//$itinerary.attr("data-listname",list['name']);
	
	//get the itinerary from the server
	$.ajax({
		type:	"GET",
		url:	baseURL+"list/"+listID,
		dataType:	"json",
		statusCode:	{
			200: function(data){
				var itemList ='';
				var serverList = new Array; //save list of IDs for comparison
				
				for (i in data.list){
					var l = data.list[i];
					if(typeof l.Neighbourhood == 'undefined') l.Neighbourhood = '';
					//make the item and add it to the list
					itemList += '<li class="added" data-id="'+i+'" data-lat="'+l.lat+'" data-lng="'+l.lng+'"><h3>'+l.name+'</h3><p class="place"><span class="neighbourhood">'+l.Neighbourhood+'</span><span class="addr">'+l.address+'</span></p><p class="desc">'+l.Description+'</p><button class="removeBuilding building-'+i+'" data-id="'+i+'" tip-top title="Remove from itinerary"><img src="'+baseURL+'style/_.gif" class="icon minus" alt="" /></button></li>';
					addLocation(i,false);
					serverList.push(i);
					if(!activateMarker(i)){
						addMarker(i,l.lat,l.lng);
						activateMarker(i);
					};
					i++;
				}
				$itinerary.append(itemList);
				//sync up local items that aren't on the server
				var newItems = new Array;
				$itinerary.children('li').each(function(){
					var bID = $(this).attr('data-id');
					if($.inArray(bID,serverList) == -1){newItems.push(bID)}
				});
				
				if(newItems.length > 0){
					$.ajax({
						type: "POST",
						url: baseURL+"list/"+listID+"/",
						dataType: "json",
						data: {newBIDs:newItems}
					}).done(function(data){})
						.fail(function(data){sync_failed()});
				}
			},204: function(){
				showMessage('warning','No buildings found on your itinerary. If there should be, try loading again.');
			},404: function(){
				showMessage("error","Your itinerary could not be found. Please try again.");
			}
		}
	});
	$itinerary.removeClass("loading");
}

function syncify(){
	//if(!user) return false;
	var lID = $itinerary.attr("data-listid");
	
	listArray = {
			name:		$itinerary.attr("data-listname"),
			locs:		locArray
		};
	
	$.ajax({
		type: "POST",
		url: baseURL+"list/",
		dataType: "json",
		data: {uid:user,listID:lID,list:listArray.locs}
	}).done(function(data){})
		.fail(function(data){sync_failed()});
}

function sync_failed(){
	showMessage("error","Could not sync your itinerary to the server. Reload and try again.")
}

function showMessage(type,message){
	var bubble = $('<div class="alert-box"></div>');
	var close = $('<a href="#" class="close">&times;</a>');
	switch(type){
		case "success":
			bubble.addClass("success");
			break;
		case "message":
			bubble.addClass("message");
			break;
		case "warning":
			bubble.addClass("warning");
			break;
		case "error":
			bubble.addClass("error");
			break;
		default: return false; break;
	}
	
	bubble.text(message).append(close)
		.appendTo("#alert-container")
		.fadeIn(500)
		.delay(4000)
		.fadeOut(500);
}

//send logout to server and navigator.id.logout

//passphrase
//generate on request
//creates simple account with cookie ID

function maxify(){	
	var height = $window.outerHeight();
	var width = $window.outerWidth();
	if(width>767){
		$itinerary.css({"height":height-96});
		$slidePane.height(height);
		$map.height(height-48);
	}else{
		$map.height(250);
		$itinerary.css({"height":height-298});
	}
}

function paneSlidify(){
	var pane = $("#itinerary");
	var $map = $("#navigator");
	var winWidth = $window.outerWidth();
	var paneWidth = winWidth - pane.outerWidth();
	if(winWidth > 767){
		if(pane.hasClass("slid")){
			pane.removeClass("slid");
			$map.animate({left:"66.7%"});
		}else{
			pane.addClass("slid");
			$map.animate({left:"0%"});
			$map.css({position:"fixed"});
		}
	}else{
		if(pane.hasClass("slid")){
			pane.removeClass("slid");
			pane.animate({height:"0px"});
			$allLists.fadeIn(500);
		}else{
			pane.addClass("slid");
			var winHeight = $window.outerHeight();
			pane.animate({height:winHeight-48+"px"});
			$allLists.fadeOut(500);
		}
	}
}

/*
GMaps handlers */
function startMap(){
	var winWidth = $window.outerWidth();
	var opt = {
		center: new google.maps.LatLng(43.732,-79.383),
		zoom: 11,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	
	if(winWidth > 767){
		$("#omni").append($map);
	}else{
		$itinerary.before($map);
		opt.zoom=10;
		}
	
	gmap = new google.maps.Map(document.getElementById("navigator"),opt);
	$("#navigator").change(function(){
		});
	
}

function addMarker(bID,lat,lng){
	var pin = baseURL+'style/inactive_pin.png';
	var pt = new google.maps.LatLng(lat,lng);
	marker = new google.maps.Marker({
		position:pt,
		map: gmap,
		icon: pin
	});
	markerArray.push(marker);
	locArray.push(bID);
	
	attachMarkerInfo(marker,bID);
}

function removeMarker(bID){
	var i = $.inArray(bID,locArray);
	if(i>-1){
		markerArray[i].setMap(null); //remove from map
		markerArray.splice(i,1); //remove from marker array
		locArray.splice(i,1); //remove from location array
	}else{/*return an error*/}
}

function activateMarker(bID){
	var i = $.inArray(bID,locArray);
	if(i>-1){
		markerArray[i].setIcon(baseURL+'style/active_pin.png');
	}else{return false;}
}

function deactivateMarker(bID){
	var i = $.inArray(bID,locArray);
	if(i>-1){
		markerArray[i].setIcon(baseURL+'style/inactive_pin.png');
		$(".map-button")
			.animate({backgroundColor:"#2E9CED"},500,"swing")
			.animate({backgroundColor:"#0157A7"},750,"swing");
	}
}

function attachMarkerInfo(marker,bID){
	
	google.maps.event.addListener(marker,'click',function(){
	var el = $list.children("li[data-id="+bID+"]");
		if(detailStr = localStorage.getItem("building"+bID)){
			if(typeof JSON !== 'undefined') details = JSON.parse(detailStr);
			else details = json_parse(detailStr);
		}else{
			details	=	{
				name:	el.children("h3").text(),
				address:	el.children(".place").children(".addr").text()
			}
		}
		if(el.hasClass('added')){
			var button = '<button class="removeBuilding verbose building-'+bID+'" data-id="'+bID+'" title="Remove from itinerary"><img src="'+baseURL+'style/_.gif" class="icon minus" alt="Remove from itinerary" /><label>Remove from itinerary</label></button>'
		}else{
			var button = '<button class="addBuilding verbose building-'+bID+'" data-id="'+bID+'" title="Add to itinerary"><img src="'+baseURL+'style/_.gif" class="icon add" alt="Add to itinerary" /><label>Add to itinerary</label></button>'
		}
		console.log(details);
		winStr = "<h3>"+details.name+"</h3><p>"+details.address+"</p><p>"+button+"</p>";
		var infowindow = new google.maps.InfoWindow({
			content:	winStr,
			size:	new google.maps.Size(200,100)
		});
			
		infowindow.open(gmap,marker);
	})
}

/*
Support functions */

function startify(){
	//respond to window resizing
	$window.resize(function(){maxify();});
	
	//if page says cookie, try autologin
	if($("body").hasClass("logged")) navigator.id.get(gotAssertion,{silent:true});
	
	if(typeof listID != 'undefined') getList(listID)
	else getList();
	
	$featured.orbit({
		animation:'fade',
		advanceSpeed:4000,
		pauseOnHover: true
	});
	
	$info = $("#profile-info");
	$("#profile").on("click",function(){
		$info.slideToggle();
	});
	$info.on("submit",function(){
		var uname = $info.children("#name").val();
		var privacy = $info.children("#privacy").prop("checked");
		
		$.ajax({
			type: "POST",
			url: baseURL+"profile/",
			dataType: "json",
			data: {uid:user,name:uname,privacy:privacy},
			statusCode:{
				200: function(){
					showMessage('success','Profile saved.');
					$("#profile-info").slideToggle();
					$(".pending-username").slideToggle();
					username = uname;
				},304: function(){
					showMessage("error","Could not save your profile. Please try again.");
					username = uname;
				},403: function(){
					showMessage("error","Name cannot use spaces or question marks and must have a non-number character");
				},406: function(){
					showMessage("error","Name not available. Please try again.");
				}
			}
		});
	});
	$("#share").on("click",function(){
		if($("#sharebox").length == 0){
			var sharebox = $('<div id="sharebox"></div>').insertAfter("#sync");
		
			if(!username){
				if(!$info.is(":visible")) $info.slideToggle();
				$info.prepend('<div class="alert-box warning">Need a name to identify your itinerary</div>');
				sharebox.addClass("pending pending-username");
			}else{fillSharebox();}
		}else{
			$("#sharebox").slideToggle();
		};	
	});

	//respond to history changes
	History.Adapter.bind(window,'statechange',function(){
		var state = History.getState();
		var parts = state.hash.split('/');
		var n = parts.indexOf('list');
		
		if(n >= 0){ getList(parts[n+1])
		}else{getList();}
	})
}

function fillSharebox(){
	var listURL = 'http://monifest.com/list/'+username;
	var urlBox = '<input type="text" id="listurl" value="'+listURL+'" />';
	$("#sharebox")
		.append('<p><label for="listurl">URL</label>'+urlBox+'</p>')
		.append('<div id="qrcode-list"></div>')
		.removeClass("pending pending-username");

	if(!$.qrcode){
		$.ajax({
			url:			baseURL+'javascripts/jquery.qrcode.min.js',
			dataType:	"script",
			cache:		true
		}).done(function(data){
			$("#qrcode-list").qrcode(listURL);
		});
	}
}

function jqExtend(){
	jQuery.expr[':'].Contains = function(a, i, m) {
		return jQuery(a).text().toUpperCase()
			.indexOf(m[3].toUpperCase()) >= 0;
	};
}

function listURL(list){
	console.log(list);
	if(typeof History.pushState !== 'undefined' && History.pushState()){
		if(list == 'all' || list == null) url = '';
		if(list !== null) url = 'list/'+list;
		return History.pushState('','',baseURL+url);
	}else{
		getList(list);
	}
}

/*
Controller */

jQuery(document).ready(function ($) {
	itinerarify();
	maxify();
	startify();
	startMap();
	jqExtend();
	if($window.outerWidth() < 767){appmenufy();}
});
