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


$individual = new Individual($_REQUEST['individual_id']);
$individual->feed();
$memberships = $individual->getMemberships();

$doc_title = $individual->getWholeName();
?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo $doc_title ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css" type="text/css">
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1><?php echo ToolBox::toHtml($doc_title); ?> <small><a href="individual_edit.php?individual_id=<?php echo $individual->getId() ?>"><span class="glyphicon glyphicon-edit"></span></a></small></h1>
	<section>
	<div>	
		<?php
		if ($individual->getPhotoUrl()) {
			echo $individual->getPhotoHtml();
		}
		?>
		<div>
			<?php
				$contact_data = array();
				if ($individual->getPhoneNumber()) {
					$contact_data['phone'] = $individual->getPhoneNumber();
				}
				if ($individual->getMobilePhoneNumber()) {
					$contact_data['mobile'] = $individual->getMobilePhoneNumber();
				}
				if ($individual->getEmailAddress()) {
					$contact_data['email'] = $individual->getEmailHtml();
				}

				if ($individual->getDescription()) {
					echo '<p>'.$individual->getDescription().'</p>';
				}
				if ($individual->getWeb()) {
					echo '<p>'.$individual->getHtmlLinkToWeb().'</p>';
				}
				if (count($contact_data) >0) {
					echo '<p>'.implode('<span> | </span>', $contact_data).'</p>';
				}
				if ($individual->getBirthDate()) {
					echo '<p><small>naissance : </small>'.$individual->getBirthDate().'</p>';
				}
				if ($individual->getAddress()) {
					echo '<p>'.$individual->getAddress().'</p>';
				}
				if ($individual->getCvUrl()) {
					echo '<p><a href="'.$individual->getCvUrl().'">cv</a></p>';
				}
				if ($individual->getGoogleQueryUrl()) {
					echo '<p><a href="'.$individual->getGoogleQueryUrl().'" target="_blank">'.$individual->getWholeName().' dans Google</a></p>';
				}
			?>
		</div>
	</div>
	</section>		

	<section>
		<h2>Participations <small><a href="membership_edit.php?individual_id=<?php echo $individual->getId() ?>."><span class="glyphicon glyphicon-plus"></span></a></small></h2>
		<?php
		if (isset($memberships)){
			echo '<ul class="list-group">';
			foreach ($memberships as $ms) {
				$s = $ms->getSociety();
				echo '<li class="list-group-item">';
				echo '<h3>';
				echo $s->getHtmlLinkToSociety();
				if ($ms->getDepartment()) echo ' <small> ('.$ms->getDepartment().')</small>';
				echo '</h3>';
				
				if ($ms->getPeriod()) {
					echo '<p>'.ucfirst($ms->getPeriod()).'</p>';
				}
				
				if ($ms->getTitle()) {
					echo '<p>'.ucfirst($ms->getTitle()).'</p>';
				}
				if ($ms->getUrl()) {
					echo '<p>'.$ms->getHtmlLinkToWeb().'</p>';
				}
				$data = array();
				if ($ms->getPhone()) $data[] = $ms->getPhone();
				if ($ms->getEmail()) {
					$data[] = '<a href="mailto:'.ToolBox::toHtml($individual->getFirstName()).'%20'.ToolBox::toHtml($individual->getLastName()).'%20<'.$ms->getEmail().'>">'.$ms->getEmail().'</a>';
				}
				if (count($data)>0) {
					echo '<p>'.implode('<span> | </span>', $data).'</p>';
				}
				if ($ms->getDescription()) echo '<p>'.$ms->getDescription().'</p>';
				echo '<p><a href="membership_edit.php?membership_id='.$ms->getId().'"><span class="glyphicon glyphicon-edit"></span> édition</a></p>';
				echo '</li>';
			}
			echo '</ul>';
		}
		?>
	</section>
</div>	
</body>
</html>