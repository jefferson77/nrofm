<?php
/*
* Classe Conf
* 
* Contient la configuration
* 
* @author 	Fred
* @created 	20/12/09
*/

class Conf {

	static $__data = array();

	#
	# Read
	#
	# Retourne la valeur de la variable
	#
	# @param		String		$var		Identifiant de la variable
	# @return		Mixed		Null dans le cas où la variable n'existe pas, sinon la variable
	public static function r($var) { self::read($var); }
	public static function read($var) {
		if (!isset(self::$__data[$var])) return null;
		else return self::$__data[$var];
	}

	#
	# Write
	#
	# Enregistre une valeur
	#
	# @param		String		$var		Identifiant de la variable
	# @param		Mixed		$val		Valeur de la variable
	# @return		Void
	#
	public static function w($var, $val) { self::write($var, $val); }
	public static function write($var, $val) {
		self::$__data[$var] = $val;
	}
	
	#
	# Debug
	#
	# Imprime l'état de toute la configuration
	#
	public static function debug() {
		print "<pre><strong>Config settings</strong>\n";
		var_dump(self::$__data);
		print "</pre>";	
	}
}
?>