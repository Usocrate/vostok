<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
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
	$society->feed();
}
elseif (isset($_REQUEST['society_name'])) {
	$society->setName($_REQUEST['society_name']);
}

$h1_content = $society->hasId() ? $society->getHtmlLinkToSociety() : 'Une nouvelle société';

if (isset($_POST['task'])) {
	switch ($_POST['task']) {
		case 'registration':
			ToolBox::formatUserPost($_POST);
			$society->feed($_POST, 'society_');
			
			if (!empty($system->getGoogleMapsApiKey()) && !$society->isAddressComplete()) {
				$society->getAddressFromGoogle();
			}
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
				$relationship->setItemRole('Filiale', 0);
				$relationship->setItem($parent, 1);
				$relationship->setItemRole('Maison-mère', 1);
				$relationship->toDB();
			}
			header('Location:society.php?society_id='.$society->getId());
			exit;
		default :
			trigger_error('Demande d\'éxécution d\'une tâche inconnue.');
	}
}
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php strip_tags($h1_content) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>    
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="js/society-name-autocomplete.js"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
    <h1 class="bd-title"><?php echo $h1_content ?></h1>
    <section>
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    	<?php
    	if (isset($_REQUEST['society_id'])) {
    		echo '<input name="society_id" type="hidden" value="'.$society->getId().'" />';
    	}
    	?>
    	
    	<div class="row">
    		<div class="col-md-6">
            	<div class="form-group">
        	    	<label for="society_name_i">Nom</label>
        	    	<input id="society_name_i" type="text" name="society_name" value="<?php echo ToolBox::toHtml($society->getName()) ?>" size="35" class="form-control" />
        		</div>
        		
        		<div class="form-group">
            		<label for="s_parent_name_i">Maison-mère</label>
                	<?php
	                	$parentSociety = $society->getParentSociety();
	                	$value = $parentSociety ? $parentSociety->getName() : '';
                	?>
                	<input id ="s_parent_name_i" name="society_parent_name" is="society-name-autocomplete" type="text" value="<?php echo ToolBox::toHtml($value); ?>" size="35" class="form-control" />
                	<input type="hidden" name="society_lastparent_name" value="<?php echo ToolBox::toHtml($value); ?>" />
            	</div>
        		
        		<div class="form-group">
        			<label for="society_url_i">URL</label>
        			<input type="url" id="society_url_i" name="society_url" value="<?php echo ToolBox::toHtml($society->getUrl()); ?>" size="55" class="form-control" onchange="javascript:checkUrlInput('society_web_input', 'society_web_link');" /> 
        		</div>
				
				<div class="form-group">
					<label for="society_phone_id">Téléphone</label>
					<input id="society_phone_id" type="tel" name="society_phone" value="<?php echo ToolBox::toHtml($society->getPhone()); ?>" size="20" class="form-control" />
				</div>
				        		
				<fieldset>
					<legend>Localisation</legend>
					<div class="form-group">
						<label for="society_street_i">Rue</label>
						<input id="society_street_i" type="text" name="society_street" value="<?php echo ToolBox::toHtml($society->getstreet()); ?>" size="55" class="form-control" />
					</div>
					
					<div class="form-group">
		    			<label for="society_city_i">Ville</label>
		    			<input id="society_city_i" type="text" name="society_city" value="<?php echo ToolBox::toHtml($society->getCity()); ?>" size="55" class="form-control" />
		    		</div>
					
					<div class="form-group">
						<label for="society_subAdministrativeAreaName_i">Département</label>
						<input id="society_subAdministrativeAreaName_i" type="text" name="society_subAdministrativeAreaName" value="<?php echo ToolBox::toHtml($society->getSubAdministrativeAreaName()); ?>" size="55" class="form-control" />
					</div>
					
					<div class="form-group">
						<label for="society_administrativeAreaName_i">Région</label>
						<input id="society_administrativeAreaName_i" type="text" name="society_administrativeAreaName" value="<?php echo ToolBox::toHtml($society->getAdministrativeAreaName()); ?>" size="55" class="form-control" />
					</div>
					
					<div class="form-group">
						<label for="society_countryNameCode_i">Code pays</label>
						<input id="society_countryNameCode_i" type="text" name="society_countryNameCode" value="<?php echo ToolBox::toHtml($society->getCountryNameCode()); ?>" size="3" class="form-control" />
					</div>								
					
					<div class="form-group">
						<label for="society_postalcode_i">Code postal</label>
						<input id="society_postalcode_i" type="text" name="society_postalcode" value="<?php echo ToolBox::toHtml($society->getPostalCode()); ?>" size="55" class="form-control" />
					</div>
				<fieldset>
    		</div>
    		
    		<div class="col-md-6">
        		<div class="form-group">
        			<label for="industries_i">Activité</label>
        			<select id="industries_i" name="industries_ids[]" multiple="multiple" size="9"  class="form-control">
        	    		<option value="">-- choisir --</option>
            			<?php echo $system->getIndustryOptionsTags($society->getIndustriesIds()) ?>
            		</select>
        		</div>
        		
        		<div class="form-group">
        			<label for="society_description_i">Notes</label>
            		<textarea name="society_description" cols="51" rows="5" class="form-control"><?php echo $society->getDescription(); ?></textarea>
            	</div>
    		</div>
    	</div>
   	
    	<div>
    		<?php if (!$society->hasId()) : ?>
    			<a href="societies.php" class="btn btn-link">Quitter</a>
    		<?php endif; ?>
    		
    		<?php if ($society->hasId()) : ?>
    		    <a href="society.php?society_id=<?php echo $society->getId() ?>" class="btn btn-link">Quitter</a>
	    	<?php endif; ?>
	    	
	    	<button name="task" type="submit" value="registration" class="btn btn-primary">Enregistrer</button>
    	</div>
    </form>
	<?php
		if($society->hasId()) {
			echo '<p>Tu veux oublier cette société ? C\'est <a id="delete_a" href="#">ici</a>.</p>';
		}
	?>    
    </section>
</div>
<?php if($society->hasId()): ?>
<script type="text/javascript">
	const apiUrl = '<?php echo $system->getApiUrl() ?>';
	
	document.addEventListener("DOMContentLoaded", function() {
		customElements.define("society-name-autocomplete", SocietyNameAutocomplete, { extends: "input" });
		
		const delete_a = document.getElementById('delete_a');
		delete_a.addEventListener('click', function (event) {
		  event.preventDefault();
		  var xhr = new XMLHttpRequest();
		  xhr.open("POST", "api/societies/", true);
		  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		  xhr.responseType = 'json';
		  xhr.onreadystatechange = function () {
		    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
		    	alert(this.response.message);
		    	if (this.response.data.location !== undefined) {
			    	window.location.replace(this.response.data.location);
		    	}
	    	}				  
		  };
		  xhr.send("id=<?php echo $society->getId() ?>&task=deletion");
		});
	});
</script>
<?php endif; ?>
</body>
</html>