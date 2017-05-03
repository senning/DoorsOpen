<?php 
	require_once 'event.php';
	$event = new Doto;
	require_once 'config.php';

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
		<title>Monifest uploader</title>
		<link href="style/foundation.css" media="screen" rel="stylesheet" type="text/css" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
		<script src="javascripts/jquery.imagecrop.js" type="text/javascript"></script>
		<script src="javascripts/stocker.js" type="text/javascript"></script>
		<script type="text/javascript">
			<?php
			/*
			TODO USE YOUR OWN API KEY
			-------------------------
			

			load to imagebox
			on submit
				-crop the image and resize to 1200x300
				-crop the image and resize to 600x300
			Save to server
			*/
			?>

		</script>
		<style type="text/css">
			.image-decorator{
				box-shadow: 0 0 5px; #000;
				float:left;
				min-height: 200px;
				min-width: 150px;
				padding: 5px;
				max-width: 50%;
			}
			form{
				width: 425px;
				float:right;
				padding: 0 20px 0 0;
			}
			img{
				max-width: 100%;
			}
			form label,input{font-size: 1.5em;}
			input{width: 100%; margin: 0 0 1em;}
			cite{
				position:absolute;
				top: 0; right:0;
			}
			#alert-container{
		    left: 50%;
		    margin-left: -200px;
		    position: fixed;
		    top: 20px;
		    width: 400px;
		    z-index: 1000;
			}
		</style>
	</head>

	<body>
		<h1>Monifest image uploader</h1>
		<div id="alert-container"></div>
		
		<div class="image-decorator">
			<img alt="" id="preview" src="" />
		</div><!-- .image-decorator -->

		<form id="infoForm" action="#" method="post" onsubmit="">
			<label for="flickrID">flickr photo ID</label><input type="text" name="flickrID" id="flickrID" />
			
			<input id="x" name="x" type="hidden" />
			<input id="y" name="y" type="hidden" />
			<input id="width" name="width" type="hidden" />
			<input id="height" name="height" type="hidden" />
			<input type="hidden" id="tmp_file" name="tmp_file" />
			<?php 
				//TODO: add these
				?>
			<p><label for="license">License</label><input type="text" name="license" id="license" /></p>
			<p><label for="owner">Photographer</label><input type="text" name="owner" id="owner" /></p>
			<p><label for="url">Photo URL</label><input type="text" name="url" id="url" /></p>
			<p>
				<label for="location">Monifest location</label>
				<select name="location" id="location">
				<?php
					$locations = $event->get_buildings($opt);
					foreach($locations as $k=>$l){
						printf('<option value="%s">%s</option>',$k,$l['name']);
					}
				?>
				</select>
			</p>
			<input type="submit" value="Crop Image" />
		</form>
		<cite>Based on <a href="http://net.tutsplus.com/tutorials/javascript-ajax/how-to-create-a-jquery-image-cropping-plug-in-from-scratch-part-ii/">Catalin Dogaru's work NetTuts+</a></cite>
		<script type="text/javascript">
			var flickrKey = '<?php echo FLICKR_API_KEY; ?>';
			var baseURL		= '<?php echo BASE_URL; ?>';
		</script>
		<script type="text/javascript" src="javascripts/foundation.js"></script>
	</body>
</html>
