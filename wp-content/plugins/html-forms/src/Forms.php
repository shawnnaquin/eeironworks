<?php

namespace HTML_Forms;

class Forms
{

    /**
     * @var string
     */
    private $plugin_file;

    /**
     * @var array
     */
    private $settings;

    /**
     * Forms constructor.
     *
     * @param string $plugin_file
     * @param array $settings
     */
    public function __construct($plugin_file, array $settings )
    {
        $this->plugin_file = $plugin_file;
        $this->settings = $settings;
    }

    public function hook()
    {
        add_action('init', array($this, 'register'));
        add_action('init', array($this, 'listen'));
        add_action('wp_enqueue_scripts', array($this, 'assets'));
    }

    public function register()
    {
        // register post type
        register_post_type('html-form', array(
                'labels' => array(
                    'name' => 'HTML Forms',
                    'singular_name' => 'HTML Form',
                ),
                'public' => false
            )
        );

        add_shortcode('hf_form', array($this, 'shortcode'));

        // enable shortcodes in text widgets
        add_filter( 'widget_text', 'shortcode_unautop' );
        add_filter( 'widget_text', 'do_shortcode', 11 );
    }

    public function assets()
    {
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
        $assets_url = plugins_url( 'assets/', $this->plugin_file );

        wp_enqueue_script('html-forms', $assets_url . "js/public{$suffix}.js", array(), HTML_FORMS_VERSION, true);
        wp_localize_script('html-forms', 'hf_js_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
        ));

        if( $this->settings['load_stylesheet'] ) {
            wp_enqueue_style( 'html-forms', $assets_url . "css/forms{$suffix}.css", array(), HTML_FORMS_VERSION );
        }
    }

    /**
     * @param Form $form
     * @param array $data
     * @return string
     */
    private function validate_form(Form $form, array $data)
    {
        // validate honeypot field
        $honeypot_key = sprintf( '_hf_h%d', $form->ID );
        if( ! isset( $data[$honeypot_key] ) || $data[$honeypot_key] !== "" ) {
            return 'spam';
        }

        // validate size of POST array
        if( count( $data ) > substr_count( strtolower( $form->get_html() ), 'name=' ) && apply_filters( 'hf_validate_form_request_size', true ) ) {
            return 'spam';
        }

        $required_fields = $form->get_required_fields();
        foreach ($required_fields as $field_name) {
            $value = hf_array_get( $data, $field_name );
            if ( empty( $value ) ) {
                return 'required_field_missing';
            }
        }

        $email_fields = $form->get_email_fields();
        foreach ($email_fields as $field_name) {
            $value = hf_array_get( $data, $field_name );
            if ( ! empty( $value ) && ! is_email( $value ) ) {
                return 'invalid_email';
            }
        }

        /**
         * This filter allows you to perform your own form validation.
         *
         * Return a non-empty string if you want to raise an error.
         * Error codes with a specific error message are: "required_field_missing", "invalid_email", and "error"
         *
         * @param string $error_code
         * @param Form $form
         * @param array $data
         */
        $error = apply_filters( 'hf_validate_form', '', $form, $data );
        if( ! empty( $error ) ) {
            return $error;
        }

        // all good: no errors!
        return '';
    }

    /**
    * Sanitize array with values before saving. Can be called recursively.
    *
    * @param mixed $value
    */
    public function sanitize( $value )
    {
        if (is_string($value)) {
            // strip all HTML tags & whitespace
            $value = trim(strip_tags($value));

            // convert &amp; back to &
            $value = html_entity_decode($value, ENT_NOQUOTES);
        } elseif ( is_array($value) || is_object($value) ) {
            $new_value = array();
            $vars = is_array( $value ) ? $value : get_object_vars( $value );

            foreach($vars as $key => $sub_value) {
                // skip empty values
                if(empty($sub_value)) {
                   continue;
                }

                // sanitize key
                $key = trim(strip_tags($key));

                // sanitize sub value
                $new_value[$key] = $this->sanitize($sub_value);
            }
            $value = is_object( $value ) ? (object) $new_value : $new_value;
        } 

        return $value;
    }

    public function listen() 
    {
        // only respond to AJAX requests with _hf_form_id set.
        if (empty($_POST['_hf_form_id'])
            || empty( $_SERVER['HTTP_X_REQUESTED_WITH'] )
            || strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) !== strtolower( 'XMLHttpRequest' ) ) {
            return;
        }

        $data = $_POST;
        $form_id = (int) $data['_hf_form_id'];
        $form = hf_get_form($form_id);
        $error_code = $this->validate_form($form, $data);

        if (empty( $error_code ) ) {
            // filter out all field names starting with _
            foreach( $data as $key => $value ) {
                if( strpos( $key, '_' ) === 0 ) {
                    unset( $data[$key] );
                }
            }

            // strip slashes
            $data = stripslashes_deep( $data );

            // sanitize data: strip tags etc.
            $data = $this->sanitize( $data );

            // save form submission
            $submission = new Submission();
            $submission->form_id = $form_id;
            $submission->data = $data;
            $submission->ip_address = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
            $submission->user_agent = sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] );
            $submission->referer_url = sanitize_text_field( $_SERVER['HTTP_REFERER'] );
            $submission->save();

            // process form actions
            foreach( $form->settings['actions'] as $action_settings ) {
                /**
                 * Processes the specified form action and passes related data.
                 *
                 * @param array $action_settings
                 * @param Submission $submission
                 * @param Form $form
                 */
                do_action('hf_process_form_action_' . $action_settings['type'], $action_settings, $submission, $form );
            }

            /**
             * General purpose hook after all form actions have been processed.
             *
             * @param Submission $submission
             * @param Form $form
             */
            do_action( 'hf_form_success', $submission, $form );
        } else {

            /**
             * General purpose hook for when a form error occurred
             *
             * @param string $error_code
             * @param Form $form
             * @param array $data
             */
            do_action( 'hf_form_error', $error_code, $form, $data );
        }

        $response = $this->get_response_for_error_code( $error_code, $form );

        // clear output, some plugin or hooked code might have thrown errors by now.
        if( ob_get_level() > 0 ) {
            ob_end_clean();
        }

        send_origin_headers();
        send_nosniff_header();
        nocache_headers();

        wp_send_json($response, 200);
        exit;
    }

    private function get_response_for_error_code( $error_code, Form $form ) 
    {
        // return success response for empty error code string or spam (to trick bots)
        if( $error_code === "" || $error_code === "spam" ) {
            $response = array(
                'message' => array(
                    'type' => 'success',
                    'text' => $form->get_message( 'success' ),
                ),
                'hide_form' => (bool)$form->settings['hide_after_success'],
            );

            if (!empty($form->settings['redirect_url'])) {
                $response['redirect_url'] = $form->settings['redirect_url'];
            }

            return $response;
        }

        // get error message
        $message = $form->get_message( $error_code );
        if( empty( $message ) ) {
            $message = $form->get_message( 'error' );
        }

        // return error response
        return  $response = array(
            'message' => array(
                'type' => 'warning',
                'text' => $message,
            ),
            'error' => $error_code,
        );
    }

    public function shortcode($attributes = array(), $content = '')
    {
        $slug_or_id = empty( $attributes['id'] ) ? $attributes['slug'] : $attributes['id'];

        try {
            $form = hf_get_form( $slug_or_id );
        } catch( \Exception $e ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                return $content;
            }

            return sprintf( '<p><strong>%s</strong> %s</p>', __( 'Error:', 'html-forms' ), sprintf( __( 'No form found with slug %s', 'html-forms' ), $attributes['slug'] ) );
        }

        return $form . $content;
    }
}
