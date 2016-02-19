<?php
function cpt_staff() {
    $labels = array(
        'name'               => _x( 'Staff Members', 'post type general name' ),
        'singular_name'      => _x( 'Staff Member', 'post type singular name' ),
        'parent_item_colon'  => '',
        'menu_name'          => 'Staff Members'
    );
    
    $args = array(
        'labels'        => $labels,
        'description'   => 'Landing page modules.',
        'public'        => true,
        'menu_position' => 40,
        'menu_icon' => 'dashicons-nametag',
        'supports'      => array( 'title', 'editor', 'excerpt', 'custom-fields', 'page-attributes', 'thumbnail' ),
        'has_archive'   => false,
    );
    
    register_post_type( 'staff_member', $args );
}
add_action( 'init', 'cpt_staff' );

if( is_admin() ) {
    $my_box = new CPT_meta_box( array(
        'id'        => 'homepage_modules',
        'title'     => 'Staff Member Details',
        'pages'     => array('staff_member'), // multiple post types
        'context'   => 'normal',
        'priority'  => 'high',
        'fields'    => array(
            array(
                'name' => 'Position / Role',
                'id' => PREFIX . 'staff_position',
                'type' => 'text',
                'std' => ''
            ),
        )
    ) );
} // end is_admin() check


function cpt_list_staff_members($args = null) {
    $args = array(
        'post_type' => 'staff_member',
        'orderby ' => 'menu_order',
        'order' => 'ASC'
    );
    
    $staff_query = new WP_Query($args);
    
    if ( !$staff_query->have_posts() )
        return;
    
    $str = '<div class="row">';
    
    while( $staff_query->have_posts() ) {
        $staff_query->the_post();
        $role = get_post_meta(get_the_ID(), PREFIX . 'staff_position', true);
        
        $str .= '<div class="col-md-6">';
        $str .= '<figure>' . get_the_post_thumbnail(get_the_ID(), null, array('class' => 'img-responsive')) . '</figure>';
        $str .= '<h4>' . get_the_title() . '</h4>';
        
        if( !empty($role) )
            $str .= '<p>' . $role . '</p>';
        
        $str .= wpautop(get_the_content());
        $str .= '</div>';
    }
    
    $str .= '</div>';
    
    wp_reset_postdata();
    
    return $str;
}
add_shortcode('list_staff', 'cpt_list_staff_members');