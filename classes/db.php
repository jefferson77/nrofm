<?php
function alertBox($msg) {
	echo '<script language="javascript">alert(\''.addslashes($msg).'\');</script>';
}
function redirURL($url) {
	echo '<script language="javascript">document.location.href="'.$url.'";</script>';
}
class db
{

######## MySQL config site #########
	var $server;
	var $login;
	var $pass;
	var $dbname;
	var $lang;
	var $result;
	var $connection;
	var $db;
	var $table;
	var $idname;
	var $addid;
	var $FoundCount = 0;
	var $quid;
	var $quod;
	var $error;
	var $recherche;
	var $affected;
	var $sql;

	var $MAJid;
	var $ADDid;

# Exception Functions
	function db ($table = 'notdefined', $idname = 'notdefined', $db = 'notdefined')
	{
		if ($db == 'notdefined') $db = Conf::read('Sql.database');

		$this->server = Conf::read('Sql.server');
		$this->login = Conf::read('Sql.login');
		$this->pass = Conf::read('Sql.pass');

		$this->dbname = $db;
		$this->table = $table;
		$this->idname = $idname;

		$this->connection = @mysql_connect($this->server, $this->login, $this->pass) or die("Couldn't Connect.");
		mysql_select_db($this->dbname, $this->connection) or die("Couldn't select database.");
		mysql_query("SET NAMES 'utf8'");
	}

	function sqlmail ($sql, $errnr, $errtext) {
		$to      = Conf::read('Env.adminmail');
		$subject = '['.Conf::read('Env.PID').'] Erreur SQL';
		$message = "Erreur SQL :\r\n============================================\r\nPage : ".$_SERVER['PHP_SELF']."\r\nReferrer : ".$_SERVER['HTTP_REFERER']."

Requete :\r\n============================================\r\n".$sql."

Error #". $errnr . ": \r\n" . $errtext."\r\n";

$message .= "\r\nGET values\r\n============================================\r\n";
foreach ($_GET as $key => $value) { $message .= "'".$key."' -> '".print_r($value, TRUE)."'\r\n"; }

$message .= "\r\nPOST values\r\n============================================\r\n";
foreach ($_POST as $key => $value) { $message .= "'".$key."' -> '".print_r($value, TRUE)."'\r\n"; }

$message .= "\r\nSESSION values\r\n============================================\r\n";
foreach ($_SESSION as $key => $value) { $message .= "'".$key."' -> '".print_r($value, TRUE)."'\r\n"; }

		$headers = 'From: nico@exception2.com' . "\r\n" .
		    'X-Mailer: PHP/' . phpversion();

		mail($to, $subject, $message, $headers);

		echo '<div class="error">Une erreur SQL s\'est produite.<br>Un mail avec les informations nécessaires au débogage a été envoyé à l\'Administrateur</div>';

	}

	function journal ($action) {
		$this->result = $this->DoQuery("INSERT INTO journal (idagent, action) VALUES ('".$_SESSION['idagent']."', '".addslashes($action)."');");
	}

	function inline ($sql)
	{
		$this->result = $this->DoQuery($sql);
		if (is_array($this->result)) {
			$this->FoundCount = mysql_num_rows($this->result);
		}
		$this->addid = mysql_insert_id() ;
		$this->error = mysql_error() ;

		$this->affected = mysql_affected_rows() ;
	}

	## php profiler logging function
	function logQuery($sql, $start) {
		global $sqlQueries;
		$query = array(
				'sql' => $sql,
				'time' => ($this->getTime() - $start)*1000
			);
		array_push($sqlQueries, $query);
	}

	function getTime() {
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$start = $time;
		return $start;
	}

	public function getReadableTime($time) {
		$ret = $time;
		$formatter = 0;
		$formats = array('ms', 's', 'm');
		if($time >= 1000 && $time < 60000) {
			$formatter = 1;
			$ret = ($time / 1000);
		}
		if($time >= 60000) {
			$formatter = 2;
			$ret = ($time / 1000) / 60;
		}
		$ret = number_format($ret,3,'.','') . ' ' . $formats[$formatter];
		return $ret;
	}

	##### Start fonctions Dom ##############"
	function insertArray($table, $array) {
		foreach($array as $row) {
			foreach(array_values($row) as $val) {
				if(!is_numeric($val)) $values[]='"'.htmlentities(stripslashes($val)).'"';
				else $values[]=$val;
			}
			$fields = join(array_keys($row), ',');
			$values = join($values, ',');
			$this->inline('INSERT INTO '.$table.' ('.$fields.') VALUES ('.$values.')');
			## tools : sont inclus dans le inline
		}
	}

