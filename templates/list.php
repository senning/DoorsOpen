<?php require 'header.php' ?>

		<div class="row" id="omni">
			<div class="eight columns" id="location-list">
				<div class="identifier"><?php echo $name; ?> thinks these could be great</div>
				<div id="featured-container">
				<div id="featured">
				<?php
					try{list_top_locations($lid);
					} catch (Exception $e){echo $e;}
				?>
				</div>
				</div> 
				<div id="filters">
					<a href="">Doors Open</a>
					<a href="">Curated</a>
					<label for="filter">Search</label><input type="text" id="filter" />
				</div>
				<ul class="block-grid two-up mobile" id="big-list">
					<?php 
						try{list_locations($lid);						
						} catch (Exception $e){
							echo $e;
						}
						?>
					<h3>Other locations</h3>
					<?php
						try{the_locations(array("not_in_list"=>$lid));
						}catch(Exception $e){
							echo $e;
						}
					?>
				</ul>
			</div>		
<?php require 'footer.php'; ?>
