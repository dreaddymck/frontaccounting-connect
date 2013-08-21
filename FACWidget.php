<?php
/*
Widget Name: Frontaccounting Connect Widget
Widget URI: http://waynemckenzie.com/
Description: Frontaccounting connect stock display widget. Navigate to <a href="/wp-admin/widgets.php">Appearance / Widgets</a> to manage settings.
Author: dreaddymck
Version: 1
Author URI: http://waynemckenzie.com/
*/

if (!class_exists("FACWidget")) { 
	
	
	class FACWidget extends WP_Widget
	{		
		
		function FACWidget()
		{
			$widget_ops = array('classname' => 'FACWidget', 'description' => 'Frontaccounting connect stock display widget.' );
			$this->WP_Widget('FACWidget', 'FAConnect', $widget_ops);
		}
	 
		function form($instance)
		{
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'item_num' => '', 'randomi' => '', ) );
			$title = $instance['title'];
			$item_num = $instance['item_num'];
			$randomi = $instance['randomi'];
		?>
		  <p>
		  
			<label for="<?php echo $this->get_field_id('title'); ?>">
			Title: 
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</label>
			
			<label for="<?php echo $this->get_field_id('item_num'); ?>">
			Frontaccounting Stock id<br><i><small>( comma separated ):</small></i>
			<textarea class="widefat" id="<?php echo $this->get_field_id('item_num'); ?>" name="<?php echo $this->get_field_name('item_num'); ?>"><?php echo esc_attr($item_num); ?></textarea>
			</label>
			<!-- 
			<label for="<?php //echo $this->get_field_id('randomi'); ?>">
			Randomize: 	
			<input class="" id="<?php //echo $this->get_field_id('randomi'); ?>" name="<?php //echo $this->get_field_name('randomi'); ?>" type="checkbox" value="CHECKED" <?php //echo esc_attr($randomi) ?> />
			</label>
			 -->
		</p>
		<?php
		}
	 
		function update($new_instance, $old_instance)
		{
			$instance = $old_instance;
			$instance['title'] = $new_instance['title'];
			$instance['item_num'] = $new_instance['item_num'];
			$instance['randomi'] = $new_instance['randomi'];
			return $instance;
		}
		function widget($args, $instance)
		{
			extract($args, EXTR_SKIP);

			echo $before_widget;
			
			$title = empty($instance['title']) ? ' ' : apply_filters('widget_title', trim($instance['title']));
			$stock_id = empty($instance['item_num']) ? ' ' : apply_filters('widget_text', trim($instance['item_num']));
			$randomi = empty($instance['randomi']) ? ' ' : apply_filters('widget_text', trim($instance['randomi']));
			
			if (!empty($title))
				echo $before_title . $title . $after_title; 
			
			
			if( $stock_id ) {
			
				$stock_id_array = explode(",", $stock_id);
				
				array_walk($stock_id_array, 'trim');
	
				$args = array(					
						'meta_query' => array(							
								'relation' => 'OR',
								array(
									'key' => 'stock_id',
									'value' => $stock_id_array,
									'compare' => 'IN'
								),							
						),
				);
				
				//var_dump($args);
				
				$query = new WP_Query( $args );				
		
				//var_dump($query);
			
				//exit;
				
				$content = null;
				
				if ($query->have_posts()) { 
					
					$htmlObj 	= new FAConnectItemHtml;
					
					$template 	= $htmlObj->widget_content();
					
					while ($query->have_posts()) : $query->the_post();
	
						$meta = get_post_meta( get_the_ID() );				
						
						$new_content = $template;			
						
						$new_content = preg_replace("/<stock_id\/>/i", $meta['stock_id'][0], $new_content);
			
						$new_content = preg_replace("/<image\/>/i", get_the_post_thumbnail( get_the_ID(), 'thumbnail'), $new_content);
						
						$new_content = preg_replace("/<permalink\/>/i", get_permalink( get_the_ID() ), $new_content);
						
						$new_content = preg_replace("/<price\/>/i", $meta['price'][0], $new_content);
						$new_content = preg_replace("/<curr_symbol\/>/i", $meta['curr_symbol'][0], $new_content);
						$new_content = preg_replace("/<tax_name\/>/i", $meta['tax_name'][0], $new_content);
						$new_content = preg_replace("/<units\/>/i", $meta['units'][0], $new_content);			
						$new_content = preg_replace("/<title\/>/i", get_the_title(get_the_ID()), $new_content);
	
						$content = $content.$new_content;
					
			
					endwhile;
				}
				
				echo $content;
			}
			
			echo $after_widget;
			
			// Restore original Post Data
			wp_reset_postdata();
		}	 
	}
}

if (class_exists("FAConnectAdmin")) {
	$obj = new FAConnectAdmin();
	if($obj->setup_check() ) {
		//add_action( 'widgets_init', create_function('', 'return unregister_widget("FACWidget");') );
		add_action( 'widgets_init', create_function('', 'return register_widget("FACWidget");') );
	}
}


?>