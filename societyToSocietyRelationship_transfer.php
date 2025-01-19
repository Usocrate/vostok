<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System ( 'config/host.json' );

session_start ();

if (! isset ( $_SESSION ['pendingProcess'] )) {
	$_SESSION ['pendingProcess'] = array ();
	$_SESSION ['pendingProcess'] ['name'] = 'society relationship transfer';
	
	// toutes les données à collecter pour accomplir le processus
	$_SESSION ['pendingProcess'] ['society'] = null;
	$_SESSION ['pendingProcess'] ['formerRelationship'] = null;
	$_SESSION ['pendingProcess'] ['society_role'] = null;
	$_SESSION ['pendingProcess'] ['targetSociety'] = null;
	$_SESSION ['pendingProcess'] ['targetSociety_role'] = null;
	$_SESSION ['pendingProcess'] ['followingSocietiesCollectionIds'] = null;

	// l'étape du processus dans lequel on se trouve
	$_SESSION ['pendingProcess'] ['currentStep'] = 'target society role selection';
}


// la société à transférer
if (array_key_exists ( 'society_id', $_REQUEST )) {
	$_SESSION ['pendingProcess'] ['society'] = new Society($_REQUEST ['society_id']);
	$_SESSION ['pendingProcess'] ['society']->feed();
}

// la relation d'origine
if (array_key_exists ( 'relationship_id', $_REQUEST )) {
	$_SESSION ['pendingProcess'] ['formerRelationship'] = new Relationship($_REQUEST ['relationship_id']);
	$_SESSION ['pendingProcess'] ['formerRelationship']->feed();
}

// la société ciblée
if (array_key_exists ( 'targetSociety_id', $_REQUEST )) {
	$_SESSION ['pendingProcess'] ['targetSociety'] = new Society($_REQUEST ['targetSociety_id']);
	$_SESSION ['pendingProcess'] ['targetSociety']->feed();
}

$society = $_SESSION['pendingProcess']['society'];
$relationship = $_SESSION['pendingProcess']['formerRelationship'];
$formerRelatedSociety = $relationship->getRelatedItem($society);

if (isset ( $_POST )) {
	ToolBox::formatUserPost ( $_POST );
}

if (isset ( $_POST ['cmd'] )) {
	switch ($_POST ['cmd']) {
		case 'Quitter' :
			unset ( $_SESSION ['pendingProcess'] );
			header ( 'location:' . $formerRelatedSociety->getDisplayUrl() );
			exit ();
	}
}

$fb = new UserFeedBack ();

