<?php
/*
Plugin Name: Software Information
Description: Creates some functionality for manage software information on your site
Version: 1.0.1
Author: Evgenii Solovei
Author URI: http://iwest-media.ru
License:     GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Domain Path: /languages
*/
?>
<?php
	/*************************/
	// load plugin text domain
	add_action('plugins_loaded', 'swi_load_textdomain');
	
	function swi_load_textdomain() {
		load_plugin_textdomain( 'swi', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
	}
	
	/*************************/
	// create post types and taxonomies
	function swi_setup_post_types() {
		$labels = array(
			'name'               => __( 'Software', 'swi' ),
			'singular_name'      => __( 'Software', 'swi' ),
			'menu_name'          => __( 'Software', 'swi' ),
			'name_admin_bar'     => __( 'Software', 'swi' ),
			'add_new'            => __( 'Add New', 'swi' ),
			'add_new_item'       => __( 'Add New Software', 'swi' ),
			'new_item'           => __( 'New Software', 'swi' ),
			'edit_item'          => __( 'Edit Software', 'swi' ),
			'view_item'          => __( 'View Software', 'swi' ),
			'all_items'          => __( 'All Software', 'swi' ),
			'search_items'       => __( 'Search Software', 'swi' ),
			'parent_item_colon'  => __( 'Parent Software:', 'swi' ),
			'not_found'          => __( 'No Software found.', 'swi' ),
			'not_found_in_trash' => __( 'No Software found in Trash.', 'swi' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'swi' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'software' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' )
		);
		register_post_type( 'software',$args);
		
		$labels = array(
			'name'              => __( 'Developers', 'swi' ),
			'singular_name'     => __( 'Developer', 'swi' ),
			'search_items'      => __( 'Search Developer', 'swi' ),
			'all_items'         => __( 'All Developers', 'swi' ),
			'parent_item'       => __( 'Parent Developer', 'swi' ),
			'parent_item_colon' => __( 'Parent Developer:', 'swi' ),
			'edit_item'         => __( 'Edit Developer', 'swi' ),
			'update_item'       => __( 'Update Developer', 'swi' ),
			'add_new_item'      => __( 'Add New Developer', 'swi' ),
			'new_item_name'     => __( 'New Developer Name', 'swi' ),
			'menu_name'         => __( 'Developer', 'swi' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'developer' ),
		);

		register_taxonomy( 'developer', array( 'software' ), $args );
		
		$labels = array(
			'name'              => __( 'Year', 'swi' ),
			'singular_name'     => __( 'Year', 'swi' ),
			'search_items'      => __( 'Search Year', 'swi' ),
			'all_items'         => __( 'All Years', 'swi' ),
			'edit_item'         => __( 'Edit Year', 'swi' ),
			'update_item'       => __( 'Update Year', 'swi' ),
			'add_new_item'      => __( 'Add New Year', 'swi' ),
			'new_item_name'     => __( 'New Year Name', 'swi' ),
			'menu_name'         => __( 'Year', 'swi' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'year' ),
		);
		
		register_taxonomy( 'year', array( 'software' ), $args );
	}
	
	add_action( 'init', 'swi_setup_post_types' );
	
	/*************************/
	// plugin activation 
	function swi_activate_plugin() {
		swi_setup_post_types();
		flush_rewrite_rules();
	}
	
	register_activation_hook( __FILE__, 'swi_activate_plugin' );
	
	/*************************/
	// plugin deactivation 
	function swi_deactivate_plugin() {
		
	}
	register_deactivation_hook( __FILE__, 'swi_deactivate_plugin' );
	
	/*************************/
	// add custom metabox to software page
	function swi_add_meta_boxes( $post ){
		add_meta_box( 'software_meta_box', __( 'Additional', 'swi' ), 'swi_build_meta_box', 'software', 'advanced', 'low' );
	}
	add_action( 'add_meta_boxes_software', 'swi_add_meta_boxes' );
	
	function swi_build_meta_box() {
		global $post;
		wp_nonce_field( basename( __FILE__ ), 'swi_meta_box_nonce' );
		$current_relevance = get_post_meta( $post->ID, '_swi_relevance', true );
		?>
		<div class='inside'>
			<h3><?php _e( 'Relevance', 'swi' ); ?></h3>
			<p>
				<input type="checkbox" name="relevance" <?php checked( $current_relevance, 'on' ); ?> /> <?php echo __('Actual version'); ?><br />
			</p>
<?php
	}
	
	function swi_save_meta_boxes_data( $post_id ){
		if ( !isset( $_POST['swi_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['swi_meta_box_nonce'], basename( __FILE__ ) ) ){
			return;
		}
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){
			return;
		}
		
		if ( ! current_user_can( 'edit_post', $post_id ) ){
			return;
		}
		
		if ( isset( $_POST['relevance'] ) ) {
			update_post_meta( $post_id, '_swi_relevance', sanitize_text_field($_POST['relevance'] ));
		} else {
			delete_post_meta( $post_id, '_swi_relevance' );
		}
	}
	add_action( 'save_post_software', 'swi_save_meta_boxes_data', 10, 2 );
	
	/*************************/
	// add help information on software page 
	function swi_admin_notice(){
		global $post;
		global $pagenow;
		if (($post->post_type=="software")&&($pagenow=='post-new.php')) {
			 echo '<div class="notice notice-info is-dismissible">
						<p>'.__('Add software. You can enter shortcode [swinfo id="" width=""] on any page to show software card or cards.').'</p>
						<p>'.__('id - list of ids of software posts. You can find it on software list page.').'</p>
						<p>'.__('width - width of software card in %.').'</p>
						<p>'.__('Shortcode examples:').'</p>
						<p>'.__('[swinfo id="all" width="100%"]: displays all software posts, width of software card is 100% of parent container.').'</p>
						<p>'.__('[swinfo id="2,6,7" width="70%"]: displays software posts with ids 2,6 and 7, width of software card is 70% of parent container.').'</p>
						<p>'.__('[swinfo id="12" width="30%"]: displays one software posts with id 12, width of software card is 30% of parent container.').'</p>
				</div>';
		}
		if (($post->post_type=="software")&&($pagenow=='post.php')) {
			echo '<div class="notice notice-info is-dismissible">
						<p>'.__('This software id is ').$post->ID.'</p>
				</div>';
		}
	}
	add_action('admin_notices', 'swi_admin_notice');
	
	/*************************/
	// shortcode output
	function swi_html_output($attrs) {
		wp_enqueue_style('swi-main-styles');
		$attrs = shortcode_atts(
			array(
				'id' => 'all',
				'template' => 'modern',
				'width' => '100%'
			), $attrs, 'swinfo' );
		//echo($attrs['id']);
		if ($attrs['id']=='all') {
			$args = array(
				'post_type' => 'software',
				'numberposts' => 0
			); 
		} else {
				$ids = explode(',',$attrs['id']);
				$args = array(
					'post_type' => 'software',
					'post__in' => $ids
				);
			}
		
		$softw_array = get_posts( $args );
		//print_r($softw_array);
		if ($attrs['width']!="100%") {
			$card_style = 'style="width:'.$attrs['width'].'!important;"';
		}
		if (count($softw_array)>0) {
			$s='';
			foreach ($softw_array as $soft) {
				$sw_image = get_the_post_thumbnail_url($soft->ID);
				
				$years = get_the_terms( $soft->ID, 'year' );
				if ( $years && ! is_wp_error( $years ) ) { 
					$years_text = array();
					foreach ( $years as $year ) {
						$years_text[] = $year->name;
					}
					$years_output = join( ", ", $years_text );
					$years_output=' <span>'.$years_output.'</span>';
				}
				if ($sw_image) {
					$sw_image_output = '<a href=""><img src="'.$sw_image.'"></a>';
				}
				$developers_txt = swi_get_post_taxonomy($soft->ID,"developer");
				$years_txt = swi_get_post_taxonomy($soft->ID,"year");
				$s.='<div class="swi_card" '.$card_style.'>
					<div class="swi_image">'.$sw_image_output.'</div>
					<div class="swi_content">
						<h5>'.$soft->post_title;
				if ($developers_txt) {
					$s.=' '.$developers_txt;
				}
				if ($years_txt) {
					$s.=', '.$years_txt;
				}
				$s.='</h5>';
				if ($soft->post_excerpt) {
					$s.='<p>'.$soft->post_excerpt.'</p>';
				}
				$s.='<div class="swi_rm">
						<a href="'.$soft->guid.'">'.__('Read more','swi').'</a>
					</div>
					</div>
				</div>';
			}
		}
		//return 'swinfo: ' . $attrs['id'] . ' ' . $attrs['template'];
		return $s;
	}
	add_shortcode('swinfo', 'swi_html_output');
	
	function swi_get_post_taxonomy($id,$taxonomy) {
		$terms = get_the_terms( $id, $taxonomy );
		//print_r($terms);
		if ( $terms && ! is_wp_error( $terms ) ) { 
			$terms_text = array();
			foreach ( $terms as $term ) {
				$terms_text[] = $term->name;
			}
			$terms_output = join( ", ", $terms_text );
			$terms_output='<span>'.$terms_output.'</span>';
		}
		return $terms_output;
	}
	/*************************/
	// add shortcode button to WP visual editor
	function swi_add_btn_script($plugin_array) {
		$plugin_array["swi_button_plugin"] =  plugin_dir_url(__FILE__) . "js/editor.js";
		return $plugin_array;
	}

	add_filter("mce_external_plugins", "swi_add_btn_script");
	
	function swi_add_button($buttons) {
        array_push($buttons, "swinfo");
		return $buttons;
	}

	add_filter("mce_buttons", "swi_add_button");
	
	/*************************/
	// load css file
	function swi_register_css() {
		wp_register_style("swi-main-styles", plugins_url("css/main.css", __FILE__), array(), "1.0", "all");
	}
	add_action( 'init', 'swi_register_css' );
	
	/*************************/
	// change list table, add new column
	function swi_add_column( $columns ) {
		$columns["swi_id"] = "ID";
		return $columns;
	}
	add_action('manage_edit-software_columns', 'swi_add_column', 10, 2);
	
	function swi_change_id_column( $name, $id ) {
    if ( $name == 'swi_id')
          echo $id;
	}
	add_action('manage_software_posts_custom_column', 'swi_change_id_column', 10, 2);
?>