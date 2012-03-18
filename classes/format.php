<?php
/**
 * getFileExt
 * Retourne l'extension d'un fichier
 *
 * @author Fred
 **/
function getFileExt($filename) {
    $default = array('extension' => '');
    $f = pathinfo($filename);
    $f = array_merge($default, $f);
    return $f['extension'];
}

/**
 * Alias de var_dump() qui affiche la variable dans un <pre/>
 *
 * @author Fred
 **/
function v($var) {
    print "<pre>";
    var_dump($var);
    print "</pre>";
}

/**
 * Retourne l'illu du fichier selon l'extension
 * Usage : echo iconfile($file)   ->    <img src="/illus/page_white.png" alt="" width="16" height="16">
 *
 * @return nom du fichier
 * @author Nico
 **/
function iconfile ($file) {
    $ftype = strrchr($file, '.');

    $iconfiles = array(
    ## Office Documents
        '.doc'  => 'page_white_word.png',
        '.docx' => 'page_white_word.png',
        '.ppt'  => 'powerpoint.png',
        '.xls'  => 'page_white_excel.png',
        '.xlsx' => 'page_white_excel.png',
        '.rtf'  => 'page_white_word.png',
    ## PDF
        '.pdf'  => 'page_white_acrobat.png',
    ## Images
        '.gif'  => 'page_white_picture.png',
        '.jpeg' => 'page_white_picture.png',
        '.jpg'  => 'page_white_picture.png',
        '.png'  => 'page_white_picture.png',
    ## Archives
        '.zip'  => 'page_white_compressed.png'
    );

    if (array_key_exists($ftype, $iconfiles)) {
        $image = $iconfiles[$ftype];
    } else {
        $image = 'page_white.png';
    }

    return '<img src="'.STATIK.'nro/illus/'.$image.'" alt="" width="16" height="16">';
}

/**
 * Vérifie si un folder est vide
 *
 * @return bool
 * @author Nico
 **/
function isEmptyDir($dir) {
     return (($files = @scandir($dir)) && count($files) <= 2);
}

/**
 * dirFiles : récupère les fichiers stockés dans un dossier et clean les . et .. (et effaces les .DS_Store)
 *
 * @return array
 * @author Nico
 **/
function dirFiles ($dir, $regexp = '') {
    $lesfiles = array();
    $ignorefiles = array('.', '..');
    $deletefiles = array('/^\.DS_Store$/', '/^\._.*/');

    if (is_dir($dir)) {
        $lesfiles = scandir($dir);

        if (substr($dir, -1) != "/") $dir .= "/";

        foreach ((array)$lesfiles as $file) {
            $deljumper = 'nodel';
            foreach ($deletefiles as $delfile) {
                if (preg_match($delfile, $file)) $deljumper = 'todel';
            }

            if ($deljumper == 'todel') {
                unlink($dir.$file);
                $warning[] = "Fichiers ".$file." effacé";
            } elseif (!in_array($file, $ignorefiles)) {
                if ((!empty($regexp) and (preg_match($regexp."i", $file))) or (empty($regexp))) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }
}

function qualiteNL ($qualitefr) {
    switch ($qualitefr) {
        case 'Mr':
        case 'Monsieur':
            $qualitenl = 'Meneer';
        break;
        case 'Madame':
            $qualitenl = 'Mevrouw';
        break;
        case 'Mlle':
        case 'Mademoiselle':
            $qualitenl = 'Juffrouw';
        break;
    }

    return $qualitenl;
}

## Vérifie si une date est au format SQL yyyy-mm-dd
function validSqlDate($date) {
    if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $date, $matches)) {
        if (checkdate($matches[2], $matches[3], $matches[1])) return true;
    }

    return false;
}

function weekfromdate ($date) {
    if (!strstr($date, '-')) $date = fdatebk($date);

    $semaine = date('W', strtotime($date));
    $mois = date('n', strtotime($date));
    $annee = date('Y', strtotime($date));
    if (($mois == 12) and ($semaine == 1)) $semaine = 53;
    if ($annee == 2010) $semaine++;

    return $semaine;
}