if (isset ( $_POST ['task_id'] )) {

	switch ($_POST ['task_id']) {
		case 'target society role selection' :
			if (isset ( $_POST ['cmd'] )) {
				switch ($_POST ['cmd']) {
					case 'Poursuivre' :
						if (isset($_POST ['targetSociety_role'])) {
							$_SESSION ['pendingProcess'] ['targetSociety_role'] = $_POST ['targetSociety_role'];
							$_SESSION ['pendingProcess'] ['currentStep'] = 'target society selection';
						} else {
							$fb->addWarningMessage ( 'Il faut choisir une des options.' );
						}
						break;
				}
			}
			break;
			
		case 'target society selection' :
			if (isset ( $_POST ['cmd'] )) {
				switch ($_POST ['cmd']) {
					case 'Poursuivre' :
						if (isset($_POST ['targetSociety_id'])) {
							$_SESSION ['pendingProcess'] ['targetSociety'] = new Society($_POST ['targetSociety_id']);
							$_SESSION ['pendingProcess'] ['targetSociety'] -> feed();
							$_SESSION ['pendingProcess'] ['currentStep'] = 'new role definition';
						} else {
							$fb->addWarningMessage ( 'Il faut choisir une des options.' );
						}
						break;
				}
			}
			break;
			
		case 'new role definition' :
			if (isset ( $_POST ['cmd'] )) {
				switch ($_POST ['cmd']) {
					case 'Poursuivre' :
						$_SESSION ['pendingProcess'] ['society_role'] = $_POST ['society_role'];
						$_SESSION ['pendingProcess'] ['currentStep'] = 'target society role definition';
						break;
				}
			}
			break;
			
		case 'target society role definition' :
			if (isset ( $_POST ['cmd'] )) {
				switch ($_POST ['cmd']) {
					case 'Poursuivre' :
						$_SESSION ['pendingProcess'] ['targetSociety_role'] = $_POST ['society_role'];
						
						$items = $formerRelatedSociety->getRelatedSocieties($relationship->getRole($society));
						if (count($items)>1) {
							$_SESSION ['pendingProcess'] ['currentStep'] = 'following societies selection';
						} else {
							// on passe l'étape de transfert des sociétés tenant le même rôle s'il n'y en a pas
							$_SESSION ['pendingProcess'] ['currentStep'] = 'confirmation';
						}
						break;
				}
			}
			break;

		case 'following societies selection' :
			if (isset ( $_POST ['cmd'] )) {
				switch ($_POST ['cmd']) {
					case 'Poursuivre' :
						if (isset($_POST['followingSocietiesCollectionIds'])) {
							$_SESSION ['pendingProcess'] ['followingSocietiesCollectionIds'] = $_POST['followingSocietiesCollectionIds'];
						}
						$_SESSION ['pendingProcess'] ['currentStep'] = 'confirmation';
						break;
				}
			}
			break;
			
		case 'confirmation' :
			if (isset ( $_POST ['cmd'] )) {
				switch ($_POST ['cmd']) {
					case 'Je confirme' :
				
						$relationship->setRole($society, $_SESSION ['pendingProcess'] ['society_role']);
						$relationship->setRelatedItem($society, $_SESSION ['pendingProcess'] ['targetSociety'],  $_SESSION ['pendingProcess'] ['targetSociety_role']);
						
						if ($relationship->toDB()) {
							if (isset($_SESSION['pendingProcess']['followingSocietiesCollectionIds'])){
								$followingOnes = $system->getSocieties(array("ids"=>$_SESSION['pendingProcess']['followingSocietiesCollectionIds']));
								foreach ($followingOnes as $s) {
									$r = $system->getRelationship($s,$formerRelatedSociety);
									$r->setRole($s, $_SESSION ['pendingProcess'] ['society_role']);
									$r->setRelatedItem($s, $_SESSION ['pendingProcess'] ['targetSociety'],  $_SESSION ['pendingProcess'] ['targetSociety_role']);
									$r->toDB();
								}
							}
							unset ( $_SESSION ['pendingProcess'] );
							header ( 'location:' . $formerRelatedSociety->getDisplayUrl() );
							exit ();
						}
				}
			}
			break;
	}
}

$formerRelatedSociety = $relationship->getRelatedItem($society);
$targetSociety = $_SESSION['pendingProcess']['targetSociety'];

/*
echo '<h2>Post</h2>';
var_dump($_POST);

echo '<h2>Session</h2>';
var_dump($_SESSION);
*/
?>

