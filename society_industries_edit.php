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
$society->setId($_REQUEST['society_id']);
$society->feed();

$industries = $system->getIndustries(null, 'Alphabetical');

$h1_content = $society->getHtmlLinkToSociety();

if (isset($_POST['task'])) {
	switch ($_POST['task']) {
		case 'registration':
			ToolBox::formatUserPost($_POST);

			/*
			$formerIndustriesIds = $society->getIndustriesIds();
			$checkedIndustriesIds = $_POST['industries_ids'];
			
			$toRemoveIndustriesIds = array_diff($formerIndustriesIds, $checkedIndustriesIds);
			$toAddIndustriesIds = array_diff($checkedIndustriesIds, $formerIndustriesIds);
						
			*/
			
			$society->resetIndustries();
			if (isset($_POST['industries_ids']) && is_array($_POST['industries_ids'])) {
				foreach ($_POST['industries_ids'] as $id) {
					if (empty($id)) continue;
					$i = new Industry($id);
					$society->addIndustry($i);
				}
			}
			$society->saveIndustries();
		
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
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>    
</head>
<body>
<div class="container-fluid">
    <h1><?php echo $h1_content ?></h1>
    <section>
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    	<?php
    	if (isset($_REQUEST['society_id'])) {
    		echo '<input name="society_id" type="hidden" value="'.$society->getId().'" />';
    	}
    	?>
    	<div>
    	<fieldset class="tag-cloud">
    		<legend>Activités principales</legend>
   			<?php
      			foreach ($industries as $key=>$i) {
      				$input_id = 'i'.$key;
      				
      				if ($society->isIndustry($i)) {
      					echo '<label for="'.$input_id.'" class="checked">';
      					echo '<input id="'.$input_id.'"  name="industries_ids[]" type="checkbox" checked value="'.$i->getId().'" />';
      					echo Toolbox::toHtml($i->getName());
      					echo '</label>';
      				} else {
      					echo '<label for="'.$input_id.'">';
      					echo '<input id="'.$input_id.'"  name="industries_ids[]" type="checkbox" value="'.$i->getId().'" />';
      					echo Toolbox::toHtml($i->getName());
      					echo '</label>';
      				}
       			}
   			?>
    	</filedset>
    	</div>
   	
    	<div>
   		    <a href="society.php?society_id=<?php echo $society->getId() ?>" class="btn btn-link">Quitter</a>
	    	<button name="task" type="submit" value="registration" class="btn btn-primary">Enregistrer</button>
    	</div>
    </form>
    </section>
</div>
</body>
</html>