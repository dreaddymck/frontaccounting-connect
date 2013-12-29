<?php //wp_enqueue_style( 'jquery-ui.css', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css' ); ?>
<?php //wp_register_script( 'jquery-ui.js', 'http://code.jquery.com/ui/1.10.3/jquery-ui.js', array(), '1.10.3', true ); ?>
<?php //wp_enqueue_script( 'jquery-ui.js'); ?>

<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<style type="text/css">
  	@import url("http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css");
</style>

<script language="javascript" >

var myurl = "<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>"

jQuery(document).ready(function() {
	
	jQuery('#fac-main-import-btn').click( function() {
		jQuery('#main').hide('fast', function(){
			jQuery('#main-import').show('fast');
			jQuery('#import-to-posts-btn').css('background', '#FFFF00').delay(500).queue(function(d) {
				jQuery(this).css('background', '');
				jQuery(this).dequeue();
		    }),
			jQuery('fac-debug').text('');
			});
	});
	jQuery('#fac-main').click( function() {
		jQuery('#main-import').hide('fast', function() {
			jQuery('#main').show('fast');
		});
		jQuery('#update-settings').css('background', '#FFFF00').delay(500).queue(function(d) {
			jQuery(this).css('background', '');
			jQuery(this).dequeue();
	    });
 	});	
 	jQuery('#import-to-posts-btn').click(function() {
 	 	
 		jQuery('#fac-debug').text('Processing'); 

 		jQuery(".resetProgressBar").resetProgressBar(  );
 		
 		jQuery.ajax({
 			  type: "POST",
 			  url: myurl,
 			  data: { totalpage: true }
 			}).done(function( msg ) {  	 							 
 				jQuery(".processItems").processItems( msg );
 			});		
 	});

 	jQuery.fn.processItems = function (msg) { 		
 		
 		obj = jQuery.parseJSON( msg );

 		jQuery("body").css("cursor", "progress");
        jQuery("#progressbar").progressbar({max: obj.total});
        jQuery("#progressbar").progressbar({value: obj.current}); 		

 		if( jQuery.isNumeric(obj.total) && jQuery.isNumeric(obj.current)  ) {

			if( obj.total >= obj.current ) {

	 	 		jQuery.ajax({
	 	 			  type: "POST",
	 	 			  url: myurl,
	 	 			  data: { processpage: true, page: obj.current, total: obj.total  }

	 	 			}).done(function( msg ) {
	 	 				jQuery(".processItems").processItems( msg );
 	 	 			});
				}else{
						jQuery("body").css("cursor", "auto");
 		        		jQuery("#progressbar").progressbar({disabled: true});	
	 		         	jQuery("#fac-debug").text("Processing finished."); 
				} 			
 		}
 	 		
 	}; 
 	jQuery.fn.resetProgressBar = function() {
 	 	
 		jQuery("#progressbar" ).progressbar({disabled: false});
 		jQuery("#progressbar").progressbar({max: 100});
 		jQuery("#progressbar").progressbar({value: 0}); 
 	};	
 	
});
	
</script>	



