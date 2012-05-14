<?php

define ("BASE_DIR", dirname($_SERVER["SCRIPT_FILENAME"]));
define ("RUTA_DESCARGA", BASE_DIR . '/download');
define ("JSON_DESCARGAS", BASE_DIR .'/descargas.json');
//define ("JSON_WEBDESCARGAS", BASE_DIR .'/../../www/sites/default/files/descargas.json');
define ("JSON_WEBDESCARGAS", BASE_DIR .'/web_descargas.json');

$bDoDownload = true; // Debug variable: true for actually download of files

/**
 * Downloads a file and saves it in a local disk
 * @param $file_source URL of the remote file to be downloaded
 * @param $file_target local path of the saved file
 * @return true on download success, false on failure
 */
function download($file_source, $file_target) {
	$rh = fopen($file_source, 'rb');
	$wh = fopen($file_target, 'wb');
	if ($rh===false || $wh===false) {
		// error reading or opening file
		return false;
	}
	while (!feof($rh)) {
		if (fwrite($wh, fread($rh, 1024)) === FALSE) {
			// 'Download error: Cannot write to file ('.$file_target.')';
			return false;
		}
	}
	fclose($rh);
	fclose($wh);
	// No error
	return true;
}

/**
 * Format a size in bytes a human readable power of 2
 * @param $size the size in bytes to be formatted
 * @return an string with the size figure (always lower than 1024) and a measure unit (KB, MB, GB, etc.)
 */
function format_bytes($size) {
	$sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
	if ($size == 0) { return('n/a'); } else {
	return (round($size/pow(1024, ($i = floor(log($size, 1024)))), $i > 1 ? 2 : 0) . $sizes[$i]); }
}

/****************************/
/* Main program begins here */
/****************************/

$aProducts = array('Firefox', 'Thunderbird', 'SeaMonkey');
$aPlatforms = array('win', 'linux', 'osx');
$conf = array();
$jsonData = array();
$mailBody = "";
$jsonUpdated = FALSE;

// Loads the existing JSON info file in case it exists
if (file_exists(JSON_DESCARGAS)) {
  $conf = json_decode(file_get_contents(JSON_DESCARGAS), true);
}

// For each product, we complete or replace JSON data for it, leaving version
// untouched until later
foreach($aProducts as $item) {
  $token = strtolower($item);
  switch($token) {
    case 'firefox':
    case 'thunderbird':
      $conf[$token]['url_json'] = sprintf('http://www.mozilla.org/includes/product-details/json/%s_versions.json', $token);
      $conf[$token]['patron_descarga']['xpi'] = sprintf('http://releases.mozilla.org/pub/mozilla.org/%1$s/releases/%%s/linux-i686/xpi/es-ES.xpi',
                                                        $token);
      break;
    case 'seamonkey':
      $conf[$token]['url_json'] = 'http://www.seamonkey-project.org/seamonkey_versions.json';
      $conf[$token]['patron_descarga']['xpi'] = 'ftp://ftp.mozilla.org/pub/mozilla.org/seamonkey/releases/%1$s/langpack/seamonkey-%1$s.es-ES.langpack.xpi';
      break;
  }
  foreach($aPlatforms as $platform) {
    $conf[$token]['patron_descarga'][$platform] = sprintf('http://download.mozilla.org/?product=%1$s-%%s&os=%2$s&lang=es-ES',
                                                          $token, $platform);    
  }
}

/*
$conf['firefox']['url_json'] = 'http://www.mozilla.org/includes/product-details/json/firefox_versions.json';
$conf['firefox']['patron_descarga']['windows'] = 'http://download.mozilla.org/?product=firefox-%s&os=win&lang=es-ES';
$conf['firefox']['patron_descarga']['linux'] = 'http://download.mozilla.org/?product=firefox-%s&os=linux&lang=es-ES';
$conf['firefox']['patron_descarga']['macos'] = 'http://download.mozilla.org/?product=firefox-%s&os=osx&lang=es-ES';
$conf['firefox']['patron_descarga']['xpi'] = 'http://releases.mozilla.org/pub/mozilla.org/firefox/releases/%s/linux-i686/xpi/es-ES.xpi';
$conf['firefox']['version'] = '8.0.1';
$conf['firefox']['md5_hash']['windows'] = 'dab9aae728fafcb487ca84aef9bb8b48';
$conf['firefox']['md5_hash']['linux'] = 'dab9aae728fafcb487ca84aef9bb8b48';
$conf['firefox']['md5_hash']['macos'] = 'dab9aae728fafcb487ca84aef9bb8b48';
$conf['firefox']['md5_hash']['xpi'] = 'dab9aae728fafcb487ca84aef9bb8b48';
$conf['firefox']['hr_size']['windows'] = '14 MB';
$conf['firefox']['hr_size']['linux'] = '15 MB';
$conf['firefox']['hr_size']['macos'] = '23 MB';
$conf['firefox']['hr_size']['xpi'] = '490 KB';


$conf['thunderbird']['url_json'] = 'http://www.mozilla.org/includes/product-details/json/thunderbird_versions.json';
$conf['thunderbird']['patron_descarga']['windows'] = 'http://download.mozilla.org/?product=thunderbird-%s&os=win&lang=es-ES';
$conf['thunderbird']['patron_descarga']['linux'] = 'http://download.mozilla.org/?product=thunderbird-%s&os=linux&lang=es-ES';
$conf['thunderbird']['patron_descarga']['macos'] = 'http://download.mozilla.org/?product=thunderbird-%s&os=osx&lang=es-ES';
$conf['thunderbird']['patron_descarga']['xpi'] = 'http://releases.mozilla.org/pub/mozilla.org/thunderbird/releases/%s/linux-i686/xpi/es-ES.xpi';

$conf['seamonkey']['url_json']   = 'http://www.seamonkey-project.org/seamonkey_versions.json';
$conf['seamonkey']['patron_descarga']['windows'] = 'http://download.mozilla.org/?product=seamonkey-%s&os=win&lang=es-ES';
$conf['seamonkey']['patron_descarga']['linux'] = 'http://download.mozilla.org/?product=seamonkey-%s&os=linux&lang=es-ES';
$conf['seamonkey']['patron_descarga']['macos'] = 'http://download.mozilla.org/?product=seamonkey-%s&os=osx&lang=es-ES';
$conf['seamonkey']['patron_descarga']['xpi'] = 'ftp://ftp.mozilla.org/pub/mozilla.org/seamonkey/releases/%s/langpack/seamonkey-%s.es-ES.langpack.xpi';
*/

