<?php require 'header.php' ?>

		<div class="row" id="omni">
			<div class="eight columns" id="location-list">
				<div id="featured-container">
				<div id="featured">
				<?php
					try{the_top_locations();
					} catch (Exception $e){echo $e;}
				?>
				</div>
				</div>
				<div id="filters">
					<a href="" id="link-main">All</a>
					<a href="" id="link-thelists">Lists</a>
					<label for="filter">Search</label><input type="text" id="filter" />
				</div>
				<div id="list-list"><ul></ul></div>
				<ul class="block-grid two-up mobile" id="big-list">
					<?php 
						/*
						try{the_locations();						
						} catch (Exception $e){
							echo $e;
						}
						*/
						?>
				</ul>
			</div>		
<?php require 'footer.php'; ?>