	function DoQuery($sql) {
		$res = mysql_query($sql) or die("<div id=\"sqlerror\">Page : ".$_SERVER['PHP_SELF']."<br><br>Requete : ".$sql."<br><br>Error #". mysql_errno() . ": " . mysql_error()."</div>");
		return $res;
	}

	function getArray($sql, $idfield='') {
		$array = array();
		$res = $this->DoQuery($sql);
		if(mysql_num_rows($res)==0) {
			return $array;
		} else {
			while($data = mysql_fetch_assoc($res)) {
				if (!empty($idfield)) {
					$array[$data[$idfield]] = $data;
				} else {
					$array[] = $data;
				}
			}
			return $array;
		}
	}
	function getColumn($sql) {
		$res = $this->DoQuery($sql);
		if(mysql_num_rows($res)==0) {
			return false;
		} else {
			while($data = mysql_fetch_array($res)) {
				$array[]=$data[0];
			}
			return $array;
		}
	}
	function getRow($sql) {
		$res = $this->DoQuery($sql);
		if(mysql_num_rows($res)==0) {
			return false;
		} else {
			$row=mysql_fetch_assoc($res);
			return $row;
		}
	}
	function getOne($sql) {
		$res = $this->DoQuery($sql);
		if(mysql_num_rows($res)==0) {
			return false;
		} else {
			$row=mysql_fetch_array($res);
			return $row[0];
		}
	}
	###### END fonctions Dom #########

# ======================================================
# = Old Tools - TODO remplacer les Old Tools par les New Tools
# ======================================================

	## Efface une fiche
	function EFFACE ($id) {
		$sql = "DELETE FROM `$this->table` WHERE `$this->idname` = $id";
		$res = $this->DoQuery($sql);
	}

	## INSERT
	function AJOUTE ($liste) {
		# passe en revue les infos de Liste et regroupe les valeurs multiples (issues de checkbox)
		foreach ($liste as $value) {
			if (is_array($_POST[$value])) {
				foreach ($_POST[$value] as $value2) {
					$vari[$value] .= addslashes($value2).'%%';
				}
			} else {
				$vari[$value] = addslashes($_POST[$value]);
			}

		}
		# Fin

		$sql = "INSERT INTO `$this->table` SET ";
		foreach ($vari as $key => $value) {
			switch ($value) {
				case "NULL":
					$sql .= "`$key` = NULL, ";
				break;

				case "NOW":
					$sql .= "`$key` = NOW(), ";
				break;
				default:
					$sql .= "`$key` = '".$value."', ";
			}
		}
		$sql = substr($sql, 0, -2);

		$res = $this->DoQuery($sql);
		$this->addid = mysql_insert_id() ;
	}

	## Modifie
	function MODIFIE ($id, $liste) {
		# passe en revue les infos de Liste et regroupe les valeurs multiples (issues de checkbox)
		foreach ($liste as $value) {
            if (is_array($_POST[$value])) {
                foreach ($_POST[$value] as $value2) {
                    $vari[$value] .= addslashes($value2).'%%';
                }
            } else {
                $vari[$value] = addslashes($_POST[$value]);
            }
		}
		# Fin

		$thistime = strftime("%Y-%m-%d %T");

		$sql = "UPDATE `$this->table` SET ";
		foreach ($vari as $key => $value) {
			switch ($value) {
				case "NULL":
					$sql .= "`$key` = NULL, ";
				break;

				case "NOW":
					$sql .= "`$key` = NOW(), ";
				break;
				default:
					$sql .= "`$key` = '".$value."', ";
			}
		}
		$sql = substr($sql, 0, -2)." WHERE `$this->idname` = $id;";
		$this->sql = $sql;
		$res = $this->DoQuery($sql);
	}

	## Va chercher une valeur unique stockée dans la base 'config'
	function CONFIG ($valconfig) {
		$sql = "SELECT `vvaleur` FROM `config` WHERE `vnom` = '$valconfig'";
		$res = $this->DoQuery($sql);
		$row = mysql_fetch_array($res);
		return $row['vvaleur'];
	}

# ======================================================
# = New Tools ==========================================
# ======================================================
/*
	TODO : regroupe les fonctions de MAJ et ADD (qui sont quasi identiques)
*/