## Calcul de l'age
function age($naiss, $vdate = '') {
    list($annee, $mois, $jour) = explode("-", $naiss);

    if (empty($vdate)) $vdate = date("Y-m-d"); # date de validite du salaire

    $today['mois'] = date('n', strtotime($vdate));
    $today['jour'] = date('j', strtotime($vdate));
    $today['annee'] = date('Y', strtotime($vdate));
    $annees = $today['annee'] - $annee;

    if ($today['mois'] <= $mois) {
        if ($mois == $today['mois']) {
            if ($jour > $today['jour']) $annees--;
        } else {
            $annees--;
        }
    }

    return $annees;
}

## Arrondi des heures SQL au quart d'heure le plus proche
function sqrtime ($sqltime) {
    ## reçois   00:17:27
    ## renvoie  00:15:00
    list($heures, $minutes, $secondes) = explode(":", $sqltime);
    return $heures.':'.(round($minutes / 15) * 15).':00';
}

## ajoute assez de zeros devant un chaine pour obtenir X caracteres
function prezero ($string, $nombchar, $fix='') {
    if (strlen($string) > $nombchar) {
        echo 'too long : "'.$string.'" pour "'.$nombchar.'" '.$fix.'<br>';
        return $string;
    } else {
        return str_repeat('0', $nombchar - strlen($string)).$string;
    }
}

## ajoute assez de zeros en fin de chaine pour obtenir X caracteres
function postzero ($string, $nombchar) {
    if (strlen($string) > $nombchar) {
    echo 'trunc : "'.$string.'" on "'.$nombchar.'"<br>';
        return substr($string, 0, $nombchar) ;
    } else {
        return $string.str_repeat('0', $nombchar - strlen($string));
    }
}

### Formatte une taille de fichier en KB MB
function fsize($size) {
    if ($size < 1024) {
        return $size.' o';
    } elseif ($size < 1048576) {
        return number_format(($size/1024), 1, ',', ' ').' Ko';
    } elseif ($size < 1000000000) {
        return number_format(($size/(1024*1024)), 1, ',', ' ').' Mo';
    } elseif ($size < 1000000000000) {
        return number_format(($size/(1024*1024*1024)), 1, ',', ' ').' Go';
    } elseif ($size >= 1000000000000) {
        return number_format(($size/(1024*1024*1024*1024)), 1, ',', ' ').' To';
    }
}

## Enleve les accents
setlocale(LC_CTYPE, 'fr_FR');
function fmclean ($string) {
    $string = iconv('ISO-8859-1', 'ASCII//TRANSLIT', $string);

    $a = array('ø', 'é');
    $b = array('o', 'e');

    $string = str_replace($a, $b, $string);

    $pattern = "/([^A-Za-z0-9 -\.@:_\s])/U";
    if (preg_match_all($pattern, $string, $m)) {
        foreach($m as $l) file_put_contents("badchars.txt", implode("", $l), FILE_APPEND);
        $string = preg_replace($pattern, '_', $string);
    }

    return $string;
}

function removeaccents($string) {
    $string= strtr($string,
    "ËçåÌñîïÍ¯¿éæèíêëìôòóØ",
    "aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn");

    return $string;
}

### Explode un multichamp vers une array
function xplode($text) {
    $new_array = array();
    $arr = explode("|", $text);
    foreach ($arr as $value) {
        if (!empty($value)) $new_array[] = $value;
    }
    return $new_array;
}