<!doctype html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="<?php echo ToolBox::toHtml($system->getAppliDescription()) ?>" />
	<title><?php echo ToolBox::toHtml($system->getAppliName().' : déplacer une société'); ?></title>
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl(); ?>/theme.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
</head>
<body>
	<div class="container-fluid">
		<?php
		switch ($_SESSION ['pendingProcess'] ['currentStep']) {
			
			case 'target society role selection' :

				$role = $relationship->getRole($society);
				$options = $formerRelatedSociety->getRelatedSocietiesRoles();
				
				echo '<header><h1>Transférer vers une société tenant quel rôle ? <small>étape 1</small></h1></header>';
				
				echo $fb->toHtml();
				
				echo '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';

				echo '<input type="hidden" name="task_id" value="target society role selection" />';
				
				echo '<ul class="list-group list-group-flush">';
				
				for($i= 0; $i<count($options); $i++) {
					
					if (strcasecmp($options[$i], $role)==0) {
						continue;
					}
					
					echo '<li class="p-2 list-group-item">';
					echo '<div class="form-check">';
					echo '<input id="targetSociety_role_i'.$i.'" name="targetSociety_role" class="form-check-input" type="radio" value="'.$options[$i].'" />';
					echo '<label class="form-check-label" for="targetSociety_role_i'.$i.'">'.Toolbox::toHtml($options[$i]).'</label>';
					echo '</div>';
					echo '</li>';
				}
				echo '</ul>';
				
				echo '<div class="btn-group">';
				echo '<input type="submit" name="cmd" value="Poursuivre" class="btn btn-default btn-primary" />';
				echo '<input type="submit" name="cmd" value="Quitter" class="btn btn-default" />';
				echo '</div>';
				
				echo '</form>';

				break;
				
			case 'target society selection' :
				$options = $formerRelatedSociety->getRelatedSocieties($_SESSION ['pendingProcess'] ['targetSociety_role']);
			
				echo '<header><h1>Quelle est la société ? <small>étape 2</small></h1></header>';
				
				echo $fb->toHtml();
				
				echo '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';
				echo '<input type="hidden" name="task_id" value="target society selection" />';
				
				echo '<ul class="list-group list-group-flush">';
				foreach($options as $o) {
					echo '<li class="p-2 list-group-item">';
					echo '<div class="form-check">';
					echo '<input id="targetSociety_i" name="targetSociety_id" class="form-check-input" type="radio" value="'.$o[0]->getId().'">';
					echo '<label class="form-check-label" for="targetSociety_role_i">'.Toolbox::toHtml($o[0]->getName()).'</label>';
					echo '</div>';
					echo '</li>';
				}
				echo '</ul>';
				
				
				echo '<div class="btn-group">';
				echo '<input type="submit" name="cmd" value="Poursuivre" class="btn btn-default btn-primary" />';
				echo '<input type="submit" name="cmd" value="Quitter" class="btn btn-default" />';
				echo '</div>';
				echo '</form>';
				
				break;
				
			case 'new role definition' :
					
				echo '<header><h1>Quel rôle pour '.$society->getName().' ? <small>étape 3</small></h1></header>';
				
				echo '<p>Vis à vis de <em>'.$_SESSION['pendingProcess']['targetSociety']->getName().'</em></p>';
				
				echo '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';
				echo '<input type="hidden" name="task_id" value="new role definition" />';
				
				echo '<datalist id="role_list">';
				foreach (Relationship::getKnownRoles() as $r) {
					echo '<option value="'.ToolBox::toHtml($r['role']).'"/>';
				}
				echo '</datalist>';
				
				echo '<div class="form-group">';
				echo '<label for="society_role_i">Son rôle</label>';
				echo '<input id="society_role_i" name="society_role" type="text" list="role_list" value="'.$relationship->getRole($society).'" size="20" class="form-control" />';
				echo '</div>';
				
				echo '<div class="btn-group">';
				echo '<input type="submit" name="cmd" value="Poursuivre" class="btn btn-default btn-primary" />';
				echo '<input type="submit" name="cmd" value="Quitter" class="btn btn-default" />';
				echo '</div>';
				echo '</form>';
				
				break;
				
			case 'target society role definition' :
					
				echo '<header><h1>Quel rôle pour '.$_SESSION['pendingProcess']['targetSociety']->getName().' ? <small>étape 4</small></h1></header>';
				
				echo '<p>Vis à vis de <em>'.$society->getName().'</em></p>';
				
				echo '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';
				echo '<input type="hidden" name="task_id" value="target society role definition" />';
				
				echo '<datalist id="role_list">';
				foreach (Relationship::getKnownRoles() as $r) {
					echo '<option value="'.ToolBox::toHtml($r['role']).'"/>';
				};
				echo '</datalist>';
				
				echo '<div class="form-group">';
				echo '<label for="society_role_i">Son rôle</label>';
				echo '<input id="society_role_i" name="society_role" type="text" list="role_list" value="'.$_SESSION ['pendingProcess'] ['targetSociety_role'].'" size="20" class="form-control" />';
				echo '</div>';
				
				echo '<div class="btn-group">';
				echo '<input type="submit" name="cmd" value="Poursuivre" class="btn btn-default btn-primary" />';
				echo '<input type="submit" name="cmd" value="Quitter" class="btn btn-default" />';
				echo '</div>';
				echo '</form>';
				
				break;

			case 'following societies selection' :
				$options = $formerRelatedSociety->getRelatedSocieties($relationship->getRole($society));
				
				echo '<header><h1>Même chemin pour d&apos;autres sociétés ? <small>étape 5</small></h1></header>';
				
				echo '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';
				echo '<input type="hidden" name="task_id" value="following societies selection" />';
				
				echo '<ul class="list-group list-group-flush">';
				for($i=0; $i<count($options); $i++) {
					if(strcmp($options[$i][0]->getId(),$society->getId())==0){
						continue;	
					}
					echo '<li class="p-2 list-group-item">';
					echo '<div class="form-check">';
					echo '<input id="followingSocieties_i'.$i.'" name="followingSocietiesCollectionIds[]" class="form-check-input" type="checkbox" value="'.$options[$i][0]->getId().'">';
					echo '<label class="form-check-label" for="followingSocieties_i'.$i.'">'.Toolbox::toHtml($options[$i][0]->getName()).'</label>';
					echo '</div>';
					echo '</li>';
				}
				echo '</ul>';
				
				echo '<div class="btn-group">';
				echo '<input type="submit" name="cmd" value="Poursuivre" class="btn btn-default btn-primary" />';
				echo '<input type="submit" name="cmd" value="Quitter" class="btn btn-default" />';
				echo '</div>';
				echo '</form>';
				
				break;

			case 'confirmation' :
				
				echo '<header><h1>Récapitulatif <small>étape 6</small></h1></header>';
				
				if (isset($_SESSION['pendingProcess']['followingSocietiesCollectionIds'])) {
					$followingOnes = $system->getSocieties(array("ids"=>$_SESSION['pendingProcess']['followingSocietiesCollectionIds']));
				}
		
				
				echo '<div class="table-responsive">';
				echo '<table class="table">';
				echo '<thead><tr><th></th><th scope="col">avant</th><th scope="col">après</th></tr></thead>';
				echo '<tbody>';
				
				echo '<tr>';
				echo '<th scope="row">'.Toolbox::toHtml($society->getName()).'</th>';
				echo '<td>';
				if (!empty($relationship->getPeriod())) {
					echo '<div><small>'.$relationship->getPeriod().'</small></div>';
				}
				echo Toolbox::toHtml($relationship->getRole($society)).' <em>'.Toolbox::toHtml($formerRelatedSociety->getName()).'</em>';
				if (!empty($relationship->getDescription())) {
					echo '<p>'.Toolbox::toHtml($relationship->getDescription()).'</p>';
				}
				echo '</td>';
				echo '<td>';
				if (!empty($relationship->getPeriod())) {
					echo '<div><small>'.$relationship->getPeriod().'</small></div>';
				}
				echo Toolbox::toHtml($_SESSION ['pendingProcess'] ['society_role']).' <em>'.Toolbox::toHtml($targetSociety->getName()).'</em>';
				if (!empty($relationship->getDescription())) {
					echo '<p>'.Toolbox::toHtml($relationship->getDescription()).'</p>';
				}
				echo '</td>';
				echo '</tr>';
				
				foreach ($followingOnes as $s) {
					
					$r = $system->getRelationship($s,$formerRelatedSociety);
					
					echo '<tr>';
					echo '<th scope="row">'.Toolbox::toHtml($s->getName()).'</th>';
					echo '<td>';
					if (!empty($r->getPeriod())) {
						echo '<div><small>'.$r->getPeriod().'</small></div>';
					}
					echo Toolbox::toHtml($r->getRole($s)).' <em>'.Toolbox::toHtml($formerRelatedSociety->getName()).'</em>';
					if (!empty($r->getDescription())) {
						echo '<p>'.Toolbox::toHtml($r->getDescription()).'</p>';
					}
					echo '</td>';
					echo '<td>';
					if (!empty($r->getPeriod())) {
						echo '<div><small>'.$r->getPeriod().'</small></div>';
					}
					echo Toolbox::toHtml($_SESSION ['pendingProcess'] ['society_role']).' <em>'.Toolbox::toHtml($targetSociety->getName()).'</em>';
					if (!empty($r->getDescription())) {
						echo '<p>'.Toolbox::toHtml($r->getDescription()).'</p>';
					}
					echo '</td>';
					echo '</tr>';
				}
				
				echo '</tbody>';
				echo '</table>';
				echo '</div>';
				
				echo '<form action="' . $_SERVER ['PHP_SELF'] . '" method="post">';
				echo '<input type="hidden" name="task_id" value="confirmation" />';
				echo '<div class="btn-group">';
				echo '<input type="submit" name="cmd" value="Je confirme" class="btn btn-default btn-primary" />';
				echo '<input type="submit" name="cmd" value="Quitter" class="btn btn-default" />';
				echo '</div>';
				
				break;
				
			default :
				echo '<p>'.$_SESSION ['pendingProcess'] ['currentStep'].' est une étape inconnue</p>.';
		}
		?>
	</div>
</body>
