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
			'supports'           => array( 'title', 'editor', 'author', 'thumbnail' )
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
	// add custom metavox to software page
	function swi_add_meta_boxes( $post ){
		add_meta_box( 'software_meta_box', __( 'Additional', 'swi' ), 'swi_build_meta_box', 'software', 'side', 'low' );
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
						<p>'.__('Help information.').'</p>
				</div>';
		}
	}
	add_action('admin_notices', 'swi_admin_notice');
?>