### Affiche le temps restant avant une deadline
function timeleft($timestampin, $timestampout='NOW') {
    if ($timestampout=='NOW') $timestampout = date("Y-m-d H:i:s");

    if ($timestampin == '0000-00-00 00:00:00') {
        return '<font color="#CCC">--</font>';
    }

    #Convert en mktime
    $echeance = strtotime($timestampin);
    $now = strtotime($timestampout);

    $diff = $echeance - $now;
    $absdiff = abs($diff);

    if ($absdiff < 3600) {
        $result = floor($absdiff/60).'min';
        $color = "#DF7417";
    } elseif ($absdiff < 86400) {
        $result = floor($absdiff/3600).'h '.floor(fmod($absdiff, 3600)/60).'m';
        $color = "#4B8E39";
    } else {
        $result = floor($absdiff/86400).'j '.floor(fmod($absdiff, 86400)/3600).'h ';
        $color = "#000";
    }

    # délais dépassés
    if ($diff < 0) {
        $color="#F00";
        $result = '- '.$result;
    }

    return '<font color="'.$color.'">'.$result.'</font>';
}

function myzone ($datetime, $dir = 'to') {
    global $timezones;
    if ($datetime != "0000-00-00 00:00:00") {
        if($dir == 'from') {
            $decal = 0 - $timezones[$_SESSION['timezone']];
        } else {
            $decal = $timezones[$_SESSION['timezone']];
        }

        if ($decal > 0) $decal = "+".$decal." hours";
        if ($decal < 0) $decal = $decal." hours";

        if (!empty($decal))
        {
            $newtime = date("Y-m-d H:i:s", strtotime($decal, strtotime($datetime)));
        } else {
            $newtime = $datetime;
        }

        return $newtime;
    } else {
        return $datetime;
    }
}

function fdtsplit($datetime) {
    $datetime = myzone($datetime, 'to');
    $parts = explode(" ", $datetime);
    $darray['D'] = fdate($parts[0]);
    $darray['T'] = ftime($parts[1]);
    return $darray;
}

function fdtsplitbk($dtarray) {
    if (!empty($dtarray['D'])) {
        $datebk = fdatebk($dtarray['D']);
    } else {
        $datebk = '0000-00-00';
    }

    $datetime = $datebk.' '.ftimebk($dtarray['T']);
    $datetime = myzone($datetime, 'from');

    return $datetime;
}

function cleanalpha($texte) {
    $texte = remaccents($texte);
    $texte = strtolower($texte);
    $texte = preg_replace("/[^a-z]/", "", $texte); # vire les crasses qui ne sont pas des lettres majuscules
    return $texte;
}

function cleannombreonly($nombre) {
    $nombre = preg_replace("/[^0-9]/", "", $nombre); # vire les crasses
    return $nombre;
}

function remaccents ($chaine) {
    $chars = array(
        "à" => "a",
        "â" => "a",
        "ä" => "a",
        "å" => "a",
        "ã" => "a",
        "é" => "e",
        "è" => "e",
        "ê" => "e",
        "ë" => "e",
        "ç" => "c",
        "ô" => "o",
        "î" => "i",
        "ï" => "i"
    );

    $chaine = str_replace(array_keys($chars), array_values($chars), $chaine);

    if (preg_match("/[^a-zA-Z0-9 ]/", $chaine)) {
        $badchars = preg_replace("/[a-zA-Z0-9\. -]/", "", $chaine);
        file_put_contents(Conf::read('Env.root')."media/badchars.txt", $badchars, FILE_APPEND);
        $chaine = preg_replace("/[^a-zA-Z0-9\. -]/", "", $chaine);
    }

    return $chaine;
}

function cleantext($string) {
    return(stripslashes(trim($string)));
}

## Calculer le nombre de jours entre 2 dates
function nbjours($debut, $fin) {
    $tDeb = explode("-", $debut);
    $tFin = explode("-", $fin);

    $diff = mktime(0, 0, 0, $tFin[1], $tFin[2], $tFin[0]) - mktime(0, 0, 0, $tDeb[1], $tDeb[2], $tDeb[0]);
    return(($diff / 86400));
}

## Affiche une longueur maximum de charactère ( utile dans un tableau pour éviter les colonnes trop larges)
function showmax ($val, $length) {
    if (strlen($val) > $length) { return substr($val, 0, $length).'...';} else { return $val ;}
}

