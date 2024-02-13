<?php

namespace HkiEvents\Admin\Elements;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Element base class.
 *
 */
abstract class Element {
    
    /**
     * @var string
     * 
     * Element label.
     */
    protected $label;
    
    /**
     * @var string
     * 
     * Element name.
     */
    protected $name;
    
    /**
     * @var mixed
     * 
     * Element value.
     */
    protected $value;

    /**
     * @var string
     * 
     * ID of the field that element belongs to
     */
    protected $field_id;

    /**
     * Element constructor.
     *
     * @param string $field_id Field ID.
     * @param array  $options    Options.
     */
    public function __construct( $field_id, $options = array() ) {

        $this->label = $options['label'];
        $this->name  = $options['name'];
        $this->value = $options['value'];
        $this->field_id = $field_id;

    }

    /**
     * Get the stored value for option.
     */
    protected function get_stored_value() {

        $stored_value = get_option( $this->field_id );

        return $stored_value;

    }

    /**
     * Render the element.
     */
    abstract public function render();

}