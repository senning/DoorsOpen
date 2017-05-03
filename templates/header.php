<!DOCTYPE html>

<!-- paulirish.com/2008/conditional-style-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<meta name="description" content="The easy way to make and share your Doors Open plans, monifest maps your itinerary, makes a link and QR code to share with your friends, and adapts so you can bring it with you on your phone." />
	<meta property="og:image" content="<?php echo BASE_URL ?>style/monifest-square.png" />
	<meta property="og:site_name" content="monifest" />

	<!-- Set the viewport width to device width for mobile -->
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />

	<title>Doors Open Toronto Planner</title>
  
	<!-- Included CSS Files -->
	<link rel="stylesheet" href="<?php info('base_url'); ?>style/foundation.css">
	<link rel="stylesheet" href="<?php info('base_url'); ?>style/app.css">

	<!--[if lt IE 9]>
		<link rel="stylesheet" href="style/ie.css">
	<![endif]-->
	
	<script src="<?php info('base_url'); ?>javascripts/modernizr.foundation.js"></script>
	<script type="text/javascript"
      src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAuEPKPA7yp3w17bP1rKLlKM7Lbmh_3GBE&sensor=true">
    </script>

	<!-- IE Fix for HTML5 Tags -->
	<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	
	<!--[if gte IE 9]>
  <style type="text/css">
    .gradient {
       filter: none;
    }
  </style>
	<![endif]-->
</head>

<body class="<?php if(isset($track) && $track !== NULL) echo 'logged' ?>">

	<!-- container -->
	<div class="container">
		<header id="main-header">
			<h1><img src="<?php info('base_url'); ?>style/_.gif" id="logo" /></h1>
			<nav>
				<button class="appmenu map-button" id="appmenu-button"><img src="<?php info('base_url'); ?>style/_.gif" class="icon personal" /></button>
				<ul class="nav-bar">
					<li class="appmenu"><a href="#" class=".browserID"><img src="<?php info('base_url'); ?>style/sign_in_green.png" alt="" /> Sign in</a></li>
					<li><a href="#" class="appmenu showMap">Map</a></li>
					<li><a href="#" data-reveal-id="about-monifest">About</a></li>
					<li><a href="#" data-reveal-id="about-privacy">Privacy</a></li>
					<li><a href="mailto:senning@puppydogtales.ca">Contact</a></li>
				</ul>
			</nav>
			<h2>Doors Open Toronto</h2> 
		</header>
