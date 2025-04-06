<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System( 'config/host.json' );

session_start ();

if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {
	$user = new User ( $_SESSION ['user_id'] );
	$user->feed ();
}

$membership = new Membership ();

$fb = new UserFeedBack ();

if (! empty ( $_REQUEST ['membership_id'] )) {
	$membership->setId ( $_REQUEST ['membership_id'] );
	$membership->feed();
} else {
	header ( 'location:index.php' );
	exit ();
}

if (isset ( $_POST ['task'] )) {

	ToolBox::formatUserPost ( $_POST );

	switch ($_POST ['task']) {
		case 'membership_upgrade' :
			$membership->setWeight ($_POST['weight']);
			if ($membership->toDB ()) {
				$fb->addSuccessMessage ( 'Pondération enregistrée.' );
			}
			break;
	}
}
//print_r($membership);

?>
<!doctype html>
<html lang="fr">
<head>
	<title>Modification du poids d'une participation</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
</head>
<body>
<div class="container-fluid">
	<div class="bd-title">
	<h1>Quelle poids pour cette participation ?</h1>
	<small><?php echo $membership->getHtmlLinkToIndividual () .' chez ' . $membership->getHtmlLinkToSociety () ?></small>
	</div>
	
	<?php echo $fb->toHtml() ?>

	<form id="membership_upgrade_form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    	<?php echo '<input name="membership_id" type="hidden" value="' . $membership->getId () . '" />'; ?>
		<div class="card-deck">
		<?php 
		for($i=0;$i<4;$i++) {
			echo '<div class="card">';
			$id = 'opt'.$i;

			echo '<div class="card-body">';
			echo '<label for="'.$id.'">';
			echo $i>0 ? str_repeat('<span style="font-size:2em;mix-blend-mode:luminosity">🏅</span>', $i) : '<small>aucun</small>';
			echo '</label>';
			echo '</div>';

			echo '<div class="card-footer">';
			if ($membership->getWeight() == $i) {
				echo '<input id="'.$id.'" name="weight" type="radio" value="'.$i.'" checked></input>';
			} else {
				echo '<input id="'.$id.'" name="weight" type="radio" value="'.$i.'"></input>';
			}
			echo '</div>';

			echo '</div>';
		}
		?>
		</div>
		<div class="my-3">
			<button name="task" type="submit" value="membership_upgrade" class="btn btn-primary">Enregistrer</button>
		</div>
	</form>
</div>
</body>
</html>