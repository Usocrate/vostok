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

require_once './config/boot.php';

session_start();
ToolBox::getDBAccess();

if (empty ($_SESSION['user_id'])) {
	header('Location:login.php');
	exit;
} else {
	$user = new User($_SESSION['user_id']);
	$user->feed();
}

// messages à délivrer
$messages = array();

// Formatage des données saisies par l'utilisateur
if (isset($_POST)) {
	ToolBox::formatUserPost($_POST);
}

if (isset($_REQUEST['lead_id']) && is_numeric($_REQUEST['lead_id'])) {
	//
	// la piste est connue
	//
	$lead = new Lead($_REQUEST['lead_id']);
	if (!empty($_POST['deletion_order'])) {
		//
		// on supprime la piste
		//
		$lead->delete();
		header('Location:leads.php');
		exit;
	} else {
		//
		// on récupère les données de la piste
		//
		$lead->feed();
		$individual = $lead->getIndividual();
		if (isset($individual)) {
			$individual->feed();
		}
		$society = $lead->getSociety();
		if (isset($society)) $society->feed();
	}
} else {
	//
	// la piste est nouvelle
	//
	$lead = new Lead();
}

//
// Traitement de la société concernée par la piste.
//
if (isset($_REQUEST['society_id']) && is_numeric($_REQUEST['society_id'])) {
	//
	// on demande le rattachement de la piste à une société donnée.
	//
	$society = new Society($_REQUEST['society_id']);
	$society->feed();
	$lead->setSociety($society);
} elseif (isset($_POST['society_submission']) && !empty($_POST['society_submission'])) {
	//
	// on demande le rattachement de la piste à une nouvelle société.
	//
	$society = new Society();
	$society->feed($_POST);

	// on exige au moins un nom pour créer la Société
	if ($society->getName() || $society->getId()){
		if (!$society->getId()) $society->identifyFromName();

		// geolocalisation
		if (!empty($_POST['society_address'])) {
			$society->getAddressFromGoogle($_POST['society_address']);
		}

		//
		// société-mère
		//
		if (!empty($_POST['society_parent_name'])) {
			$society_parent = new Society();
			$society_parent->setName($_POST['society_parent_name']);
			if (!$society_parent->identifyfromName()) {
				// création d'une nouvelle société
				$society_parent->toDB();
			}
			$relationship = new Relationship();
			$relationship->setItem($society, 0);
			$relationship->setItemRole('filiale', 0);
			$relationship->setItem($society_parent, 1);
			$relationship->setItemRole('maison-mère', 1);
		}
		$society->toDB();
		if (isset($relationship)) $relationship->toDB();
		$lead->setSociety($society);

		//
		// activités
		//
		if (isset($_POST['industries_ids'])) {
			$society->resetIndustries();
			foreach ($_POST['industries_ids'] as $id) {
				if (empty($id)) continue;
				$i = new Industry($id);
				$society->addIndustry($i);
			}
			$society->saveIndustries();
		}
	}
}

//
// Traitement de l'individu lié à la piste
//
if (isset($_REQUEST['individual_id']) && is_numeric($_REQUEST['individual_id'])) {
	//
	// On demande l'association de la piste avec un individu donné
	//
	$individual = new Individual($_REQUEST['individual_id']);
	$individual->feed();
	$lead->setIndividual($individual);
} elseif (isset($_POST['individual_input']) && is_numeric($_POST['individual_input'])) {
	//
	// On demande l'association de la piste avec un nouvel individu
	//
	$individual = new Individual();
	$individual->feed($_POST);
	// on exige au moins un nom ou un prénom pour créer l'individu
	if ($individual->getFirstName() || $individual->getLastName() || $individual->getId()){
		/**
		 * si identifiant inconnu, tentative d'identification à partir du prénom et nom
		 */
		if (!$individual->getId() && $individual->getFirstName() && $individual->getLastName()) {
			$individual->identifyFromName();
		}
		$individual->toDB();
		$lead->setIndividual($individual);
	}
}

//
// Traitement de la participation de l'individu à la société concernée.
//
if (!empty($_POST['membership_submission'])) {
	//
	// enregistrement de la participation
	//
	$memberships = $individual->getMemberships($society);
	if (count($memberships)==0) {
		$membership = new Membership();
		$membership->feed($_POST, 'membership_');
		$membership->setIndividual($individual);
		$membership->setSociety($society);
		$membership->toDB();
	}
}

