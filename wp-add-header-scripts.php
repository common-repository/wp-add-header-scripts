<?php
/*----------------------------------------------------------------------------------------------------------------------
Plugin Name: WP Add Header Scripts
Plugin URI: http://www.puniokid.com/
Description: WP Add Header Scripts will insert a script that was set between HEAD tag and HEAD tag.
Author: Punio Kid Works
Author URI: http://www.puniokid.com/
Version: 1.0.0
----------------------------------------------------------------------------------------------------------------------*/
define('WAHS_DIR', dirname(__FILE__));
define('WAHS_URL', plugins_url('',__FILE__));

/*-----------------------------------------------------------
 * プラグインの有効化／無効化
 -----------------------------------------------------------*/
register_activation_hook(__FILE__, 'WAHS_execActivated');
function WAHS_execActivated(){

}

register_deactivation_hook(__FILE__, 'WAHS_execDeactivated');
function WAHS_execDeactivated(){

}


/*-----------------------------------------------------------
 * 言語ファイルの読込
-----------------------------------------------------------*/
add_action( 'plugins_loaded', 'WAHS_load_textdomain');
function WAHS_load_textdomain(){
	load_plugin_textdomain('wahs', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang');
}


/*-----------------------------------------------------------
 * 管理画面
-----------------------------------------------------------*/
add_action('admin_menu', 'WAHS_addAdminMenu', 99);

function WAHS_addAdminMenu(){
	if( function_exists('add_options_page') ){
		$page = add_options_page( 'WP Add Header Scripts Option Settings',
									'WP Add Header Scripts',
									'manage_options',
									__FILE__,
									'WAHS_execAdminPage' );

		add_action( 'admin_enqueue_scripts', 'WAHS_adminLoad' );
	}
}

function WAHS_adminLoad() {
	wp_enqueue_style('WAHS_CSS', plugins_url('css/wahs.css', __FILE__) );
	wp_enqueue_script('WAHS_JS', plugins_url('js/wahs.js', __FILE__), array('jquery'));
}

function WAHS_execAdminPage(){
	require_once(WAHS_DIR.'/classes/WAHS_Options.php');
	$options = new WAHS_Options();

	if( isset( $_POST['save'] ) ){
		// 設定を更新
		$options->setLimit($_POST['limit']);
		$options->setRandom($_POST['random']);
		$s = array();
		foreach ($_POST as $key => $value) {
			if(preg_match("/^script_[0-9]+$/",$key)){
				$idx = str_replace('script_', "", $key);
				(int)$idx;
				$s[$idx] = $value;
			}
		}
		$options->setScripts($s);
		$options->update();
		echo '<div class="updated"><p><strong>' .'Options saved' .'</strong></p></div>';
	}
	else if( isset( $_POST['restore'] ) ){
		// 設定をリセット
		$options->restore();
		echo '<div class="updated"><p><strong>' .'Restore defaults' .'</strong></p></div>';
	}

	$options->initFromDb();
?>

	<div class="wrap">
		<h2>WP Add Header Scripts <?php _e('Option Settings', 'wahs'); ?></h2>
		<form method='POST' action="<?php echo $_SERVER['REQUEST_URI'] ?>">
			<table class='form-table'>
			<tr>
				<th scope="row"><?php _e('Maximum number', 'wahs'); ?>:</th>
				<td>
				<input type="number" name='limit' min="0" step="1" required value='<?= $options->getLimit() ?>'>
				<div class="wahs-memo">*<?php _e('If it is set to 0, it will be unlimited.', 'wahs'); ?></div>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Random', 'wahs'); ?>:</th>
				<td>
				<select name='random'>
				<option value='0' <?php if( !$options->isRandom() ) echo 'selected'; ?>>No.</option>
				<option value='1' <?php if( $options->isRandom() ) echo 'selected'; ?>>Yes.</option>
				</select>
				<div class="wahs-memo">*<?php _e('If you select YES, as the upper limit of the Maximum number you have set, you can display the input scripts at random. If you select No, you can view the scripts in order.', 'wahs'); ?></div>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e('Script', 'wahs'); ?>:</th>
				<td>
					<table id="script-table" style="width: 100%">
						<?php for ($i = 0; $i < count($options->getScripts()); $i++) { ?>

						<tr id="script-tr-<?= $i ?>">
							<th style="width:1px; text-align:right;"><?= ($i+1) ?></th>
							<td><textarea name="script_<?= $i ?>" required ><?=$options->getScripts()[$i] ?></textarea></td>
							<td style="width:1px;"><input type="button" value="<?php _e('Delete', 'wahs'); ?>" class="del_btn"></td>
						</tr>

						<?php } ?>
						<tr>
						<td colspan="3"><input type="button" value="<?php _e('Add input form', 'wahs'); ?>" id="add_btn"></td>
						</tr>

					</table>

				</td>
			</tr>
			</table>

			<p class="submit">
			<input class="button-primary" type="submit" name="save" value="<?php _e('Update options', 'wahs'); ?>" />
			<input type="submit" name="restore" value="<?php _e('Reset options', 'wahs'); ?>" />
			</p>
		</form>
	</div>

<?php
}


/*-----------------------------------------------------------
 * フロント画面出力
-----------------------------------------------------------*/
add_action('wp_head','WAHS_addHeaderScripts');

function WAHS_addHeaderScripts(){
	require_once(WAHS_DIR.'/classes/WAHS_Options.php');
	$options = new WAHS_Options();

	$plugin_data = get_plugin_data( __FILE__ , false);
	echo "\n<!-- " .$plugin_data['Name'] ." " .$plugin_data['Version'] ." by " .$plugin_data['Author'] ." [START] -->\n";
	echo $options->getOutScripts();
	echo "<!-- " .$plugin_data['Name'] ." " .$plugin_data['Version'] ." by " .$plugin_data['Author'] ." [END] -->\n\n";
}
