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
ToolBox::getDBAccess();

if (empty ($_SESSION['user_id'])) {
	header('Location:login.php');
	exit;
} else {
	$user = new User($_SESSION['user_id']);
	$user->feed();
}

$membership = new Membership();

// Formatage des données saisies par l'utilisateur
if (isset($_POST)) ToolBox::formatUserPost($_POST);

if (!empty($_REQUEST['membership_id'])) {
	//
	// la participation à traiter est identifiée
	//
	$membership->setId($_REQUEST['membership_id']);
	$membership->feed();

	if (isset($_POST['task']) && strcmp($_POST['task'], 'membership_deletion')==0) {
		// demande de suppression de la participation
		$individual =& $membership->getIndividual();
		$membership->delete();
		header('location:individual.php?individual_id='.$individual->getId());
		exit;
	} else {
		// récupération des données en base
		$individual =& $membership->getIndividual();
		if (is_a($individual, 'Individual')) $individual->feed();
		$society =& $membership->getSociety();
		if (is_a($society, 'Society')) $society->feed();
	}
} else {
	//
	// la participation est nouvelle
	//
	if (!empty($_REQUEST['individual_id'])) {
		$individual = new Individual($_REQUEST['individual_id']);
		$individual->feed();
		$membership->setIndividual($individual);
	}
	if (!empty($_REQUEST['society_id'])) {
		$society = new Society($_REQUEST['society_id']);
		$society->feed();
		$membership->setSociety($society);
	}
}