<div class="wrap">

	<?php 
		echo "<h2>" . __( 'Frontaccounting Connect Settings', 'fac_trdom' ) . "</h2>"
	?>
	
	<div style="float:left;padding-right:24px; padding-bottom: 12px">	
		<button style="float:left" id="fac-main" class="button ">
			<?php _e( 'Setup' ); ?>
		</button> 
	
	<?php if ( $this->setup_check() ) { ?>	
	
		<button style="float:left" id="fac-main-import-btn" class="button">
			<?php _e( 'Import/Update Frontaccounting to WP posts' ); ?>
		</button>
	
	<?php } ?>
	
	</div>
	
	<div style="clear:both;"></div>	
	
	<div id="main" style="display:block;">
	
		<div id="main-content" style="float:left;padding-right:24px;">
		
		<form name="fac_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
	
			<p class="submit">
			<input type="submit" name="update-settings" id="update-settings" class="button" value="<?php _e('Update Settings', 'fac_trdom' ) ?>" />
			</p>
			
			<input type="hidden" name="fac_hidden" value="Y">
			<?php 
				echo "<h4>" . __( 'Database Settings', 'fac_trdom' ) . "</h4>"; 
			?>
			<i><?php _e( 'Changes to FA settings will require a manual update to THESE settings' ); ?></i>	
			
			<?php
			if ( is_super_admin()	)
			{		
			?>
			<div class="updated below-h2" style="width:500px;">
				<p><strong><?php _e("Database host: " ); ?></strong><input type="text" name="fac_dbhost" value="<?php echo $fac_dbhost; ?>" size="30"><?php _e(" ex: localhost" ); ?></p>
				<p><strong><?php _e("Database name: " ); ?></strong><input type="text" name="fac_dbname" value="<?php echo $fac_dbname; ?>" size="30"><?php _e(" ex: db" ); ?></p>
				<p><strong><?php _e("Database user: " ); ?></strong><input type="text" name="fac_dbuser" value="<?php echo $fac_dbuser; ?>" size="30"><?php _e(" ex: root" ); ?></p>
				<p><strong><?php _e("Database password: " ); ?></strong><input type="password" name="fac_dbpwd" value="<?php echo $fac_dbpwd; ?>" size="30"></p>
				
				
				<?php if ( $this->setup_check() ) { ?>
				
				<p><strong><?php _e("Table preference: " ); ?></strong>
					<input type="text" name="fac_dbtblpref" value="<?php echo $fac_dbtblpref; ?>" size="10">
					<?php _e(" ex: 0 (company index)" ); ?>
				</p>
				<p>
					<strong>
					<?php _e("F.A. URL: " ); ?>
					</strong>
					<input type="text" name="fac_store_url" value="<?php echo $this->options['fac_store_url']; ?>" style="width:93%">
				</p>
				
				
				<?php } ?>
			
			</div>
			
			<?php
			}		
			?>		
	
			
			<?php if ( $this->setup_check() ) { ?>
			
			<?php echo "<h4>" . __( 'User Settings', 'fac_trdom' ) . "</h4>"; ?>		
			
			<input type="hidden" name="fac_itemperpage" value="<?php echo get_option('posts_per_page '); ?>"></p>
			
					
			<?php } ?>
	
			
	
			<p class="submit">
			<input type="submit" name="update-settings" id="update-settings" class="button" value="<?php _e('Update Settings', 'fac_trdom' ) ?>" />
			</p>
			
			
	
		
		</div>
		
		<?php if ( $this->setup_check() && !$this->error[0] ) { ?>
		
		<div id=main-info style="float:left;"  class="updated below-h2">
			<h3><?php  echo $this->options['fac_coy_name']; ?></h3>
			<hr>
			<p><?php  echo $this->options['fac_email']; ?></p>
			<p><?php  echo $this->options['fac_postal_address']; ?></p>
			<p><b><?php _e("co #:" ); ?></b> <?php  echo $this->options['fac_coy_no']; ?></p>
			<p><b><?php _e("Phone: " ); ?></b> <?php  echo $this->options['fac_phone']; ?></p>
			<p><strong><?php _e("Fax: " ); ?></strong> <?php  echo $this->options['fac_fax']; ?></p>
			<p><strong><?php _e("Currency: " ); ?></strong> 
				<?php  echo $this->options['fac_curr_default']; ?> 
				<?php  echo $this->options['fac_curr_symbol']; ?>
			</p>
	
	<!---		
			<div class="error" id="tax_rate_msg"><p><?//php _e("Please Select Default Tax Rate: " ); ?></p></div>	-->	
			<strong><?php _e("Tax Rate: " ); ?></strong>		
			<select name="fac_tax_rate" id="fac_tax_rate" >			
			<?php
				if( is_array($this->options['fac_tax_rate_array']) ) {
					foreach($this->options['fac_tax_rate_array'] as $value)
					{
			?>
					
				<option value="<?php echo $value['name']?>|<?php echo $value['rate']?>">
					<?php echo $value['name']?> - (<?php echo $value['rate']  ?>)
				</option>
					
			<?php
					}
				}
			?>
			</select>
			</p>
	
			<strong><?php _e("Tax Type: " ); ?></strong>		
			<ol>			
			<?php
				if( is_array($this->options['fac_tax_array']) ) {
					foreach($this->options['fac_tax_array'] as $value)
					{
			?>				
				<li>
					<?php echo $value['name']?> - (<?php _e("tax exempt: "); ?> <?php echo _e($value['exempt'] ? "Yes" : "No"); ?>)
					&nbsp;
				</li>				
			<?php
					}
				}					
			?>
			</ol>		
			
			<strong><?php _e("Sales Type: " ); ?></strong>
			<ol>
			<?php
				if( is_array($this->options['fac_sales_types_array']) ) {
					foreach($this->options['fac_sales_types_array'] as $value)
					{
			?>
					
				<li>
					<?php echo $value['sales_type']?> - ( <?php _e("tax included: " ); ?> <?php echo _e($value['tax_included'] ? "Yes" : "No"); ?>)
				</li>
					
			<?php
					}
				}
			?>		
			</ol>
			<strong><?php _e("Item Units: " ); ?></strong>		
			<ol>			
			<?php
				$this->options['fac_item_units_array'] = $this->options['fac_item_units_array'];
				if( is_array($this->options['fac_item_units_array']) ){
					foreach($this->options['fac_item_units_array'] as $value)
					{
			?>
					
				<li><?php echo $value['name']?></li>
					
			<?php
					}
				}					
			?>
			</ol>
			<p><strong><?php _e("Price Decimal: " ); ?></strong>2</p>
			
			<?php
			if ( is_super_admin()	)
			{		
			?>		
			<p><strong><?php _e("Markup: " ); ?></strong> <?php  echo $this->options['fac_add_pct']; ?>%</p>
			<?php
			}		
			?>		
			
			<hr>
			
			<p><strong><?php _e("Frontaccouting version: " ); ?></strong> <i><?php  echo $this->options['fac_version_id']; ?></i></p>
				
		
		</form>
	
			<p>
			<?php _e("Shortcode" ); ?> [ fac-display-items ]
			</p>
			<p>
				<strong>
				<a href="<?php echo plugins_url('readme.txt',__FILE__ )?>" >README.TXT</a>
				</strong>
			</p>		
			
		<?php } ?>
			Donations appreciated.
			<ul>
				<li>
				
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
	<input type="hidden" name="cmd" value="_s-xclick">
	<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
	">
	<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
	<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>
				
				</li>
				<li>
					<a class="lamazon" href="http://amzn.com/w/2K3TXW3QDBWBI">My Amazon Wish List</a>
				</li>
			</ul>
			
						
		
		</div>
	
	</div>
	
	<div id="main-import" style="display:none;">
		
		<button id="import-to-posts-btn" class="button">click to start</button>
		<p>
			Sync FrontAccounting products to Wordpress post:<br>
		</p>
		<p>			
			<div id="progressbar" style=""></div>
			<pre id="fac-debug"></pre>
		</p>
	</div>
</div>