//
// Enregistrement des données de la piste.
//
if (!empty($_POST['toDB_order'])) {
	if (!empty($_POST['lead_shortdescription'])) {
		$lead->setShortDescription($_POST['lead_shortdescription']);
	}
	if (isset($_POST['lead_description'])) {
		$lead->setDescription($_POST['lead_description']);
	}
	if (isset($_POST['lead_type'])) {
		$lead->setType($_POST['lead_type']);
	}
	if (isset($_POST['lead_status'])) {
		$lead->setStatus($_POST['lead_status']);
	}
	if (isset($_POST['lead_source'])) {
		$lead->setSource($_POST['lead_source']);
	}
	if (isset($_POST['lead_source_description'])) {
		$lead->setSourceDescription($_POST['lead_source_description']);
	}
	$lead->toDB();
	header('Location:leads.php');
	exit;
}
$doc_title = isset($society) && $society->hasId() ? 'Une piste chez '.$society->getName() : 'Une piste à suivre';
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo ToolBox::toHtml($doc_title); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css" type="text/css">
    <link rel="icon" type="image/x-icon" href="<?php echo $system->getSkinUrl() ?>favicon.ico" />
    <script type="text/javascript" src="js/controls.js"></script>
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script>
</head>
<body id="leadEditDoc" >
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1><?php echo isset($society) && $society->hasId() ? 'Une piste chez <a href="/society.php?society_id='.$society->getId().'">'.$society->getNameForHtmlDisplay().'</a>' : 'Une piste à suivre'; ?>	</h1>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<input name="lead_id" type="hidden" value="<?php if ($lead->getId()) echo $lead->getId(); ?>" />
		<?php
		if (isset($society) && $society->hasId()) {
			echo '<input name="society_id" type="hidden" value="'.$society->getId().'"/>';
		}
		?>
        <div>
          <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#tab1" aria-controls="tab1" role="tab" data-toggle="tab">Piste</a></li>
            <?php if (!(isset($society ) && $society->hasId())): ?>
			<li role="presentation"><a href="#tab2" aria-controls="tab2" role="tab" data-toggle="tab">Société</a></li>
            <?php endif; ?>
            <li role="presentation"><a href="#tab3" aria-controls="tab3" role="tab" data-toggle="tab">Contact</a></li>
          </ul>
        
          <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="tab1">
				<div class="row">
        			<div class="col-md-6">
    					<section>
        					<div class="form-group">
            					<label for="l_description_i">Description</label>
            					<textarea id="l_description_i" name="lead_description" cols="55" rows="18" class="form-control"><?php echo $lead->getDescription() ?></textarea>
        					</div>
    					</section>
        			</div>
        			<div class="col-md-6">
						<section>
        					<div class="form-group">
            					<label for="l_shortdescription_i">Intitulé <small>(piste)</small></label>
            					<input id="l_shortdescription_i" name="lead_shortdescription" type="text" value="<?php echo ToolBox::toHtml($lead->getShortDescription()); ?>" maxlength="255" class="form-control" /> 
        					</div>
         					<div class="form-group">
            					<label for="lead_type_i">Type</label>
            					<input id="lead_type_i" name="lead_type" type="text" value="<?php echo ToolBox::toHtml($lead->getType()) ?>" class="form-control" />
            				</div>
            				<div class="form-group">
            					<label for="lead_source_i">Origine</label>
            					<input id="lead_source_i" name="lead_source" type="text" value="<?php echo ToolBox::toHtml($lead->getSource()) ?>" size="55" class="form-control" />
            				</div>
            				<div class="form-group">
            					<label for="l_source_description_ta">Précisions sur l'origine</label>
            					<textarea id="l_source_description_ta" name="lead_source_description" cols="55" rows="3" class="form-control"><?php echo ToolBox::toHtml($lead->getSourceDescription()); ?></textarea>
        					</div>
        					<div class="form-group">
            					<label for="l_status_i">Etat</label>
            					<select id="l_status_i" name="lead_status" class="form-control"><?php echo $lead->getStatusOptionsTags(); ?></select>
        					</div>
    					</section>
        			</div>
				</div>
			</div>
            <?php if (!(isset($society ) && $society->hasId())): ?>
            <div role="tabpanel" class="tab-pane" id="tab2">
				<div class="row">
					<div class="col-md-6">
						<section>
        					<div class="form-group">
            					<label for="s_name_i">nom</label>
            					<input id="s_name_i" name="society_name" type="text" size="35" class="form-control" />
        					</div>
        					    					
        					<div class="form-group">
            					<label for="s_description_ta">Description</label>
            					<textarea id="s_description_ta" name="society_description" cols="15" rows="15" class="form-control"></textarea>
        					</div>
        					
        					<div class="form-group">
            					<label for="s_parent_name_i">Société mère</label>
            					<input id="s_parent_name_i" type="text" name="society_parent_name" size="35" class="form-control" />
        					</div>
    					</section>
					</div>
					<div class="col-md-6">
						<section>
        					<div class="form-group">
            					<label for="ind_s">Activité</label>
            					<select id="ind_s" name="industries_ids[]" multiple="multiple" size="9" class="form-control">
            						<option value="">-- choisir --</option>
            						<?php echo isset($society) ? $system->getIndustryOptionsTags($society->getIndustriesIds()) : $system->getIndustryOptionsTags() ?>
            					</select>
        					</div>
        					
        					<div class="form-group">
            					<label for="s_url_i">URL</label>
            					<input id="s_url_i" name="society_url" type="url" size="35" onchange="javascript:checkUrlInput('s_url_i', 's_web_link_o');" class="form-control" /> 
            					<a id="s_web_link_o" href="#" style="display: none">[voir]</a> 
        					</div>
        					
        					<div class="form-group">
            					<label for="s_address_i">Adresse</label>
            					<input id="s_address_i" name="society_address" type="text" size="35" class="form-control" /> 
        					</div>
        					
        					<div class="form-group">
            					<label for="s_phone_i">Téléphone</label> 
            					<input id="s_phone_i" name="membership_phone" type="tel" size="12" class="form-control" /> 
        					</div>
        					
        					<input name="society_submission" type="hidden" value="1" />
    					</section>
					</div>
				</div>
			</div>
			<?php endif; ?>
            <div role="tabpanel" class="tab-pane" id="tab3">
				<?php if (!(isset($individual) && $individual->hasId())) : ?>
				<div class="row">
            		<div class="col-md-6">
        				<section>
           					<div>
            					<div class="radio">
            						<label for="i_o1"><input for="i_o1" name="individual_input" type="radio" value="1" <?php echo isset($society) && $society->hasId() ? 'checked="checked"' : 'disabled="disabled"' ?> /> un membre déjà enregistré</label>
            					</div>
            					<select id="i_s" name="individual_id" class="form-control" <?php if (!(isset($society) && $society->hasId())) echo ' style="display:none"' ?>>
            							<option value="">-- choisis --</option>
            							<?php if (isset($society)) echo $society->getMembersOptionsTags() ?>
            					</select>
        					</div>
        					
        					<div>
        						<div class="radio">
        							<label for="i_o2"><input id="i_o2" name="individual_input" type="radio" value="2" <?php if(!(isset($society) && $society->hasId())) echo ' checked="checked"' ?> /> un nouvel individu</label>
        						</div>
        						
        						<div class="form-group">
            						<label for="i_salutation_s">civilité</label> 
            						<select id="i_salutation_s" name="individual_salutation" class="form-control">
            							<option value="">-- choisis --</option>
            							<?php
            							$individual = new Individual;
            							echo $individual->getSalutationOptionsTags();
            							?>
            						</select>
        						</div>
        						
        						<div class="form-group">
            						<label for="i_firstName_i">prénom</label> 
            						<input id="i_firstName_i" name="individual_firstName" type="text" size="15" class="form-control" />
        						</div> 
        
        						<div class="form-group">
            						<label for="i_lastName_i">nom</label> 
            						<input id="i_lastName_i" name="individual_lastName" type="text" size="15" class="form-control" /> 
        						</div>
        						
        						<div class="form-group">
            						<label for="i_mobile_i">mobile</label>
            						<input id="i_mobile_i" name="individual_mobile" type="text" size="12" class="form-control" /> 
        						</div>
        												
        						<input name="individual_submission" type="hidden" value="1" />
           					</div>
        				</section>                		
            		</div>
            		<div class="col-md-6">
						<section id="membership_fieldset">
							<p>Quelle implication ?</p>
						
    						<div class="checkbox">
    							<label for="ms_submission_i"><input id="ms_submission_i" type="checkbox" name="membership_submission" value="1" checked /> fait partie de la société ?</label>
    						</div>
    						
							<div class="form-group">
    							<label for="ms_department_i">service</label> 
    							<input id="ms_department_i" name="membership_department" type="text" size="35" class="form-control" />
							</div>
							
							<div class="form-group"> 
    							<label for="ms_title_i">fonction</label> 
    							<input id="ms_title_i" name="membership_title" type="text" size="35" class="form-control" />
							</div>
							
							<div class="form-group"> 
    							<label for="ms_phone_i">téléphone</label> 
    							<input id="ms_phone_i" name="membership_phone" type="text" size="12" class="form-control" />
							</div>
							
							<div class="form-group"> 
								<label for="ms_email_i">email</label>
								<input id="ms_email_i" name="membership_email" type="email" size="35" class="form-control" />
							</div>
							
							<div class="form-group"> 
    							<label for="ms_url_i">Page perso.</label>
    							<input id="ms_url_i" name="membership_url" type="url" size="35" maxlength="255" class="form-control" onchange="javascript:checkUrlInput('ms_url_i', 'membership_url_link');" /> <a id="membership_url_link" href="#" style="display: none">[voir]</a>
    						</div>
						</section>                		
            		</div>
            	</div>
    			<?php endif; ?>
    			<?php
    			if (isset($individual) && $individual->hasId()) {
    				echo '<section>';
    				echo '<a href="individual.php?individual_id='.$individual->getId().'">'.$individual->getWholeName().'</a>';
    				//echo ' <small>(<a href="lead_contact_edit.php?lead_id='.$lead->getId().'">modifier</a>)</small>';
    				echo '<input name="individual_id" type="hidden" value="'.$individual->getId().'"/>';
    				echo '</section>';
    			}
    			?>
			</div>
          </div>
        </div>
		<div>
			<button name="toDB_order" type="submit" value="1" class="btn btn-primary">enregistrer</button>
			<button name="deletion_order" type="submit" value="1" class="btn btn-default">supprimer</button>
		</div>
	</form>
