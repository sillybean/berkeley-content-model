<?php

add_action( 'init', 'berkeley_eng_cpt_slugs', 99 );

function berkeley_eng_cpt_slugs() {
	if ( !function_exists( 'genesis' ) )
		return;
	
    $post_types = get_post_types( array( 'public' => true ) ); 
	$flush = false;
	
	foreach ( $post_types as $post_type ) {
		
		$slug = sanitize_title( genesis_get_cpt_option( 'slug', $post_type ) );

		if ( empty( $slug ) )
			continue;
		
		if ( taxonomy_exists( $slug ) || post_type_exists( $slug ) ) {		
			add_action( 'admin_notices', 'berkeley_eng_cpt_url_error_notice' );
			return;
		}	

		$args = get_post_type_object( $post_type );
		$args->rewrite['slug'] = $slug;
		register_post_type( $post_type, $args );
		
		if ( !$flush )	
			$flush = true;
			
	}
	if ( $flush )
		flush_rewrite_rules();
}

add_action( 'genesis_cpt_archives_settings_metaboxes' , 'berkeley_eng_register_cpt_settings_box' );

function berkeley_eng_register_cpt_settings_box( $hook ) {
	add_action( 'admin_enqueue_scripts', 'berkeley_eng_cpt_archive_scripts' );
	add_meta_box( 'berkeley-url-settings', esc_html__( 'URL Settings' ), 'berkeley_eng_cpt_url_settings_box', $hook, 'main', 'low' );
	add_meta_box( 'berkeley-post-layout-settings', esc_html__( 'Post Layout Settings' ), 'berkeley_eng_post_layout_settings_box', $hook, 'main', 'low' );
	
}

function berkeley_eng_cpt_archive_scripts() {
	wp_register_script( 'berkeley-cpt-archive-toggle-js', plugins_url( '/js/cpt-archive-toggle.js', dirname(__FILE__) ), array('jquery','jquery-ui-droppable','jquery-ui-draggable', 'jquery-ui-sortable') );
	wp_localize_script( 'berkeley-cpt-archive-toggle-js', '_cpt_archives', GENESIS_CPT_ARCHIVE_SETTINGS_FIELD_PREFIX . $_REQUEST['post_type'] );
	wp_enqueue_script( 'berkeley-cpt-archive-toggle-js' );
	
	wp_enqueue_style( 'berkeley-cpt-archive-styles', plugins_url( '/css/coe-cpt-archives.css', dirname(__FILE__) ) );
}


add_filter( 'genesis_cpt_archive_settings_defaults', 'berkeley_eng_cpt_genesis_settings_defaults', 10, 2 );

function berkeley_eng_cpt_genesis_settings_defaults( $settings, $post_type ) {
	// Backward compatibility with Bill Erickson's Genesis Grid Loop plugin
	if ( function_exists( 'genesis_get_option' ) ) {
		$gg = array(
			'grid_on' => genesis_get_option( 'grid_on_' . $post_type, 'genesis-grid' ),
			'features_on_front' => (int) genesis_get_option( 'features_on_front', 'genesis-grid' ),
			'teasers_on_front' =>  (int) genesis_get_option( 'teasers_on_front', 'genesis-grid' ),
			'features_inside' =>   (int) genesis_get_option( 'features_inside', 'genesis-grid' ),
			'teasers_inside' =>    (int) genesis_get_option( 'teasers_inside', 'genesis-grid' ),
			'teaser_columns' =>    (int) genesis_get_option( 'teaser_columns', 'genesis-grid' ),
			'teaser_image_size' => genesis_get_option( 'teaser_image_size', 'genesis-grid' ),
		);
	}
	
	$settings['slug'] = '';
	$settings['post_layout'] = 'list'; 
	if ( class_exists( 'BE_Genesis_Grid' ) && $gg[ 'grid_on'] )
		$settings['post_layout'] = 'grid'; 
	$settings['subdivide'] = '';
	$settings['posts_per_archive_page'] = get_option( 'posts_per_page' );
	$settings['grid_columns'] = (int) $gg[ 'teaser_columns' ];
	$settings['grid_rows'] = (int) $gg[ 'teasers_on_front' ] / $settings['grid_columns'];
	$settings['grid_thumbnail_size'] = $gg['teaser_image_size'];
	$settings['table_headers'] = berkeley_eng_get_default_table_view_headers( $post_type );
	$settings['show_excerpt'] = 1;
	$settings['before_excerpt'] = '';
	$settings['after_excerpt'] = '';
	$settings['excerpt_words'] = 75;
	$settings['excerpt_readmore'] = __( '[Continue Reading]', 'beng' );

    return $settings;
}

