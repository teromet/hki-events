<?php

namespace HkiEvents\Admin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HkiEvents\Utils;

use HkiEvents\Admin\Elements\Element;
use HkiEvents\Admin\Elements\CheckboxElement;
use HkiEvents\Admin\Elements\NumberElement;
use HkiEvents\Admin\Elements\SelectElement;

use HkiEvents\Exceptions\ElementTypeException;

/**
 * Field class.
 *
 */
class Field {

    /**
     * @var string
     * 
     * ID of the field
     */
    private $field_id;

    /**
     * @var string
     * 
     * ID of the section this field belongs to.
     */
    private $section_id;

    /**
     * @var string
     * 
     * Field description.
     */
    private $description;

    /**
     * @var Element[]
     * 
     * Field elements.
     */     
    private $elements = array();

    /**
     * @var string
     * 
     * Slug-name of the settings page this field belongs to.
     */
    private $page;

    /**
     * Field constructor.
     *
     * @param string $field_id    ID of the field
     * @param string $section_id  ID of the section this field belongs to.
     * @param string $page        Slug-name of the settings page.
     * @param array  $options     Options.
     */
    public function __construct( $field_id, $section_id, $page, $options = array() ) {

        $this->field_id = $field_id;
        $this->section_id  = $section_id;
        $this->page = $page;
        $this->description = $options['description'];

        add_settings_field(
            $options['id'],
            $options['label'],
            array( $this, 'render' ),
            $page,
            $section_id
        );

        $this->add_elements( $options['elements'] );
        $this->register_options();

    }

    /**
     * Register field to database
     * 
     * TODO: Create an adaptive solution for checkboxes
     * 
     * @param array $field
     */
    private function register_options() {
        
        if( $this->field_id === 'hki_events_categories' || $this->field_id === 'hki_events_demographic_groups' ) {
            register_setting( $this->page, $this->field_id, array( 'type' => 'array' ) );
        }
        else {
            register_setting( $this->page, $this->field_id );
        }

    }


    /**
     * Create a new element object.
     *
     * @throws ElementTypeException If there are no classes for the given element type.
     * @throws ElementTypeException If the given element type is not an `Element`.
     *
     * @param string $element_type
     * @param array  $options
     *
     * @return Element
     */
    private function create_element( $element_type, $options ) {

        $element_type = __NAMESPACE__ . '\\Elements\\' . $element_type;
        
        if ( ! class_exists( $element_type ) ) {
            throw new ElementTypeException( 'No class exists for the specified type' );
        }

        $element = new $element_type( $this->field_id, $options );
        
        if ( ! ( $element instanceof Element ) ) {
            throw new ElementTypeException( 'The specified type is invalid' );
        }
        
        return $element;

    }

    /**
     * Add elements to field.
     * 
     * @param array $elements
     */
    private function add_elements( $elements ) {

        if ( ! empty ( $elements ) ) {

            foreach ( $elements as $key => $element ) {

                if ( ! empty ( $element['type'] ) ) {

                    $this->add_element(                
                        $element['type'],         
                        array(
                            'label' => $element['label'],
                            'name' => $element['name'] ? $element['name'] : $this->field_id.'_'.$key,
                            'value' => $element['value']
                            ) 
                    );

                }
                
            }
            
        }

    }
        
    /**
     * Add a new element object to this field.
     *
     * @param string $element_type
     * @param array  $options
     */
    private function add_element( $element_type, $options ) {

        try {
            $element = $this->create_element( $element_type, $options );
            $this->elements[] = $element;
        } catch ( ElementTypeException $e ) {
            Utils::log( 'error', 'Caught exception: '.$e->getMessage() );
        }

    }

    /**
     * Render the field.
     */
    public function render() {

        if ( ! empty( $this->description ) ) {
            printf(
                '<p class="description">%s</p>',
                esc_html( $this->description )
            );
        }
        echo '<fieldset>';
        foreach ( $this->elements as $key => $element ) {
            $element->render();
        }
        echo '</fieldset>';

    }

}