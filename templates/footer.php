			<div class="four columns" id="itinerary">
				<header>
					<h2>Itinerary</h2>
					<button id="slideButton" class="map-button"><img src="<?php info('base_url'); ?>style/_.gif" title="Map" class="icon map" /></button>
				</header>
				<div id="signin">
					<a href="#" class="browserID" title="Sign in with BrowserID">
						<img src="<?php info('base_url'); ?>style/sign_in_green.png" alt="Sign in">
					</a> & Share
				</div>
				<div id="sync">
					<button id="profile"><img src="<?php info('base_url'); ?>style/_.gif" class="icon profile" /></button>
					<button id="share"><img src="<?php info('base_url'); ?>style/_.gif" class="icon share" /></button>
				</div>
				<form action="#" id="profile-info">
					<label for="name">Hey, what's your name?</label>
					<input type="text" id="name" />
					<label for="name" class="hint">We only use your name to identify your itinerary if you choose to share it (and just to say hello when you visit!).</label>
					<input type="checkbox" id="privacy" />
					<label for="privacy" class="side">Share my itinerary</label>
					<label for="privacy" class="hint">By making your list public, you can share it with your friends so they know what you'd like to see.</label>
					<input type="submit" class="submit" value="Save my profile" />
				</form>
				<ul data-listID=""></ul>
			</div>
		</div>

	</div>
	<!-- container -->
	<div id="about-monifest" class="reveal-modal">
		<h2>Who made Monifest?</h2>
		<p>Monifest is an independent, voluntary development by <a href="http://puppydogtales.ca">Puppy Dog Tales web & design</a> and not affiliated in any way with the City of Toronto nor with Doors Open or any of its sponsors. However, the City's open data initiative made this project far more practical than it would otherwise have been - that support is awesome.</p>
		
		<p>From its beginning 12 years ago, Doors Open has been on a weekend near my birthday; the annual tours around the city's architectural and engineering gems has been a real gift for me. I've always wanted to return the favour, so this year I made Monifest.</p>

		<h2>and more questions I wanted to answer:</h2>
		<dl>
			<dt>monifest made it so much easier to enjoy Doors Open and coordinate with my friends. Since it's ad-free could I donate to support your work?</dt>
			<dd>Yes please! You can donate through PayPal!
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="X8DWLNJW5ZF4Q">
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" />
			</form>
			</dd>
			<dt>I'm planning a festival and something like monifest would be great for our guests. How can I use it for my event?</dt>
			<dd>monifest is an open-source project and you can (soon) get the code on <a href="https://github.com/senning/Monifest">GitHub</a>. I also plan to flesh out monifest so that organizers can post their events and bundle them in to festivals. Until then, I'm also available to customize it for your needs!</dd>
			<dt>I've got some photographs you might want!</dt>
			<dd>Excellent! I can use any Creative Commons licensed or public domain photos (to simplify any legal issues) hosted on Flickr (I made a tool that makes importing Flickr photos way easier for me). Send me a link or your Flickr photo ID at <a href="mailto:senning@puppydogtales.ca">. Thank you!</a></dd>
			<dt>I'm own a photo you used/am involved with a building here and I'm not happy with something.</dt>
			<dd>I'm sorry! <a href="mailto:senning@puppydogtales.ca">Send me</a> a note with the issue and I'll try to resolve it right away.</dd>
			<dt>monifest must have taken a lot of work to make. Did you get any help with it?</dt>
			<dd>I can't thank enough the wonderful people at Zurb (creators of <a href="http://foundation.zurb.com/">Foundation</a>) and jQuery for producing such great bases on which to build the web. Mozilla's <a href="https://persona.org/">Personas</a> project provided an easy, secure sign in. The neighbourhood labels will hopefully make it easier to figure out where these sites are; the Toronto Star's excellent <a href="http://www.thestar.com/staticcontent/788429">neighbourhoods map</a> and Google's <a href="https://developers.google.com/maps/documentation/geocoding/">geocoding API</a> made that possible. <a href="http://en.wikipedia.org/wiki/LAMP_%28software_bundle%29">Linux, Apache, MySQL and PHP</a> are the cornerstones of this profession and their commitment to free and Free/Libre software make possible the internet as we know it.</dd>
		</dl>
	</div>
	
	<div id="about-privacy" class="reveal-modal">
		<h2>On your side</h2>
		<p>You've placed your trust in monifest and I intend to earn it. That's why I used Mozilla's Persona system to provide a secure, simple sign-in. And I will not:</p>
		<ul>
			<li>Sell or share your information (except allowing other users to see your itinerary, if you've chosen to make it public)</li>
			<li>Contact you, except for security announcements</li>
		</ul>
		<p>If you have any concerns about privacy or security on monifest, please <a href="mailto:senning@puppydogtales.ca">let me know</a>.</p>
	</div>

	<!-- Included JS Files -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script src="<?php info('base_url'); ?>javascripts/foundation.js"></script>
	<script src="<?php info('base_url'); ?>javascripts/app.js"></script>
	<script src="https://browserid.org/include.js"></script>
	<script src="<?php info('base_url'); ?>javascripts/wojo-storage.min.js"></script>
	<script src="<?php info('base_url'); ?>javascripts/history.js"></script>
	<script src="<?php info('base_url'); ?>javascripts/history.adapter.jquery.js"></script>
	<script src="<?php info('base_url'); ?>javascripts/jquery.scrollTo.js"></script>
	<script src="<?php info('base_url'); ?>javascripts/jquery.color.js"></script>
	<script type="text/javascript">
		var baseURL = '<?php echo BASE_URL ?>';
		<?php if(isset($lid)): ?>var listID = '<?php echo $lid ?>';<?php endif; ?>
		<?php if(isset($name)): ?>var listUser = '<?php echo $name ?>';<?php endif; ?>
	</script>

	<div id="alert-container"></div>
</body>
</html>
