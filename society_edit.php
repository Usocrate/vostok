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

$society = new Society();
if (isset($_REQUEST['society_id'])) {
	$society->setId($_REQUEST['society_id']);
	$society->feed();
}
$doc_title = 'Une société (sa fiche détaillée en édition)';

if (isset($_POST['task'])) {
	switch ($_POST['task']) {
		case 'deletion':
			if (!$society->delete()) trigger_error('échec de la suppression de la société');
			header('Location:societies_list.php');
			exit;
			break;
		case 'registration':
			ToolBox::formatUserPost($_POST);
			$society->feed($_POST);
			$society->getAddressFromGoogle();
			$society->toDB();

			//
			// traitement de la sélection d'activités.
			//
			$society->resetIndustries();
			if (isset($_POST['industries_ids']) && is_array($_POST['industries_ids'])) {
				foreach ($_POST['industries_ids'] as $id) {
					if (empty($id)) continue;
					$i = new Industry($id);
					$society->addIndustry($i);
				}
			}
			$society->saveIndustries();

			if (!empty($_POST['society_parent_name']) && strcmp($_POST['society_parent_name'], $_POST['society_lastparent_name'])!=0) {
				$parent = new Society();
				$parent->setName($_POST['society_parent_name']);
				if (!$parent->identifyfromName()) {
					// création d'une nouvelle société
					$parent->toDB();
				}
				$relationship = new Relationship();
				$relationship->setItem($society, 0);
				$relationship->setItemRole('filiale', 0);
				$relationship->setItem($parent, 1);
				$relationship->setItemRole('maison-mère', 1);
				$relationship->toDB();
			}
			header('Location:society.php?society_id='.$society->getId());
			exit;
			break;
		default :
			trigger_error('Demande d\'éxécution d\'une tâche inconnue.');
	}
}
?>
<!doctype html>
<html lang="fr">
<head>
<title><?php ToolBox::toHtml($doc_title) ?></title>
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
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" class="pure-form pure-form-stacked">
				<?php
				if (isset($_REQUEST['society_id'])) {
					echo '<input name="society_id" type="hidden" value="'.$society->getId().'" />';
				}
				?>
				<label>nom</label> <input type="text" name="society_name" value="<?php echo ToolBox::toHtml($society->getName()) ?>" size="35" /><br /> <label>nom de la société mère</label>
				<?php
				$parentSociety = $society->getParentSociety();
				$value = $parentSociety ? $parentSociety->getName() : '';
				?>
				<input type="text" name="society_parent_name" value="<?php echo ToolBox::toHtml($value); ?>" size="35" /><br /> <input type="hidden" name="society_lastparent_name" value="<?php echo ToolBox::toHtml($value); ?>" /> <label for="society_url">url</label> <input type="text" id="society_web_input" name="society_url" value="<?php echo ToolBox::toHtml($society->getUrl()); ?>" size="55" onchange="javascript:checkUrlInput('society_web_input', 'society_web_link');" /> <a id="society_web_link"
					class="weblink" href="#" style="display: none">[voir]</a><br /> <label>rue <small>(adresse de facturation)</small>
				</label> <input type="text" name="society_street" value="<?php echo ToolBox::toHtml($society->getStreet()); ?>" size="55" /> <br /> <label>ville</label> <input type="text" name="society_city" value="<?php echo ToolBox::toHtml($society->getCity()); ?>" size="35" /> <br /> <label>code postal</label> <input type="text" name="society_postalcode" value="<?php echo ToolBox::toHtml($society->getPostalCode()); ?>" size="15" /> <br /> <label>téléphone</label> <input type="text"
					name="society_phone" value="<?php echo ToolBox::toHtml($society->getPhone()); ?>" size="20" /> <br /> <label>activité</label> <select name="industries_ids[]" multiple="multiple" size="7">
					<option value="">-- choisir --</option>
					<?php echo $system->getIndustryOptionsTags($society->getIndustriesIds()) ?>
				</select><br /> <label>description</label>
				<textarea name="society_description" cols="51" rows="10"><?php echo ToolBox::toHtml($society->getDescription()); ?></textarea>
				<br />
				<button name="task" type="submit" value="registration" class="pure-button pure-button-primary">enregistrer</button>
				<button name="task" type="submit" value="deletion" class="pure-button">supprimer</button>
			</form>
		</div>
		<div class="pure-u-1">
			<footer><?php include 'menu.inc.php'; ?></footer>
		</div>
	</div>
</body>
</html>