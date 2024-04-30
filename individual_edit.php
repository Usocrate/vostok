<?php
require_once 'config/boot.php';
require_once 'classes/System.class.php';
$system = new System( 'config/host.json' );

session_start();

if (empty($_SESSION['user_id'])) {
    header('Location:login.php');
    exit;
} else {
    $user = new User($_SESSION['user_id']);
    $user->feed();
}

$messages = array();

$individual = new Individual();

if (! empty($_REQUEST['individual_id'])) {
    $individual->setId($_REQUEST['individual_id']);
    $individual->feed();
} elseif (! empty($_REQUEST['individual_lastname'])) {
    $individual->setLastName($_REQUEST['individual_lastname']);
}

if (isset($_POST['toDB_order'])) {

    ToolBox::formatUserPost($_POST);
    
    // enregistrement des données de l'individu
    $individual->feed($_POST);
    
    if ( ! empty ( $_POST['individual_address'] ) ) {
    	$individual->getAddressFromGoogle($_POST['individual_address']);
    }
    $individual->toDB();
    
    // rattachement éventuel à une première société
    if ( ! empty( $_POST['society_id'] ) ) {
        $individual->addMembershipRow($_POST['society_id']);
    }
    
    // upload d'un fichier image
    if ( ! empty ($_FILES['individual_photo_file']) && $_FILES['individual_photo_file']['size']>0) {
    	$individual->storePhotoFile($_FILES['individual_photo_file']);
    	$individual->reworkPhotoFile();
    }
    
    header('Location:individual.php?individual_id=' . $individual->getId());
    exit;
}

$doc_title = $individual->hasId() ? $individual->getWholeName() : 'Un individu';
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo ToolBox::toHtml($doc_title) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
</head>

