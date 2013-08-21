<?php

/*
*
*
*
*/

if( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

if (!class_exists("FACImportPost")) {

	class FACImportPost {
		

		public $total		= null;
		public $obj			= null;
		public $options		= null;
		
		function __construct() {}
		
		function handlePostImport() {
	
			$this->obj 	= new FAConnect();
						
			$results 	= $this->obj->items_handler( array('results' => 'true') );
			
			$this->options	= $this->obj->itemObj->options;
			
			$pagetot	= ceil($this->obj->itemObj->total / $this->options['fac_itemperpage']);
			
			$currpage	= $this->obj->itemObj->pg;
			
			echo "Total items to process: ".$this->obj->itemObj->total."\n";		
			echo "*\n";
						
			$this->process(&$results);
			
			
			if($pagetot > 1) {
				
			
				do{
					
					$currpage++;
				
					$results 	= $this->obj->items_handler( 
															array(
																'results' => 'true', 
																'page' => $currpage,
																) 
															);
					
					echo "*\n";
					
					$this->process(&$results);
					
					/*
					 * just in case
					 * 
					 */
					
					if($currpage > 99999) {
						exit();
					}
					
				} while ($currpage < $pagetot ); 
				
				//var_dump($this->options);
				

				
			
			}
			update_option('fac_options', $this->options);
			
			echo "Finished\n";	

			// Restore original Post Data
			wp_reset_postdata();

		
		}
		function process($results) {
			
			foreach ($results as $value)
			{			
			
				$terms 		= $this->obj->itemObj->get_category_desc( $value["category_id"] );				
				$taxonomy	= 'category';
				
				$wpterm 	= term_exists( $terms, $taxonomy );
				
				if( ! $wpterm['term_id'] ) {

					$wpterm 	= wp_insert_term($terms, $taxonomy);				
				
				}
				
				//echo  "TERM ID".$wpterm['term_id']."\n";
			
				$post = array(
						'post_title'    => $value['description'],
						'post_content'  => $value['long_description'],
						'post_category' => array( $wpterm['term_id'] ),
						'post_status'   => 'draft',
						'post_author'   => 1,
				);
				
				
				
				$post_meta_method = "update_post_meta";
				//$post_meta_method = "add_post_meta";
								
				$query = $this->getExistingPostObj($value['stock_id']);				
				
				$post_id = null;
				
				if ( $query->have_posts() ) {
				
					$post_id = $query->post->ID;
				}				
				
				if( $post_id ) 
				{
					$post['ID'] = $post_id;
					$post['post_status'] = $query->post->post_status;
		
					wp_update_post( $post );
				}
				else
				{
					$post_meta_method = "add_post_meta";
					$post_id = wp_insert_post( $post);
				}
				
				/* add permalink to storage for further use.
				*/
				$this->options['permalink'][ $value['stock_id'] ] = get_permalink($post_id);				
				
				//echo "debug: ".get_permalink($post_id)."\n";
				
				$attachments = get_children( array('post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image') );				

/*debug
 * *
 */	
//var_dump($attachments);
//exit;			
				
				if ( $attachments ) {
					
					foreach ( $attachments as $attachment ) 
					{
						wp_delete_attachment( $attachment->ID, 'true' );
					}					
						
				}				

				
/*debug
 * *
*/
//var_dump($attachments);
//exit;				
				
				wp_set_post_terms( $post_id, $wpterm , $taxonomy );
								
				$post_meta_method($post_id, "stock_id", $value['stock_id']); 
				$post_meta_method($post_id, "curr_abrev", $this->options['fac_curr_abrev']);
				$post_meta_method($post_id, "curr_symbol", $this->options['fac_curr_symbol']);
				$post_meta_method($post_id, "tax_rate", $this->options['fac_tax_rate']);
				$post_meta_method($post_id, "tax_name", $this->options['fac_tax_name']);
				$post_meta_method($post_id, "qty", $value['qty']);
				$post_meta_method($post_id, "units", $value['units']);
				
				
				$tmpimgsrc = $this->obj->itemObj->get_image( $value['stock_id'] );
				
				if( ! $this->obj->itemObj->http_file_exists($tmpimgsrc) ) {
					
					$h = get_option('thumbnail_size_h');
					$w = get_option('thumbnail_size_w');
					
					$this->obj->itemObj->setDefaltImage($h,$w);					
					
					$tmpimgsrc = $this->obj->itemObj->defimg;
					
				}				

				
				
//var_dump( $tmpimgsrc );
								
				
				
				$imgsrc = $this->side_load_attachemt( $tmpimgsrc, $post_id, $value['description']) ;
				
				
//var_dump( $imgsrc );
				
				
				
				$this->set_wp_featured_image($imgsrc, $post_id, $post_meta_method);
				
				$post_meta_method( $post_id, "image", $imgsrc );

				$price = $this->obj->itemObj->price(&$value);
				
/*
var_dump ( $value['stock_id'] );
var_dump ( $value['price'] );
var_dump ( $value['std_cost_unit'] );
var_dump ( $value['actual_cost'] );
var_dump ( $value['material_cost'] );
var_dump ( is_array($price)) ;
var_dump ( $price );
*/			
				
				if( $price ) 
				{
					if( is_array($price) ) 
					{
						// not used atm - display wholesale/retail
						// or sales type description
						$sale_type_id = explode(",",$value["salestypecombined"]);						
						
						$innerprice	= 0;
						$inner_pri_w_tax	= null;
						
						for($i=0; $i < sizeof($price); $i++) {
						
							$innerprice = $price[$i];
							
							//do this before finish innerprice
							//							
							/*if( ! $value['price'] ) {															
								$inner_pri_w_tax	= $this->obj->itemObj->addtax($innerprice);															
							}*/
							
							/*var_dump ( $value['material_cost'] );
							var_dump ( $value['labour_cost'] );
							var_dump ( $value['overhead_cost'] );*/
							
							$innerprice	= $this->obj->itemObj->round_to_nearest( $innerprice );
							$inner_pri_w_tax = money_format('%i', $innerprice);
							
							$innerprice	= money_format('%i', $innerprice);
							
							$sales_type_row = $this->obj->itemObj->get_sales_type_row( $sale_type_id[$i] );	
							
							$post_meta_method($post_id, 'sales_type', $sales_type_row['sales_type'] );
							$post_meta_method($post_id, 'price', number_format( $innerprice, 2) );
							$post_meta_method($post_id, 'price_with_tax', number_format( $inner_pri_w_tax, 2 ) );
							$post_meta_method($post_id, 'tax_exempt', $this->obj->itemObj->tax_exempt_status(&$value['tax_type_id']) );
							
							/*echo "<pre>";
							var_dump($this->obj->itemObj->tax_exempt_status(&$value['tax_type_id']));
							echo "</pre>";
							*/
						}					
					}
					else
					{
						$fac_sales_types_array 	= $this->options['fac_sales_types_array'];
						
						if( is_array($fac_sales_types_array) )
						{
							$innerprice = 0;
							$inner_pri_w_tax	= null;
								
							foreach($fac_sales_types_array as $row)
							{
								$factor		= $row['factor'] ? $row['factor'] : 1;
								
								$innerprice	= $price * $factor;
								$inner_pri_w_tax	= ($innerprice);
						
								//do this before finish innerprice
								//
								/*if( ! $value['price'] ) {
									$inner_pri_w_tax	= $this->obj->itemObj->addtax($innerprice);
								}*/
								
								$inner_pri_w_tax	= $this->obj->itemObj->round_to_nearest( $inner_pri_w_tax );
								$inner_pri_w_tax 	= number_format( $inner_pri_w_tax, 2) ;
								
								$innerprice	= $this->obj->itemObj->round_to_nearest( $innerprice );
								$innerprice	= number_format( $innerprice, 2);
								
								
								$post_meta_method($post_id, 'sales_type', $row['sales_type'] );
								$post_meta_method($post_id, 'price', $innerprice );
								$post_meta_method($post_id, 'price_with_tax', $inner_pri_w_tax );
								$post_meta_method($post_id, 'tax_exempt', $this->obj->itemObj->tax_exempt_status(&$value['tax_type_id']) );
								
								/*
								echo "<pre>";
								var_dump($this->obj->itemObj->tax_exempt_status(&$value['tax_type_id']));
								echo "</pre>";								
								*/
							}
						}								
					}					
				}		

				
				//var_dump( $this->obj->itemObj->price(&$value) );
			
			}			
			
			//echo( $post_id );			

		}
		function getExistingPostObj($id) {
		
			$args = array(
					'post_type' => 'post',
					'meta_query' => array(
										array(
											'key'   => 'stock_id',
											'value'   => "$id",
										)),
					'posts_per_page' => '', //limit
					'paged' => get_query_var( 'page' ),
					'order' => 'DESC',
					'orderby' => 'date',
			);
			$query = new WP_Query($args);
				
			return ( $query );
		
		}	
		function side_load_attachemt($url, $post_id, $desc) {

			//$url = "http://s.wordpress.org/style/images/wp3-logo.png";
			$tmp = download_url( $url );

			// Set variables for storage
			// fix file filename for query strings
			preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $url, $matches);
			
			$file_array['name'] = basename($matches[0]);
			$file_array['tmp_name'] = $tmp;
			
			// If error storing temporarily, unlink
			if ( is_wp_error( $tmp ) ) {
				@unlink($file_array['tmp_name']);
				$file_array['tmp_name'] = '';
			}
			

//var_dump ( $file_array );



					
			// do the validation and storage stuff
			$id = media_handle_sideload( $file_array, $post_id, $desc );
			
			// If error storing permanently, unlink
			if ( is_wp_error($id) ) {
				@unlink($file_array['tmp_name']);
				return $id;
			}
			
			@unlink($file_array['tmp_name']);
			
			$src = wp_get_attachment_url( $id );
						
			return $src;
		}
		function set_wp_featured_image($filename, $post_id, $post_meta_method)
		{
			$wp_filetype = wp_check_filetype($filename, null);
			$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title' => $filename,
					'post_content' => '',
					'post_status' => 'inherit'
			);
		
			$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
			// you must first include the image.php file
			// for the function wp_generate_attachment_metadata() to work
			//require_once(ABSPATH . 'wp-admin/includes/image.php');
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
			wp_update_attachment_metadata( $attach_id, $attach_data );
		
			// add featured image to post
			//add_post_meta($post_id, '_thumbnail_id', $attach_id);
			$post_meta_method($post_id, '_thumbnail_id', $attach_id);
		
		}
		/*
		function tax_exempt_status($tax_id) {
		
			$id = get_post_meta($post_id, 'stock_id', true);
				
			$tax_type_row = $this->obj->itemObj->get_tax_type_row( $id );

				
			if( $tax_type_row['exempt'] ){
				return true;
			}
			return false;
		}
		*/					
	
	}

	
	new FACImportPost;
}


?>