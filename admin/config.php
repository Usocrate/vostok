<?php

require_once '../config/boot.php';
require_once '../classes/System.class.php';
$system = new System( '../config/host.json' );

if ($system->configFileExists ()) {
	$system->parseConfigFile ();
} else {
	/*
	 * On propose des valeurs par défaut.
	 */
	$system->setDbName ( 'usocrate_vostok' );
	$system->setDbUser ( 'root' );
	$system->setDbHost ( 'localhost' );
	
	$system->setAppliName ( 'Vostok' );
	$system->setAppliDescription ( 'Mon module de prospection commerciale' );
	$system->setAppliUrl ( $_SERVER ['REQUEST_SCHEME'] . '://' . $_SERVER ['HTTP_HOST'] . $_SERVER ['CONTEXT_PREFIX'] );
	
	$path = '../';
	$system->setDirPath ( realpath ( $path ) );
}

if (isset ( $_POST ['task_id'] )) {
	$fb = new UserFeedBack ();
	ToolBox::formatUserPost ( $_POST );
	switch ($_POST ['task_id']) {
		case 'save' :
			if (isset ( $_POST ['db_host'] )) {
				$system->setDbHost ( $_POST ['db_host'] );
			}
			if (isset ( $_POST ['db_name'] )) {
				$system->setDbName ( $_POST ['db_name'] );
			}
			if (isset ( $_POST ['db_user'] )) {
				$system->setDbUser ( $_POST ['db_user'] );
			}
			if (isset ( $_POST ['db_password'] )) {
				$system->setDbPassword ( $_POST ['db_password'] );
			}
			if (isset ( $_POST ['appli_url'] )) {
				$system->setAppliUrl ( $_POST ['appli_url'] );
			}
			if (isset ( $_POST ['appli_name'] )) {
				$system->setAppliName ( $_POST ['appli_name'] );
			}
			if (isset ( $_POST ['appli_description'] )) {
				$system->setAppliDescription ( $_POST ['appli_description'] );
			}
			if (isset ( $_POST ['googlemaps_api_key'] )) {
				$system->setGoogleMapsApiKey( $_POST ['googlemaps_api_key'] );
			}			
			if (isset ( $_POST ['dir_path'] )) {
				$system->setDirPath ( $_POST ['dir_path'] );
			}
			if ($system->saveConfigFile ()) {
				$fb->addSuccessMessage ( 'Configuration enregistrée.' );
			} else {
				$fb->addDangerMessage ( 'Echec de l\'enregistrement de la configuration.' );
			}
			break;
	}
}
include_once '../config/boot.php';
header ( 'charset=utf-8' );
?>
<!doctype html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0" />
	<meta name="description" content="<?php echo $system->getAppliDescription() ?>" />
	<title><?php echo $system->getAppliName().' : '.$system->getappliDescription() ?></title>
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
	<link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
</head>
<body>
<?php require 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1>Configuration</h1>
	<?php
	if (isset ( $fb )) {
		echo '<div>';
		echo $fb->AllMessagesToHtml ();
		echo '</div>';
	}
	?>
	<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
		<div class="row">
			<div class="col-md-6">
				<fieldset>
					<legend>Projet</legend>
					<div class="form-group">
						<label for="appli_url_i">Url</label><input id="appli_url_i" id="appli_url_i" type="url" name="appli_url" class="form-control" value="<?php echo ToolBox::toHtml($system->getAppliUrl()); ?>" />
					</div>
					<div class="form-group">
						<label for="appli_name_i">Nom</label><input id="appli_name_i" type="text" name="appli_name" class="form-control" value="<?php echo ToolBox::toHtml($system->getAppliName()); ?>" />
					</div>
					<div class="form-group">
						<label for="appli_description_i">Description</label><input id="appli_description_i" type="text" name="appli_description" class="form-control" value="<?php echo ToolBox::toHtml($system->getAppliDescription()); ?>" />
					</div>
				</fieldset>
				<fieldset>
					<legend>Base de données</legend>
					<div class="alert alert-info">NB : Les mêmes identifiants seront demandés pour accéder aux zones sécurisées de l'application.</div>
					<div class="form-group">
						<label for="db_name_i">Nom</label><input id="db_name_i" type="text" name="db_name" class="form-control" value="<?php echo ToolBox::toHtml($system->getDbName()); ?>" />
					</div>
					<div class="form-group">
						<label for="db_user_i">Utilisateur</label><input id="db_user_i" type="text" name="db_user" class="form-control" value="<?php echo ToolBox::toHtml($system->getDbUser()); ?>" />
					</div>
					<div class="form-group">
						<label for="db_password_i">Mot de passe</label><input id="db_password_i" type="password" name="db_password" class="form-control" value="<?php echo ToolBox::toHtml($system->getDbPassword()); ?>" />
					</div>
					<div class="form-group">
						<label for="db_host_i">Hôte</label><input id="db_host_i" type="text" name="db_host" class="form-control" value="<?php echo ToolBox::toHtml($system->getDbHost()); ?>" />
					</div>
				</fieldset>
			</div>
			<div class="col-md-6">
				<fieldset>
					<legend>Chemin d'accès aux fichiers</legend>
					<div class="form-group">
						<label for="dir_path_i">Répertoire où l'application est installée</label><input id="dir_path_i" type="text" name="dir_path" class="form-control" value="<?php echo ToolBox::toHtml($system->getDirPath()); ?>" />
					</div>
				</fieldset>
				<fieldset>
					<legend>Google Maps</legend>
					<div class="form-group">
						<label for="googlemaps_api_key_i">Clé</label><input id="googlemaps_api_key_i" type="text" name="googlemaps_api_key" class="form-control" value="<?php echo ToolBox::toHtml($system->getGoogleMapsApiKey()); ?>" />
					</div>
				</fieldset>				
			</div>
			<a href="../index.php" class="btn btn-link">Quitter</a>
			<button name="task_id" type="submit" value="save" class="btn btn-primary">Enregistrer</button>
		</div>
	</form>
</div>
</body>
</html>