	## Ajoute les elements d'une fiche
	function ADD ($table, $data = null) {

		# Par défaut, les données à enregistrer sont reprises de $_POST
		if (is_null($data)) $data = $_POST;

		# init
		$fup       = array();
		$unhandeld = array();

		$fields = $this->getArray("SHOW FULL COLUMNS FROM ".$table.";");

		foreach ($fields as $field) {
			$flist[$field['Field']]['Type']    = $field['Type'];
			$flist[$field['Field']]['Key']     = $field['Key'];
			$flist[$field['Field']]['Comment'] = $field['Comment'];
		}

		foreach ($data as $key => $value) {
			if (is_array($value)) $value = "|".implode("|", $value)."|";

			$value = addslashes($value);

			if (array_key_exists($key, $flist)) {
				if ($value == 'NOW()') {
					$fup[] = "`".$key."` = NOW()";

				} elseif (substr($flist[$key]['Type'], 0, 7) == 'varchar') {
					$fup[] = "`".$key."` = '".$value."'";
				} elseif (substr($flist[$key]['Type'], 0, 4) == 'char') {
					$fup[] = "`".$key."` = '".$value."'";
				} elseif ($flist[$key]['Type'] == 'date') {
					if (validSqlDate($value)) {
						$fup[] = "`".$key."` = '".$value."'";
					} else {
						$fup[] = "`".$key."` = '".fdatebk($value)."'";
					}
				} elseif (
					($flist[$key]['Type'] == 'text') or
					($flist[$key]['Type'] == 'mediumtext') or
					(substr($flist[$key]['Type'], 0, 8) == 'tinytext') or
					(substr($flist[$key]['Type'], 0, 8) == 'longtext'))
				{
					$fup[] = "`".$key."` = '".$value."'";
				} elseif (substr($flist[$key]['Type'], 0, 4) == 'enum') {
					$fup[] = "`".$key."` = '".$value."'";
				} elseif ((substr($flist[$key]['Type'], 0, 3) == 'int') or (substr($flist[$key]['Type'], 0, 7) == 'tinyint') or (substr($flist[$key]['Type'], 0, 9) == 'mediumint') or (substr($flist[$key]['Type'], 0, 8) == 'smallint')) {
					$fup[] = "`".$key."` = '".$value."'";
				} elseif (substr($flist[$key]['Type'], 0, 7) == 'decimal' || substr($flist[$key]['Type'], 0, 5) == 'float') {
					$fup[] = "`".$key."` = '".fnbrbk($value)."'";
				} elseif ($flist[$key]['Type'] == 'datetime') {
					/*
						TODO : n'accepte que les format datetime deja formatés sinon formate.
					*/
					$fup[] = "`".$key."` = '".$value."'";
				} elseif ($flist[$key]['Type'] == 'time') {
					if (strstr($flist[$key]['Comment'], "sq15")) {
						$fup[] = "`".$key."` = '".sqrtime(ftimebk($value))."'";
					} else {
						$fup[] = "`".$key."` = '".ftimebk($value)."'";
					}
				} else {
					$unhandeld[] = 'type:'.$flist[$key]['Type'].' key :'.$key.' val:'.$value;
					$fup[] = "`".$key."` = '".$value."'";
				}
			} else {
				$notin[] = $key;
			}
		}

		if(array_key_exists('agentmodif', $flist)) $fup[] = "`agentmodif` = '".$_SESSION['idagent']."'";
		if(array_key_exists('datemodif', $flist)) $fup[] = "`datemodif` = NOW()";

		if(count($unhandeld) > 0) echo '<div id="sqlerror">'.print_r($unhandeld).'</div>'; #DEBUG

		$this->inline('INSERT INTO '.$table.' SET '.implode(", ", $fup));
		$this->ADDid = $this->addid;

		return $this->ADDid;
		## Tools compris dans l'inline
	}
	## update les elements d'une fiche
	function MAJ ($table, $force='', $data = null) {

		# Par défaut, les données à enregistrer sont reprises de $_POST
		if (is_null($data)) $data = $_POST;

		# init
		$fup          = array();
		$forcedfields = array();
		$unhandeld    = array();


		## force la valeur NULL aux champ checkbox spécifiés, permet de décocher la dernière checkbox
		if (!empty($force)) {
			$forcedfields = explode("|", $force);
			foreach ($forcedfields as $ffield) {
				if (empty($data[$ffield])) $data[$ffield] = 'NULL';
			}
		}

		$fields = $this->getArray("SHOW FULL COLUMNS FROM ".$table.";");

		foreach ($fields as $field) {
			$flist[$field['Field']]['Type'] = $field['Type'];
			$flist[$field['Field']]['Key'] = $field['Key'];
			$flist[$field['Field']]['Comment'] = $field['Comment'];
			if ($field['Key'] == 'PRI') $TABLEid = $field['Field'];
		}

		if (!empty($data['tableid'])) $data[$TABLEid] = $data['tableid'];

		foreach ($data as $key => $value) {
			if (is_array($value)) $value = "|".implode("|", $value)."|";

			$value = addslashes($value);
			$value = trim($value);

			if (array_key_exists($key, $flist)) {
				if ($flist[$key]['Key'] == 'PRI') {
					$refid = $key." = ".$value;
					$this->MAJid = $value;
				} elseif ($value == 'NULL') {
					$fup[] = "`".$key."` = NULL"; #forcedfields
				} elseif (substr($flist[$key]['Type'], 0, 7) == 'varchar') {
					$fup[] = "`".$key."` = '".$value."'";
				} elseif (substr($flist[$key]['Type'], 0, 4) == 'char') {
					$fup[] = "`".$key."` = '".$value."'";
				} elseif ($flist[$key]['Type'] == 'date') {
					if (validSqlDate($value)) {
						$fup[] = "`".$key."` = '".$value."'";
					} else {
						$fup[] = "`".$key."` = '".fdatebk($value)."'";
					}
				} elseif (
					($flist[$key]['Type'] == 'text') or
					($flist[$key]['Type'] == 'mediumtext') or
					(substr($flist[$key]['Type'], 0, 8) == 'tinytext') or
					(substr($flist[$key]['Type'], 0, 8) == 'longtext'))
				{
					$fup[] = "`".$key."` = '".$value."'";
				} elseif (substr($flist[$key]['Type'], 0, 4) == 'enum') {
					$fup[] = "`".$key."` = '".$value."'";
				} elseif ((substr($flist[$key]['Type'], 0, 3) == 'int') or (substr($flist[$key]['Type'], 0, 7) == 'tinyint') or (substr($flist[$key]['Type'], 0, 9) == 'mediumint') or (substr($flist[$key]['Type'], 0, 8) == 'smallint')) {
					$fup[] = "`".$key."` = '".$value."'";
				} elseif ((substr($flist[$key]['Type'], 0, 7) == 'decimal') or (substr($flist[$key]['Type'], 0, 5) == 'float')) {
					$fup[] = "`".$key."` = '".fnbrbk($value)."'";
				} elseif ($flist[$key]['Type'] == 'datetime') {
					/*
						TODO : n'accepte que les format datetime deja formatés sinon formate.
					*/
					$fup[] = "`".$key."` = '".$value."'";
				} elseif ($flist[$key]['Type'] == 'time') {
					if (strstr($flist[$key]['Comment'], "sq15")) {
						$fup[] = "`".$key."` = '".sqrtime(ftimebk($value))."'";
					} else {
						$fup[] = "`".$key."` = '".ftimebk($value)."'";
					}
				} else {
					$unhandeld[] = 'type:'.$flist[$key]['Type'].' key :'.$key.' val:'.$value;
					$fup[] = "`".$key."` = '".$value."'";
				}
			} else {
				$notin[] = $key;
			}
		}

		if(array_key_exists('agentmodif', $flist)) $fup[] = "`agentmodif` = '".@$_SESSION['idagent']."'";
		if(array_key_exists('datemodif', $flist)) $fup[] = "`datemodif` = NOW()";

		if(count($unhandeld) > 0) echo '<div id="sqlerror">'.print_r($unhandeld).'</div>'; #DEBUG

		$this->inline('UPDATE '.$table.' SET '.implode(", ", $fup).' WHERE '.$refid);

		return $this->MAJid;
	}

