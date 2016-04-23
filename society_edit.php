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
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <script type="text/javascript" src="js/controls.js"></script>
    <link rel="stylesheet" href="<?php echo SKIN_URL ?>main.css" type="text/css">
</head>
<body>
    <header><div class="brand"><a href="<?php echo APPLI_URL?>"><?php echo ToolBox::toHtml(APPLI_NAME) ?></a></div></header>
    
    <h1><?php echo ToolBox::toHtml($doc_title); ?></h1>
    
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    	<?php
    	if (isset($_REQUEST['society_id'])) {
    		echo '<input name="society_id" type="hidden" value="'.$society->getId().'" />';
    	}
    	?>
    	
    	<div class="row">
    		<div class="col-md-6">
            	<div class="form-group">
        	    	<label>nom</label>
        	    	<input type="text" name="society_name" value="<?php echo ToolBox::toHtml($society->getName()) ?>" size="35" class="form-control" />
        		</div>
        		
        		<div class="form-group">
            		<label>nom de la société mère</label>
                	<?php
                	$parentSociety = $society->getParentSociety();
                	$value = $parentSociety ? $parentSociety->getName() : '';
                	?>
                	<input type="text" name="society_parent_name" value="<?php echo ToolBox::toHtml($value); ?>" size="35" class="form-control" />
                	<input type="hidden" name="society_lastparent_name" value="<?php echo ToolBox::toHtml($value); ?>" />
            	</div>
        		
        		<div class="form-group">
        			<label for="society_url">url</label>
        			<input type="url" id="society_web_input" name="society_url" value="<?php echo ToolBox::toHtml($society->getUrl()); ?>" size="55" class="form-control" onchange="javascript:checkUrlInput('society_web_input', 'society_web_link');" /> 
        			<a id="society_web_link" href="#" style="display: none">[voir]</a>
        		</div>
        		
        		<div class="form-group">
        			<label>rue <small>(adresse de facturation)</small></label>
        			<input type="text" name="society_street" value="<?php echo ToolBox::toHtml($society->getStreet()); ?>" size="55" class="form-control" />
        		</div>
        		
        		<div class="form-group">
        			<label>ville</label>
        			<input type="text" name="society_city" value="<?php echo ToolBox::toHtml($society->getCity()); ?>" size="35" class="form-control" />
        		</div>
        		
        		<div class="form-group">
        			<label>code postal</label>
        			<input type="text" name="society_postalcode" value="<?php echo ToolBox::toHtml($society->getPostalCode()); ?>" size="15" class="form-control" />
        		</div>
        		
        		<div class="form-group">
        			<label>téléphone</label>
        			<input type="tel"	name="society_phone" value="<?php echo ToolBox::toHtml($society->getPhone()); ?>" size="20" class="form-control" />
        		</div>
    		</div>
    		
    		<div class="col-md-6">
        		<div class="form-group">
        			<label>activité</label>
        			<select name="industries_ids[]" multiple="multiple" size="9"  class="form-control">
        	    		<option value="">-- choisir --</option>
            			<?php echo $system->getIndustryOptionsTags($society->getIndustriesIds()) ?>
            		</select>
        		</div>
        		
        		<div class="form-group">
        			<label>description</label>
            		<textarea name="society_description" cols="51" rows="10" class="form-control"><?php echo ToolBox::toHtml($society->getDescription()); ?></textarea>
            	</div>
    		</div>
    	</div>
   	
    	<button name="task" type="submit" value="registration" class="btn btn-primary">Enregistrer</button>
    	<button name="task" type="submit" value="deletion" class="btn btn-default">Supprimer</button>
    </form>
    
	<footer><?php include 'menu.inc.php'; ?></footer>
</body>
</html>