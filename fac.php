<?php
/*
	Plugin Name: Frontaccounting Connect
	Plugin URI: http://www.waynemckenzie.com
	Description: A simple <a href="http://frontaccounting.com/">Frontaccounting</a> ERP stock display interface. [ <a href="options-general.php?page=fac_settings"><strong>Settings</strong></a> ] [ <a href="/wp-admin/widgets.php"><strong>Widgets</strong></a> ].
	
	Author: Wayne Mckenzie
	Version: 0.1
	Author URI: http://www.waynemckenzie.com
	
/////////////////////
// LICENSE
/////////////////////

// Frontaccounting Connect is released under a Creative Commons
// Attribution-Share Alike 3.0 United States license
// (http://creativecommons.org/licenses/by-sa/3.0/us/). Feel
// free to contact me to discuss any specifics https://www.facebook.com/Dreaddymck or http://www.waynemckenzie.com ).	
*/


if( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

if (!function_exists('write_log')) {
	function write_log ( $log )  {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}

define( 'PLUGIN_PATH', plugin_dir_path(__FILE__) );

require_once ( PLUGIN_PATH.'FAConnectdb.php');
require_once ( PLUGIN_PATH.'FAConnectAdmin.php');
require_once ( PLUGIN_PATH.'FAConnectItems.php');
require_once ( PLUGIN_PATH.'FAConnectItemHtml.php');
require_once ( PLUGIN_PATH.'FACWidget.php');
require_once ( PLUGIN_PATH.'FACImportPost.php');
require_once ( PLUGIN_PATH.'FACAdminMeta.php');
 
require_once( ABSPATH . 'wp-admin/includes/media.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/image.php' );

//unregister_widget("FACWidget");
			
if (!class_exists("FAConnect")) {
	
	class FAConnect {
	
		public $itemObj 		= null;
		
		function __construct() {
			
			$post = isset( $_POST ) ? $_POST : null;

			//write_log('FACconnect construct: '.json_encode($post));
			
			if( isset( $post['update'] ) ){				
				add_action('wp_default_scripts', array($this, 'import_to_post'));				
			}
			else
			if(isset( $post['totalpage'] )){
				add_action('wp_default_scripts', array($this, 'import_total'));
			}
			else
			if(isset( $post['processpage'] )){
				add_action('wp_default_scripts', array($this, 'import_process'));
			}
			else{			
				add_action('admin_menu', array($this, 'admin_menu'));			
				add_action('add_admin_bar_menus', array($this, 'fac_admin_bar_render'));
				add_action('add_meta_boxes', array($this, 'add_custom_meta_box'));
				
				add_action('save_post', array($this,'save_custom_meta'));
				
				add_filter('the_content', array($this, 'append_to_fac_post'));
			}
	
		}		
		/*
		 * admin menu
		 */
		function admin_menu () {
			add_options_page('Frontaccounting Connect settings','FaC','manage_options','fac_settings', array($this, 'admin_settings'));			
			global $wp_version;
			if ( version_compare($wp_version, '2.7', '>=' ) ) {
				//add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'admin_action_link') );
				add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'readme') );
			}	// Add link "Settings" to the plugin in /wp-admin/plugins.php		
		}
		
		function admin_settings() {
			$obj = new FAConnectAdmin();
			$obj->options_page();
		}
		
		function admin_action_link($links) {
			$settings_link = '<a href="'.$this->options_url().'fac_settings">' . __('Settings') . '</a>';
			array_unshift($links, $settings_link);
			return $links;
		}
		
		function readme($links) {
			$settings_link = '<a href="'.plugins_url('readme.txt',__FILE__ ).'">' . __('readme.txt') . '</a>';
			array_push($links, $settings_link);
			return $links;
		}		
		function fac_admin_bar_render() {
			global $wp_admin_bar, $wpdb;
						
			$this->add_root_menu("FaC", "fac", "/wp-admin/options-general.php?page=fac_settings");
			/*$this->add_sub_menu("FAConnect pages", "/", "fcbl");	*/		
						
		}
		
		/**
		* Add's new global menu, if $href is false menu is added but registred as submenuable
		*
		* $name String
		* $id String
		* $href Bool/String
		*
		* @return void
		**/

		function add_root_menu($name, $id, $href = FALSE)
		{
			global $wp_admin_bar;
			if ( !is_admin_bar_showing() )
			  return;

			$wp_admin_bar->add_menu( array(
			'id' => $id,
			'title' => $name,
			'href' => $href ) );
		}
		/**
		* Add's new submenu where additinal $meta specifies class, id, target or onclick parameters
		*
		* $name String
		* $link String
		* $root_menu String
		* $meta Array
		*
		* @return void
		**/
		function add_sub_menu($name, $link, $root_menu, $meta = FALSE)
		{
			global $wp_admin_bar;
			if ( !is_admin_bar_showing() )
			  return;

			$wp_admin_bar->add_menu( array(
			'parent' => $root_menu,
			'title' => $name,
			'href' => $link,
			'meta' => $meta) );

		}
		function options_url() {
			// "options-general.php?page=fac_settings"
			$adminurl = trailingslashit(get_option('siteurl')).'wp-admin/';
			return $adminurl = trailingslashit(admin_url())."options-general.php?page=";		
		}

		
		function append_to_fac_post( $content ){
			
			$meta 		= get_post_meta( get_the_ID() );

 			if( isset( $meta['stock_id'][0]) ) {
				
		
				$htmlObj 		= new FAConnectItemHtml;
				
				$new_content	= $htmlObj->the_content();			
				
				
				$new_content = preg_replace("/<stock_id\/>/i", $meta['stock_id'][0], $new_content);
				$new_content = preg_replace("/<the_content\/>/i", $content, $new_content);
				
				if( is_search() ) {				
					$new_content = preg_replace("/<image\/>/i", null, $new_content);
				}else {
					$new_content = preg_replace("/<image\/>/i", the_post_thumbnail('medium'), $new_content);
				}
				
				$new_content = preg_replace("/<price\/>/i", $meta['price'][0], $new_content);
				$new_content = preg_replace("/<curr_symbol\/>/i", $meta['curr_symbol'][0], $new_content);
				$new_content = preg_replace("/<tax_name\/>/i", $meta['tax_name'][0], $new_content);
				$new_content = preg_replace("/<units\/>/i", $meta['units'][0], $new_content);

				$obj = new FACAdminMeta();
				
				$custom_meta_fields = $obj->custom_meta_fields();
				// loop through fields and save the data - apply_filters('the_content', $meta)
				foreach ($custom_meta_fields as $field) {
					$meta = get_post_meta(get_the_ID(), $field['id'], true);
					if($meta) {
						$new_content = preg_replace("/<".$field['id']."\/>/i", $meta , $new_content);
					}else {
						$new_content = preg_replace("/<".$field['id']."\/>/i", null , $new_content);
					}
					
				} // end foreach				
				
				
				return $new_content;
			}
			 
			return $content;

		}
		
		/*
		 * import posts routine
		*/
		function import_to_post() {
			$obj 	= new FACImportPost();
			$obj->handlePostImport();
			exit();
		}
		function import_total() {
			$obj 	= new FACImportPost();
			$obj->getPagingInfo();
			exit();
		}		
		function import_process() {
			
			$post 	= isset( $_POST ) ? $_POST : null;
			$obj 	= new FACImportPost();
			$obj->processInfoByPage( $post['page'], $post['total'] );
			exit();
		}		
		
		/* admin custom Meta Box
		 *
		*/
		function add_custom_meta_box() {			
			$obj 	= new FACAdminMeta();			
			$obj->add_custom_meta_box();
		}		
		// Save the admin custom meta box data
		function save_custom_meta($post_id) {
			$obj 	= new FACAdminMeta();				
			$obj->save_custom_meta($post_id);
		}
		

		/* front page item handler
		*
		**/		
		function items_handler($atts) {		
		
			$options = get_option('fac_options');
		
			/*
			*	verify basic settings before running routine
			*/
			if( !is_array($options) || !is_numeric( $options['fac_dbtblpref'] ) || empty( $options['fac_itemperpage'] ) ) {
				echo "<pre class='error'>";
				echo _e("Setup missing required parameters. Contact your administrator." );
				echo "</pre>";
				exit();
			}			
			
			// default values
			extract(
				shortcode_atts(
					array(
						'stock_id' 		=> null,
						'results'		=> null,
						'page'			=> null,
						'total'			=> null,
					), $atts));
			
			$out 	= null;
			
			if(!$stock_id) {
				$stock_id = isset( $_GET['stock_id'] ) ? $_GET['stock_id'] : null;
			}else
			if(!$stock_id) {
				$stock_id = isset( $_POST['stock_id'] ) ? $_POST['stock_id'] : null;
			}
						
			$options['stock_id'] = $stock_id;
	
			if( $stock_id )
			{				
				//$obj->fa_get_item_details();
				
				$args = array(
						'meta_query' => array(
								array(
										'key' => 'stock_id',
										'value' => $stock_id,
										'compare' => '='
								),
						),
				);

				$query = new WP_Query( $args );

				//var_dump($args);
				//exit;
				
				if ($query->have_posts()) { 
					
					$htmlObj 	= new FAConnectItemHtml;
					
					$template 	= $htmlObj->shortcode_content();
										
					while ($query->have_posts()) : $query->the_post();				
				
						$meta = get_post_meta( $query->post->ID );				
						
						$new_content = $template;			
						
						$new_content = preg_replace("/<the_content\/>/i",  $query->post->post_content, $new_content);
					
						$new_content = preg_replace("/<stock_id\/>/i", $meta['stock_id'][0], $new_content);
			
						$new_content = preg_replace("/<image\/>/i", get_the_post_thumbnail( $query->post->ID, 'thumbnail'), $new_content);
						
						$new_content = preg_replace("/<permalink\/>/i", get_permalink( $query->post->ID ), $new_content);
						
						$new_content = preg_replace("/<price\/>/i", $meta['price'][0], $new_content);
						$new_content = preg_replace("/<curr_symbol\/>/i", $meta['curr_symbol'][0], $new_content);
						$new_content = preg_replace("/<tax_name\/>/i", $meta['tax_name'][0], $new_content);
						$new_content = preg_replace("/<units\/>/i", $meta['units'][0], $new_content);			
						$new_content = preg_replace("/<title\/>/i", get_the_title($query->post->ID), $new_content);
						
						$out = $out.$new_content;					
					
					endwhile;
				}
				
				wp_reset_postdata();
			}
			else 
			if($results) 
			{
			
				$obj = new FAConnectItems( $options );
				
				if($page) 
				{
					$obj->override_pg($page);
				}
				$obj->fa_get_items();

				/* object shared between
				 * FACImportPost, FAConnectItems
				 */
				$this->itemObj = $obj;
				
				$out = ($obj->results);				

				wp_reset_query();
				
			}
			else
			if($total)
			{					
				$obj = new FAConnectItems( $options );
			
				if($page)
				{
					$obj->override_pg($page);
				}
				$obj->fa_get_total();
			
				/* object shared between
				 * FACImportPost, FAConnectItems
				*/
				$this->itemObj = $obj;
			
				$out = ($obj->results);
			
				wp_reset_query();
				
				return null;
			}			
			return $out;
		}
		
	}
	new FAConnect;		
	add_shortcode( 'fac', array('FAConnect', 'items_handler') );	
	
}




?>