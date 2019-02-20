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

$membership = new Membership();

$fb = new UserFeedBack();

if (!empty($_REQUEST['membership_id'])) {
	//
	// la participation à traiter est identifiée
	//
	$membership->setId($_REQUEST['membership_id']);
	if ($membership->feed()) {
		$membership->getIndividual()->feed();
		$membership->getSociety()->feed();		
	} else {
		header('location:index.php');
		exit;		
	}
} else {
	//
	// la participation est nouvelle
	//
	if (!empty($_REQUEST['individual_id'])) {
		$applicant = new Individual($_REQUEST['individual_id']);
		$applicant->feed();
		$membership->setIndividual($applicant);
	}
	if (!empty($_REQUEST['society_id'])) {
		$applicant = new Society($_REQUEST['society_id']);
		$applicant->feed();
		$membership->setSociety($applicant);
	}
}

if (isset($_POST['task'])) {
	
	ToolBox::formatUserPost($_POST);
	
	switch($_POST['task']) {
		case 'membership_submission':
			//
			// enregistrement des données de la participation
			//
			$membership->feed($_POST);
			if (! is_a($membership->getSociety(), 'Society') && !empty($_POST['society_name'])) {
				// aucune société n'est encore déclarée comme contexte de la participation
				$s = new Society();
				$s->feed($_POST);
				if (!$s->identifyFromName()) $s->toDB();
				$membership->setSociety($s);
			}
			if (!empty($_POST['newsociety_id'])) {
				// demande de transfert de la participation dans une autre société
				$membership->setSociety(new Society($_POST['newsociety_id']));
			}
			if (! is_a($membership->getIndividual(), 'Individual') && !empty($_POST['individual_lastName'])) {
				// personne n'est déclaré comme participant
				$i = new Individual();
				$i->feed($_POST);
				if (!$i->identifyFromName()) $i->toDB();
				$membership->setIndividual($i);
			}
			if ($membership->toDB()) {
				// enregistrement effectif !
				if (isset($_REQUEST['serie']) && $_REQUEST['serie']==1) {
					// enchaînement sur la déclaration d'une autre participation

					$fb = new UserFeedBack();
					
					if (isset($applicant)) {
						switch (get_class($applicant)) {
							case 'Society' :
								$fb->addSuccessMessage('La participation de '.$membership->getHtmlLinkToIndividual().' est enregistrée.');
								$membership = new Membership();
								$membership->setSociety($applicant);
								break;
							case 'Individual' :
								$fb->addSuccessMessage('La participation à '.$membership->getHtmlLinkToSociety().' est enregistrée.');
								$membership = new Membership();
								$membership->setIndividual($applicant);
								break;						
						}
					} else {
						$fb->addSuccessMessage('Participation enregistrée.');
					}
					
					// dans le cadre d'une série on propose de conserver la fonction de la particpation précédente
					if (isset($_REQUEST['title'])) {
						$membership->setTitle($_REQUEST['title']);
					}

				} else {
					// pas de déclaration en série, redirection vers l'écran de l'entité à l'origine de la demande de déclaration
					if (isset($applicant)) {
						switch (get_class($applicant)) {
							case 'Society' :
								header('location:society.php?society_id='.$applicant->getId());
								exit;
							case 'Individual' :
								header('location:individual.php?individual_id='.$applicant->getId());
								exit;						
						}
					} else {
						$fb->addSuccessMessage('Mise à jour de la participation effective.');
					}
				}
			}			
			break;
		case 'membership_deletion':
			// demande de suppression de la participation
			$individual = $membership->getIndividual();
			$membership->delete();
			header('location:individual.php?individual_id='.$individual->getId());
			exit;
	}
}