	## Construction du Quid
	function MAKEsearch ($searchfields) {
		### Boucle de construction de la recherche
		foreach ($searchfields as $srchfld => $fldname) {

			if (is_array($_POST[$fldname])) {
				foreach ($_POST[$fldname] as $value) {
					if (!empty($value)) {
						$this->quid .= ((!empty($this->quid))?' AND ':'')."$srchfld LIKE '%|$value|%' ";
						$this->quod .= ((!empty($this->quod))?' ET ':'')."$srchfld LIKE '%|$value|%' ";
					}
				}
			} elseif ((!empty($_POST[$fldname])) or ($_POST[$fldname] == '0')) {
				## Ajout un AND si la requete existe déja
				if ($this->quid != '')
				{
					$this->quid .= " AND ";
					$this->quod .= " ET ";
				}

				## Détermine le type de cas
				if ((strstr($_POST[$fldname], ',')) and (strstr($_POST[$fldname], '...'))) {$cas = '01';} else {
				if (strstr($_POST[$fldname], ',')) {$cas = '02';} else {
				if (strstr($_POST[$fldname], '...')) {$cas = '03';} else {
				if (strstr($_POST[$fldname], '>=')) {$cas = '04';} else {
				if (strstr($_POST[$fldname], '<=')) {$cas = '05';} else {
				if (strstr($_POST[$fldname], '>')) {$cas = '06';} else {
				if (strstr($_POST[$fldname], '<')) {$cas = '07';} else {
				if (strstr($_POST[$fldname], '=')) {$cas = '08';} else {
				if (strstr($_POST[$fldname], '*')) {$cas = '09';} else {
				if (strstr($_POST[$fldname], '?')) {$cas = '10';} else {
				if (strstr($_POST[$fldname], '!')) {$cas = '11';} else {$cas = '';}}}}}}}}}}}

				## Construit la requete selon le cas
				switch ($cas) {
					case "01":
						$pos = strpos($_POST[$fldname], '...');
						$str1 = substr($_POST[$fldname], 0, $pos);
						$pos2 = strrpos($str1, ',');
						$str1 = substr($str1, 0, $pos2);
 						$str2 = substr($_POST[$fldname], strlen($str1) + 1);

						$pos3 = strpos($str2, '...');
						$par1 = substr($str2, 0, $pos3);
						$par2 = substr($str2, $pos3 + 3);
						$str1 = str_replace(",", "','", $str1);
						$str1 = "'" . $str1 . "'";

						$this->quid .="($srchfld IN($str1) OR $srchfld BETWEEN '$par1' AND '$par2')";
						$this->quod .= "";
					break;

					case "02":
						$par = explode (",", $_POST[$fldname]);
						for($i = 0; $i < count($par); $i++)
						{
							$par2 .= $par[$i] . ",";
						}
						$par2 = substr($par2, 0, strlen($par2) - 1);
						$par2 = str_replace(",", "','", $par2);
						$par2 = "'" . $par2 . "'";

						$this->quid .= "($srchfld IN ($par2))";
						$this->quod .= "";
					break;

					case "03":
						$pos = strpos($_POST[$fldname], "...");
						if($pos == 0)
						{
							$str1 = substr($_POST[$fldname], 3);
							if (strstr($str1, '/')) $str1 = fdatebk($str1);
							$this->quid .= "$srchfld <= '$str1'";
							$this->quod .= "";
						}
						if($pos > 0) {
							$str1 = substr($_POST[$fldname], $pos);
							$len = strlen($str1);
							if($len == 3)
							{
								$str2 = substr($_POST[$fldname], 0, strlen($_POST[$fldname]) - 3);
								if (strstr($str2, '/')) $str2 = fdatebk($str2);
								$this->quid .= "$srchfld >= '$str2'";
								$this->quod .= "";
							}
							else
							{
								$str1 = substr($_POST[$fldname], 0, $pos);
								$str2 = substr($_POST[$fldname], $pos + 3);
								if (strstr($str1, '/')) $str1 = fdatebk($str1);
								if (strstr($str2, '/')) $str2 = fdatebk($str2);
								$this->quid .= "$srchfld BETWEEN '$str1' AND '$str2'";
							}
						}
					break;

					case "04":
						$_POST[$fldname] = trim($_POST[$fldname]);
						$par1 = substr($_POST[$fldname], 2);
						if (strstr($par1, '/')) $par1 = fdatebk($par1);

						$this->quid .= "$srchfld >= '$par1'";
						$this->quod .= "";
					break;

					case "05":
						$_POST[$fldname] = trim($_POST[$fldname]);
						$par1 = substr($_POST[$fldname], 2);
						if (strstr($par1, '/')) $par1 = fdatebk($par1);

						$this->quid .= "$srchfld <= '$par1'";
						$this->quod .= "";
					break;

					case "06":
						$_POST[$fldname] = trim($_POST[$fldname]);
						$par1 = substr($_POST[$fldname], 1);
						if (strstr($par1, '/')) $par1 = fdatebk($par1);

						$this->quid .= "$srchfld > '$par1'";
						$this->quod .= "";
					break;

					case "07":
						$_POST[$fldname] = trim($_POST[$fldname]);
						$par1 = substr($_POST[$fldname], 1);
						if (strstr($par1, '/')) $par1 = fdatebk($par1);

						$this->quid .= "$srchfld < '$par1'";
						$this->quod .= "";
					break;

					case "08":
						$par1 = substr($_POST[$fldname], 1);
						if (strstr($par1, '/')) $par1 = fdatebk($par1);

						$this->quid .= "$srchfld = '$par1'";
						$this->quod .= "";
					break;

					case "09":
						$par = explode('*', $_POST[$fldname]);
						for($i = 0; $i < count($par); $i++)
						{
							$par2 .= $par[$i] . "%";
						}

						$par2 = substr($par2, 0, strlen($par2) -1);

						$this->quid .= "$srchfld LIKE '".$par2."'";
						$this->quod .= "";
					break;

					case "10":
						$par = explode('?', $_POST[$fldname]);
						for($i = 0; $i < count($par); $i++)
						{
							$par2 .= $par[$i] . "_";
						}
						$par2 = substr($par2, 0, strlen($par2) - 1);

						$this->quid .= "$srchfld LIKE '". $par2 ."'";
						$this->quod .= "";
					break;

					case "11":
						$_POST[$fldname] = str_replace(' ', '', $_POST[$fldname]);
						$par1 = substr($_POST[$fldname], 0, 1);
						$par1 = str_replace('!', '<> ', $par1);
						$par2 = substr($_POST[$fldname], 1);
						$this->quid .= "$srchfld  $par1 '$par2'";
						$this->quod .= "";
					break;

					default:
						if(is_numeric($_POST[$fldname])) {
							$this->quid .= "$srchfld LIKE '".$_POST[$fldname]."'";
							$this->quod .= "$fldname = ".$_POST[$fldname];
						} else {
							if (strstr($_POST[$fldname], '/')) {
								$_POST[$fldname] = fdatebk($_POST[$fldname]);
								$this->quid .= "$srchfld = '".$_POST[$fldname]."'";
							} else {
								$this->quid .= "$srchfld LIKE '%".addslashes($_POST[$fldname])."%'";
							}
								$this->quod .= "$fldname = ".$_POST[$fldname];
						}
				}

				unset($cas);
			}
		}
		if (!empty($this->quid)) {$this->quid = ' '.$this->quid;} else {$this->quid = ' 1';}
		if (!empty($this->quod)) {$this->quod = 'Recherche '.$this->quod;}

		return $this->quid;
	}

