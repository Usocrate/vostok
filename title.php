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

require_once 'config/boot.php';

session_start();

if (empty ($_SESSION['user_id'])) {
	header('Location:login.php');
	exit;
} else {
	$user = new User($_SESSION['user_id']);
	$user->feed();
}

$title = empty($_REQUEST['title']) ? null : $_REQUEST['title'];

$memberships = $system->getMembershipHavingThatTitle($title);

$doc_title = $title;

//print_r($_SESSION);
//print_r($preferences);
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php ToolBox::toHtml($doc_title) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo MASONRY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo IMAGESLOADED_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1 class="bd-title"><?php echo ToolBox::toHtml(ucfirst($doc_title)); ?></h1>
    <section>
        <?php
			//print_r($memberships);
			$data = $system->getMembershipTitleAverageDuration($title);
			if ($data) {
				echo '<p>Poste occupé en moyenne pendant ';
				echo $data['avg'] > 1 ? $data['avg'].' ans' : $data['avg'].' an';
				echo '.</p>';
			}
			
			echo '<table class="table">';
			echo '<thead><tr><th>Qui ?</th><th>Où ?</th><th>Quand ?</th><tr></thead>';
			echo '<tbody>';
			foreach ($memberships as $m) {
				echo '<tr>';
				echo '<td>'.$m->getIndividual()->getHtmlLinkToIndividual().'</td>';
				echo '<td>'.$m->getSociety()->getHtmlLinkToSociety().'</td>';
				echo '<td>'.$m->getPeriod().'</td>';
				echo '</tr>';
			}
			echo '</tbody>';
			echo '</table>';
        ?>
	</section>
</div>
<script type="text/javascript">
	$(document).ready(function() {
	});
</script>
</body>
</html>