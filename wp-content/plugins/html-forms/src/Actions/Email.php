<?php

namespace HTML_Forms\Actions;

use HTML_Forms\Form;
use HTML_Forms\Submission;

class Email extends Action {

   public $type = 'email';
   public $label = 'Send Email';

   public function __construct() {
       $this->label = __( 'Send Email', 'html-forms' );
   }

   public function page_settings( $settings, $index ) {
       $defaults = array_fill_keys( array( 'from', 'to', 'subject', 'message' ), '' );
       $settings = array_merge( $defaults, $settings );
       ?>
       <span class="hf-action-summary"><?php printf( 'From %s. To %s.', $settings['from'], $settings['to']  ); ?></span>
       <input type="hidden" name="form[settings][actions][<?php echo $index; ?>][type]" value="<?php echo $this->type; ?>" />
       <table class="form-table">
           <tr>
               <th><label><?php echo __( 'From', 'html-forms' ); ?> <span class="hf-required">*</span></label></th>
               <td>
                   <input name="form[settings][actions][<?php echo $index; ?>][from]" value="<?php echo esc_attr( $settings['from'] ); ?>" type="text" class="regular-text" placeholder="jane@email.com" required />
               </td>
           </tr>
           <tr>
               <th><label><?php echo __( 'To', 'html-forms' ); ?> <span class="hf-required">*</span></label></th>
               <td>
                   <input name="form[settings][actions][<?php echo $index; ?>][to]" value="<?php echo esc_attr( $settings['to'] ); ?>" type="text" class="regular-text" placeholder="john@email.com" required />
               </td>
           </tr>
           <tr>
               <th><label><?php echo __( 'Subject', 'html-forms' ); ?></label></th>
               <td>
                   <input name="form[settings][actions][<?php echo $index; ?>][subject]" value="<?php echo esc_attr( $settings['subject'] ); ?>" type="text" class="regular-text" placeholder="<?php echo esc_attr( __( 'Your email subject', 'html-forms' ) ); ?>" />
               </td>
           </tr>
           <tr>
               <th><label><?php echo __( 'Message', 'html-forms' ); ?> <span class="hf-required">*</span></label></th>
               <td>
                   <textarea name="form[settings][actions][<?php echo $index; ?>][message]" rows="8" class="widefat" placeholder="<?php echo esc_attr( __( 'Your email message', 'html-forms' ) ); ?>" required><?php echo esc_textarea( $settings['message'] ); ?></textarea>
                    <p class="help"><?php _e( 'You can use the following variables: ', 'html-forms' ); ?><span class="hf-field-names"></span></p>
               </td>
           </tr>
       </table>
        <?php
   }

    /**
     * Processes this action
     *
     * @param array $settings
     * @param Submission $submission
     * @param Form $form
     */
   public function process( array $settings, Submission $submission, Form $form ) {
       if( empty( $settings['to'] ) || empty( $settings['message'] ) ) {
           return;
       }

       $to = hf_template( $settings['to'], $submission->data );
       $subject = ! empty( $settings['subject'] ) ? hf_template( $settings['subject'], $submission->data ) : '';
       $message = nl2br( hf_template( $settings['message'], $submission->data ) );
       $headers = array( 'Content-Type: text/html' );

       if( ! empty( $settings['from'] ) ) {
           $from = hf_template($settings['from'], $submission->data);
           $headers[] = sprintf( 'From: %s', $from );
       }

       wp_mail( $to, $subject, $message, $headers );
   }
}
