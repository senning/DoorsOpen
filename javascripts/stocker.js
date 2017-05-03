var selectionExists;

function triggers(){
	$("#flickrID").on('change',function(){
		var flickrID = $(this).val();
		if(flickrID && flickrID != "") get_photo(flickrID);
	});
	
	$("#infoForm").on('submit',function(){
		submit_info();
		return false;
	});
}

function cropify(){
	$('img#preview').imageCrop({
		displayPreview : true,
		displaySize : true,
		overlayOpacity : 0.25,
		aspectRatio: 4,
		onSelect : updateForm
	});
}

function get_photo(flickrID){
	
	$.ajax({
    url: baseURL+"photo/"+flickrID,
    type: "GET",
    dataType: "json"
	}).done(function(data){
		$("#tmp_file").val(data.saved);
		$("#license").val(data.license);
		$("#owner").val(data.owner);
		$("#url").val(data.page);
		$(".image-decorator")
			.empty()
			.append('<img id="preview" />');
		
		$("#preview")
			.attr("src",baseURL+data.saved)
			.attr("data-width",data.width)
			.attr("data-height",data.height);
		cropify();
		});
}

function submit_info(){
	var info = {};
	$("#infoForm input").each(function(){
		var name	= $(this).attr('name');
		var val		= $(this).val();
		info[name]= 	val;
	});
	var lID = $("#location").val();
	info['location'] = lID;
	
	$.ajax({
		url: baseURL+"photo/",
		type: "POST",
		data: info,
		statusCode:	{
			201:	function(){
				$("#infoForm input[type=text]").val('');
				showMessage("success","Photo added!");
			},
			400: function(){showMessage("error","Photo not added.");}
		}
	})
}


// Update form inputs
function updateForm(crop) {
	var $image = $("img#preview");
	var scale = parseInt($image.attr("data-width"))/parseInt($image.width());	
	
	$('input#x').val(parseInt(crop.selectionX)*scale);
	$('input#y').val(parseInt(crop.selectionY)*scale);
	$('input#width').val(parseInt(crop.selectionWidth)*scale);
	$('input#height').val(parseInt(crop.selectionHeight)*scale);
	selectionExists = crop.selectionExists();
	};

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

$(document).ready(function(){
	triggers();
	cropify();
});
