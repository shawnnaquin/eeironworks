<?php
 /*
 * Plugin Name:       External URL Featured Image
 * Plugin URI:        https://decodecms.com
 * Description:       This plugin allows to use External URL Images for your posts types, it's compatible with Nelio External Featured Image metadata.
 * Version:           1.0.2
 * Author:            Jhon Marreros Guzmán
 * Author URI:        https://decodecms.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       external-url-featured-image
 * Domain Path:       /languages
 */

//if this file is called directly, abort
if ( ! defined( 'ABSPATH' ) ) exit;

// constants definition
define('DCMS_EUFI_PATH_INCLUDE', plugin_dir_path( __FILE__ ).'includes/');
define('DCMS_EUFI_PATH_LANGUAGE', 'external-url-featured-image/languages');
define('DCMS_EUFI_DOMAIN','external-url-featured-image');

require_once DCMS_EUFI_PATH_INCLUDE.'class-dcms-external-url-featured-image.php';

new Dcms_External_Url_Featured_Image();