// Now we complete and update the product JSON data
$mailBody .= "Comenzando la descarga en " .RUTA_DESCARGA ."\n\n";
if (!file_exists(RUTA_DESCARGA)) {
  mkdir(RUTA_DESCARGA, 0700);
} else {
  if (!is_dir(RUTA_DESCARGA)) {
    unlink(RUTA_DESCARGA);
    mkdir(RUTA_DESCARGA, 0700);
  }
}

foreach($aProducts as $item) {
  $token = strtolower($item);
  
  $mailBody .= "{$item} - Descargando datos JSON... ";
  $jsonData[$token] = json_decode(file_get_contents($conf[$token]['url_json']));
  
  switch($token) {
    case 'firefox': $version = $jsonData[$token]->LATEST_FIREFOX_VERSION; break;
    case 'thunderbird': $version = $jsonData[$token]->LATEST_THUNDERBIRD_VERSION; break;
    case 'seamonkey': $version = $jsonData[$token]->LATEST_SEAMONKEY_VERSION; break;
  }

  // We recover the latest version information from Mozilla (or SeaMonkey) website
  $jsonData[$token] = json_decode(file_get_contents($conf[$token]['url_json']));
  $mailBody .= "La última versión es {$version}.";
  
  if ((!array_key_exists('version', $conf[$token])) || ($version != $conf[$token]['version'])) {
    // We have a new version, so let's update it in the product JSON data
    $conf[$token]['version'] = $version;

    $mailBody .= " Nueva versión -> Descargando binarios...\n";

    foreach ($conf[$token]['patron_descarga'] as $idSO => $sPatronDescarga) {
    	$file_source = sprintf($sPatronDescarga, $version);
    	$file_target = RUTA_DESCARGA ."/" .$token ."_" .$idSO .".bin";
    	$mailBody .= "- Descargando binario de $idSO... ";

    	if ($bDoDownload) {
    		download($file_source, $file_target);
    	}
    	if (is_file($file_target)) {
      	$conf[$token]['url_descarga'][$idSO] = $file_source;
        $conf[$token]['md5_hash'][$idSO] = md5_file($file_target);
        $conf[$token]['hr_size'][$idSO] = format_bytes(filesize($file_target));
    	  
    		$mailBody .= "OK: " .basename($file_target)
    		    ." " .$conf[$token]['hr_size'][$idSO]
    		    ." " .$conf[$token]['md5_hash'][$idSO] ."\n";
    		    
    	  unlink($file_target);
    	}
    }
    file_put_contents(JSON_DESCARGAS, json_encode($conf));
    $jsonUpdated = TRUE;
  } else {
    $mailBody .= " OK, al día.\n";
  }
  $mailBody .= "\n";
}

// We update the file in the web only if it has been updated or
// it doesn't exist
if (($jsonUpdated) || (!file_exists(JSON_WEBDESCARGAS))) {
  if (!(copy(JSON_DESCARGAS, JSON_WEBDESCARGAS))) {
    $mailBody .= "\nERROR - No se ha podido copiar el archivo descargas.json\n"
                 ."a su ubicación definitiva\n\n";
  }
}

if (!mail("aaa@gmail.com, bbb@proyectonave.es",
     "Proyectonave.es - Informe de descargas automáticas",
     $mailBody, "From: noreply@proyectonave.es\r\n"
               . "Content-Type: text/plain; charset=UTF-8\r\n"
               . "Content-Transfer-Encoding: quoted-printable")) {
       
  $mailBody .= "ERROR - No se ha podido enviar el mensaje de correo.\n\n";
  file_put_contents(BASE_DIR . "/descargas_error.txt", $mailBody);     
}
?>
