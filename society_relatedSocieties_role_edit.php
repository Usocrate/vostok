<?php
use Google\Auth\Credentials\UserRefreshCredentials;

require_once 'config/boot.php';
require_once 'classes/System.class.php';
$system = new System( 'config/host.json' );

session_start();

if (empty ($_SESSION['user_id'])) {
	header('Location:login.php');
	exit;
} else {
	$user = new User($_SESSION['user_id']);
	$user->feed();
}

$society = new Society();
if (isset($_REQUEST['society_id'])) {
	$society->setId($_REQUEST['society_id']);
}
$society->initFromDB();

if (isset($_POST['task'])) {
	
	$feedBack = new UserFeedBack();
	
	switch ($_POST['task']) {
		case 'registration':
			ToolBox::formatUserPost($_POST);
			
			try {
				foreach ($_POST['relatedSocietyToUpdateIds'] as $id) {
					$s = new Society($id);
					$system->updateSocietyRole($s, $society, $_POST['newRole']);
				}
				$feedBack->addSuccessMessage('Le nouveau rôle est enregistré.');
				
			} catch (ErrorException $e) {
				$feedBack->addErrorMessage('Quelque chose a posé problème.');
			}
			//header('Location:society.php?society_id='.$society->getId());
			//exit;
		default :
			$feedBack->addErrorMessage('Demande d\'éxécution d\'une tâche inconnue.');
	}
}

// participations
$relatedSocieties = $society->getRelatedSocieties();
$relatedSocietiesPlayingRole = array();
$otherRolesPlayedByRelatedSocieties = array();

foreach ($relatedSocieties as $item) {
	// $item[0] : Société
	// $item[1] : Identifiant de la relation;
	// $item[2] : Rôle
	// $item[3] : Description
	if(strcmp($item[2], $_REQUEST['role'])==0){
		$relatedSocietiesPlayingRole[] = $item;
	} else {
		if(!in_array($item[2], $otherRolesPlayedByRelatedSocieties)) {
			$otherRolesPlayedByRelatedSocieties[] = $item[2];
		}
	}
}
?>
<!doctype html>
<html lang="fr">
<head>
    <title>Réviser la catégorisation des sociétés liées</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script src="<?php echo POPPER_JS_URI; ?>"></script>
	<script src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1 class="bd-title"><?php echo ToolBox::toHtml($_REQUEST['role']); ?></h1>
	<p>Chez <?php echo $society->getHtmlLinkToSociety(); ?></p>

	<?php if (isset($feedBack)) echo $feedBack->toHtml(); ?>

	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
	<input name="society_id" type="hidden" value="<?php echo ToolBox::toHtml($_REQUEST['society_id']); ?>"></input>
	<input name="role" type="hidden" value="<?php echo ToolBox::toHtml($_REQUEST['role']); ?>"></input>
		
	<div class="row">
		<div class="col-md-6">
			<section>
			<h2>Les sociétés assumant ce rôle</h2>
			<?php
			if (count($relatedSocietiesPlayingRole)>0) {
				echo '<ul class="list-group list-group-flush">';
				foreach ($relatedSocietiesPlayingRole as $item) {
					// $item[0] : Société
					// $item[1] : Identifiant de la relation;
					// $item[2] : Rôle
					// $item[3] : Description
					echo '<li class="container p-2 list-group-item">';
					echo '<div class="form-check">';
					echo '<input name="relatedSocietyToUpdateIds[]" class="form-check-input" type="checkbox" value="'.$item[0]->getId().'">';
					echo '<label class="form-check-label" for="'.$item[0]->getId().'">'.$item[0]->getNameForHtmlDisplay().'</label>';
					echo '</div>';
					echo '</li>';
				}
				echo '</ul>';
			} else {
				echo '<p>Aucune société enregistrée</p>';
			}
			?>
			</section>
		</div>
		<div class="col-md-6">
			<section>
			<h2>Nouveau rôle à attribuer aux sociétés sélectionnées</h2>
			<?php	
				echo '<ul class="list-group list-group-flush">';
				foreach($otherRolesPlayedByRelatedSocieties as $otherRole) {
					echo '<li class="p-2 list-group-item">';
					echo '<div class="form-check">';
					echo '<input id="newRole_i" name="newRole" class="form-check-input" type="radio" value="'.$otherRole.'">';
					echo '<label class="form-check-label" for="newRole_i">';
					echo ToolBox::toHtml($otherRole);
					echo '</label>';
					echo '</div>';
					echo '</li>';				
				}
				echo '</ul>';
			?>
			</section>
		</div>
   	</div>
    <a href="society.php?society_id=<?php echo $society->getId() ?>" class="btn btn-link">quitter</a>
   	<button name="task" type="submit" value="registration" class="btn btn-primary">enregistrer</button>
	</form>
</div>
</body>
</html>