<?php

// Send taxonomy archive links to the same post type we're currently viewing
add_action( 'genesis_before', 'berkeley_filter_term_links' );

function berkeley_filter_term_links() {
	if ( !is_admin() && function_exists( 'berkeley_taxonomy_link_for_post_type' ) )
		add_filter( 'term_link', 'berkeley_taxonomy_link_for_post_type', 10, 3 );
}

// Filter the "no content matched your criteria" error
add_filter( 'genesis_noposts_text', 'berkeley_noposts_text', 10, 2 );
function berkeley_noposts_text( $text ) {
	if ( is_search() ) {
		$text = esc_html__( "I'm sorry. I couldn't find any pages with that phrase. Try again?", 'berkeley-coe-theme' );
	} elseif ( is_archive() ) {
		$text = esc_html__( "There are no entries in this section.", 'berkeley-coe-theme' );
	}
	$text .= get_search_form( false );
	return $text;
}


// Filter Skip link text
add_filter( 'genesis_skip_links_output', 'berkeley_skip_links_output' );
function berkeley_skip_links_output( $links ) {
	$links['genesis-content'] = esc_html__( 'Skip to main content', 'beng' );
	return $links;
}

// Filter breadcrumbs
add_filter( 'genesis_build_crumbs', 'berkeley_breadcrumbs', 10, 2 );
function berkeley_breadcrumbs( $crumbs, $args ) {
	// remove existing final crumb, which includes parent and current page (WHY)
	$lastcrumb = array_pop( $crumbs );
	$pos = strrpos( $lastcrumb, '>' );
	$current = substr( $lastcrumb, $pos + 1 );
	$crumbs[] = str_replace( $current, '', $lastcrumb ) . '<span class="breadcrumb-current">'.$current.'</span>';
	return $crumbs;
}

/*	Content is filtered here instead of in single- and archive- templates
	so the filters will be applied throughout the site--e.g., search results.
/**/

// Main content filters
// Prepend / Append custom field output to post body ($content)

