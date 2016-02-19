<?php
function cpt_testimonial() {
    $labels = array(
        'name'               => _x( 'Testimonials', 'post type general name' ),
        'singular_name'      => _x( 'Testimonial', 'post type singular name' ),
        'parent_item_colon'  => '',
        'menu_name'          => 'Testimonials'
    );
    
    $args = array(
        'labels'        => $labels,
        'description'   => 'Client testimonials.',
        'public'        => true,
        'menu_position' => 42,
        'menu_icon' => 'dashicons-star-filled',
        'supports'      => array( 'title', 'editor', 'custom-fields', 'page-attributes', 'thumbnail' ),
        'has_archive'   => false,
    );
    
    register_post_type( 'testimonial', $args );
}
add_action( 'init', 'cpt_testimonial' );

if( is_admin() ) {
    function cpt_testimonial_change_title( $title ){
        $screen = get_current_screen();
        if ( 'testimonial' == $screen->post_type ){
            $title = 'Client Name';
        }
        return $title;
    }
    add_filter( 'enter_title_here', 'cpt_testimonial_change_title' );
    
    $my_box = new CPT_meta_box( array(
        'id'        => 'testimonial',
        'title'     => 'Testimonial Details',
        'pages'     => array('testimonial'), // multiple post types
        'context'   => 'normal',
        'priority'  => 'high',
        'fields'    => array(
            array(
                'name' => 'Who Said It',
                'id' => CPT_PREFIX . 'person_name',
                'type' => 'text',
                'std' => ''
            ),
            array(
                'name' => 'Rating (out of 5)',
                'id' => CPT_PREFIX . 'testimonial_rating',
                'type' => 'select',
                'std' => '',
                'options' => array(
                    '' => '',
                    '1' => '1 Star',
                    '2' => '2 Star',
                    '3' => '3 Star',
                    '4' => '4 Star',
                    '5' => '5 Star',
                )
            ),
        )
    ) );
} // end is_admin() check


function get_single_testimonial($atts = null) {
    $atts = shortcode_atts(array(
        'post_id' => 0,
    ), $atts, 'testimonial');
    
    $testimonial = get_post($atts['post_id']);
    $meta = get_post_meta($atts['post_id']);
    
    return array(
        'title' => $testimonial->post_title,
        'content' => $testimonial->post_content,
        'rating' => $meta[CPT_PREFIX . 'testimonial_rating'],
    );
}

function get_single_testimonial_html($atts = null) {
    $atts = shortcode_atts(array(
        'post_id' => 0,
    ), $atts, 'testimonial_html');
    
    $testimonial = get_post($atts['post_id']);
    $meta = get_post_meta($atts['post_id']);
    
    $str = '<blockquote>
        <p><quo>' . $testimonial->post_content . '</quo></p>
        <p>';
    
    if(has_post_thumbnail($atts['post_id'])) {
        $thumb_id = get_post_thumbnail_id($atts['post_id']);
        $thumb_url_array = wp_get_attachment_image_src($thumb_id, 'thumbnail', true);
        $thumb_url = $thumb_url_array[0];
        $str .= '<img src="' . $thumb_url . '" alt="' . $testimonial->post_title . '" width="60" height="60" class="img-circle">';
    }
    
    $str .= '<cite>' . $testimonial->post_title . '</cite></p>
    </blockquote>';
    
    return $str;
}

add_shortcode('testimonial', 'get_single_testimonial');
add_shortcode('testimonial_html', 'get_single_testimonial_html');