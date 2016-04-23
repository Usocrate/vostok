<?php
require 'main.inc.php';
include_once '../class/ToolBox.class.php';
include_once '../class/system.class.php';

ToolBox::getDBAccess();

// récupération des utilisateurs
$users = $system->getUsers();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Génération de mot de passe htaccess</title>
<link rel="stylesheet" href="<?php echo SKIN_URL ?>standalone.css" type="text/css" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"><link rel="stylesheet" href="<?php echo SKIN_URL ?>main.css" type="text/css"></head>
<body>
<h1><?php echo $system->getHtmlLink(); ?> Génération de mot de passe htaccess</h1>
<blockquote>
Chacun des utilisateurs enregistrés en base de données se voit attribuer un compte dans le fichier .htpassword pour pouvoir accéder aux répertoires à accès restreint.
</blockquote>
<?php
//
// écriture du fichier
//
ignore_user_abort(true);
$fp = fopen('.htpasswd', "w+");
if (flock($fp, LOCK_EX)) {
	echo '<ul style="font-size:1.5em">';
	foreach ($users as $user) {
		$item = $user->getName().':'.crypt($user->getPassword());
		if (fputs($fp, "$item\n")) {
			echo '<li>'.$user->getName().'<small> : '.crypt($user->getPassword()).'</small></li>';
		}
	}
	echo '</ul>';	
	flock($fp, LOCK_UN); // ouverture du verrou
} else {
	trigger_error('Le fichier .htpasswd est verrouillé !', E_USER_WARNING);
}
fclose($fp);
ignore_user_abort(false);
?>
</body>
</html>