function berkeley_display_custom_field_content( $content ) {
	
	$before_content = $after_content = '';
	$post_type = get_post_type();

	
	if ( 'facility' == $post_type && is_singular() ) :
			
		$contact = '';
		
		
		$link = get_field( 'link' );
		if ( !empty( $link ) ) :
			$label = apply_filters( 'berkeley_facility_website_label', esc_html__( 'Website', 'berkeley-coe-theme' ) );
			$contact .= sprintf( '<p class="facility-link"><a href="%s">%s</a></p>', esc_url( $link ), esc_html( $label ) );
		endif;
		
		$phone = get_field( 'phone_number' );
		$email = get_field( 'email' );
		
		if ( !empty( $email ) ) :
			$contact .= sprintf( '<p class="facility-email"><a href="mailto:%1$s">%1$s</a></p>', antispambot( $email ) );
		endif;
		
		if ( !empty( $phone ) ) :
			$punctuation = array( '(', ')', '-', ':', '.', ' ' );
			$number = str_replace( $punctuation, '', $phone );
			$prefix = apply_filters( 'berkeley_phone_prefix', esc_html__( 'Phone:', 'berkeley-coe-theme' ) );
			$contact .= sprintf( '<p class="facility-phone">%s <a class="tel" href="tel:%d">%s</a></p>', $prefix, $number, $phone );
		endif;
		
		$content = sprintf( '<div class="one-half first"><div class="facility-details">%s</div> %s</div>', $contact, $content );
		
		$address = get_field( 'street_address' );
		if ( !empty( $address ) ) :
			$address = sprintf( '<address>%s</address>', $address );
		endif;
		
		$location = get_field( 'map' );
		if ( !empty( $location ) ):
			$map = sprintf( '<div class="acf-map">
				<div class="marker" data-lat="%s" data-lng="%s"></div>
			</div>', $location['lat'], $location['lng'] );
		endif;
		
		$after_content .= sprintf( '<div class="one-half">%s %s</div>', $address, $map );
		
	
	endif; // facility
	
	
	if ( 'publication' == $post_type ) :
		
		$before_content .= '<div class="pub-details">';
		$before_content .= sprintf( '<p class="pub-author">%s</p>', get_field( 'author' ) );
		
		if ( $link = get_field( 'link' ) )
			$before_content .= sprintf( '<p class="pub-link"><a href="%s">%s</a></p>', esc_url( $link ), esc_html( get_field( 'publication_name' ) ) );
		
		if ( $pub_date = get_field( 'publication_date') ) {
			$date_format = apply_filters( 'berkeley_publication_date_format', get_option( 'date_format' ) );
			$before_content .= sprintf( '<p class="pub-date">%s</p>', esc_html( date( $date_format, $pub_date ) ) );
		}
		
		if ( $citation = get_field( 'citation' ) )
			$before_content .= sprintf( '<div class="pub-citation">%s</div>', esc_html( $citation ) );
		
		$before_content .= '</div>';
	endif; // publication
	
	
	if ( 'course' == $post_type ) :
		
		if ( is_singular() ) {
		
			$before_content .= get_field( 'course_number' );
		
			// description is the main content field
		
			$after_content .= '<div class="course-info">';
			if ( !empty( get_field( 'instructors' ) ) ) {
				$prefix = apply_filters( 'berkeley_course_instructors_prefix', esc_html__( 'Instructor(s):', 'berkeley-coe-theme' ) );
				$after_content .= sprintf( '<p><strong>%s</strong> %s</p>', $prefix, get_field( 'instructors' ) );
			}
			if ( !empty( get_field( 'credits' ) ) ) {
				$prefix = apply_filters( 'berkeley_course_credits_prefix', esc_html__( 'Credits:', 'berkeley-coe-theme' ) );
				$after_content .= sprintf( '<p><strong>%s</strong> %s</p>', $prefix, get_field( 'credits' ) );
			}
			if ( !empty( get_field( 'prerequisites' ) ) ) {
				$prefix = apply_filters( 'berkeley_course_prereqs_prefix', esc_html__( 'Prerequisites:', 'berkeley-coe-theme' ) );
				$after_content .= sprintf( '<p><strong>%s</strong> %s</p>', $prefix, get_field( 'prerequisites' ) );
			}
			if ( !empty( get_field( 'times' ) ) ) {
				$prefix = apply_filters( 'berkeley_course_time_prefix', esc_html__( 'Time:', 'berkeley-coe-theme' ) );
				$after_content .= sprintf( '<p><strong>%s</strong> %s</p>', $prefix, get_field( 'times' ) );
			}
			if ( !empty( get_field( 'location' ) ) ) {
				$prefix = apply_filters( 'berkeley_course_location_prefix', esc_html__( 'Location:', 'berkeley-coe-theme' ) );
				$after_content .= sprintf( '<p><strong>%s</strong> %s</p>', $prefix, get_field( 'location' ) );
			}
			$after_content .= '</div>';
		
		}
	endif; // course
	
	
	if ( 'people' == $post_type ) :
		
		$before_content = $after_content = '';
		
		$before_content = '<div class="bio-details">';
		
		// featured image
		if ( is_singular() )
			$before_content .= get_the_post_thumbnail( get_the_ID(), 'medium' );
		
		$before_content .= sprintf( '<div class="job_title">%s</div> ', get_field( 'job_title' ) );
		
		if ( has_term( 'staff', 'people_type' ) )
			$before_content .= get_field( 'responsibilities' );

		$email = get_field( 'email' );
		if ( !empty( $email ) ) :
			$before_content .= sprintf( '<p class="bio-email"><a href="mailto:%1$s">%1$s</a></p>', antispambot( $email ) );
		endif;
		
		$phone = get_field( 'phone' );
		if ( !empty( $phone ) ) :
			$punctuation = array( '(', ')', '-', ':', '.', ' ' );
			$number = str_replace( $punctuation, '', $phone );
			$prefix = apply_filters( 'berkeley_phone_prefix', esc_html__( 'Phone:', 'berkeley-coe-theme' ) );
			$before_content .= sprintf( '<p class="bio-phone"><strong>%s </strong><a href="tel:%d">%s</a></p>', $prefix, $number, $phone );
		endif;

		
		
		
		$before_content .= berkeley_links_repeater();
		
		
		if ( is_singular() ) {

			$address = array_filter( array( 
				'line1' => get_field( 'address_line_1' ), 
				'line2' => get_field( 'address_line_2' ),
				'city'  => get_field( 'city' ),
				'state' => get_field( 'state' ),
				'zip' 	=> get_field( 'zip' ),
				'country' => get_field( 'country' )
			) );

			if ( isset( $address['state'] ) ) {
				$address['city'] .= ', ' . $address['state'];
				unset($address['state']);
			}

			if ( isset( $address['zip'] ) ) {
				$address['city'] .= ' ' . $address['zip'];
				unset($address['zip']);
			}

			$before_content .= sprintf( '<address>%s</address>', implode( '<br>', array_filter( $address ) ) );


			$hours = get_field( 'hours' );
			if ( !empty( $hours ) ) :
				if ( has_term( 'staff', 'people_type' ) )
					$label = apply_filters( 'berkeley_staff_hours_prefix', esc_html__( 'Hours:', 'berkeley-coe-theme' ) );
				else
					$label = apply_filters( 'berkeley_faculty_hours_prefix', esc_html__( 'Office Hours:', 'berkeley-coe-theme' ) );
				$before_content .= sprintf( '<p class="bio-hours"><strong>%s</strong> %s</p>', $label, $hours );
			endif;
			
			$before_content .= '</div>';

			// $content

			if ( has_term( 'student', 'people_type' ) ) {
				if ( get_field( 'major' ) ) {
					$prefix = apply_filters( 'berkeley_student_major_prefix', esc_html__( 'Major:', 'berkeley-coe-theme' ) );
					$before_content .= sprintf( '<p class="class-major"><strong>%s</strong> %s</p>', $prefix, get_field( 'major' ) );
				}
				if ( get_field( 'class_year' ) ) {
					$prefix = apply_filters( 'berkeley_class_year_prefix', esc_html__( 'Class:', 'berkeley-coe-theme' ) );
					$before_content .= sprintf( '<p class="class-year"><strong>%s</strong> %s</p>', $prefix, get_field( 'class_year' ) );
				}
					
				$before_content .= '<p></p>';
			}

			if ( has_term( '', 'subject_area' ) ) {
				$prefix = apply_filters( 'berkeley_subject_area_prefix', esc_html__( 'Research Interests: ', 'berkeley-coe-theme' ) );
				$after_content .= get_the_term_list( get_the_ID(), 'subject_area', '<h3>'.$prefix.'</h3><div class="subject_area">', ', ', '</div>' );
			}
			if ( has_term( 'faculty', 'people_type' ) )	
				$after_content .= get_field( 'research_description' );

			// WYSIWYG fields
			$sections = array(
				'education'					=> apply_filters( 'berkeley_people_education_prefix', esc_html__( 'Education', 'berkeley-coe-theme' ) ),
				'awards'					=> apply_filters( 'berkeley_people_awards_prefix', esc_html__( 'Awards', 'berkeley-coe-theme' ) ),
				'experience'				=> apply_filters( 'berkeley_people_experience_prefix', esc_html__( 'Experience', 'berkeley-coe-theme' ) ),
				'publications'				=> apply_filters( 'berkeley_people_publications_prefix', esc_html__( 'Publications', 'berkeley-coe-theme' ) ),
				'additional_information'	=> apply_filters( 'berkeley_people_additional_prefix', esc_html__( 'Additional Information', 'berkeley-coe-theme' ) ),
			);

			foreach ( $sections as $section => $section_title ) {
				$section_content = get_field( $section );
				if ( !empty( $section_content ) ) {
					$after_content .= sprintf( '<h3 id="%s">%s</h3> %s', $section, $section_title, $section_content );
				}

			}
		}

	endif; // people
	
	
	// Additional Content field (all post types)
	
	// check if the repeater field has rows of data
	if ( have_rows( 'collapsing_sections' ) ):
		$after_content .= '<div id="accordion">';
		// loop through the rows of data
	    while ( have_rows( 'collapsing_sections' ) ) : the_row();
	        // display a sub field value
			$heading = get_sub_field( 'section_heading' );
			$section_content = get_sub_field( 'collapsible_section' );
			$class = '';
			$expanded = 'false';
			if ( !empty( $heading ) && !empty( $section_content ) ) {
				$open = get_sub_field( 'open' );
				if ( $open ) {
					$class = 'activated';
					$expanded = 'true';
				}
				$after_content .= sprintf( '<h3 class="accordion-toggle %s" aria-expanded="%s">%s</h3>', $class, $expanded, $heading );
				$after_content .= sprintf( '<div class="accordion-content %s">%s</div>', $class, $section_content );
			}
	    endwhile;
		$after_content .= '</div> <!-- #accordion -->';
	endif;
	
	return $before_content . $content . $after_content;
}

add_filter( 'the_content', 'berkeley_display_custom_field_content' );


function berkeley_links_repeater() {
	$content = '';
	// links repeater
	// check if the repeater field has rows of data
	if ( have_rows( 'links' ) ):
		$links = array();
		$content = '<p class="bio-links">';
	 	// loop through the rows of data
	    while ( have_rows( 'links' ) ) : the_row();
			$url = get_sub_field( 'url' );
			$site_title = get_sub_field( 'link_text' );
			if ( !empty( $url ) && !empty( $site_title ) ) {
				$links[] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $site_title ) );
			}
	    endwhile;
		$content .= sprintf( '%s </p> <!-- #bio-links -->', implode( '<br>', array_filter( $links ) ) );
	endif;
	return $content;
}

