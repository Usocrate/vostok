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

/**
 * messages à délivrer.
 */
$messages = array();

//	DataBase Connection
ToolBox::getDBAccess();

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
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" type="text/css" href="<?php echo PURE_SEEDFILE_URI ?>">

<script type="text/javascript" src="js/controls.js"></script>
<link rel="stylesheet" href="<?php echo SKIN_URL ?>main.css" type="text/css"><link rel="stylesheet" href="<?php echo SKIN_URL ?>pure-skin-vostok.css" type="text/css"></head>
<body class="pure-skin-vostok">
	<div class="pure-g-r">
		<div class="pure-u-1 ban">
			<header><div class="brand"><a href="<?php echo APPLI_URL?>"><?php echo ToolBox::toHtml(APPLI_NAME) ?></a></div><?php echo ToolBox::toHtml($doc_title); ?></header>
		</div>
		<div class="pure-u-1">
			<form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" class="pure-form pure-form-stacked">
				<input name="lead_id" type="hidden" value="<?php if ($lead->getId()) echo $lead->getId() ?>" />
				<div>
					<?php if (is_null($individual) || !$individual->getId()): ?>
					<fieldset>
						<legend>Qui est à l'origine de la piste ?</legend>
						<label for="individual_salutation">civilité</label> <select name="individual_salutation">
							<option value="">-- choisis --</option>
							<?php
							$individual = new Individual();
							echo $individual->getSalutationOptionsTags();
							?>
						</select> <label for="individual_firstName">prénom <small>(individual)</small>
						</label> <input name="individual_firstName" type="text" size="15" /> <label for="individual_lastName">nom</label> <input name="individual_lastName" type="text" size="15" /> <label for="individual_mobile">mobile</label> <input name="individual_mobile" type="text" size="12" /> <input name="individual_submission" type="hidden" value="1" />
						<fieldset id="membership_fieldset">
							<legend>fait partie de la société ?</legend>
							<input type="checkbox" name="membership_submission" value="1" /> <label for="membership_submmission" style="display: inline">oui</label> <br /> <label for="membership_department">service</label> <input name="membership_department" type="text" size="12" /> <label for="membership_title">fonction</label> <input name="membership_title" type="text" size="12" /> <label for="membership_phone">téléphone</label> <input name="membership_phone" type="text" size="12" /> <label for="membership_email">email</label>
							<input name="membership_email" type="text" size="12" /> <label for="membership_url">Page perso</label> <input id="membership_url_input" name="membership_url" type="text" size="35" maxlength="255" onchange="javascript:checkUrlInput('membership_url_input', 'membership_url_link');" /> <a id="membership_url_link" class="weblink" href="#" style="display: none">[voir]</a>
						</fieldset>
					</fieldset>
					<?php endif; ?>
				</div>
				<?php
				if (isset($individual) && $individual->getId()) {
					echo '<label>Qui est à l\'origine de la piste<label>';
					echo '<select name="individual_id" type="hidden" value="'.$individual->getId().'">';
					echo '<option>-- choisir --</option>';
					echo $society->getMembersOptionsTags($individual->getId());
					echo '</select>';
				}
				?>
				<hr />
				<button name="toDB_order" type="submit" value="1" class="pure-button pure-button-primary">enregistrer</button>
				<button name="deletion_order" type="submit" value="1" class="pure-button">supprimer</button>
			</form>
		</div>
		<div class="pure-u-1">
			<footer><?php include 'menu.inc.php'; ?></footer>
		</div>
	</div>
</body>
</html>