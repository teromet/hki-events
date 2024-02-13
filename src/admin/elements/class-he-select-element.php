<?php

namespace HkiEvents\Admin\Elements;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * SelectElement class.
 *
 */
class SelectElement extends Element {
    
    /**
     * Render the element.
     */
    public function render() {
        ?>
        <label for="<?php echo esc_attr( $this->name ); ?>">
            <select name="<?php echo esc_attr( $this->name ); ?>" id="<?php echo esc_attr( $this->name ); ?>">
                <?php foreach ( $this->value as $key => $value ): ?>
                    <option value="<?php echo esc_attr( $value ); ?>"<?php if ( $value == $this->get_stored_value() ): echo 'selected="selected"'; endif; ?>><?php echo $key; ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php
    }
    
}