function berkeley_display_custom_excerpts( $excerpt ) {
	$post_type = get_post_type();
	$post_id = get_the_ID();
	$pre = $excerpt = '';
	
	switch ( $post_type ) {
		case 'people':
			return $excerpt;
			break;
		case 'publication':
			$pre = sprintf( '<p class="pub-author">%s</p>', esc_html( get_field( 'author' ) ) );

			if ( $link = get_field( 'link' ) )
				$pre .= sprintf( '<p class="pub-link"><a href="%s">%s</a></p>', esc_url( $link ), esc_html( get_field( 'publication_name' ) ) );

			if ( $pub_date = get_field( 'publication_date' ) )
				$pre .= sprintf( '<p class="pub-date">%s</p>', esc_html( $pub_date ) );

			break;
		
		case 'facility':
			
			if ( $street = get_field( 'street_address' ) )
				$pre .= sprintf( '<address>%s</address>', esc_html( $street ) );
			
			if ( $link = get_field( 'link' ) ) {
				$label = apply_filters( 'berkeley_facility_website_label', esc_html__( 'Website', 'berkeley-coe-theme' ) );
				$pre .= sprintf( '<p class="facility-link"><a href="%s">%s</a></p>', esc_url( $link ), esc_html( $label ) );
			}
			break;
		
		case 'course':
			if ( $course = get_field( 'course_number' ) )
				$pre = sprintf( '<p class="course-number">%s</p>', esc_html( $course ) );
			break;
			
		default: break;
	}
	
	return $pre . $excerpt;
}