## Affiche une longueur maximum de charactère ( efface le milieu)
function showmaxMid ($val, $length) {
    if (strlen($val) > $length) {
        $stucklength = floor(($length - 3)/2);
        return substr($val, 0, $stucklength).'...'.substr($val, 0-$stucklength);
    } else {
        return $val ;
    }
}

## Formater une heure (venant de DB)
function ftime($temps) {
    if (empty($temps)) $temps = '00:00:00';
    $explo = explode(":", $temps);
        if (strlen($explo[1]) == '0') { $explo[1] = '00';}
        if (strlen($explo[0]) == '0') { $explo[0] = '00';}
    $formated = $explo[0].':'.$explo[1];
    if ($formated == '00:00') {$formated = '';}
    return $formated;
}

## Formater une heure (VERS la DB)
function ftimebk($temps) {
    $explo = explode(":", $temps);
        if (empty($explo[2])) { $explo[2] = '00';}
        if (strlen($explo[2]) == '1') { $explo[2] = '0'.$explo[2];}
        if (empty($explo[1])) { $explo[1] = '00';}
        if (strlen($explo[1]) == '1') { $explo[1] = '0'.$explo[1];}
        if (empty($explo[0])) { $explo[0] = '00';}
        if (strlen($explo[0]) == '1') { $explo[0] = '0'.$explo[0];}
    $formated = $explo[0].':'.$explo[1].':'.$explo[2];
    return $formated;
}

## Formater une date (venant de DB) ou date = timedate
function fdatetime($jour) {
    $explo1 = explode(" ", $jour);
    $explo = explode("-", $explo1[0]);
    $formated = $explo[2].'/'.$explo[1].'/'.$explo[0];
    if ($formated == '//'){$formated = '';}
    if ($formated == '00/00/0000'){$formated = '';}
    return $formated;
}

## Formater une date + heure (venant de DB) ou date = timedate
function fdatetime2($jour) {
    $explo1 = explode(" ", $jour); # séparation date et heure
    $explo = explode("-", $explo1[0]); # séparation dans la date
    $explo2 = explode(":", $explo1[1]); # séparation dans l'heure
    $formated = $explo[2].'/'.$explo[1].'/'.$explo[0].' ('.$explo2[0].':'.$explo2[1].')';
    if ($formated == '00/00/0000 (:)'){$formated = '';}
    if ($formated == '// (:)'){$formated = '';}
    return $formated;
}

## Formater une date (venant de DB)
function fdate($jour) {
    if (($jour != '0000-00-00') and (!empty($jour))) {
        $explo = explode("-", $jour);
        $formated = $explo[2].'/'.$explo[1].'/'.$explo[0];
    }

    if (!isset($formated)) $formated = '';

    return $formated;
}

## Formater une date (VERS la DB)
function fdatebk($jour) {
    $jour = trim($jour);
    $from = array(" ", ".", "-", ",", ";");
    $jour = str_replace($from, "/", $jour);

    $yearencours = date("Y");
    $explo = explode("/", $jour);
        if (empty($explo[1])) $explo[1] = '';
        if (empty($explo[2])) $explo[2] = '';

        if (strlen(trim($explo[2])) == '0') { $explo[2] = $yearencours;}
        if (strlen(trim($explo[2])) == '1') { $explo[2] = '200'.$explo[2];}
        if ((strlen(trim($explo[2])) == '2') and ($explo[2] > 25)) { $explo[2] = '19'.$explo[2];}
        if ((strlen(trim($explo[2])) == '2') and ($explo[2] <= 25)) { $explo[2] = '20'.$explo[2];}
        if (strlen(trim($explo[1])) == '1') { $explo[1] = '0'.$explo[1];}
        if (strlen(trim($explo[0])) == '1') { $explo[0] = '0'.$explo[0];}
    $formated = trim($explo[2]).'-'.trim($explo[1]).'-'.trim($explo[0]);
    if ($formated == $yearencours.'--'){$formated = '';}
    return $formated;
}