function berkeley_eng_get_default_table_view_headers( $post_type ) {
	$headers = berkeley_eng_get_available_table_view_headers( $post_type );
	switch ( $post_type ) {
		case 'course':
			$default_headers = array(
				'course_title_num',
				'instructors',
				'times'
			);
			break;
		case 'people':
			$default_headers = array(
				'title',
				'position',
				'email'
			);
			break;
		case 'facility':
			$default_headers = array(
				'title',
				'street_address',
				'phone_number'
			);
			break;
		case 'publication':
			$default_headers = array(
				'title',
				'publication_name',
				'publication_date'
			);
			break;
		default: 
			$default_headers = array(
				'title',
				'date'
			);
			break;
	}
	return array_intersect_key( $headers, $default_headers );
}

function berkeley_eng_get_available_table_view_headers( $post_type ) {
	switch ( $post_type ) {
		case 'course':
			$headers = array(
				'course_title_num' => 'Course Number and Title',
				'course_number' => 'Course Number',
				'title' => 'Course Title',
				'instructors' => 'Instructor(s)',
				'times' => 'Times',
				'subject_area' => 'Subject Area',
				'date' => 'Entry Date',
				'modified_date' => 'Entry Last Modified',
				'post_author' => 'Entry Author',
				'post_excerpt' => 'Entry Excerpt',
			);
			break;
		case 'people':
			$headers = array(
				'title' => 'Name',
				'first_name' => 'First Name',
				'last_name' => 'Last Name',
				'job_title' => 'Position',
				'email' => 'Email',
				'phone' => 'Phone',
				'address' => 'Address',
				'city' => 'City',
				'state' => 'State',
				'zip' => 'ZIP',
				'links' => 'Links',
				'hours' => 'Office Hours',
				'major' => 'Major',
				'class_year' => 'Class Year',
				'people_type' => 'People Type',
				'subject_area' => 'Subject Area',
				'date' => 'Entry Date',
				'modified_date' => 'Entry Last Modified',
				'post_author' => 'Entry Author',
				'post_excerpt' => 'Entry Excerpt',
			);
			break;
		case 'facility':
			$headers = array(
				'title' => 'Name',
				'link' => 'Link',
				'phone_number' => 'Phone',
				'email' => 'Email',
				'street_address' => 'Address',
				'facility_type' => 'Facility Type',
				'subject_area' => 'Subject Area',
				'service' => 'Service',
				'post_tag' => 'Entry Tags',
				'date' => 'Entry Date',
				'modified_date' => 'Entry Last Modified',
				'post_author' => 'Entry Author',
				'post_excerpt' => 'Entry Excerpt',
			);
			break;
		case 'publication':
			$headers = array(
				'title' => 'Title',
				'publication_name' => 'Publication Name',
				'publication_date' => 'Publication Date',
				'publication_type' => 'Publication Type',
				'subject_area' => 'Subject Area',
				'post_tag' => 'Entry Tags',
				'date' => 'Entry Date',
				'modified_date' => 'Entry Last Modified',
				'post_author' => 'Entry Author',
				'post_excerpt' => 'Entry Excerpt',
			);
			break;
		default: 
			$headers = array(
				'title' => 'Entry Title',
				'subject_area' => 'Subject Area',
				'post_tag' => 'Entry Tags',
				'date' => 'Entry Date',
				'modified_date' => 'Entry Last Modified',
				'post_author' => 'Entry Author',
				'post_excerpt' => 'Entry Excerpt',
			);
			break;
	}
	return $headers;
}


add_action( 'genesis_settings_sanitizer_init', 'berkeley_eng_theme_options_sanitize_settings' );

