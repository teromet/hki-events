<?php

namespace HkiEvents;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * CPT class.
 * 
 * Class for registering new custom post types
 *
 */
class CPT {

    /**
     * @var string
     *
     * Set post type params
     */
    private $type;               
    private $slug;               
    private $name;             
    private $singular_name;  
    private $is_public;
    private $show_in_menu;
    private $menu_icon;
    private $taxonomies;

    /**
     * Constructor
     *
     * @param array $attr - item from array $post_type_conf
     * 
     */
    function __construct( $attr ) {

        // Register the post type
        $this->type = $attr['type'];            
        $this->slug = $attr['slug'];         
        $this->name = $attr['name'];           
        $this->singular_name = $attr['singular_name'];
        $this->is_public = $attr['is_public'];
        $this->show_in_menu = $attr['show_in_menu'];
        $this->menu_icon = $attr['menu_icon'];
        $this->taxonomies = $attr['taxonomies'];

    }

    /**
     * Register post type
     */
    public function register() {

        $labels = array(
            'name'                  => $this->name,
            'singular_name'         => $this->singular_name,
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New '   . $this->singular_name,
            'edit_item'             => 'Edit '      . $this->singular_name,
            'new_item'              => 'New '       . $this->singular_name,
            'all_items'             => 'All '       . $this->name,
            'view_item'             => 'View '      . $this->name,
            'search_items'          => 'Search '    . $this->name,
            'not_found'             => 'No '        . strtolower( $this->name ) . ' found',
            'not_found_in_trash'    => 'No '        . strtolower( $this->name ) . ' found in Trash',
            'parent_item_colon'     => '',
            'menu_name'             => $this->name
        );

        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'publicly_queryable'    => true,
            'exclude_from_search'   => true,
            'show_ui'               => true,
            'show_in_menu'          => $this->show_in_menu,
            'show_in_rest'          => true,
            'menu_icon'             => $this->show_in_menu ? $this->menu_icon : null,
            'taxonomies'            => $this->taxonomies,
            'query_var'             => true,
            'rewrite'               => array( 'slug' => $this->slug ),
            'capability_type'       => 'post',
            'has_archive'           => false,
            'hierarchical'          => false,
            'menu_position'         => 8,
            'supports'              => array( 'title', 'thumbnail', 'editor' ),
            'yarpp_support'         => true
        );

        register_post_type( $this->type, $args );

    }

}