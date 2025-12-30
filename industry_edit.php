<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System( './config/host.json' );
$systemIdInSession = $system->getAppliName();

session_start();

if (empty ($_SESSION[$systemIdInSession]['user_id'])) {
	header('Location:login.php');
	exit;
} else {
	$user = new User($_SESSION[$systemIdInSession]['user_id']);
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
				header('Location:societies_industries.php');
				exit;
			default :
				trigger_error('La tâche à éxécuter est inconnue');
		}
	}
} else {
	header('Location:societies_industries.php');
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
    <link type="text/css" rel="stylesheet" href="<?php echo PHOSPHOR_URI ?>"></link>    
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<main class="container-fluid">
    <h1><?php echo $h1_content ?></h1>
    
    <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    	<?php echo '<input name="id" type="hidden" value="'.$industry->getId().'" />';	?>
    	<div class="row">
    		<div class="col-md-12">
            	<div class="form-group">
        	    	<label for="industry_name_i">Désignation de l'activité</label>
        	    	<input id="industry_name_i" type="text" name="name" value="<?php echo ToolBox::toHtml($industry->getName()) ?>" size="35" class="form-control" />
        		</div>
    		</div>
    	</div>

		<div>   	
	    	<a href="societies_industries.php" class="btn btn-link">Quitter</a>
	    	<!-- <button name="task" type="button" value="deletion" class="btn  btn-outline-secondary">supprimer</button> -->
	    	<button name="task" type="submit" value="registration" class="btn btn-primary">Enregistrer</button>
    	</div>
    </form>
</main>
</body>
</html>