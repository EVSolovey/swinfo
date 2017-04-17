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
		swi_create_log_table();
		add_option('swi_actual_card_color','#e6ee9c');
		add_option('swi_non_actual_card_color','#ffd0b0');
		add_option('swi_actual_button_color','#689f38');
		add_option('swi_non_actual_button_color','#e53935');
		add_option('swi_delete_logs',1);
	}
	
	function swi_create_log_table() {
		global $wpdb;
		$wpdb->query( 
				"CREATE TABLE IF NOT EXISTS  `swi_log` (
					`id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
					`swi_date` DATETIME NOT NULL ,
					`page_link` VARCHAR( 255 ) NOT NULL ,
					`software_id` BIGINT( 20 ) UNSIGNED NOT NULL
					)"
			);
		if($wpdb->last_error !== '') {
				// log errors
		} else {
			$posts_table = $wpdb->prefix."posts";
			$wpdb->query( 
					"ALTER TABLE `swi_log` ADD FOREIGN KEY (`software_id`) REFERENCES `".$posts_table."` (`ID`)"
			);
		}
	}
	
	register_activation_hook( __FILE__, 'swi_activate_plugin' );
	
	/*************************/
	// plugin deactivation 
	function swi_deactivate_plugin() {
		delete_option( 'swi_actual_card_color' );
		delete_option( 'swi_non_actual_card_color' );
		delete_option( 'swi_actual_button_color' );
		delete_option( 'swi_non_actual_button_color' );
		if (get_option('swi_delete_logs')=='1') {
			global $wpdb;
			$wpdb->query( 
				"DROP TABLE IF EXISTS `swi_log`"
			);
		}
		delete_option( 'swi_delete_logs' );
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
		if (isset($post)) {
			if (($post->post_type=="software")&&($pagenow=='post-new.php')) {
				 echo '<div class="notice notice-info is-dismissible">
							<p>'.__("Add software. You can enter shortcode [swinfo id='' width=''] on any page to show software card or cards.","swi").'</p>
							<p>'.__('id - list of ids of software posts. You can find it on software list page.',"swi").'</p>
							<p>'.__('width - width of software card in %.',"swi").'</p>
							<p>'.__('Shortcode examples:',"swi").'</p>
							<p>'.__('[swinfo id="all" width="100%"]: displays all software posts, width of software card is 100% of parent container.',"swi").'</p>
							<p>'.__('[swinfo id="2,6,7" width="70%"]: displays software posts with ids 2,6 and 7, width of software card is 70% of parent container.',"swi").'</p>
							<p>'.__('[swinfo id="12" width="30%"]: displays one software posts with id 12, width of software card is 30% of parent container.',"swi").'</p>
					</div>';
			}
			if (($post->post_type=="software")&&($pagenow=='post.php')) {
				echo '<div class="notice notice-info is-dismissible">
							<p>'.__('This software id is ').$post->ID.'</p>
					</div>';
			}
		}
	}
	add_action('admin_notices', 'swi_admin_notice',25);
	
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
			$swi_opt_actual = get_option("swi_actual_card_color");
			$swi_opt_non_actual=get_option("swi_non_actual_card_color");
			$swi_opt_btn_actual = get_option("swi_actual_button_color");
			$swi_opt_btn_non_actual=get_option("swi_non_actual_button_color");
			foreach ($softw_array as $soft) {
				//$sw_image = get_the_post_thumbnail_url($soft->ID);
				$actuality = get_post_meta($soft->ID,'_swi_relevance',true);
				if ($actuality=='on') {
					if (($swi_opt_actual)&&(preg_match( '/^#[a-f0-9]{6}$/i', $swi_opt_actual ))) {
						$card_div = 'class="swi_card" style="background-color:'.$swi_opt_actual.'"';
						
					} else {
						$card_div='class="swi_card swi_green"';
						
					}
					if (($swi_opt_btn_actual)&&(preg_match( '/^#[a-f0-9]{6}$/i', $swi_opt_btn_actual ))) {
						$button_div = 'style="background-color:'.$swi_opt_btn_actual.'"';
					} else {
						$button_div = 'class="swi_btn_green"';
					}
				} else {
					if (($swi_opt_non_actual)&&(preg_match( '/^#[a-f0-9]{6}$/i', $swi_opt_non_actual ))) {
						$card_div = 'class="swi_card" style="background-color:'.$swi_opt_non_actual.'"';
						
					} else {
						$card_div='class="swi_card swi_red"';
					}
					if (($swi_opt_btn_non_actual)&&(preg_match( '/^#[a-f0-9]{6}$/i', $swi_opt_btn_non_actual ))) {
						$button_div = 'style="background-color:'.$swi_opt_btn_non_actual.'"';
					} else {
						$button_div = 'class="swi_btn_green"';
						$button_div = 'class="swi_btn_red"';
					}
				}
				if (has_post_thumbnail($soft->ID)) {
					$sw_image=get_the_post_thumbnail( $soft->ID);
					$sw_image_output = '<a href="'.$soft->guid.'">'.$sw_image.'</a>';
				} else {
					$sw_image_output='';
				}
				$card_style='';
				$developers_txt = swi_get_post_taxonomy($soft->ID,"developer");
				$years_txt = swi_get_post_taxonomy($soft->ID,"year");
				$s.='<div '.$card_div.'>
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
						<a '.$button_div.' href="'.$soft->guid.'">'.__('Read more','swi').'</a>
					</div>
				  </div>
				</div>';
				$now = date("Y-m-d H:i:s");
				global $post;
				swi_log_view($now, $post->guid, $soft->ID);
			}
		}
		//return 'swinfo: ' . $attrs['id'] . ' ' . $attrs['template'];
		return $s;
	}
	add_shortcode('swinfo', 'swi_html_output');
	
	function swi_log_view($time, $page, $software) {
		global $wpdb;
		$wpdb->insert( 
			'swi_log', 
			array( 
				'swi_date' => $time, 
				'page_link' => $page,
				'software_id' => $software
			)
		);
	}
	
	function swi_get_post_taxonomy($id,$taxonomy) {
		$terms = get_the_terms( $id, $taxonomy );
		$terms_output = '';
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
	
	/*************************/
	// add settings page
	function swi_settings_page(){
		?>
	    <div class="wrap">
	    <h1><?php echo __("Software plugin settings","swi"); ?></h1>
	    <form method="post" action="options.php">
	        <?php
	            settings_fields("swi_settings_fields");
	            do_settings_sections("swi_plugin_options");      
	            submit_button(); 
	        ?>          
	    </form>
		</div>
	<?php
	}
	
	function swi_display_actual_card_color_element() {
		?>
			<input type="text" name="swi_actual_card_color" id="swi_actual_card_color" value="<?php echo get_option('swi_actual_card_color'); ?>" />
			<p><?php echo __("Background color of relevant software card in HEX format.","swi"); ?></p>
		<?php
	}
	
	function swi_display_actual_button_color_element() {
		?>
			<input type="text" name="swi_actual_button_color" id="swi_actual_card_color" value="<?php echo get_option('swi_actual_button_color'); ?>" />
			<p><?php echo __("Background color of relevant software 'Read more' button in HEX format.","swi"); ?></p>
		<?php
	}

	function swi_display_non_actual_card_color_element() {
		?>
			<input type="text" name="swi_non_actual_card_color" id="swi_non_actual_card_color" value="<?php echo get_option('swi_non_actual_card_color'); ?>" />
			<p><?php echo __("Background color of not relevant software card in HEX format.","swi"); ?></p>
		<?php
	}
	
	function swi_display_non_actual_button_color_element() {
		?>
			<input type="text" name="swi_non_actual_button_color" id="swi_non_actual_button_color" value="<?php echo get_option('swi_non_actual_button_color'); ?>" />
			<p><?php echo __("Background color of not relevant software 'Read more' button in HEX format.","swi"); ?></p>
		<?php
	}

	function swi_display_delete_logs() {
		?>
			<input type="checkbox" name="swi_delete_logs" value="1" <?php checked(1, get_option('swi_delete_logs'), true); ?> /> 
		<?php
	}

	function swi_display_settings_page_fields() {
		add_settings_section("swi_settings_fields", __("Main options","swi"), null, "swi_plugin_options");
		
		add_settings_field("swi_actual_card_color", __("Actual card color","swi"), "swi_display_actual_card_color_element", "swi_plugin_options", "swi_settings_fields");
		add_settings_field("swi_actual_button_color", __("Actual button color","swi"), "swi_display_actual_button_color_element", "swi_plugin_options", "swi_settings_fields");
		add_settings_field("swi_non_actual_card_color", __("Not relevant card color","swi"), "swi_display_non_actual_card_color_element", "swi_plugin_options", "swi_settings_fields");
		add_settings_field("swi_non_actual_button_color", __("Not relevant button color","swi"), "swi_display_non_actual_button_color_element", "swi_plugin_options", "swi_settings_fields");
		add_settings_field("swi_delete_logs", __("Delete logs after plugin deactivation?","swi"), "swi_display_delete_logs", "swi_plugin_options", "swi_settings_fields");

		register_setting("swi_settings_fields", "swi_actual_card_color");
		register_setting("swi_settings_fields", "swi_actual_button_color");
		register_setting("swi_settings_fields", "swi_non_actual_card_color");
		register_setting("swi_settings_fields", "swi_non_actual_button_color");
		register_setting("swi_settings_fields", "swi_delete_logs");
	}

	add_action("admin_init", "swi_display_settings_page_fields");
	
	function swi_add_settings_link() {
		add_menu_page(__("Software settings page","swi"), __("Software settings","swi"), "edit_posts", "swi_options_page", "swi_settings_page", '', 84);
	}
	add_action("admin_menu", "swi_add_settings_link");
?>