// on détermine le titre de la page
if ($membership->isSocietyIdentified() && $membership->isIndividualIdentified()) {
	$h1_content = $membership->getHtmlLinkToIndividual(). ' <small>chez '.$membership->getHtmlLinkToSociety().'</small>';
} elseif ($membership->isIndividualIdentified()) {
	$h1_content = 'Une participation de '.$membership->getHtmlLinkToIndividual();
} elseif ($membership->isSocietyIdentified()) {
	$h1_content = 'Une participation à '.$membership->getHtmlLinkToSociety();
} else {
	$h1_content = 'Une participation';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo strip_tags($h1_content); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
    <script type="text/javascript" src="js/controls.js"></script>
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script></head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1 class="bd-title"><?php echo $h1_content ?></h1>
	<?php echo $fb->toHtml() ?>
	<section>
    	<form id="membership_form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    		<?php
    		if ($membership->hasId()) {
    			echo '<input name="membership_id" type="hidden" value="'.$membership->getId().'" />';
    		}
    		if (isset($applicant)) {
    			switch (get_class($applicant)) {
    				case 'Society':
    					echo '<input name="society_id" type="hidden" value="'.$applicant->getId().'" />';
    					break;
    				case 'Individual' :
    					echo '<input name="individual_id" type="hidden" value="'.$applicant->getId().'" />';
    					break;
    			}
    			
    		}

			if (!$membership->isSocietyIdentified()) {
				echo '<div class="form-group">';
				echo '<label for="s_name_i">Nom de la Société</label>';
				echo '<input id="s_name_i" name="society_name" type="text" class="form-control" maxlength="255" size="35" />';
				echo '</div>';
			} else {
				if ($membership->isIndividualIdentified()) {
					$relatedSocieties = $membership->getSociety()->getRelatedSocieties();
					if (count($relatedSocieties)>0) {
						echo '<div class="form-group">';
						echo '<label for="newsociety_id">Transférer dans une société liée</label>';
						echo '<select name="newsociety_id" class="form-control">';
						echo '<option value="">-- choisir --</option>';
						echo $membership->getSociety()->getRelatedSocietiesOptionsTags();
						echo '</select>';
						echo '</div>';
					}
				}	
			}

			if (!$membership->isIndividualIdentified()) {
				//echo '<fieldset>';
				//echo '<legend>Qui ?</legend>';
				echo '<div class="form-row">';
				/*
				echo '<div class="form-group col-md-2">';
				echo '<label for="individual_salutation_i">Civilité</label>';
				echo '<select id="individual_salutation_i" name="individual_salutation" class="form-control">';
				echo '<option value="">-- choisis --</option>';
				echo Individual::getSalutationOptionsTags();
				echo '</select>';
				echo '</div>';
				*/
				echo '<div class="form-group col-md-4">';
				echo '<label for="individual_firstName_i">Prénom</label>';
				echo '<input id="individual_firstName_i" name="individual_firstName" type="text" maxlength="255" class="form-control" />';
				echo '</div>';
				echo '<div class="form-group col-md-6">';
				echo '<label for="individual_lastName_i">Nom</label>';
				echo '<input id="individual_lastName_i" name="individual_lastName" type="text" maxlength="255" class="form-control" />';
				echo '</div>';
				echo '</div>';
				//echo '</fieldset>';
			}
			?>

			<div class="form-row">
				<div class="form-group col-md-4">
					<label for="title_i">Fonction</label>
					<input id="title_i" name="title" type="text" value="<?php echo $membership->getTitle(); ?>" size="35" maxlength="255" class="form-control" />
				</div>
				<div class="form-group col-md-4">
					<label for="department_i">Service</label>
					<input id="department_i" name="department" type="text" value="<?php echo $membership->getDepartment(); ?>" size="35" maxlength="255" class="form-control" /> 
				</div>
				<div class="form-group col-md-2">
					<label for="init_year_i">Année d'ouverture</label>
					<input id="init_year_i" name="init_year" type="text" value="<?php echo $membership->getAttribute('init_year'); ?>" size="20" class="form-control" />
				</div>
				<div class="form-group col-md-2">
					<label for="end_year_i">Année de clôture</label>
					<input id="end_year_i" name="end_year" type="text" value="<?php echo $membership->getAttribute('end_year'); ?>" size="20" class="form-control" />
				</div>
			</div>
			
    		<div class="form-row">
				<div class="form-group col-md-4">
					<label for="phone_i">Téléphone</label>
					<input id="phone_i" name="phone" type="tel" value="<?php echo $membership->getPhone(); ?>" size="15" maxlength="255" class="form-control" />
				</div>
				
				<div class="form-group col-md-8">
					<label for="email_i">Mél</label>
					<input id="email_i" name="email" type="email" value="<?php echo $membership->getEmail(); ?>" size="35" maxlength="255" class="form-control" />
				</div>
			</div>

			<div class="form-group">
				<label for="comment_i">Commentaire</label>
				<textarea id="comment_i" name="description" cols="51" rows="5" class="form-control"><?php echo $membership->getDescription(); ?></textarea>
			</div>
			
			<div class="form-group">
				<label for="membership_url_i">Sur le web</label>
				<input id="membership_url_i" name="url" type="url" value="<?php echo $membership->getUrl(); ?>" size="35" maxlength="255" class="form-control" onchange="javascript:checkUrlInput('membership_url_i', 'membership_url_link');" />
				<a id="membership_url_link" href="#" style="display: none">[voir]</a>
			</div>

			<?php if (!$membership->hasId()) : ?>
			<div class="form-group">
				<div class="form-check">
				  <input class="form-check-input" type="checkbox" value="1" id="serie_i" name="serie" <?php if(isset($_REQUEST['serie']) && $_REQUEST['serie']==1) echo ' checked' ?>>
				  <label class="form-check-label" for="serie_i">Enchaîner sur la déclaration d'une autre participation ?</label>
				</div>
			</div>
			<?php endif; ?>
			
			<button name="task" type="submit" value="membership_submission" class="btn btn-primary">Enregistrer</button>

    		<?php if ($membership->hasId()) : ?>
    			<button name="task" type="submit" value="membership_deletion" class="btn btn-default">Supprimer</button>
    		<?php endif; ?>
    	</form>
	</section>
	
	<?php
		if (!empty($membership->getTitle())) {
			echo '<nav><p>Voir tous les gens ayant comme fonction <a href="title.php?title='.urlencode($membership->getTitle()).'">'.ToolBox::toHtml($membership->getTitle()).'</a>.</p></nav>';
		}
	?>
	
	<script type="text/javascript">
		$(document).ready(function(){
			$('#individual_firstName_i').focus();

			$('#s_name_i').autocomplete({
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
		   		},
		        focus: function( event, ui ) {
					$('#s_name_i').val( ui.item.value );
		        	return false;
		        },
		        select: function( event, ui ) {
					$('#s_name_i').val( ui.item.value );
		        	return false;
		        },
		        _renderItem: function( ul, item ) {
				    return $( "<li>" ).append(item.label).appendTo( ul );
			    }
		   	});
		    $('#title_i').autocomplete({
				minLength: 3,
		   		source: function( request, response ) {
		            $.ajax({
						method:'GET',
		                url:'api/membership_titles.json.php',
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
		        },
		        _renderItem: function( ul, item ) {
				    return $( "<li>" ).append(item.value + ' <small>(' + item.count +')</small>').appendTo( ul );
			    }
		   	});
		    $('#department_i').autocomplete({
				minLength: 3,
		   		source: function( request, response ) {
		            $.ajax({
						method:'GET',
		                url:'api/membership_departments.json.php',
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
		        },
		        _renderItem: function( ul, item ) {
				    return $( "<li>" ).append(item.value + ' <small>(' + item.count +')</small>').appendTo( ul );
			    }
		   	});	    		    
		});
	</script>
</div>
</body>
</html>