if (isset($_POST['task']) && strcmp($_POST['task'], 'membership_submission')==0) {
	//
	// enregistrement des données de la participation
	//
	$membership->feed($_POST);
	if (!is_a($society, 'Society') && !empty($_POST['society_name'])) {
		// aucune société n'est encore déclarée comme contexte de la participation
		$society = new Society();
		$society->feed($_POST);
		if (!$society->identifyFromName()) $society->toDB();
		$membership->setSociety($society);
	}
	if (!empty($_POST['newsociety_id'])) {
		// demande de transfert de la participation dans une autre société
		$membership->setSociety(new Society($_POST['newsociety_id']));
	}
	if (!is_a($individual, 'Individual') && !empty($_POST['individual_lastName'])) {
		// personne n'est déclaré comme participant
		$individual = new Individual();
		$individual->feed($_POST);
		if (!$individual->identifyFromName()) $individual->toDB();
		$membership->setIndividual($individual);
	}
	if ($membership->toDB()) {
		// enregistrement effectif !
		header('location:individual.php?individual_id='.$individual->getId());
		exit;
	}
}
$doc_title = isset($individual) && $individual->getId() ? 'Une participation de '.$individual->getWholeName() : 'Une participation';
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo $doc_title ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <script type="text/javascript" src="js/controls.js"></script>
    <link rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css" type="text/css">
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script></head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<?php 
		if (isset($individual)&& $individual->getId()) {
			if (isset($society)&& $society->getId()) {
				$h1 = '<a href="/individual.php?individual_id='.$individual->getId().'">'.ToolBox::toHtml($individual->getWholeName()).'</a> chez <a href="/society.php?society_id='.$society->getId().'">'.ToolBox::toHtml($society->getName()).'</a>';
			} else {
				$h1 = 'Une participation de <a href="/individual.php?individual_id='.$individual->getId().'">'.ToolBox::toHtml($individual->getWholeName()).'</a>';
			}
		} else {
			$h1 = 'Une participation';
		}
	?>
	<h1><?php echo $h1 ?></h1>
	<section>
    	<form id="membership_form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    		<?php
    		if ($membership->getId()) {
    			echo '<input name="membership_id" type="hidden" value="'.$membership->getId().'" />';
    		}
    		if (isset($_REQUEST['individual_id'])) {
    			echo '<input name="individual_id" type="hidden" value="'.$_REQUEST['individual_id'].'" />';
    		}
    		if (isset($_REQUEST['society_id'])) {
    			echo '<input name="society_id" type="hidden" value="'.$_REQUEST['society_id'].'" />';
    		}
    		?>
    		<p>
    			<?php
    			if (!isset($society) || !$society->getId()) {
    				echo '<div class="form-group">';
    				echo '<label for="s_name_i">Nom de la Société</label>';
    				echo '<input id="s_name_i" name="society_name" type="text" class="form-control" maxlength="255" size="35" />';
    				echo '</div>';
    			} else {
    				echo '<div class="form-group">';
    				echo '<label for="newsociety_id">Transférer dans une société liée</label>';
    				echo '<select name="newsociety_id" class="form-control">';
    				echo '<option value="">-- choisir --</option>';
    				echo $society->getRelatedSocietiesOptionsTags();
    				echo '</select>';
    				echo '</div>';
    			}
    			?>
    		</p>
    		<div class="row">
    			<div class="col-md-6">
    				<?php
    				if (!isset($individual)) {
    					$individual = new Individual();
    					echo '<fieldset>';
    					echo '<legend>Qui ?</legend>';
    					echo '<div class="form-group">';
    					echo '<label for="individual_salutation_i">Civilité</label>';
    					echo '<select id="individual_salutation_i" name="individual_salutation">';
    					echo '<option>-- choisis --</option>';
    					echo $individual->getSalutationOptionsTags();
    					echo '</select>';
    					echo '</div>';
    					echo '<div class="form-group">';
    					echo '<label for="individual_firstName_i">Prénom</label>';
    					echo '<input id="individual_firstName_i" name="individual_firstName" type="text" maxlength="255" class="form-control" />';
    					echo '</div>';
    					echo '<div class="form-group">';
    					echo '<label for="individual_lastName_i">Nom</label>';
    					echo '<input id="individual_lastName_i" name="individual_lastName" type="text" maxlength="255" class="form-control" />';
    					echo '</div>';
    					echo '</fieldset>';
    				}
    				?>
    				<fieldset>
    					<legend>Activité</legend>
    					
    					<div class="form-group">
        					<label for="department_i">Service</label>
        					<input id="department_i" name="department" type="text" value="<?php echo $membership->getDepartment(); ?>" size="35" maxlength="255" class="form-control" /> 
    					</div>
    					
    					<div class="form-group">
        					<label for="title_i">Fonction</label>
        					<input id="title_i" name="title" type="text" value="<?php echo $membership->getTitle(); ?>" size="35" maxlength="255" class="form-control" /> 
    					</div>
    					
    					<div class="form-group">
        					<label>Page perso</label>
        					<input id="membership_url_input" name="url" type="url" value="<?php echo $membership->getUrl(); ?>" size="35" maxlength="255" class="form-control" onchange="javascript:checkUrlInput('membership_url_input', 'membership_url_link');" />
        					<a id="membership_url_link" href="#" style="display: none">[voir]</a>
    					</div>
    					
    				</fieldset>
    
    				<fieldset>
    					<legend>Contact</legend>
    					
    					<div class="form-group">
        					<label for="phone_i">Téléphone</label>
        					<input id="phone_i" name="phone" type="tel" value="<?php echo $membership->getPhone(); ?>" size="15" maxlength="255" class="form-control" />
    					</div>
    					
    					<div class="form-group">
    						<label>Mél</label>
    						<input name="email" type="email" value="<?php echo $membership->getEmail(); ?>" size="35" maxlength="255" class="form-control" />
    					</div>
     				</fieldset>
    			</div>
    			<div class="col-md-6">
    				<fieldset>
    					<legend>Période</legend>
    					<div class="form-group">
    						<label for="init_date_i">Début</label>
    						<input id="init_date_i" name="init_date" type="text" value="<?php echo $membership->getAttribute('init_date'); ?>" size="20" class="form-control" />
						</div>
						<div class="form-group">
							<label for="end_date_i">Fin</label>
							<input id="end_date_i" name="end_date" type="text" value="<?php echo $membership->getAttribute('end_date'); ?>" size="20" class="form-control" />
						</div>
    				</fieldset>
    				<div class="form-group">
    					<label for="comment_i">Commentaire</label>
    					<textarea id="comment_i" name="description" cols="51" rows="5" class="form-control"><?php echo $membership->getDescription(); ?></textarea>
    				</div>
    			</div>
    		</div>
    		<button name="task" type="submit" value="membership_submission" class="btn btn-primary">Enregistrer</button>
    		<?php if ($membership->getId()) : ?>
    			<button name="task" type="submit" value="membership_deletion" class="btn btn-default">Supprimer</button>
    		<?php endif; ?>
    	</form>
	</section>

	<script type="text/javascript">
		$(document).ready(function(){
			$('#s_name_i').autocomplete({
				minLength: 2,
		   		source: function( request, response ) {
		            $.ajax({
						method:'GET',
		                url:'society_names.json.php',
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
		   		},
		        focus: function( event, ui ) {
					$('#s_name_i').val( ui.item.value );
		        	return false;
		        },
		        select: function( event, ui ) {
					$('#s_name_i').val( ui.item.value );
		        	return false;
		        }
		   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
			    return $( "<li>" ).append(item.label).appendTo( ul );
		    };
		    $('#title_i').autocomplete({
				minLength: 3,
		   		source: function( request, response ) {
		            $.ajax({
						method:'GET',
		                url:'membership_titles.json.php',
		                dataType: 'json',
		                data:{
		                    'query': request.term
		                 },
		                 dataFilter: function(data,type){
		                     return JSON.stringify(JSON.parse(data).titles);
		                 },
		                 success : function(data, textStatus, jqXHR){
							response(data);
		                 }
		         	})
		   		},
		        focus: function( event, ui ) {
					$('#title_i').val( ui.item.value );
		        	return false;
		        },
		        select: function( event, ui ) {
					$('#title_i').val( ui.item.value );
		        	return false;
		        }
		   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
			    return $( "<li>" ).append(item.value + ' <small>(' + item.count +')</small>').appendTo( ul );
		    };
		    $('#department_i').autocomplete({
				minLength: 3,
		   		source: function( request, response ) {
		            $.ajax({
						method:'GET',
		                url:'membership_departments.json.php',
		                dataType: 'json',
		                data:{
		                    'query': request.term
		                 },
		                 dataFilter: function(data,type){
		                     return JSON.stringify(JSON.parse(data).departments);
		                 },
		                 success : function(data, textStatus, jqXHR){
							response(data);
		                 }
		         	})
		   		},
		        focus: function( event, ui ) {
					$('#department_i').val( ui.item.value );
		        	return false;
		        },
		        select: function( event, ui ) {
					$('#department_i').val( ui.item.value );
		        	return false;
		        }
		   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
			    return $( "<li>" ).append(item.value + ' <small>(' + item.count +')</small>').appendTo( ul );
		    };	    		    
		});
	</script>
</div>
</body>
</html>