function berkeley_eng_theme_options_sanitize_settings() {
	$post_types = get_post_types( array( 'public' => true ) ); 

	foreach ( $post_types as $post_type ) {
		
		if ( !function_exists( 'genesis_add_option_filter' ) )	
			return;
		
		if ( isset( $post_type ) && is_object( $post_type ) && post_type_exists( $post_type ) ) {
			$setting = '_genesis_admin_cpt_archives_' . $post_type;

		    genesis_add_option_filter(
		        'no_html',
		        $GLOBALS[$setting]->settings_field,
		        array(
		            'slug',
					'post_layout',
					'subdivide',
					'grid_thumbnail_size',
					'table_headers',
					'excerpt_words',
					'excerpt_readmore'
		        )
		    );
		
			genesis_add_option_filter(
		        'safe_html',
		        $GLOBALS[$setting]->settings_field,
		        array(
					'before_excerpt',
					'after_excerpt'
		        )
		    );
		
			genesis_add_option_filter(
		        'absint',
		        $GLOBALS[$setting]->settings_field,
		        array(
					'grid_columns',
					'grid_rows',
					'posts_per_archive_page',
					'show_excerpt'
		        )
		    );
		}
	}
}

function berkeley_eng_cpt_url_error_notice() {
	printf( '<div class="error notice"><p>%s</p></div>', esc_html__( 'The URL slug you have entered is already being used by another post type or taxonomy. This archive will be unreachable until you choose a different slug.' ) );
}


function berkeley_eng_cpt_url_settings_box() { 
	if ( !function_exists( 'genesis' ) )
		return;
		
	$name = GENESIS_CPT_ARCHIVE_SETTINGS_FIELD_PREFIX . $_REQUEST['post_type'] . '[slug]';
	$slug = sanitize_title( genesis_get_cpt_option( 'slug', $_REQUEST['post_type'] ), false );
	?>
	<table class="form-table">
	<tbody>

	<tr valign="top">
		<th scope="row">
			<label for="<?php echo esc_attr( $name ); ?>"><?php esc_html_e( 'Change archive URL slug to' );?></label>
		</th>
		<td>
		<p>
		<?php echo get_option( 'home' ) . '/'; ?>
		<input type="text" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $slug ); ?>" /> 
		</p>
		</td>
	</tr>

	</tbody>
	</table>
    <?php
}

