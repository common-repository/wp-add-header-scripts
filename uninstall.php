<?php
/**
 * Uninstall procedure.
 *
 * @since Version 1.0.0
 * @author Punio Kid Works
 */

/* Exit if plugin delete hasn't be called */
if (!defined('WP_UNINSTALL_PLUGIN')) exit();

require_once(WAHS_DIR.'/classes/WAHS_Options.php');
$options = new WAHS_Options();
$options->restore();

?>