<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
		
	<h1 class="bd-title"><a href="individual.php?individual_id=<?php echo $individual->getId() ?>"><?php echo ToolBox::toHtml($doc_title); ?></a></h1>
	
	<?php
        if (count($messages) > 0) {
            echo '<div class="alert alert-info" role="alert">';
            foreach ($messages as $m) {
                echo '<p>' . ToolBox::toHtml($m) . '</p>';
            }
            echo '</div>';
        }
    ?>
  
	<section>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
		<?php
			if (isset($_REQUEST['society_id'])) {
				echo '<input name="society_id" type="hidden" value="'.$_REQUEST['society_id'].'" />';
			}
			if ($individual->hasId()) {
				echo '<input name="individual_id" type="hidden" value="'.$individual->getId().'" />';
			}
		?>
		<div class="form-row">
			<div class="form-group col-md-3">
				<label for="individual_firstname_i">Prénom</label>
				<input id="individual_firstname_i" type="text" name="individual_firstName" value="<?php echo ToolBox::toHtml($individual->getFirstName()) ?>" size="25" class="form-control" /> 
			</div>
			<div class="form-group col-md-3">
				<label for="individual_lastname_i">Nom</label>
				<input id="individual_lastname_i" type="text" name="individual_lastName" value="<?php echo ToolBox::toHtml($individual->getLastName()) ?>" size="25"  class="form-control" />
			</div>
			<div class="form-group col-md-6">
				<label for="individual_photo_file_i">Photo</label>
				<input id="individual_photo_file_i" type="file" name="individual_photo_file" size="55" class="form-control-file"></input>
				<small id="passwordHelpBlock" class="form-text text-muted">
				<?php if ($individual->getGoogleQueryUrl('images')) : ?>
					<a href="<?php echo $individual->getGoogleQueryUrl('images') ?>" target="_blank">Une photo chez Google ?</a>
				<?php endif; ?>
				</small>
			</div>
		</div>

		<div class="form-row">
			<div class="form-group col-md-4">
				<label for="individual_twitter_id_i" class="sr-only">Compte Twitter</label>
				<div class="input-group">
					<div class="input-group-prepend">
				      <div class="input-group-text"><i class="fab fa-twitter"></i></div>
				    </div>
					<input type="text" id="individual_twitter_id_i" name="individual_twitter_id" value="<?php echo $individual->getTwitterId(); ?>" size="15" maxlength="15" class="form-control" placeholder="identifiant Twitter" />
				</div>
			</div>
			
			<div class="form-group col-md-4">
				<label for="individual_Linkedin_id_i" class="sr-only">Compte Linkedin</label>
				<div class="input-group">
					<div class="input-group-prepend">
				      <div class="input-group-text"><i class="fab fa-linkedin"></i></div>
				    </div>
					<input type="text" id="individual_Linkedin_id_i" name="individual_linkedin_id" value="<?php echo $individual->getLinkedinId(); ?>" size="15" maxlength="255" class="form-control" placeholder="Person ID Linkedin" />
				</div>
			</div>			
		</div>
		<div class="row">
			<div class="col">
				<fieldset>
					<legend>Infos complémentaires</legend>
					<div class="form-group">	
						<label for="individual_birth_date_i">Date de naissance</label>
						<input id="individual_birth_date_i" type="date" name="individual_birth_date" value="<?php echo ToolBox::toHtml($individual->getBirthDate()) ?>" size="10" class="form-control" />
					</div>
					<div class="form-group">
    					<label for="individual_description_i">Notes</label>
    					<textarea id="individual_description_i" cols="51" rows="5" name="individual_description" class="form-control"><?php echo $individual->getDescription(); ?></textarea>
					</div>
					
					<div class="form-group">
						<label for="individual_web_input">Sur le web</label>
						<input type="text" id="individual_web_input" name="individual_web" value="<?php echo $individual->getWeb(); ?>" size="55" maxlength="255" class="form-control" onchange="checkUrlInput('individual_web_input', 'individual_web_link');" /> 
						<a id="individual_web_link" href="#" target="_blank" style="display: none">[voir]</a>
					</div>
				</fieldset>
			</div>
			<div class="col">
				<fieldset>
					<legend>Coordonnées (perso)</legend>
					<div class="form-group">
    					<label>Tél. mobile</label>
    					<input type="tel" name="individual_mobile" value="<?php echo ToolBox::toHtml($individual->getMobilePhoneNumber()) ?>" size="15" class="form-control"/>
					</div>
					
					<div class="form-group">
						<label>Téléphone</label>
						<input type="tel" name="individual_phone" value="<?php echo ToolBox::toHtml($individual->getPhoneNumber()) ?>" size="15" class="form-control" />
    				</div>
    				
    				<div class="form-group">
    					<label>Email</label>
    					<input type="email" name="individual_email" value="<?php echo ToolBox::toHtml($individual->getEmailAddress()) ?>" size="55" class="form-control" />
					</div>
					
					<div class="form-group">
						<label>Adresse</label>
						<input type="text" name="individual_address" value="<?php echo ToolBox::toHtml($individual->getAddress()) ?>" size="55" class="form-control" />
					</div>
				
				</fieldset>
			</div>
		</div>
		<div>
    		<?php if (!$individual->hasId()) : ?>
    			<a href="individuals.php" class="btn btn-link">Quitter</a>
    		<?php endif; ?>
    		
    		<?php if ($individual->hasId()) : ?>
    		    <a href="individual.php?individual_id=<?php echo $individual->getId() ?>" class="btn btn-link">Quitter</a>
	    	<?php endif; ?>		

			<button name="toDB_order" type="submit" value="1" class="btn btn-primary">Enregistrer</button>
		</div>
	</form>
	<?php
		if($individual->hasId()) {
			echo '<p>Tu veux oublier cet individu ? C\'est <a id="delete_a" href="#">ici</a>.</p>';
		}
	?>
	</section>
</div>
<?php if($individual->hasId()): ?>
<script type="text/javascript">
	document.addEventListener("DOMContentLoaded", function() {
		const delete_a = document.getElementById('delete_a');
		delete_a.addEventListener('click', function (event) {
		  event.preventDefault();
		  var xhr = new XMLHttpRequest();
		  xhr.open("POST", "api/individuals/", true);
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
		  xhr.send("id=<?php echo $individual->getId() ?>&task=deletion");
		});
	});
</script>
<?php endif; ?>
</body>
</html>