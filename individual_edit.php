<?php
require_once 'config/main.inc.php';

session_start();
ToolBox::getDBAccess();

if (empty($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $user = new User($_SESSION['user_id']);
    $user->feed();
}

$doc_title = 'éditer la fiche détaillée d\'un individu';
$messages = array();

$individual = new Individual();

if (! empty($_REQUEST['individual_id'])) {
    $individual->setId($_REQUEST['individual_id']);
}

if (isset($_POST['deletion_order'])) {
    $individual->delete();
    header('Location:individuals.php');
    exit();
} elseif (isset($_POST['toDB_order'])) {
    ToolBox::formatUserPost($_POST);
    // enregistrement des données de l'individu
    $individual->feed($_POST);
    $individual->toDB();
    // rattachement éventuel à une première société
    if ($_POST['society_id']) {
        $individual->addMembershipRow($_POST['society_id']);
    }
    // upload d'un fichier image (à terminer)
    if (isset($_FILES['individual_photo_file'])) {
        $messages[] = $individual->filePhoto($_FILES['individual_photo_file']) ? 'La photo est mise à jour' : 'Echec de la mise à jour de la photo';
    }
    header('Location:individual.php?individual_id=' . $individual->getId());
    exit();
} else {
    $individual->feed();
}
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo ToolBox::toHtml($doc_title) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <script type="text/javascript" src="js/controls.js"></script>
    <link rel="stylesheet" href="<?php echo SKIN_URL ?>main.css" type="text/css">
<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script><script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script></head>

<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
		
	<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>
	
	<?php
        if (count($messages) > 0) {
            echo '<section>';
            foreach ($messages as $m) {
                echo '<p>' . ToolBox::toHtml($m) . '</p>';
            }
            echo '</section>';
        }
    ?>
	<blockquote>Les informations suivantes concernent la personne d&rsquo;un point de vue strictement individuel, hors toute société.</blockquote>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
		<?php if (isset($_REQUEST['society_id'])) echo '<input name="society_id" type="hidden" value="'.$_REQUEST['society_id'].'" />'?>
		<input name="individual_id" type="hidden" value="<?php echo $individual->getId() ?>" />
		<div class="row">
			<div class="col-md-4">
				<fieldset>
					<legend>identité</legend>
					
					<div class="form-group">
						<label for="individual_salutation_i">civilité</label>
						<select id="individual_salutation_i" name="individual_salutation" class="form-control">
							<option value="">-- choisir --</option>
							<?php echo $individual->getSalutationOptionsTags($individual->getSalutation()); ?>
						</select>
					</div>
					
					<div class="form-group">
						<label for="individual_firstname_i">prénom</label>
						<input id="individual_firstname_i" type="text" name="individual_firstName" value="<?php echo ToolBox::toHtml($individual->getFirstName()) ?>" size="25" class="form-control" /> 
					</div>
				
					<div class="form-group">
						<label for="individual_lastname_i">nom</label>
						<input id="individual_lastname_i" type="text" name="individual_lastName" value="<?php echo ToolBox::toHtml($individual->getLastName()) ?>" size="25"  class="form-control" />
					</div>
					
					<div class="form-group">	
						<label for="individual_birth_date_i">date de naissance</label>
						<input id="individual_birth_date_i" type="text" name="individual_birth_date" value="<?php echo ToolBox::toHtml($individual->getBirthDate()) ?>" size="10" class="form-control" />
					</div>
					
					<div class="form-group">
						<label for="individual_photo_file_i">Photo</label>
						<input id="individual_photo_file_i" type="file" name="individual_photo_file" size="55" class="form-control"></input>
					</div>
					
					<?php if ($individual->getGoogleQueryUrl()) : ?>
						<p><a href="<?php echo $individual->getGoogleQueryUrl() ?>"><?php echo $individual->getWholeName() ?> dans Google</a></p>
					<?php endif; ?>
					
					<?php if ($individual->getGoogleQueryUrl('images')) : ?>
						<p><a href="<?php echo $individual->getGoogleQueryUrl('images') ?>">La photo de <?php echo $individual->getWholeName() ?> dans Google ?</a></p>
					<?php endif; ?>
					</fieldset>
			</div>
			<div class="col-md-4">
				<fieldset>
					<legend>infos complémentaires</legend>
					
					<div class="form-group">
    					<label for="individual_description_i">description</label>
    					<textarea id="individual_description_i" cols="51" rows="5" name="individual_description" class="form-control">
    						<?php echo ToolBox::toHtml($individual->getDescription()); ?>
    					</textarea>
					</div>
					
					<div class="form-group">
						<label for="individual_web_input">page web</label>
						<input type="text" id="individual_web_input" name="individual_web" value="<?php echo $individual->getWeb(); ?>" size="55" maxlength="255" class="form-control" onchange="checkUrlInput('individual_web_input', 'individual_web_link');" /> 
						<a id="individual_web_link" href="#" style="display: none">[voir]</a>
					</div>
					
				</fieldset>
			</div>
			<div class="col-md-4">
				<fieldset>
					<legend>coordonnées (perso)</legend>
					
					<div class="form-group">
    					<label>tél. mobile</label>
    					<input type="tel" name="individual_mobile" value="<?php echo ToolBox::toHtml($individual->getMobilePhoneNumber()) ?>" size="15" class="form-control"/>
					</div>
					
					<div class="form-group">
						<label>téléphone</label>
						<input type="tel" name="individual_phone" value="<?php echo ToolBox::toHtml($individual->getPhoneNumber()) ?>" size="15" class="form-control" />
    				</div>
    				<div class="form-group">
    					<label>email</label>
    					<input type="email" name="individual_email" value="<?php echo ToolBox::toHtml($individual->getEmailAddress()) ?>" size="55" class="form-control" />
					</div>
					
					<div class="form-group">
						<label>adresse</label>
						<input type="text" name="individual_street" value="<?php echo ToolBox::toHtml($individual->getstreet()) ?>" size="55" class="form-control" />
					</div>
					
					<div class="form-group">
						<label>ville</label>
						<input type="text" name="individual_city" value="<?php echo ToolBox::toHtml($individual->getCity()) ?>" size="35" class="form-control" />
					</div>
					
					<div class="form-group">
						<label>code postal</label>
						<input type="text" name="individual_postalCode" value="<?php echo ToolBox::toHtml($individual->getPostalCode()) ?>" size="15" class="form-control" />
					</div>
					
					<div class="form-group">
						<label>pays</label>
						<input type="text" name="individual_country" value="<?php echo ToolBox::toHtml($individual->getCountry()) ?>" size="35" class="form-control" />
					</div>
				</fieldset>
			</div>
		</div>
		<div>
			<button name="toDB_order" type="submit" value="1" class="btn btn-primary">enregistrer</button>
			<button name="deletion_order" type="submit" value="1" class="btn btn-default">supprimer</button>
		</div>
	</form>
</div>
</body>
</html>