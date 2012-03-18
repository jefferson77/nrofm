<?php
global $sqlQueries;
$sqlQueries = array();

## Configuration
require_once NIVO.'nro/classes/conf.class.php';
require_once NIVO."conf/neuro.conf";

## Classes
require_once NIVO."nro/classes/format.php";
require_once NIVO."nro/classes/db.php";

## Project specific functions
if (file_exists(NIVO."classes/nro-addon.php")) {
	require_once NIVO."classes/nro-addon.php" ;
}

## STATIK si défini dans le neuro.conf
$staticURL = Conf::read('Env.staticURL');
$staticSubnet = Conf::read('Env.staticSubnet');

if (!empty($staticURL) and !empty($staticSubnet) and isset($_SERVER["REMOTE_ADDR"])) {
	if ((substr($_SERVER["REMOTE_ADDR"], 0, strlen(Conf::read('Env.staticSubnet'))) == Conf::read('Env.staticSubnet')) or ($_SERVER['REMOTE_ADDR'] == '127.0.0.1')) {
		define('STATIK', NIVO);
	} else {
		define('STATIK', Conf::read('Env.staticURL'));
	}
} else {
	define('STATIK', NIVO);
}

### Declaration de l'objet DB
$DB = new db();

?>