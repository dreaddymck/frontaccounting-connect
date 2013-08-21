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

if (!class_exists("FACAdminMeta")) 
{

	class FACAdminMeta 
	{ 
		function __construct() { } 
		
		/* Add the Meta Box
		 *
		*/
		function add_custom_meta_box() {
			global $post;
			
			$meta 		= get_post_meta( $post->ID );
		
			if( isset( $meta['stock_id'][0]) ) {
					
				add_meta_box(
						'custom_meta_box', // $id
						'FAC Custom Meta Box', // $title
						array(&$this,'show_custom_meta_box'), // $callback
						'post', // $page
						'normal', // $context
						'high'); // $priority
			}
		}
		function show_custom_meta_box() {
			global $post;
			// Use nonce for verification
			echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
		
			$custom_meta_fields = $this->custom_meta_fields();
		
			// Begin the field table and loop
			echo '<table class="form-table">';
			foreach ($custom_meta_fields as $field) {
				// get value of this field if it exists for this post
				$meta = get_post_meta($post->ID, $field['id'], true);
				// begin a table row with
				echo '<tr>
				<td>';
				switch($field['type']) {
					// case items will go here
					// text
					case 'text':
						echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" style="width:100%;" class="newtag form-input-tip" />
						<br /><span class="description">'.$field['desc'].'</span>';
						break;
						// textarea
					case 'textarea':
							
						echo '<div class="wrap"><p>'.$field['desc'].'</p>';
						
						$content = $meta;
						$id = $field['id'];
						$settings = array(
								'quicktags' => array(
										'buttons' => 'em,strong,link',
								),
								'quicktags' => true,
								'tinymce' => true
						);
						
						wp_editor($content, $id, $settings);
						
						echo '</div>';						
						
						
						/*echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" class="wp-editor-area" style="width:100%;height:200px;" >'.$meta.'</textarea>
						<br /><span class="description">'.$field['desc'].'</span>';*/
						break;
						// select
					case 'select':
						echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
						foreach ($field['options'] as $option) {
							echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';
						}
						echo '</select><br /><span class="description">'.$field['desc'].'</span>';
						break;
				} //end switch
				echo '</td></tr>';
			} // end foreach
			echo '</table>'; // end table
		}
		// Save the Data
		function save_custom_meta($post_id) {
			//global $custom_meta_fields;
			global $post;
		
			if( metadata_exists('post', $post->ID, 'stock_id' ) ) {			
		
				$custom_meta_fields = $this->custom_meta_fields();
			
				// verify nonce
				if (!wp_verify_nonce($_POST['custom_meta_box_nonce'], basename(__FILE__)))
					return $post_id;
				// check autosave
				if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
					return $post_id;
				// check permissions
				if ('page' == $_POST['post_type']) {
					if (!current_user_can('edit_page', $post_id))
						return $post_id;
				} elseif (!current_user_can('edit_post', $post_id)) {
					return $post_id;
				}
			
				// loop through fields and save the data
				foreach ($custom_meta_fields as $field) {
					$old = get_post_meta($post_id, $field['id'], true);
					$new = $_POST[$field['id']];
					if ($new && $new != $old) {
						update_post_meta($post_id, $field['id'], $new);
					} elseif ('' == $new && $old) {
						delete_post_meta($post_id, $field['id'], $old);
					}
				} // end foreach
			}
		}
		function custom_meta_fields() {
		
			$prefix = 'fac_custom_';
			return array(
		
					array(
							'label'=> 'FaC custom content 1',
							'desc'	=> 'Use this field to add more content to imported FAC items.',
							'id'	=> $prefix.'content_1',
							'type'	=> 'textarea'
					),
					/*array(
							'label'=> 'FaC custom content 2',
							'desc'	=> 'Use this field to add more content to imported FAC items.',
							'id'	=> $prefix.'content_2',
							'type'	=> 'textarea'
					),*/
		
					/*array(
							'label'=> 'FAC image for overlay',
							'desc'	=> 'Use this field to add a link to an image to overlay FAC produt item.',
							'id'	=> $prefix.'overlay_1',
							'type'	=> 'text'
					),*/
					/*array(
					 		'label'=> 'Checkbox Input',
					 		'desc'	=> 'A description for the field.',
					 		'id'	=> $prefix.'checkbox',
					 		'type'	=> 'checkbox'
					 ),*/
					/*array(
					 		'label'=> 'Select Box',
					 		'desc'	=> 'A description for the field.',
					 		'id'	=> $prefix.'select',
					 		'type'	=> 'select',
					 		'options' => array (
					 				'one' => array (
					 						'label' => 'Option One',
					 						'value'	=> 'one'
					 				),
					 				'two' => array (
					 						'label' => 'Option Two',
					 						'value'	=> 'two'
					 				),
					 				'three' => array (
					 						'label' => 'Option Three',
					 						'value'	=> 'three'
					 				)
					 		)
					 )*/
			);
		}		
		
		function wp_editor_test_page() {
			echo '<div class="wrap"><h2>wp_editor()</h2>';
		
			$content = '';
			$id = 'editor-test';
			$settings = array(
					'quicktags' => array(
							'buttons' => 'em,strong,link',
					),
					'quicktags' => true,
					'tinymce' => true
			);
		
			wp_editor($content, $id, $settings);
		
			echo '</div>';
		
		}		
		
	
	}	

}


?>