## Formater une date (VERS la DB)
function fannifbk($jour) {
    $explo = explode("/", $jour);
        if (strlen($explo[1]) == '1') { $explo[1] = '0'.$explo[1];}
        if (strlen($explo[0]) == '1') { $explo[0] = '0'.$explo[0];}
    $formated = $explo[1].'-'.$explo[0];
    if ($formated == '-') {$formated = '';}
    return $formated;
}

## Formater un GSM (venant de DB)
function fgsm($phone) {
    $arrayfrom = array(".", "/", "=", "-", " ");
        $formated = str_replace($arrayfrom, "", $phone);
    return $formated;
}

## Formater un Montant et ne pas afficher les 0 (venant de DB)
function feuro ($montant) {
    if ($montant > 0) {
        $mnt = number_format($montant, 2, ',', ' ');
        return $mnt.' &euro;';
    }
}

## Formater un Montant et ne pas afficher les 0 (venant de DB)
function feurotcpdf ($montant) {
    if ($montant > 0) {
        $mnt = number_format($montant, 2, ',', ' ');
        return $mnt.' €';
    }
}

function fnbrFMP ($montant) {
    if ($montant > 0) {
        $arrayfrom = array(".", " ");
            $montant = str_replace($arrayfrom, "", $montant);
            $montant = str_replace(",", ".", $montant);


        $mnt = number_format($montant, 2, '.', '');
        return $mnt;
    }
}

## Formater un Montant et ne pas afficher les 0 (venant de DB) pour l'impression
function fpeuro ($montant) {
    if ($montant > 0) {
        $mnt = number_format($montant, 2, ',', ' ');
        return $mnt.' Eur';
    }
    if ($montant < 0) {
        $mnt = number_format($montant, 2, ',', ' ');
        return $mnt.' Eur';
    }
}

## Formater un nombre et ne pas afficher les 0 (venant de DB)
function fnbr ($montant) {
    if (($montant > 0) OR ($montant < 0)) {
        if (strstr($montant, '.')) {
            $explo = explode(".", $montant);
            if ($explo[1] > 0) {
                $mnt = number_format($montant, 2, ',', ' ');
            } else {
                $mnt = $explo[0];
            }
        } else {
            $mnt = $montant;
        }

    return $mnt;
    } else { return '0';}
}

## Formater un nombre et ne pas afficher les 0 (venant de DB)
function fnbr0 ($montant, $decim=0) {
    if (($montant > 0) OR ($montant < 0)) {
        $explo = explode(".", $montant);
        if (($explo[1] > 0) or ($decim > 0)) {
            if (empty($decim)) $decim = 2;
            $mnt = number_format($montant, $decim, ',', ' ');
        } else {
            $mnt = $explo[0];
        }
        return $mnt;
    }
}

## Formater un nombre les , en . (VERS la DB)
function fnbrbk ($montant) {
    if (strstr($montant, ",")) {
        $montant = preg_replace("/[^0-9,\-]/", "", $montant);
        $mnt = preg_replace("/,/", ".", $montant);
    } elseif (strstr($montant, ".")) {
        $mnt = preg_replace("/[^0-9\.\-]/", "", $montant);
    } else {
        $mnt = preg_replace("/[^0-9\-]/", "", $montant);
    }
    return $mnt;
}

## Formater un nombre les , en . (VERS la DB)
function fnbrFMPbk ($montant) {
    $montant = str_replace(" ", "", $montant);
    $montant = str_replace(".", "", $montant);
    $montant = fnbrbk($montant);
    return $montant;
}

## Formatage des accents pour affichage PDF

function ftxtpdf ($chaine) {
    $chaine = str_replace("€", "Eur", $chaine);
    return $chaine;
}

## Formater un nombre ngatif
function fnega($nombre) {
    $oknombre = fnbrbk($nombre);
    if ($oknombre < 0) {
        $formated = '<Font color="red">'.$nombre.'</font>';
    } else {
        $formated = $nombre;
    }
    return $formated;
}

