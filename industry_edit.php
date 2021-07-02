<?php
function __autoload($class_name) {
	$path = './classes/';
	if (is_file ( $path . $class_name . '.class.php' )) {
		include_once $path . $class_name . '.class.php';
	} elseif ($path . $class_name . '.interface.php') {
		include_once $path . $class_name . '.interface.php';
	}
}
$system = new System('./config/host.json');

require_once 'config/boot.php';

session_start();

if (empty ($_SESSION['user_id'])) {
	header('Location:login.php');
	exit;
} else {
	$user = new User($_SESSION['user_id']);
	$user->feed();
}

if (isset($_REQUEST['id'])) {

	$industry = $system->getIndustryFromId($_REQUEST['id']);
	//print_r($industry);

	ToolBox::formatUserPost($_POST);
	
	if (!empty($_POST ['task'])) {
		switch ($_POST ['task']) {
			case 'registration' :
				if (!empty($_POST['name'] && strcmp($industry->getName(), $_POST['name'])!=0)) {
					
					$i = $system->getIndustryFromName($_POST['name']);
					
					$industry->setName($_POST['name']);
					$industry->toDB();

					if (is_a($i,'Industry') && $i->getId() != $industry->getId()) {
						$system->mergeIndustries($industry, $i);
					}
				}
				header('Location:industries.php');
				exit;
			default :
				trigger_error('La tâche à éxécuter est inconnue');
		}
	}
} else {
	header('Location:industries.php');
	exit;
}

$h1_content = 'Une activité';
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php strip_tags($h1_content) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
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
    	<?php echo '<input name="id" type="hidden" value="'.$industry->getId().'" />';	?>
    	<div class="row">
    		<div class="col-md-12">
            	<div class="form-group">
        	    	<label>Désignation de l'activité</label>
        	    	<input type="text" name="name" value="<?php echo ToolBox::toHtml($industry->getName()) ?>" size="35" class="form-control" />
        		</div>
    		</div>
    	</div>
   	
    	<button name="task" type="submit" value="registration" class="btn btn-secondary">Enregistrer</button>
    	<!-- <button name="task" type="submit" value="deletion" class="btn btn-secondary">Supprimer</button> -->
    	<a href="industries.php">Annuler</a>
    </form>
</div>
</body>
</html>