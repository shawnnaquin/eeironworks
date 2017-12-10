<?php

namespace HTML_Forms;

class Form {

    public $ID = 0;
    public $title = '';
    public $slug = '';
    public $markup = '';
    public $messages = array();
    public $settings = array();

    /**
     * Form constructor.
     *
     * @param $ID
     */
    public function __construct( $ID ) 
    {
        $this->ID = $ID;
    }

    public function get_html() 
    {
        $form = $this;

        /**
         * Filters the CSS classes to be added to this form's class attribute.
         *
         * @param array $form_classes
         * @param Form $form
         */
        $form_classes_attr = apply_filters( 'hf_form_element_class_attr', '', $form );

        /**
         * Filters the action attribute for this form.
         *
         * @param string $form_action
         * @param Form $form
         */
        $form_action = apply_filters( 'hf_form_element_action_attr', null, $form );
        $form_action_attr = is_null( $form_action ) ? '' : sprintf('action="%s"', $form_action );

        $html = '';
        $html .= sprintf( '<!-- HTML Forms v%s - %s -->', HTML_FORMS_VERSION, 'https://wordpress.org/plugins/html-forms/' );
        $html .= sprintf( '<form method="post" %s class="hf-form hf-form-%d %s" data-title="%s" data-slug="%s">', $form_action_attr, $this->ID, esc_attr( $form_classes_attr ), esc_attr( $this->title ), esc_attr( $this->slug ) );

        $html .= '<div class="hf-fields-wrap">';
        $html .= sprintf( '<input type="hidden" name="_hf_form_id" value="%d" />', $this->ID );
        $html .= sprintf( '<div style="display: none;"><input type="text" name="_hf_h%d" value="" /></div>', $this->ID );
        $html .= $this->markup;
        $html .= '<noscript>' . __( "Please enable JavaScript for this form to work.", 'html-forms' ) . '</noscript>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '<!-- / HTML Forms -->';

        /**
         * Filters the resulting HTML for this form.
         *
         * @param string $html
         * @param Form $form
         */
        $html = apply_filters( 'hf_form_html', $html, $form );
        return $html;
    }

    /**
     * @return string
     */
    public function __toString() 
    {
        return $this->get_html();
    }

    /**
     * @return array
     */
    public function get_required_fields() 
    {
        if( empty( $this->settings['required_fields'] ) ) {
            return array();
        }

        $required_fields = explode( ',', $this->settings['required_fields'] );
        return $required_fields;
    }

    /**
     * @return array
     */
    public function get_email_fields() 
    {
        if( empty( $this->settings['email_fields'] ) ) {
            return array();
        }

        $email_fields = explode( ',', $this->settings['email_fields'] );
        return $email_fields;
    }

    /**
    * @param string $code
    */
    public function get_message( $code ) 
    {
        $form = $this;
        $message = isset( $this->messages[ $code ] ) ? $this->messages[ $code ] : '';

        /**
        * @param string $message
        * @param Form $form
        */
        $message = apply_filters( 'hf_form_message_' . $code, $message, $form );
        return $message;
    }

}
