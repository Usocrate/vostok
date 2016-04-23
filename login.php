<?php
require 'config/main.inc.php';

session_start();
ToolBox::getDBAccess();

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
	} else {
		$messages[] = 'L\'utilisateur n\'est <em>pas identifié</em>';
	}
} else {
	header('Location:index.php');
	exit;
}
$doc_title = 'Il faut s\'identifier';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo APPLI_NAME.' : identification utilisateur'; ?></title>
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <script type="text/javascript" src="<?php echo YUI_SEEDFILE_URI ?>"></script>
    <link rel="stylesheet" href="<?php echo SKIN_URL ?>main.css" type="text/css">
<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script><script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script></head>

<body id="loginDoc" >
<div class="container-fluid">	
	<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>
	
	<div>
		<p><strong><?php echo ToolBox::toHtml(APPLI_NAME); ?> </strong> est l'outil de prospection <a href="http://www.usocrate.fr" title="Lien vers maison-mère">Usocrate.fr</a>.</p>
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
	
	<script type="text/javascript">
    	YUI().use("node", function (Y) {
    		Y.on('domready', function () {
    			Y.one('#user_name_i').focus();
    		});
    	});
	</script>
</div>
</body>
</html>
