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
		case 'deletion':
			if (!$society->delete()) trigger_error('échec de la suppression de la société');
			header('Location:societies.php');
			exit;
			break;
		case 'registration':
			ToolBox::formatUserPost($_POST);
			$society->feed($_POST);
			if (! empty ( $_POST['society_address'] ) ) {
    			$society->getAddressFromGoogle($_POST['society_address']);
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
			break;
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
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>    
    <script type="text/javascript" src="js/controls.js"></script>
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
    <h1 class="bd-title"><?php echo $h1_content ?></h1>
    
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    	<?php
    	if (isset($_REQUEST['society_id'])) {
    		echo '<input name="society_id" type="hidden" value="'.$society->getId().'" />';
    	}
    	?>
    	
    	<div class="row">
    		<div class="col-md-6">
            	<div class="form-group">
        	    	<label>Nom</label>
        	    	<input type="text" name="society_name" value="<?php echo ToolBox::toHtml($society->getName()) ?>" size="35" class="form-control" />
        		</div>
        		
        		<div class="form-group">
            		<label>Maison-mère</label>
                	<?php
	                	$parentSociety = $society->getParentSociety();
	                	$value = $parentSociety ? $parentSociety->getName() : '';
                	?>
                	<input type="text" id ="s_parent_name_i" name="society_parent_name" value="<?php echo ToolBox::toHtml($value); ?>" size="35" class="form-control" />
                	<input type="hidden" name="society_lastparent_name" value="<?php echo ToolBox::toHtml($value); ?>" />
            	</div>
        		
        		<div class="form-group">
        			<label for="society_url">URL</label>
        			<input type="url" id="society_web_input" name="society_url" value="<?php echo ToolBox::toHtml($society->getUrl()); ?>" size="55" class="form-control" onchange="javascript:checkUrlInput('society_web_input', 'society_web_link');" /> 
        			<a id="society_web_link" href="#" style="display: none">[voir]</a>
        		</div>
        		
        		<div class="form-group">
        			<label>Adresse</small></label>
        			<input type="text" name="society_address" value="<?php echo ToolBox::toHtml($society->getAddress()); ?>" size="55" class="form-control" />
        		</div>

        		<div class="form-group">
        			<label>Téléphone</label>
        			<input type="tel"	name="society_phone" value="<?php echo ToolBox::toHtml($society->getPhone()); ?>" size="20" class="form-control" />
        		</div>
    		</div>
    		
    		<div class="col-md-6">
        		<div class="form-group">
        			<label>Activité</label>
        			<select name="industries_ids[]" multiple="multiple" size="9"  class="form-control">
        	    		<option value="">-- choisir --</option>
            			<?php echo $system->getIndustryOptionsTags($society->getIndustriesIds()) ?>
            		</select>
        		</div>
        		
        		<div class="form-group">
        			<label>Description</label>
            		<textarea name="society_description" cols="51" rows="5" class="form-control"><?php echo $society->getDescription(); ?></textarea>
            	</div>
    		</div>
    	</div>
   	
    	<button name="task" type="submit" value="registration" class="btn btn-primary">Enregistrer</button>
    	<button name="task" type="submit" value="deletion" class="btn btn-secondary">Supprimer</button>
    </form>
</div>
<script type="text/javascript">
	$(document).ready(function(){
	    $('#s_parent_name_i').autocomplete({
			minLength: 2,
	   		source: function( request, response ) {
	            $.ajax({
					method:'GET',
	                url:'api/society_names.json.php',
	                dataType: 'json',
	                data:{
	                    'query': request.term
	                 },
	                 dataFilter: function(data,type){
	                     return JSON.stringify(JSON.parse(data).names);
	                 },
	                 success : function(data, textStatus, jqXHR){
						response(data);
	                 }
	         	})
	   		}
	   	});	    
	})
</script>
</body>
</html>