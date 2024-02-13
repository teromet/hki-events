<?php

namespace HkiEvents\Admin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Section class.
 *
 */
class Section {

    /**
     * @var string
     * 
     * Section id.
     */
    private $id;


    /**
     * @var string
     * 
     * Section title.
     */
    private $title;

    /**
     * @var string
     * 
     * Slug-name of the settings page this section belongs to.
     */
    private $page;

    /**
     * @var string
     * 
     * Section description.
     */
    private $description;

    /**
     * @var Field[]
     * 
     * Section field objects.
     */
    protected $fields = array();

    /**
     * Section constructor.
     *
     * @param string $id          Section id.
     * @param string $title       Section title.
     * @param array $fields       Section fields.
     * @param string $page        Slug-name of the settings page.
     * @param string $description Section description.
     */
    public function __construct( $id, $title, $fields = array(), $page, $description ) {

        $this->id          = $id;
        $this->title       = $title;
        $this->page        = $page;
        $this->description = $description;

        add_settings_section(
            $this->id,
            $this->title,
            array( $this, 'print_description' ),
            $this->page
        );

        $this->add_fields( $fields );

    }

    /**
     * Print the section description.
     */
    public function print_description() {
        echo esc_html( $this->description );
    }

    /**
     * Add fields to section
     * 
     * @param array $fields
     */
    private function add_fields( $fields ) {

        // Add section fields
        foreach ( $fields as $field ) {

            $this->add_field(
                $field['id'],
                $this->id,
                $this->page,
                array(
                    'id' => $field['id'],
                    'label' => $field['label'],
                    'description' => $field['description'],
                    'elements' => $field['elements']
                )
            );

        }

    }

    /**
     * Create and add a new field object to this section.
     */
    public function add_field( $field_id, $section_id, $page, $options ) {

        $field = new Field( $field_id, $section_id, $page, $options );

        $this->fields[] = $field;
        
    }

}