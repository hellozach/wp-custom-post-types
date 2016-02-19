<?php
class CPT_meta_box {

	protected $_meta_box;

	// create meta box based on given data
	function __construct($meta_box) {
		if (!is_admin()) return;
	
		$this->_meta_box = $meta_box;

		// fix upload bug: http://www.hashbangcode.com/blog/add-enctype-wordpress-post-and-page-forms-471.html
		$current_page = substr(strrchr($_SERVER['PHP_SELF'], '/'), 1, -4);
		if ($current_page == 'page' || $current_page == 'page-new' || $current_page == 'post' || $current_page == 'post-new') {
			add_action('admin_head', array(&$this, 'add_post_enctype'));
		}
		
		add_action('admin_menu', array(&$this, 'add'));

		add_action('save_post', array(&$this, 'save'));
	}
	
	function add_post_enctype() {
		echo '
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#post").attr("enctype", "multipart/form-data");
			jQuery("#post").attr("encoding", "multipart/form-data");
		});
		</script>';
	}

	/// Add meta box for multiple post types
	function add() {
		foreach ($this->_meta_box['pages'] as $page) {
			add_meta_box($this->_meta_box['id'], $this->_meta_box['title'], array(&$this, 'show'), $page, $this->_meta_box['context'], $this->_meta_box['priority']);
		}
	}



	// Callback function to show fields in meta box
	function show() {
		global $post;

		// Use nonce for verification
		echo '<input type="hidden" name="mytheme_meta_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';
	
		echo '<table class="form-table">';

		foreach ($this->_meta_box['fields'] as $field) {
			// get current post meta data
			$meta = get_post_meta($post->ID, $field['id'], true);
		
			echo '<tr>',
					'<th style="width:20%"><label for="' . $field['id'] . '">' . $field['name'] . '</label></th>',
					'<td>';
			switch ($field['type']) {
				case 'text':
					echo '<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . ( $meta ? $meta : $field['std'] ) . '" size="30" style="width:97%" />',
						'<br />' . $field['desc'];
					break;
				case 'textarea':
					echo '<textarea name="' . $field['id'] . '" id="' . $field['id'] . '" cols="60" rows="4" style="width:97%">' . ( $meta ? $meta : $field['std'] ) . '</textarea>',
						'<br />' . $field['desc'];
					break;
				case 'wp_editor':
					echo $field['desc'] . '<br />';
					wp_editor( ( $meta ? $meta : $field['std'] ), $field['id'], $field['settings'] );
					break;
				case 'select':
					echo '<select name="' . $field['id'] . '" id="' . $field['id'] . '">';
					foreach ($field['options'] as $val => $option) {
						echo '<option  value="' . $val . '" ' . ( $meta == $val ? ' selected="selected"' : '' ) . '>' . $option . '</option>';
					}
					echo '</select>';
					break;
				case 'radio':
					foreach ($field['options'] as $option) {
						echo '<input type="radio" name="' . $field['id'] . '" value="' . $option['value'] . '"' . ( $meta == $option['value'] ? ' checked="checked"' : '' ) . ' />' . $option['name'];
					}
					break;
				case 'checkbox':
					if( empty( $meta ) && $field['checked'] == true ) $checked = true;
					if( $meta == '1' ) $checked = true;
					if( $meta == '0' ) $checked = false;
					echo '<label><input type="checkbox" name="' . $field['id'] . '" value="1" id="' . $field['id'] . '"' . ( $checked == true ? ' checked="checked"' : '' ) . ' /> ' . $field['desc'] . '</label>';
					break;
				case 'color':
					echo '<label for="' . $field['id'] . '">
							<input name="' . $field['id'] . '" type="text" value="' . ( $meta ? $meta : $field['std'] ) . '" class="meta-color" />
							' . '<br />' . $field['desc'] . '
						</label>';
					break;
				case 'media':
					echo '<label for="' . $field['id'] . '">
							<input class="button upload_image_button" type="button" data-attachto="' . $field['id'] . '" value="Select Media" />
							<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . ( $meta ? $meta : $field['std'] ) . '" size="30" />' . '<br />' . $field['desc'] . '
						</label>';
					break;
				case 'gallery':
					echo '<label for="' . $field['id'] . '">
							<input class="button gallery" type="button" data-attachto="' . $field['id'] . '" value="Select Media" />
							<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . ( $meta ? $meta : $field['std'] ) . '" size="30" />' . '<br /><div id="gallery_thumbs_' . $field['id'] . '"></div>
						</label>';
					break;
				case 'upload':
					echo '<label><input class="button upload_image_button" type="button" value="Upload Media" /><br />Just a handy place to upload media.</label>';
					break;
				case 'image':
					echo $meta ? '<img src="'.$meta.'" width="150" height="150" /><br />'.$meta.'<br />' : '<input type="file" name="' . $field['id'] . '" id="' . $field['id'] . '" />',
						'<br />' . $field['desc'];
					break;
				case 'file':
					echo $meta ? $meta."<br />" : '' . '<input type="file" name="' . $field['id'] . '" id="' . $field['id'] . '" />',
						'<br />' . $field['desc'];
					break;
				case 'taxonomy':
					echo '<select name="' . $field['id'] . '" id="' . $field['id'] . '">';
						echo '<option value="" ' . ( $meta == $term->slug ? 'selected' : '' ) . '>No Series</option>';
						$terms = get_terms( $field['taxonomy'] );
						foreach ( $terms as $term ) {
							echo '<option value="' . $term->slug . '" ' . ( $meta == $term->slug ? 'selected' : '' ) . '>' . $term->name . '</option>';
						}
					echo '</select>';
					echo '<br />' . $field['desc'];
					break;
			}
			echo 	'<td>',
				'</tr>';
		}
	
		echo '</table>';
	}

	// Save data from meta box
	function save($post_id) {
		// verify nonce
		
		if (isset($_POST['mytheme_meta_box_nonce'])) {
            if (!wp_verify_nonce($_POST['mytheme_meta_box_nonce'], basename(__FILE__))) {
                return $post_id;
            }

            // check autosave
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }

            // check permissions
            if ('page' == $_POST['post_type']) {
                if (!current_user_can('edit_page', $post_id)) {
                    return $post_id;
                }
            } elseif (!current_user_can('edit_post', $post_id)) {
                return $post_id;
            }

            foreach ($this->_meta_box['fields'] as $field) {
                $name = $field['id'];

                $old = get_post_meta($post_id, $name, true);
                $new = $_POST[$field['id']];

                if ($field['type'] == 'checkbox') {

                    if( $new == 1 )
                        update_post_meta($post_id, $name, $new);
                    else
                        update_post_meta($post_id, $name, 0);

                } else {

                    if ($field['type'] == 'file' || $field['type'] == 'image') {
                        $file = wp_handle_upload($_FILES[$name], array('test_form' => false));
                        $new = $file['url'];
                    }

                    if ($new && $new != $old) {
                        update_post_meta($post_id, $name, $new);
                    } elseif ('' == $new && $old && $field['type'] != 'file' && $field['type'] != 'image') {
                        delete_post_meta($post_id, $name, $old);
                    }

                } // end else checkbox
            }
        }
    }
}