</div>

<script type="text/javascript">
	$(document).ready(function(){
	    $('#lead_type_i').autocomplete({
			minLength: 2,
	   		source: function( request, response ) {
	            $.ajax({
					method:'GET',
	                url:'lead_types.json.php',
	                dataType: 'json',
	                data:{
	                    'query': request.term
	                 },
	                 dataFilter: function(data,type){
	                     return JSON.stringify(JSON.parse(data).types);
	                 },
	                 success : function(data, textStatus, jqXHR){
						response(data);
	                 }
	         	})
	   		},
	        focus: function( event, ui ) {
				$('#lead_type_i').val( ui.item.value );
	        	return false;
	        },
	        select: function( event, ui ) {
				$('#lead_type_i').val( ui.item.value );
	        	return false;
	        }
	   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
		    return $( "<li>" ).append(item.value + ' <small>(' + item.count +')</small>').appendTo( ul );
	    };
	    $('#lead_source_i').autocomplete({
			minLength: 2,
	   		source: function( request, response ) {
	            $.ajax({
					method:'GET',
	                url:'lead_sources.json.php',
	                dataType: 'json',
	                data:{
	                    'query': request.term
	                 },
	                 dataFilter: function(data,type){
	                     return JSON.stringify(JSON.parse(data).sources);
	                 },
	                 success : function(data, textStatus, jqXHR){
						response(data);
	                 }
	         	})
	   		},
	        focus: function( event, ui ) {
				$('#lead_source_i').val( ui.item.value );
	        	return false;
	        },
	        select: function( event, ui ) {
				$('#lead_source_i').val( ui.item.value );
	        	return false;
	        }
	   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
		    return $( "<li>" ).append(item.value + ' <small>(' + item.count +')</small>').appendTo( ul );
	    };
	    <?php if (!(isset($society) && $society->hasId())): ?>
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
		    //alert(JSON.stringify(item));
		    return $( "<li>" ).append(item.label).appendTo( ul );
	    };
	    $('#s_parent_name_i').autocomplete({
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
				$('#s_parent_name_i').val( ui.item.value );
	        	return false;
	        },
	        select: function( event, ui ) {
				$('#s_parent_name_i').val( ui.item.value );
	        	return false;
	        }
	   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
		    //alert(JSON.stringify(item));
		    return $( "<li>" ).append(item.label).appendTo( ul );
	    };	    
	    <?php endif; ?>
	    $('#ms_title_i').autocomplete({
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
				$('#ms_title_i').val( ui.item.value );
	        	return false;
	        },
	        select: function( event, ui ) {
				$('#ms_title_i').val( ui.item.value );
	        	return false;
	        }
	   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
		    return $( "<li>" ).append(item.value + ' <small>(' + item.count +')</small>').appendTo( ul );
	    };
	    $('#ms_department_i').autocomplete({
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
				$('#ms_department_i').val( ui.item.value );
	        	return false;
	        },
	        select: function( event, ui ) {
				$('#ms_department_i').val( ui.item.value );
	        	return false;
	        }
	   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
		    return $( "<li>" ).append(item.value + ' <small>(' + item.count +')</small>').appendTo( ul );
	    };
	})
</script>
</body>
</html>