<?php
require_once '../config/boot.php';
require_once '../classes/System.class.php';
$system = new System( '../config/host.json' );

// récupération des utilisateurs
$users = $system->getUsers();

?>
<!doctype html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0" />
	<title>Génération de mot de passe htaccess</title>
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
</head>
<body>
<?php require 'navbar.inc.php'; ?>
<div class="container-fluid">
		<h1>Génération htpasswd</h1>
		<blockquote>Chacun des utilisateurs enregistrés en base de données se voit attribuer un compte dans le fichier .htpassword pour pouvoir accéder aux répertoires à accès restreint.</blockquote>
<?php
//
// écriture du fichier
//
ignore_user_abort(true);
$fp = fopen('../config/.htpasswd', "w+");
if (flock($fp, LOCK_EX)) {
    echo '<ul>';
    foreach ($users as $user) {
        $item = $user->getName() . ':' . crypt($user->getPassword());
        if (fputs($fp, "$item\n")) {
            echo '<li>' . $user->getName() . '<small> : ' . crypt($user->getPassword()) . '</small></li>';
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
</div>
</body>
</html>