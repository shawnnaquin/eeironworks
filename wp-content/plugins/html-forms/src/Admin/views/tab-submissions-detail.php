<?php

defined( 'ABSPATH' ) or exit;
$datetime_format = sprintf('%s %s', get_option( 'date_format' ), get_option( 'time_format' ) );
?>

<h2><?php _e( 'Viewing Form Submission', 'html-forms' ); ?></h2>

<div>
    <style type="text/css">
        table.hf-bordered {
        font-size: 13px;
        border-collapse: collapse;
        border-spacing: 0;
        background: white;
        width: 100%;
        table-layout: fixed;
        }

        table.hf-bordered th,
        table.hf-bordered td {
            border: 1px solid #ddd;
            padding: 12px;
        }

        table.hf-bordered th {
            width: 160px;
            font-size: 14px;
            text-align: left;
        }
    </style>

    <div class="hf-small-margin">
        <table class="hf-bordered">
            <tbody>
            <tr>
                <th><?php _e( 'Timestamp', 'html-forms' ); ?></th>
                <td><?php echo date( $datetime_format, strtotime( $submission->submitted_at ) ); ?></td>
            </tr>
            <tr>
                <th><?php _e( 'User Agent', 'html-forms' ); ?></th>
                <td><?php echo esc_html( $submission->user_agent ); ?></td>
            </tr>
            <tr>
                <th><?php _e( 'IP Address', 'html-forms' ); ?></th>
                <td><?php echo esc_html( $submission->ip_address ); ?></td>
            </tr>
            <tr>
                <th><?php _e( 'Referrer URL', 'html-forms' ); ?></th>
                <td><?php echo sprintf( '<a href="%s">%s</a>', esc_attr( $submission->referer_url ), esc_html( $submission->referer_url ) ); ?></td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="hf-small-margin">
        <h3><?php _e( 'Fields', 'html-forms' ); ?></h3>
        <table class="hf-bordered">
            <tbody>
            <?php 
            if( is_array( $submission->data ) ) {
                foreach( $submission->data as $field => $value ) {
                    if( is_array( $value ) ) {
                        $value = join( ', ', $value );
                    }
                    $value = esc_html( $value );
                    echo '<tr>';
                    echo sprintf( '<th>%s</th>', esc_html( ucfirst( strtolower( $field ) ) ) );
                    echo sprintf( '<td>%s</td>', nl2br( $value ) );
                    echo '</tr>';
                }
            } ?>
            </tbody>
        </table>
        </div>

</div>

<div class="hf-small-margin">
    <h3><?php _e( 'Raw', 'html-forms' ); ?></h3>
    <pre class="hf-well"><?php print_r( $submission ); ?></pre>
</div>

<div class="hf-small-margin">
    <p><a href="<?php echo esc_attr( remove_query_arg( 'submission_id' ) ); ?>">&lsaquo; <?php _e( 'Back to submissions list', 'html-forms' ); ?></a></p>
</div>
