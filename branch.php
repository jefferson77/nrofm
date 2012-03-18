#!/usr/bin/php
<?php
define("NIVO", $_SERVER["PWD"]."/");

include NIVO.'nro/fm.php';

## conf
$ignore = array('.', '..');
$brdir = Conf::read('Env.root').'branches';

# clean screen
system("clear");

## get branch list
$branches = scandir(Conf::read('Env.root').'branches');

$i=1;
foreach ($branches as $branche) {
	if (!in_array($branche, $ignore)) {
		if (substr($branche, -3) == '.on') {
			$active = substr($branche, 0, -3);
		} else {
			$br[$i] = $branche;
			$i++;
		}
	}
}

## Check fichier .on sinon demande un nom et cree
if (empty($active)) {
	echo "
!! La branche active n'a pas de nom, comment voulez vous l'appeler ? ";
	
	$activename = trim(fgets(STDIN));
	
	if (!empty($activename)) {
		exec("touch ".$brdir.'/'.$activename.".on") or die ("Error File: ".__FILE__." on line: ".__LINE__." Message: Impossible de creer le fichier ".$brdir.'/'.$activename.".on");
	} else {
		echo "Vous devez nommer la branche active.";
		exit();
	}
}


echo "
NRO Branche Active :
--------------------
	0) ".$active."


NRO Branches disponibles :
--------------------------
";

foreach ($br as $brid => $branche) {
	echo "	".$brid.") ".$branche."\r\n";
}

echo "
	new) Créer une nouvelle branche

Quelle branche désirez vous activer ? ";

$newbranch = trim(fgets(STDIN));

if ($newbranch == 'new') {
	echo "\r\nQuel nom voulez vous donner à la nouvelle branche (X pour annuler) ? ";
	$newname = trim(fgets(STDIN));

	if (($newname != 'X') and !empty($newname)) {
		if (rename(Conf::read('Env.root').'core', $brdir.'/'.$active)) {
			# nouvelle branche
			system("svn co ".Conf::read('Env.svnrep')." ".Conf::read('Env.root').'core');
			system("cp ".Conf::read('Env.root')."core/conf/neuro.example.conf ".Conf::read('Env.root')."core/conf/neuro.conf");
			if (rename($brdir.'/'.$active.'.on', $brdir.'/'.$newname.".on")) {
				echo "\r\nNouvelle Branche Activée ! (D8)\r\n";
			} else {
				echo "\r\nERR : Echec lors du renomage de la branche active. (D7)\r\n";
			}
			
		} else {
			echo "\r\nERR : Echec lors du déplacement de la branche. (D1)\r\n";
		}
	} else {
		echo "\r\n-> Aucune Action effectuée (D2)\r\n";
	}
} else {
	if (($newbranch > 0) and (array_key_exists($newbranch, $br))) {
		if (rename(Conf::read('Env.root').'core', $brdir.'/'.$active)) {
			# active branche existante
			if (rename($brdir.'/'.$br[$newbranch], Conf::read('Env.root')."core")) {
				if (rename($brdir.'/'.$active.'.on', $brdir.'/'.$br[$newbranch].".on")) {
					system("svn update ".Conf::read('Env.root')."core");
					echo "\r\n Branche '".$br[$newbranch]."' Activée et a jour !\r\n";
				} else {
					echo "\r\nERR : Echec lors du renomage de la branche active. (D6)\r\n";
				}
			} else {
				echo "\r\nERR : Echec lors du déplacement de la branche. (D3)\r\n";
			}
		} else {
			echo "\r\nERR : Echec lors du déplacement de la branche. (D4)\r\n";
		}
	} else {
		echo "\r\n-> Aucune Action effectuée (D5)\r\n";
	}
}

?>