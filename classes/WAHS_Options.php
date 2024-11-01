<?php
/*----------------------------------------------------------------------------------------------------------------------
 class WAHS_Options

 DBに登録されているWAHS関連オプションを提供するクラスです。
----------------------------------------------------------------------------------------------------------------------*/
class WAHS_Options {

	const COL_ENTITY = "wahs_";

	private $limit     = "0";// 0:無制限
	private $random    = "0";
	private $is_random = false;
	private $scripts   = array();

	/*
	* function getLimit
	* @return String
	* @since 1.0.0
	*/
	public function getLimit(){
		return $this->limit;
	}

	/*
	* function setLimit
	* @param String $limit
	* @since 1.0.0
	*/
	public function setLimit($limit = null){
		if(ctype_digit($limit)){
			$this->limit = $limit;
		}else{
			$this->limit = "0";
		}
	}

	/*
	* function getRandom
	* @return String
	* @since 1.0.0
	*/
	public function getRandom(){
		return $this->random;
	}

	/*
	* function isRandom
	* @return boolean
	* @since 1.0.0
	*/
	public function isRandom(){
		return (boolean)$this->is_random;
	}

	/*
	* function setRandom
	* @param String $random
	* @since 1.0.0
	*/
	public function setRandom($random = null ){
		if($random == "1"){
			$this->random = "1";
			$this->is_random = true;
		} else {
			$this->random = "0";
			$this->is_random = false;
		}
	}

	/*
	 * function getScripts
	 * @return array
	 * @since 1.0.0
	 */
	public function getScripts(){
		return (is_array($this->scripts)) ? $this->scripts : array() ;
	}

	/*
	* function setScripts
	* @param array $scripts
	* @since 1.0.0
	*/
	public function setScripts($scripts = null){
		if(is_array($scripts)){
			$this->scripts = $scripts;
		} else {
			$this->scripts = array();
		}
	}

	/*
	 * function __construct
	 * コンストラクタ
	 *
	 * @since 1.0.0
	 */
	function __construct() {

	}

	/*
	* function initFromDb
	* DBから値を取得し、オブジェクトにセットします。
	*
	* @since 1.0.0
	*/
	public function initFromDb(){
		wp_cache_delete( 'alloptions', 'options');//キャッシュ削除

		// DBに登録されている値を返します。存在しない場合はデフォルト値を返します。
		// $limit
		$val = get_option(self::COL_ENTITY .'limit');
		$this->setLimit($val);

		// $is_random
		$val = get_option(self::COL_ENTITY .'random');
		$this->setRandom($val);

		// $scripts
		global $wpdb;
		$res = $wpdb->get_results(
				$wpdb->prepare("SELECT * FROM $wpdb->options WHERE option_name LIKE %s", self::COL_ENTITY .'script_%')
		);

		$val = array();
		$max = 0;
		if(!empty($res)){
			foreach ($res as $row) {
				$idx = str_replace(self::COL_ENTITY .'script_', "", $row->option_name);
				(int)$idx;
				if($idx > $max) $max = $idx;
				$val[$idx] = stripcslashes($row->option_value);
			}
		} else {
			$val[0] = "";
		}
		// 歯抜けを埋める
		for ($i = 0; $i < $max; $i++) {
			if(!isset($val[$i])){
				$val[$i] = "";
			}
		}
		$this->setScripts($val);
	}

	/*
	* function update
	* DBを更新します。
	*
	* @since 1.0.0
	*/
	public function update(){
		update_option(self::COL_ENTITY .'limit', $this->getLimit());
		update_option(self::COL_ENTITY .'random', $this->getRandom());

		// DBに登録されている行を取得
		global $wpdb;
		$res = $wpdb->get_results(
				$wpdb->prepare("SELECT * FROM $wpdb->options WHERE option_name LIKE %s", self::COL_ENTITY .'script_%')
		);
		$max = 0;
		if(!empty($res)){
			foreach ($res as $row) {
				$idx = str_replace(self::COL_ENTITY .'script_', "", $row->option_name);
				(int)$idx;
				if($idx > $max) $max = $idx;
			}
		}
		// 入力値で更新
		$input_max = 0;
		foreach ($this->getScripts() as $key => $value) {
			if($key > $input_max) $input_max = $key;
			update_option(self::COL_ENTITY .'script_' .$key, $value);
		}
		// 不要な行を削除
		if($max > $input_max){
			for ($i = $input_max+1; $i <= $max; $i++) {
				delete_option(self::COL_ENTITY .'script_' .$i);
			}
		}
	}


	/*
	* function restore
	* リストアします。
	*
	* @since 1.0.0
	*/
	public function restore(){
		global $wpdb;
		$delete = $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '" .self::COL_ENTITY ."%'");
	}


	/*
	* function getOutScripts
	* 画面に表示するスクリプトを取得します。
	* @return String
	* @since 1.0.0
	*/
	public function getOutScripts(){
		$res = "";
		$this->initFromDb();

		$limit = (int)$this->getLimit();
		$scripts = $this->getScripts();

		// 値がブランクの配列要素を削除
		foreach ($scripts as $key => $value) {
			if($value === ""){
				unset($scripts[$key]);
			}
		}

		if(empty($scripts)) return "";

		if($this->isRandom()){
			shuffle($scripts);
		}

		$cnt = 0;
		foreach ($scripts as $key => $value) {
			$cnt++;
			if($limit != 0 && $limit < $cnt){
				break;
			}
			$res .= $value ."\n";
		}

		return $res;
	}

}
