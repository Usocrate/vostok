<?php
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

/**
 * messages à délivrer.
 */
$messages = array();

// Formatage des données saisies par l'utilisateur
if (isset($_POST)) ToolBox::formatUserPost($_POST);

if (!empty($_REQUEST['lead_id'])) {
	//
	// la piste est connue
	//
	$lead = new Lead($_REQUEST['lead_id']);
	if (!empty($_POST['deletion_order'])) {
		//
		// on supprime la piste
		//
		$lead->delete();
		header('Location:lead_edit.php?lead_id='.$lead->getId());
		exit;
	} else {
		//
		// on récupère les données de la piste
		//
		$lead->feed();
		$individual =& $lead->getIndividual();
		if (isset($individual)) $individual->feed();
		$society =& $lead->getSociety();
		if (isset($society)) $society->feed();
	}
} else {
	//
	// la piste est nouvelle
	//
	$lead = new Lead();
}

//
// Traitement de l'individu lié à la piste
//
if (!empty($_REQUEST['individual_id'])) {
	//
	// On demande l'association de la piste avec un individu donné
	//
	$individual = new Individual($_REQUEST['individual_id']);
	$individual->feed();
	$lead->setIndividual($individual);
} elseif (!empty($_POST['individual_submission'])) {
	//
	// On demande l'association de la piste avec un nouvel individu
	//
	$individual = new Individual();
	$individual->feed($_POST);
	// on exige au moins un nom ou un prénom pour créer l'individu
	if ($individual->getLastName() || $individual->getLastName() || $individual->getId()){
		if (!$individual->getId()) $individual->identifyFromName();
		$individual->toDB();
		$lead->setIndividual($individual);
	}
}

//
// Traitement de la participation de l'individu à la société concernée.
//
if (!empty($_POST['membership_submission'])) {
	//
	// enregistrement de la participation
	//
	$memberships = $individual->getMemberships($society);
	if (count($memberships)==0) {
		$membership = new Membership();
		$membership->feed($_POST, 'membership_');
		$membership->setIndividual($individual);
		$membership->setSociety($society);
		$membership->toDB();
	}
}

//
// Enregistrement des données de la piste.
//
if (!empty($_POST['toDB_order'])) {
	$lead->toDB();
	header('Location:leads.php');
	exit;
}
$doc_title = 'Une piste';
?>
<!doctype html>
<html lang="fr">
<head>
	<title>Une des pistes (sa fiche détaillées)</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />	
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1 class="bd-title"><?php echo ToolBox::toHtml($doc_title); ?></h1>
    	<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post">
    		<input name="lead_id" type="hidden" value="<?php if ($lead->getId()) echo $lead->getId() ?>" />
    		<div>
    			<?php if (is_null($individual) || !$individual->getId()): ?>
    			<fieldset>
    				<legend>Qui est à l'origine de la piste ?</legend>
    				
    				<div class="form-group">
        				<label for="individual_salutation">Civilité</label>
        				<select name="individual_salutation" class="form-control">
        					<option value="">-- choisis --</option>
        					<?php echo Individual::getSalutationOptionsTags(); ?>
        				</select>
    				</div>
    				
    				<div class="form-group">
        				<label for="individual_firstName">Prénom <small>(individual)</small></label>
        				<input name="individual_firstName" type="text" size="15" class="form-control" />
    				</div>
    				
    				<div class="form-group">
        				<label for="individual_lastName">Nom</label>
        				<input name="individual_lastName" type="text" size="15" class="form-control" />
    				</div>
    				
    				<div class="form-group">
        				<label for="individual_mobile">Mobile</label>
        				<input name="individual_mobile" type="tel" size="12" class="form-control" />
        				<input name="individual_submission" type="hidden" value="1" />
    				</div>
    				
    				<fieldset id="membership_fieldset">
    					<legend>Fait partie de la société ?</legend>
    					<div class="checkbox">
    						<label><input type="checkbox" name="membership_submission" value="1" /> Oui</label>
    					</div>
						<div class="checkbox">
							<label><input name="membership_department" type="text" size="12" /> Service</label>
						</div>
						<div class="form-group">
							<label for="membership_title">Fonction</label>
							<input name="membership_title" type="text" size="12" class="form-control" />
						</div>
						
						<div class="form-group">
							<label for="membership_phone">Téléphone</label>
							<input name="membership_phone" type="tel" size="12" class="form-control" />
						</div>
						
						<div class="form-group">
						<label for="membership_email">Mél</label>
    					<input name="membership_email" type="email" size="12" class="form-control" />
    					</div>
						
						<div class="form-group">
							<label for="membership_url">Page perso</label>
							<input id="membership_url_input" name="membership_url" type="text" size="35" maxlength="255" class="form-control" onchange="javascript:checkUrlInput('membership_url_input', 'membership_url_link');" /> 
    						<a id="membership_url_link" href="#" style="display: none">[voir]</a>
						</div>
    				</fieldset>
    			</fieldset>
    			<?php endif; ?>
    		</div>
    		<?php
    		if (isset($individual) && $individual->getId()) {
    			echo '<div class="form-group">';
    		    echo '<label>Qui est à l\'origine de la piste<label>';
    			echo '<select name="individual_id" type="hidden" value="'.$individual->getId().'" class="form-control">';
    			echo '<option>-- choisir --</option>';
    			echo $society->getMembersOptionsTags($individual->getId());
    			echo '</select>';
    			echo '<div>';
    		}
    		?>
    		<div>
	    		<a href="/" class="btn btn-link">Quitter</a>
	    		<!-- <button name="deletion_order" type="button" value="1" class="btn btn-outline-secondary">supprimer</button> -->
	    		<button name="toDB_order" type="submit" value="1" class="btn btn-primary">Enregistrer</button>
    		</div>
	</form>
</div>	
</body>
</html>