add_filter( 'the_excerpt', 'berkeley_display_custom_excerpts' );

// Post meta filters
// entry header: post info
// entry footer: post meta

function berkeley_post_info_filter( $post_info ) {
	$post_type = get_post_type();
	$post_id = get_the_ID();
	$post_info = '';
	
	switch ( $post_type ) {
		
		case 'attachment':
			$image_links = array();
			$image_meta = wp_get_attachment_metadata( $post_id );
			$sizes = $image_meta['sizes'];
			foreach ( $sizes as $size => $file ) {		
			    $image_url = wp_get_attachment_image_src( $post_id, $size );
			    if ( ! empty( $image_url[0] ) ) {
			        $image_links[] = sprintf( '<a href="%s" alt="%s">%s (%s&times;%s)</a>',
			            esc_url( $image_url[0] ),
			            esc_attr( the_title_attribute( 'echo=0' ) ),
			            esc_html( $size ),
						$file['width'],
						$file['height']
			        );
			    }
			}
			$post_info = sprintf( '<span class="image-sizes">%s</span>', implode( ' | ', $image_links ) );
			break;
		
		case 'post':
			$post_info = genesis_get_option( 'post_info' );
			break;
		
		case 'facility':
			break;
			
		case 'people':
			break;
			
		case 'publication':
			break;
			
		default: 
			break;
	}
	return $post_info;
}
add_filter( 'genesis_post_info', 'berkeley_post_info_filter' );

function berkeley_post_meta_filter( $post_meta ) {
	$post_type = get_post_type();
	$post_id = get_the_ID();
	$post_meta = '';
	
	switch ( $post_type ) {
		
		case 'post':
			$post_meta = genesis_get_option( 'post_meta' );
			break;
		
		case 'publication':
			break;
			
		default: 
			break;
	}
	return $post_meta;
}
add_filter( 'genesis_post_meta', 'berkeley_post_meta_filter' );