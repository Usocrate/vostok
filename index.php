<?php
require_once 'config/main.inc.php';

session_start();
ToolBox::getDBAccess();

if (empty($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $user = new User($_SESSION['user_id']);
    $user->feed();
}

$doc_title = 'Accueil';
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo APPLI_NAME.' : '.ToolBox::toHtml($doc_title) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo SKIN_URL ?>main.css" type="text/css">
</head>
<body>
	<header>
		<div class="brand">
			<a href="<?php echo APPLI_URL?>"><?php echo ToolBox::toHtml(APPLI_NAME) ?></a>
		</div>
	</header>
	
	<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>
	
	<div class="row">
		<div class="col-md-6">
			<section>
				<h2>Les derniers évènements enregistrés</h2>
				<?php echo EventCollection::getLastHistoryEvents()->toHtml(); ?>
			</section>
		</div>
		<div class="col-md-6">
			<section>
				<h2>Les prochains évènements plannifiés</h2>
				<?php echo EventCollection::getNextPlanningEvents()->toHtml(); ?>
			</section>
		</div>
	</div>
	
	<footer><?php include 'menu.inc.php'; ?></footer>
</body>
</html>