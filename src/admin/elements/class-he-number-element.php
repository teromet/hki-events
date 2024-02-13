<?php

namespace HkiEvents\Admin\Elements;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * NumberElement class.
 *
 */
class NumberElement extends Element {
    
    /**
     * Render the element.
     */
    public function render() {
        ?>
        <label>
            <input
                type="number"
                name="<?php echo esc_attr( $this->name ); ?>"
                id="<?php echo esc_attr( $this->name ); ?>"
                value="<?php echo $this->get_stored_value() ?>"
            />
        </label>
        <?php
    }
    
}