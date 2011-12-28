<?php
define ("JSON_DESCARGAS", "sites/default/files/descargas.json");
if (file_exists(JSON_DESCARGAS)) {
$aDescargas = json_decode(file_get_contents(JSON_DESCARGAS), true);
$aDescargas['firefox']['branding']       = 'Mozilla Firefox';
$aDescargas['firefox']['logo']           = '/sites/default/files/_media/ico-ff.png';
$aDescargas['firefox']['slogan']         = 'El navegador de la nueva generación';
$aDescargas['firefox']['urlProduct']     = '/productos/firefox';

$aDescargas['thunderbird']['branding']   = 'Mozilla Thunderbird';
$aDescargas['thunderbird']['logo']       = '/sites/default/files/_media/ico-tb.png';
$aDescargas['thunderbird']['slogan']     = 'Correo, noticias y canales web en una interfaz sencilla y potente';
$aDescargas['thunderbird']['urlProduct'] = '/productos/thunderbird';

$aDescargas['seamonkey']['branding']     = 'SeaMonkey';
$aDescargas['seamonkey']['logo']         = '/sites/default/files/_media/ico-sm.png';
$aDescargas['seamonkey']['slogan']       = 'La suite de Internet con la mejor tecnología';
$aDescargas['seamonkey']['urlProduct']   = '/productos/seamonkey';

$sTpl = '';
foreach($aDescargas as $id => $aProducto) {
	$sTpl .= '<dt id="'.$id.'"><a href="'.$aProducto['urlProduct'].'"><img src="'.$aProducto['logo'].'" alt="'.$aProducto['branding'].' '.$aProducto['version'].'" /></a><a href="'.$aProducto['urlProduct'].'" class="producto">'.$aProducto['branding'].' '.$aProducto['version'].'</a> - <em>'.$aProducto['slogan'].'</em></dt>
	<dd>
	<p>Descargas: <a href="'.htmlspecialchars($aProducto['url_descarga']['win']).'">Windows</a> ('.$aProducto['hr_size']['win'].'), <a href="'.htmlspecialchars($aProducto['url_descarga']['linux']).'">Linux</a> ('.$aProducto['hr_size']['linux'].'), <a href="'.htmlspecialchars($aProducto['url_descarga']['osx']).'">Mac OS X</a> ('.$aProducto['hr_size']['osx'].'), <a href="'.htmlspecialchars($aProducto['url_descarga']['xpi']).'">XPI de idioma</a> ('.$aProducto['hr_size']['xpi'].').</p>
	<p class="md5s"><strong>MD5</strong>: <strong>Win32</strong> '.$aProducto['md5_hash']['win'].' - <strong>Linux</strong> '.$aProducto['md5_hash']['linux'].' - <br /><strong>Mac OS X</strong> '.$aProducto['md5_hash']['osx'].' - <strong>XPI</strong> '.$aProducto['md5_hash']['xpi'].'</p>

	</dd>';
}
echo $sTpl;
} else { // No se ha podido cargar el archivo JSON
	echo "</dl>\n";
	echo "<div class='warning'><strong>Error</strong>: debido a un problema técnico, no podemos suministrar los enlaces a las descargas de Firefox, Thunderbird y SeaMonkey. Disculpe las molestias.</div>\n";
	echo "<dl>\n";
}
?>