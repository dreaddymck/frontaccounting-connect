<?php

if (!class_exists("FAConnectAdmin")) {

	class FAConnectAdmin {
	
		public $post;
		public $error;
		public $options;
		public $atts;
		
		public function __construct($att=null) {

			if(is_array($att)) { 
				$this->$atts		= $att;
			}
			
			$this->post = isset( $_POST['fac_hidden'] ) ? $_POST['fac_hidden'] : null;			
			
			/*
			* uncomment to reset options.
			*/
			//delete_option('fac_options');
			
			$this->options = get_option('fac_options');

			if( ! is_array($this->options) ) {
				$this->options = array();
			}
			
			/*
			* default decimal value hard-coded for now
			*/
			$this->options['fac_price_dec'] = 2;
			
		}
		
		public function	get_post_flag(){
			return isset( $_POST['fac_hidden'] ) ? $_POST['fac_hidden'] : null;
		}
		public function get_super_admin_post_data() 
		{
			if ( !is_super_admin()	)
				return;	

			
			if( isset($_POST['fac_dbhost']) ){
				$this->options['fac_dbhost'] = trim($_POST['fac_dbhost']);
			}
			if( isset($_POST['fac_dbname']) ){
				$this->options['fac_dbname'] = trim($_POST['fac_dbname']);
			}
			if( isset($_POST['fac_dbuser']) ){
				$this->options['fac_dbuser'] = trim($_POST['fac_dbuser']);
			}
			if( isset($_POST['fac_dbpwd']) ){
				$this->options['fac_dbpwd'] = trim($_POST['fac_dbpwd']);
			}			
			if( isset( $_POST['fac_dbtblpref']) ) { 
				$this->options['fac_dbtblpref'] = trim($_POST['fac_dbtblpref']);
			}
						
		}
		public function get_post_data(){
		
			$this->options['fac_itemperpage'] 		= isset( $_POST['fac_itemperpage'] ) ? trim($_POST['fac_itemperpage']) : get_option('posts_per_page');
			
			$this->options['fac_prod_img_folder'] 	= isset( $_POST['fac_dbtblpref'] ) ? "company/".$this->options['fac_dbtblpref']."/images" : null;
			
			$this->options['fac_store_url'] 		= isset( $_POST['fac_store_url'] ) ? trim($_POST['fac_store_url']) : null;
			
			$this->options['fac_sales_type'] 		= isset( $_POST['fac_sales_type'] ) ? trim($_POST['fac_sales_type']) : null;
			
			$fac_tax_rate = isset( $_POST['fac_tax_rate'] ) ? $_POST['fac_tax_rate'] : null;
			
			if ( $fac_tax_rate ) {
			
				$tmparry = explode("|",$fac_tax_rate);				

				$this->options['fac_tax_name'] = trim($tmparry[0]);
				$this->options['fac_tax_rate'] = trim($tmparry[1]);
			}		
			

			//$this->options['fac_price_dec'] = 2;		
		}
		public function get_company_options() {		
		
			$obj	= new FAConnectDB(&$this->options);			
			$fadb 	= $obj->dbObj();
			$co		= isset( $this->options['fac_dbtblpref']) ? $this->options['fac_dbtblpref'] : 0;

			if ($fadb->query( $fadb->prepare("SELECT count(*) FROM %d_sys_prefs", $co) ) === FALSE) {
				$this->error = $fadb->error->errors["db_connect_fail"];
				//echo "<pre>";
				//var_dump( $fadb->error->errors["db_connect_fail"] );
				//echo "</pre>";
				return FALSE;
			} 			
			
			// company setup options
			//			
			$res = $fadb->get_results( 
						$fadb->prepare("SELECT * 
										FROM %d_sys_prefs", $co), 
						ARRAY_A	);			
			$this->sys_prefs2option($res);
			
			// echo "debug: ".get_option('fac_curr_default');
			// company default currency
			//
			$row = 	$fadb->get_row( 
					$fadb->prepare("SELECT cu.curr_abrev, cu.curr_symbol
									FROM %d_currencies cu
									where cu.curr_abrev = '%s'
									LIMIT 1", $co, $this->options['fac_curr_default'] ), 
								ARRAY_A );	
			if(is_array($row)) {
				foreach($row as $key => $value)	{
					
					$this->options['fac_'.$key] = trim($value);
				}
			}
			/*
			echo "<pre>";
			var_dump($row);
			echo "</pre>"; */
			
			
			//company tax array
			//
			$res = $fadb->get_results( 
						$fadb->prepare("SELECT * 
										FROM %d_item_tax_types where inactive = 0", $co), 
						ARRAY_A	);
			
			$this->options['fac_tax_array'] = $res;
			
			//company item sales type array
			//
			$res = $fadb->get_results( 
						$fadb->prepare("SELECT * 
										FROM %d_sales_types where inactive = 0", $co), 
						ARRAY_A	);
			
			$this->options['fac_sales_types_array'] = $res;

			//company item units array
			//
			$res = $fadb->get_results( 
						$fadb->prepare("SELECT * 
										FROM %d_item_units where inactive = 0", $co), 
						ARRAY_A	);
			
			$this->options['fac_item_units_array'] = $res;
			
			//company default tax			
			$res = $fadb->get_results(
					$fadb->prepare("SELECT * FROM %d_tax_types where inactive = 0",$co), 
						ARRAY_A	);
			
			$this->options['fac_tax_rate_array'] = $res;

			//company category table			
			$res = $fadb->get_results(
					$fadb->prepare("SELECT * FROM mgadmin_frontaccounting.%d_stock_category;",$co), 
						ARRAY_A	);
			
			$this->options['fac_category_table'] = $res;

			
			return true;
		}
		public function sys_prefs2option($results){			
			foreach($results as $key => $value)
			{	
				if(!is_array($value))
				{
					if($key == 'name') {
						
						
						$this->options['fac_'.$value] = $results['value'];
						/*echo "<pre>";
						echo "debug: ".$value."--".$this->options['fac_'.$value]."<br>";
						echo "</pre>";	*/					
					}
				}else{						
					$this->sys_prefs2option($value);
				}
			}		
		}
		public function item_tax_types2options($results){			
			foreach($results as $value)
			{	
				
				$this->options['fac_tax_type_'.$value['id']] = $value;
			}
			/*
			$tmp = get_option('fac_1'); 
			echo "<pre>";
			var_dump( $tmp["name"]);
			echo "</pre>";
			*/
		}
		public function setup_check() {
			
			if ( 
				isset($this->options['fac_dbhost']) && 
				isset($this->options['fac_dbname']) && 
				isset($this->options['fac_dbuser']) && 
				isset($this->options['fac_dbpwd']) 
				) 
				{
					return true;
				}
			return false;		
		}
		public function options_page() {
		
			if($this->post == 'Y') {
				
				$this->get_super_admin_post_data();
				$this->get_post_data();	
				
				if( $this->get_company_options() ) {				
					update_option('fac_options', $this->options);
				?>
				<div class="updated">
					<p><?php _e('Options saved.' ); ?></p>
				</div>
				<?php
				}
				else
				{
				?>
				<div class="error">
					<p><?php _e('Error saving options.' );?><?php echo $this->error[0]; ?></p>
				</div>
				<?php				
				}
			} 

			$fac_dbhost 			= isset( $this->options['fac_dbhost'] ) ? $this->options['fac_dbhost'] : null;
			$fac_dbname 			= isset( $this->options['fac_dbname']) ? $this->options['fac_dbname'] : null;
			$fac_dbuser 			= isset( $this->options['fac_dbuser']) ? $this->options['fac_dbuser'] : null;
			$fac_dbpwd 				= isset( $this->options['fac_dbpwd']) ? $this->options['fac_dbpwd'] : null;
			$fac_dbtblpref 			= isset( $this->options['fac_dbtblpref']) ? $this->options['fac_dbtblpref'] : 0;			

			
			include('FACAdminMenu.php');			
		}
	}
}

/*
function shrinky_init() {
	$new_options = array(
		'comments' => 'yes',
		'posts' => 'no',
		'replace' => 'yes',
		'trim' => 'no',
		'text' => 'link',
		'size' => '12',
		'scheme' => 'no',
		'www' => 'no',
		'elipse' => 'yes',
		'domain' => 'yes'
	);

	// if old options exist, update to new system
	foreach( $new_options as $key => $value ) {
		if( $existing = get_option( 'shrinky_' . $key ) ) {
			$new_options[$key] = $existing;
			
		}
	}

	add_option( 'plugin_shrinkylink_settings', $new_options );
}
*/

?>