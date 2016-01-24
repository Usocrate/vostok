<?php
require_once 'config/main.inc.php';

session_start();
ToolBox::getDBAccess();

if (empty ($_SESSION['user_id'])) {
	header('Location:login.php');
	exit;
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
    <link rel="stylesheet" type="text/css" href="<?php echo PURE_SEEDFILE_URI ?>">
    <link rel="stylesheet" href="<?php echo SKIN_URL ?>main.css" type="text/css">
    <!-- <link rel="stylesheet" href="<?php echo SKIN_URL ?>pure-skin-vostok.css" type="text/css"> -->
</head>
<body class="pure-skin-vostok">
	<div class="pure-g-r">
		<div class="pure-u-1 ban">
			<header><div class="brand"><a href="<?php echo APPLI_URL?>"><?php echo ToolBox::toHtml(APPLI_NAME) ?></a></div><?php echo ToolBox::toHtml($doc_title); ?></header>
		</div>
		<div class="pure-u-1-2">
			<section>
				<h2>Les derniers évènements enregistrés</h2>
				<?php echo EventCollection::getLastHistoryEvents()->toHtml(); ?>
			</section>
		</div>
		<div class="pure-u-1-2">
			<section>
				<h2>Les prochains évènements plannifiés</h2>
				<?php echo EventCollection::getNextPlanningEvents()->toHtml(); ?>
			</section>
		</div>
		<div class="pure-u-1">
			<footer><?php include 'menu.inc.php'; ?></footer>
		</div>
	</div>
</body>
</html>