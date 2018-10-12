<?php
function __autoload($class_name) {
	$path = './classes/';
	if (is_file ( $path . $class_name . '.class.php' )) {
		include_once $path . $class_name . '.class.php';
	} elseif ($path . $class_name . '.interface.php') {
		include_once $path . $class_name . '.interface.php';
	}
}
$system = new System( './config/host.json' );

require 'config/boot.php';

session_start();

$messages = array();

// demande d'anonymat
if (isset($_REQUEST['anonymat_submission'])) {
	unset($_SESSION['user_id']);
}
// authentification de l'utilisateur
if (empty($_SESSION['user_id'])) {
	if (isset($_POST['login_submission'])) {
		$user = new User();
		if ($user->identification($_POST['user_name'], $_POST['user_password'])) {
			// authentification suite à soumission du formulaire d'identification
			$_SESSION['user_id'] = $user->getId();
			header('Location:index.php');
			exit;
		}
	}
} else {
	header('Location:index.php');
	exit;
}
$doc_title = $system->getAppliName();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo ToolBox::toHtml($system->getAppliName()).' : identification utilisateur'; ?></title>
    <link type="text/css" rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" integrity="<?php echo BOOTSTRAP_CSS_URI_INTEGRITY ?>" crossorigin="anonymous"></link>
	<link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
</head>
<body id="loginDoc" >
<div class="container">	
	<h1 class="brand"><?php echo ToolBox::toHtml($doc_title); ?></h1>
	
	<div>
		<p><strong><?php echo ToolBox::toHtml($system->getAppliName()); ?> </strong> est l'outil de prospection <a href="http://www.usocrate.fr" title="Lien vers maison-mère">Usocrate.fr</a>.</p>
		<?php foreach ($messages as $m) echo '<p>'.$m.'</p>'; ?>
	</div>
	
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<div class="form-group">
    		<label for="user_name_i">Identifiant</label>
    		<input id="user_name_i" name="user_name" type="text" class="form-control" />
		</div>
		<div class="form-group">
    		<label for="user_password_i">Mot de passe</label>
    		<input id="user_password_i" name="user_password" type="password" class="form-control" />
		</div>
		<button name="login_submission" type="submit" value="1" class="btn btn-primary">Lancement</button>
	</form>
	
	<footer><q>Reçu. Sens bien, excellent état d'esprit, prêt à y aller</q><br /> <cite>Youri Gagarine, 12 avril 1961</cite></footer>
</div>
<script>
	$(document).ready(function() {
		$('#user_name_i').focus();
	});
</script>
</body>
</html>