## Formater une idfacture pour print
function ffac($idfacture) {
        if (strlen($idfacture) == '2') { $zero = '000';}
        if (strlen($idfacture) == '3') { $zero = '00';}
        if (strlen($idfacture) == '4') { $zero = '0';}
    $formated = $zero.$idfacture;
    return $formated;
}

## Formater un numero de compte
function fbanque($compte) {
    #if (sizeof($compte)>9)
        return substr($compte, 0, 3).'-'.substr($compte, 3, 7).'-'.substr($compte, 10, 2);
    #else return $compte;
}

## Formater usequence de variable en explode
function fsearchbk($vara) {
    $explo = explode(";", $vara);
        foreach ($explo as $x)
        {
        $varb = explode("=", $x);
        $var[$varb[0]] = $varb[1];
        }
    return $var;
}

function weekdate ($sem, $year = 'D') {
    if ($year == 'D') $year = date ("Y");

    $ajust = array(
        '2000' => '-4',
        '2001' => '+1',
        '2002' => '+0',
        '2003' => '-1',
        '2004' => '-2',
        '2005' => '-4',
        '2006' => '-5',
        '2007' => '-6',
        '2008' => '+0',
        '2009' => '-2',
        '2010' => '-3',
        '2011' => '-4',
        '2012' => '2',
        '2013' => '0',
        '2014' => '-1',
        '2015' => '-2',
        '2016' => '-3',
        '2017' => '-5',
        '2018' => '1',
        '2019' => '0',
        '2020' => '-1',
    );

    $ajustsem = array(
        '2004' => '-1',
        '2005' => '0',
        '2006' => '0',
        '2007' => '0',
        '2008' => '-1',
        '2009' => '-1',
        '2010' => '0',
        '2011' => '0',
        '2012' => '-1',
        '2013' => '-1',
        '2014' => '-1',
        '2015' => '-1',
        '2016' => '0',
        '2017' => '0',
        '2018' => '-1',
        '2019' => '-1',
        '2020' => '-1',
    );

    if ($year > 2020) echo '<br>weekdate error : Ne fonctionne que jusqu\'en 2020<br>';

     $dates['lun'] = date ("Y-m-d", mktime (0,0,0,1,(($sem + $ajustsem[$year]) * 7) + $ajust[$year] + 0, $year));
     $dates['mar'] = date ("Y-m-d", mktime (0,0,0,1,(($sem + $ajustsem[$year]) * 7) + $ajust[$year] + 1, $year));
     $dates['mer'] = date ("Y-m-d", mktime (0,0,0,1,(($sem + $ajustsem[$year]) * 7) + $ajust[$year] + 2, $year));
     $dates['jeu'] = date ("Y-m-d", mktime (0,0,0,1,(($sem + $ajustsem[$year]) * 7) + $ajust[$year] + 3, $year));
     $dates['ven'] = date ("Y-m-d", mktime (0,0,0,1,(($sem + $ajustsem[$year]) * 7) + $ajust[$year] + 4, $year));
     $dates['sam'] = date ("Y-m-d", mktime (0,0,0,1,(($sem + $ajustsem[$year]) * 7) + $ajust[$year] + 5, $year));
     $dates['dim'] = date ("Y-m-d", mktime (0,0,0,1,(($sem + $ajustsem[$year]) * 7) + $ajust[$year] + 6, $year));

    return $dates;
}

# fonctions de formatage de heure 1:30 = 1,5
function htonum ($heures) {
    $h1 = explode (':', $heures);
    $hin = $h1[0] + ($h1[1] / 60);

    return $hin;
}

# fonctions d'affichage de page dans $_SERVER['PHP_SELF']
function pagename ($pagex) {
    $page = array_reverse(explode('/',$pagex));
    $page = $page[0];

    return $page;
}

# fonctions (crado?) de redirection HTML
function MetaRedirect ($url, $time = 0) {
    $redirect = '<meta content="'.$time.'; URL='.$url.'" http-equiv="Refresh" />';
    return $redirect;
}
?>