	function quidage ($age, $field) {
		$sqlage = "ABS(FLOOR(DATEDIFF( ".$field.", CURDATE()) / 365.25))";
		$age = trim($age);

		# un age précis
		if (is_numeric($age)) {
			$agequid = $sqlage." = ".$age;
		# une fourchette
		} elseif (strstr($age, "...")) {
			$parts = explode("...", $age);
			$agequid = $sqlage." BETWEEN '".$parts[0]."' AND '".$parts[1]."'";
		# >= que
		} elseif (strstr($age, ">=")) {
			$age = fnbrbk($age);
			$agequid = $sqlage." >= ".$age;
		# <= que
		} elseif (strstr($age, "<=")) {
			$age = fnbrbk($age);
			$agequid = $sqlage." <= ".$age;
		# > que
		} elseif (strstr($age, ">")) {
			$age = fnbrbk($age);
			$agequid = $sqlage." > ".$age;
		# < que
		} elseif (strstr($age, "<")) {
			$age = fnbrbk($age);
			$agequid = $sqlage." < ".$age;
		}

		return $agequid;
	}

	function tableliste ($contient, $db='')
	{
		$tables = array();
		$return = array();

		$tables = $this->getColumn("SHOW TABLES FROM ".((!empty($db))?$db:$this->db));

		foreach ($tables as $row) if (preg_match($contient,$row)) $return[] = $row;

		sort($return);

		return $return;
	}

###############################################################################
##################### Old Functions ###########################################
###############################################################################

	function liste ()
	{
		$nbrchamps = mysql_num_fields($this->result);

		echo '<table cellspacing="1" cellpadding="1" align="center"><tr>';
		for ($i = 0; $i < $nbrchamps; $i++) {echo '<td class="level1">'.mysql_field_name($this->result, $i).'</td>';}
		echo '</tr>';

		while ($row = mysql_fetch_row($this->result))
		{
			echo '<tr>';
			foreach($row as $value)
			{
				echo '<td class="level2">'.$value.'</td>';
			}
			echo'
			<td><a href="#">edit</a></td>
		</tr>';
		}
		echo'</table>';
	}
}
?>
