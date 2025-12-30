<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System( './config/host.json' );
$systemIdInSession = $system->getAppliName();

session_start ();

$fb = new UserFeedBack ();

if (! isset ( $_SESSION[$systemIdInSession] ['pendingProcess'] )) {
	$_SESSION[$systemIdInSession] ['pendingProcess'] = array ();
	$_SESSION[$systemIdInSession] ['pendingProcess'] ['name'] = 'membership transfer to homonym';
	
	// toutes les données à collecter pour accomplir le processus
	$_SESSION[$systemIdInSession] ['pendingProcess'] ['membership'] = null;
	$_SESSION[$systemIdInSession] ['pendingProcess'] ['targetIndividual'] = null;

	// l'étape du processus dans lequel on se trouve
	$_SESSION[$systemIdInSession] ['pendingProcess'] ['currentStep'] = 'existing homonym check';
}

// la participation à transférer
if (array_key_exists ( 'membership_id', $_REQUEST )) {
	$_SESSION[$systemIdInSession] ['pendingProcess'] ['membership'] = new Membership($_REQUEST ['membership_id']);
	$_SESSION[$systemIdInSession] ['pendingProcess'] ['membership']->feed();
}

// l'individu ciblé
if (array_key_exists ('targetIndividual_id', $_REQUEST )) {
	$_SESSION[$systemIdInSession] ['pendingProcess'] ['targetIndividual'] = new individual($_REQUEST ['targetIndividual_id']);
	$_SESSION[$systemIdInSession] ['pendingProcess'] ['targetIndividual']->feed();
}

$membership = $_SESSION[$systemIdInSession] ['pendingProcess']['membership'];

$formerIndividual = $membership->getIndividual();
$formerIndividual->feed();

$targetIndividual = $_SESSION[$systemIdInSession] ['pendingProcess']['targetIndividual'];

$existingHomonyms = $system->getIndividualHomonyms($formerIndividual);

if (count($existingHomonyms)>0) {
	$_SESSION[$systemIdInSession] ['pendingProcess'] ['currentStep'] = 'homonym selection';

} else {
	$targetIndividual = new Individual();
	$targetIndividual->setFirstName($formerIndividual->getFirstName());
	$targetIndividual->setLastName($formerIndividual->getLastName()); 
	$_SESSION[$systemIdInSession] ['pendingProcess'] ['targetIndividual'] = $targetIndividual;	
	$_SESSION[$systemIdInSession] ['pendingProcess'] ['currentStep'] = 'confirmation';
}

if (isset ( $_POST )) {
	ToolBox::formatUserPost ( $_POST );
}

if (isset ( $_POST ['cmd'] )) {
	switch ($_POST ['cmd']) {
		case 'Quitter' :
			unset ( $_SESSION[$systemIdInSession] ['pendingProcess'] );
			header ( 'location:membership_menu.php?membership_id=' . $membership->getId() );
			exit ();
	}
}

if (isset ( $_POST ['task'] )) {

	switch ($_POST ['task']) {

		case 'save homonym selection' :
			if (isset ( $_POST ['cmd'] )) {
				switch ($_POST ['cmd']) {
					case 'Poursuivre' :
						if (!isset($_POST['targetIndividual_id'])) {
							$fb->addWarningMessage('Un des options proposées doit être choisie');
						} else {
							$targetIndividual = new Individual();
							if (!empty($_POST['targetIndividual_id'])) {
								$targetIndividual->setId($_POST['targetIndividual_id']);
								$targetIndividual->feed();		
							} else {
								$targetIndividual->setFirstName($formerIndividual->getFirstName());
								$targetIndividual->setLastName($formerIndividual->getLastName()); 
							}
							$_SESSION[$systemIdInSession] ['pendingProcess'] ['targetIndividual'] = $targetIndividual;	
							$_SESSION[$systemIdInSession] ['pendingProcess'] ['currentStep'] = 'confirmation';
						}
				}
			}
			break;
			
		case 'confirmation' :
			if (isset ( $_POST ['cmd'] )) {
				switch ($_POST ['cmd']) {
					case 'Je confirme' :
						
						if (!$targetIndividual->hasId()) {
							// création d'un nouvel homonyme
							$targetIndividual->toDB();
						}
												
						$membership->setIndividual($targetIndividual);
						$membership->toDB();
						
						
						unset ( $_SESSION[$systemIdInSession] ['pendingProcess'] );
						header ( 'location:' . $targetIndividual->getDisplayUrl() );
						exit ();
				}
			}
			break;
	}
}
?>

<!doctype html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="<?php echo ToolBox::toHtml($system->getAppliDescription()) ?>" />
	<title><?php echo ToolBox::toHtml($system->getAppliName().' : transférer une participation'); ?></title>
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl(); ?>/theme.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
</head>
<body>
	<main class="container-fluid">
		<?php
		switch ($_SESSION[$systemIdInSession] ['pendingProcess'] ['currentStep']) {
			
			case 'homonym selection' :

				echo '<header><h1>Transférer vers quel homonyme ? <small>étape 1</small></h1></header>';
				
				echo $fb->toHtml();
				
				echo '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';

				echo '<input type="hidden" name="task" value="save homonym selection" />';
				
				echo '<ul class="list-group list-group-flush">';
				
				for ($i= 0; $i<count($existingHomonyms); $i++) {
					$h = new Individual ();
					$h->feed($existingHomonyms[$i], 'individual_');

					echo '<li class="p-2 list-group-item">';
					echo '<div class="form-check">';
					echo '<input id="targetIndividual_i'.$i.'" name="targetIndividual_id" class="form-check-input" type="radio" value="'.$h->getId().'" />';
					echo '<label class="form-check-label" for="targetIndividual_i'.$i.'">'.Toolbox::toHtml($h->getWholeName()).' <small>('.$h->getId().')</small></label>';
					echo '</div>';
					echo '</li>';
				}
				echo '<li class="p-2 list-group-item">';
				echo '<div class="form-check">';
				echo '<input id="targetIndividual_new_i" name="targetIndividual_id" class="form-check-input" type="radio" value="" />';
				echo '<label class="form-check-label" for="targetIndividual_new_i">Nouvel homonyme</label>';
				echo '</div>';
				echo '</li>';
				echo '</ul>';
				
				echo '<div class="btn-group">';
				echo '<input type="submit" name="cmd" value="Poursuivre" class="btn btn-default btn-primary" />';
				echo '<input type="submit" name="cmd" value="Quitter" class="btn btn-default" />';
				echo '</div>';
				
				echo '</form>';

				break;
				
			case 'confirmation' :
			
				if ($targetIndividual->hasId()) {
					$fb->addInfoMessage ('La participation sera transférée à '.$targetIndividual->getWholeName().' ('.$targetIndividual->getId().')');					
				} else {
					$fb->addInfoMessage ('Nous allons en créer un autre '.$targetIndividual->getWholeName().' qui prendra la participation');
				}
				
				echo '<header><h1>Récapitulatif <small>étape 2</small></h1></header>';
				
				echo $fb->toHtml();
				
				echo '<div>';
				echo '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';
				echo '<input type="hidden" name="task" value="confirmation" />';
				echo '<div class="btn-group">';
				echo '<input type="submit" name="cmd" value="Je confirme" class="btn btn-default btn-primary" />';
				echo '<input type="submit" name="cmd" value="Quitter" class="btn btn-default" />';
				echo '</div>';
				
				break;
				
			default :
				echo '<p>'.$_SESSION[$systemIdInSession] ['pendingProcess'] ['currentStep'].' est une tâche inconnue</p>.';
		}
		?>
	</main>
</body>