function berkeley_eng_post_layout_settings_box() { 
	if ( !function_exists( 'genesis_get_cpt_option' ) )
		return;
	
	$type = $_REQUEST['post_type'];
	
	$name = GENESIS_CPT_ARCHIVE_SETTINGS_FIELD_PREFIX . $type;
		
	$settings = array(
		'subdivide' => genesis_get_cpt_option( 'subdivide', $type ),
		'post_layout' => genesis_get_cpt_option( 'post_layout', $type ),
		'posts_per_archive_page' => genesis_get_cpt_option( 'posts_per_archive_page', $type ),
		'grid_columns' => genesis_get_cpt_option( 'grid_columns', $type ),
		'grid_rows' => genesis_get_cpt_option( 'grid_rows', $type ),
		'grid_thumbnail_size' => genesis_get_cpt_option( 'grid_thumbnail_size', $type ),
		'table_headers' => genesis_get_cpt_option( 'table_headers', $type ),
		'show_excerpt' => genesis_get_cpt_option( 'show_excerpt', $type ),
		'before_excerpt' => genesis_get_cpt_option( 'before_excerpt', $type ),
		'after_excerpt' => genesis_get_cpt_option( 'after_excerpt', $type ),
		'excerpt_words' => genesis_get_cpt_option( 'excerpt_words', $type ),
		'excerpt_readmore' => genesis_get_cpt_option( 'excerpt_readmore', $type )
	);

	$settings = wp_parse_args( $settings, berkeley_eng_cpt_genesis_settings_defaults( array(), $type ) );

	$taxonomies = get_object_taxonomies( $type, 'objects' );
	
	$post_type_object = get_post_type_object( $type );
	?>
	<table class="form-table">
	<tbody>
		
		<tr valign="top" id="subdivide-row"  class="toggle-row grid list" <?php if ( $settings['post_layout'] == 'table' ) echo 'style="display: none;"' ?>>
			<th scope="row">
				<label for="<?php echo esc_attr( $name. '[subdivide]' ); ?>"><?php esc_html_e( 'Divide archives into sections by ', 'beng' );?></label>
			</th>
			<td>
			<p>
			<select id="subdivide" name="<?php echo esc_attr( $name. '[subdivide]' ); ?>" value="<?php echo esc_attr( $settings['subdivide'] ); ?>">
				<?php 
				printf( '<option value="" %s>%s</option>'."\n", selected( $settings['subdivide'], '', false), __( 'Do not divide this archive', 'beng' ) );
				foreach ( $taxonomies as $taxonomy ) :
					if ( $taxonomy->public )
						printf( '<option value="%s" %s>%s</option>'."\n", $taxonomy->name, selected( $settings['subdivide'], $taxonomy->name, false), $taxonomy->label );
				endforeach; 
				?>
			</select> 
			</p>
			</td>
		</tr>
		
	<tr valign="top">
		<th scope="row">
			<label for="<?php echo esc_attr( $name. '[post_layout]' ); ?>"><?php esc_html_e( 'Display posts as ', 'beng' );?></label>
		</th>
		<td>
		<p>
		
		<?php
		
		printf( '<input type="radio" name="%s" value="list" class="toggle" %s> %s', esc_attr( $name. '[post_layout]' ), checked( 'list', $settings['post_layout'], false ), __( 'List (default)', 'beng' ) );
		printf( '&nbsp;&nbsp;<input type="radio" name="%s" value="grid" class="toggle" %s> %s', esc_attr( $name. '[post_layout]' ), checked( 'grid', $settings['post_layout'], false ), __( 'Grid', 'beng' ) );
		printf( '&nbsp;&nbsp;<input type="radio" name="%s" value="table" class="toggle" %s> %s', esc_attr( $name. '[post_layout]' ), checked( 'table', $settings['post_layout'], false ), __( 'Table', 'beng' ) );
		?>	
		</p>
		</td>
	</tr>
	
	<tr valign="top" id="grid-columns" class="toggle-row grid" <?php if ( $settings['post_layout'] !== 'grid' ) echo 'style="display: none;"' ?>>
		<th scope="row">
			<label for="<?php echo esc_attr( $name. '[grid_columns]' ); ?>"><?php esc_html_e( 'Grid columns ', 'beng' );?></label>
		</th>
		<td>
		<p>
		
		<select name="<?php echo esc_attr( $name . '[grid_columns]' ); ?>">
			<option <?php selected( 2, $settings['grid_columns'] ); ?>>2</option>
			<option <?php selected( 3, $settings['grid_columns'] ); ?>>3</option>
			<option <?php selected( 4, $settings['grid_columns'] ); ?>>4</option>
			<option <?php selected( 5, $settings['grid_columns'] ); ?>>5</option>
			<option <?php selected( 6, $settings['grid_columns'] ); ?>>6</option>
		</select>
		</p>
		</td>
	</tr>
	
	<tr valign="top" id="grid-rows" class="toggle-row grid" <?php if ( $settings['post_layout'] !== 'grid' ) echo 'style="display: none;"' ?>>
		<th scope="row">
			<label class="toggle-label no-subdivide" for="<?php echo esc_attr( $name. '[grid_rows]' ); ?>" <?php if ( $settings['subdivide'] ) echo 'style="display: none;"' ?>><?php esc_html_e( 'Grid rows per page ', 'beng' );?></label>
			<label class="toggle-label subdivide" for="<?php echo esc_attr( $name. '[grid_rows]' ); ?>" <?php if ( !$settings['subdivide'] ) echo 'style="display: none;"' ?>><?php esc_html_e( 'Grid rows per section ', 'beng' );?></label>
		</th>
		<td>
		<p> <input name="<?php echo esc_attr( $name . '[grid_rows]' ); ?>" value="<?php echo esc_attr( $settings['grid_rows'] ) ?>"> </p>
		<p class="description"><?php _e( 'Enter -1 to show all posts.', 'beng' ); ?></p>
		</td>
	</tr>
	
	<tr valign="top" id="grid-thumbnail" class="toggle-row list" <?php if ( $settings['post_layout'] !== 'list' ) echo 'style="display: none;"' ?>>
		<th scope="row">
		</th>
		<td>
			<p> <?php _e( 'For Image settings under List view, see Customizer > Theme Settings > Content Archives', 'beng' ); ?></p>
		</td>
	</tr>
	
	
	
	<tr valign="top" id="grid-thumbnail" class="toggle-row grid" <?php if ( $settings['post_layout'] !== 'grid' ) echo 'style="display: none;"' ?>>
		<th scope="row">
			<label for="<?php echo esc_attr( $name. '[grid_thumbnail_size]' ); ?>"><?php esc_html_e( 'Grid image size ', 'beng' );?></label>
		</th>
		<td>
		<p> 
			<select name="<?php echo esc_attr( $name. '[grid_thumbnail_size]' ); ?>">
			<?php
			
			$sizes = apply_filters( 'image_size_names_choose', array(
				0 => __('No images'),
				'thumbnail' => __( 'Thumbnail' ),
				'small' 	=> __( 'Small' ),
				'medium'    => __( 'Medium' ),
				'large'     => __( 'Large' ),
				'full'      => __( 'Full Size' ),
			) );
		
			foreach ( $sizes as $size => $label ) {
				printf( '<option value="%s" %s>%s</option>', $size, selected( $size, $settings['grid_thumbnail_size'], false ), $label );
			}

			?>
			</select>
		</p>
		</td>
	</tr>
	
	<tr valign="top" id="posts_per_archive_page" class="toggle-row list table" <?php if ( $settings['post_layout'] == 'grid' || !empty( $settings['subdivide'] ) ) echo 'style="display: none;"' ?>>
		<th scope="row">
			<label for="<?php echo esc_attr( $name. '[posts_per_archive_page]' ); ?>"><?php esc_html_e( 'Posts per page ', 'beng' );?></label>
		</th>
		<td>
		<p> <input name="<?php echo esc_attr( $name . '[posts_per_archive_page]' ); ?>" value="<?php echo esc_attr( $settings['posts_per_archive_page'] ) ?>"> </p>
		<p class="description"><?php _e( 'Enter -1 to show all posts on one page.', 'beng' ); ?></p>
		</td>
	</tr>
	
	<tr valign="top" id="show_excerpt" class="toggle-row list grid" <?php if ( $settings['post_layout'] == 'table' ) echo 'style="display: none;"' ?>>
		<th scope="row">
			<?php esc_html_e( 'Excerpt settings', 'beng' );?>
		</th>
		<td>
		<p> 
			<label> <input id="show_excerpt_checkbox" type="checkbox" name="<?php echo esc_attr( $name . '[show_excerpt]' ); ?>" value="1" <?php checked( $settings['show_excerpt'], 1, true ); ?> > 
		<?php esc_html_e( 'Show excerpt', 'beng' );?></label></p>
		
		</td>
	</tr>
	
	<tr valign="top" id="excerpt_words" class="toggle-row list grid show_excerpt" <?php if ( !$settings['show_excerpt'] || $settings['post_layout'] == 'table' ) echo 'style="display: none;"' ?>>
		<th scope="row">
			<label for="<?php echo esc_attr( $name. '[excerpt_words]' ); ?>"><?php esc_html_e( 'Limit generated excerpts to number of words ', 'beng' );?></label>
		</th>
		<td>
		<p> <input name="<?php echo esc_attr( $name . '[excerpt_words]' ); ?>" value="<?php echo esc_attr( $settings['excerpt_words'] ) ?>"> </p>
		<p class="description"><?php _e( 'When the excerpt is not specified, one will be generated by truncating the post content. Enter 0 to prevent this; an excerpt will be displayed only if text has been entered into the Excerpt field.', 'beng' ); ?></p>
		</td>
	</tr>
	
	<tr valign="top" id="excerpt_readmore" class="toggle-row list grid show_excerpt" <?php if ( !$settings['show_excerpt'] || $settings['post_layout'] == 'table' ) echo 'style="display: none;"' ?>>
		<th scope="row">
			<label for="<?php echo esc_attr( $name. '[excerpt_readmore]' ); ?>"><?php esc_html_e( '"More" link text ', 'beng' );?></label>
		</th>
		<td>
			<p> <input name="<?php echo esc_attr( $name . '[excerpt_readmore]' ); ?>" value="<?php echo esc_attr( $settings['excerpt_readmore'] ) ?>"> </p>
		</td>
	</tr>
	
	<tr valign="top" id="excerpt_instructions" class="toggle-row list grid show_excerpt" <?php if ( !$settings['show_excerpt'] || $settings['post_layout'] == 'table' ) echo 'style="display: none;"' ?>>
		<th scope="row">
		</th>
		<td>
		<p class="description"><?php _e( sprintf( 'Use these fields to add text or custom fields before and after the excerpts. HTML and shortcodes are allowed. To include a custom field, enter [acf field="field_name"]. Refer to <a href="%s">the ACF field groups</a> to find individual field names.', 'edit.php?post_type=acf-field-group' ), 'beng' ); ?></p>
		</td>
	</tr>
	
	<tr valign="top" id="before_excerpt" class="toggle-row list grid show_excerpt" <?php if ( !$settings['show_excerpt'] || $settings['post_layout'] == 'table' ) echo 'style="display: none;"' ?>>
		<th scope="row">
			<label for="<?php echo esc_attr( $name. '[before_excerpt]' ); ?>"><?php esc_html_e( 'Before excerpt ', 'beng' );?></label>
		</th>
		<td>
		<p> <textarea class="excerpt_settings widefat" name="<?php echo esc_attr( $name . '[before_excerpt]' ); ?>"><?php echo esc_textarea( $settings['before_excerpt'] ) ?></textarea>
		</p>
		</td>
	</tr>
	
	<tr valign="top" id="after_excerpt" class="toggle-row list grid show_excerpt" <?php if ( !$settings['show_excerpt'] || $settings['post_layout'] == 'table' ) echo 'style="display: none;"' ?>>
		<th scope="row">
			<label for="<?php echo esc_attr( $name. '[after_excerpt]' ); ?>"><?php esc_html_e( 'After excerpt ', 'beng' );?></label>
		</th>
		<td>
			<p> <textarea class="excerpt_settings widefat" name="<?php echo esc_attr( $name . '[after_excerpt]' ); ?>"><?php echo esc_textarea( $settings['after_excerpt'] ) ?></textarea>
			</p>
		</td>
	</tr>
	
	<tr valign="top" id="table-headers" class="toggle-row table" <?php if ( $settings['post_layout'] !== 'table' ) echo 'style="display: none;"' ?>>
		<th scope="row">
			<label for="<?php echo esc_attr( $name. '[table_headers]' ); ?>"><?php esc_html_e( 'Table columns ', 'beng' );?></label>
		</th>
		<td> 
			<div class="related_pages">
			<div class="left_container">
			<?php
			printf( __('<h2>Available Columns</h2>') );
			_e( '<p class="description">Drag a column name to the right-hand container to add it to your archive table.</p>', 'beng' );
			
			$columns = berkeley_eng_get_available_table_view_headers( $type );
	
			foreach( $columns as $column => $label ) {
				printf( '<div class="page_item" data-page-id="%s">', esc_attr( $column ) );
				printf(	'<div class="page_title"><h3>%s</h3></div>', esc_html( $label ) );
				printf( '<div class="remove_item"><span class="screen-reader-text">%s</span>%s</div>', __( 'Remove' ), __( '&times;' ) );
				echo '</div>';
			}

			?>
			<div class="clearfix"></div>
			</div><!-- left_container -->
			<div class="right_container">
			<?php
			printf( __( '<h2>Your Columns</h2>' ) );
			printf( __( '<p class="description">Columns listed here will appear on your <a href="%s">archive page</a>.</p>', 'beng' ), get_post_type_archive_link( $type ) );

			if ( !empty( $settings['table_headers'] ) ) {
				
				foreach( $settings['table_headers'] as $key ) {
					printf( '<div class="page_item" data-page-id="%s">', esc_attr( $key ) );
					printf(	'<div class="page_title"><h3>%s</h3></div>', esc_html( $columns[$key] ) );
					printf( '<div class="remove_item" title="%1$s"><span class="screen-reader-text">%1$s</span>%2$s</div>', __( 'Remove' ), __( '&times;' ) );
					printf(	'<input type="hidden" name="'. $name. '[table_headers][]' .'" value="%s"/>', esc_attr( $key ) );
					echo '</div>';
				}
			}
			?>
			<div class="droppable-helper"></div>
			</div><!-- right_container -->
			</div>
		</td>
	</tr>

	</tbody>
	</table>
    <?php
}
