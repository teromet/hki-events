<?php

namespace HkiEvents\Admin\Elements;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * CheckboxElement class.
 *
 */
class CheckboxElement extends Element {
    
    /**
     * Render the element.
     */
    public function render() {

        $options = $this->get_stored_value() ? $this->get_stored_value() : array();
        $checked = array_key_exists( $this->name, $options );

        if ( ! $checked ) {
            $checked = 0;
        }

        ?>

        <label style="margin-right: 0.75rem !important; user-select: none;">
            <input
                type="checkbox"
                name="<?php echo $this->field_id.'['.$this->name.']'; ?>"
                id="<?php echo esc_attr( $this->name ); ?>"
                <?php echo 'value="1"' . checked( $checked, 1, false ); ?>
            />
            <?php echo esc_html( $this->label ); ?>
        </label>

        <?php
    }
    
}