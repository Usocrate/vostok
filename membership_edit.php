<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System ( 'config/host.json' );

session_start ();

if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {
	$user = new User ( $_SESSION ['user_id'] );
	$user->feed ();
}

$membership = new Membership ();

$fb = new UserFeedBack ();

if (! empty ( $_REQUEST ['membership_id'] )) {
	//
	// la participation à traiter est identifiée
	//
	$membership->setId ( $_REQUEST ['membership_id'] );
	if (! $membership->feed ()) {
		header ( 'location:index.php' );
		exit ();
	}
} else {
	//
	// la participation est nouvelle
	//
	if (! empty ( $_REQUEST ['individual_id'] )) {
		$applicant = new Individual ( $_REQUEST ['individual_id'] );
		$applicant->feed ();
		$membership->setIndividual ( $applicant );
	}
	if (! empty ( $_REQUEST ['society_id'] )) {
		$applicant = new Society ( $_REQUEST ['society_id'] );
		$applicant->feed ();
		$membership->setSociety ( $applicant );
	}
}

if (isset ( $_POST ['task'] )) {

	ToolBox::formatUserPost ( $_POST );
	
	switch ($_POST ['task']) {
		case 'membership_submission' :
			//
			// enregistrement des données de la participation
			//
			$membership->feed ( $_POST );
			if (! is_a ( $membership->getSociety (), 'Society' ) && ! empty ( $_POST ['society_name'] )) {
				// aucune société n'est encore déclarée comme contexte de la participation
				$s = new Society ();
				$s->feed ( $_POST, 'society_' );
				if (! $s->identifyFromName ())
					$s->toDB ();
				$membership->setSociety ( $s );
			}
			if (! empty ( $_POST ['newsociety_id'] )) {
				// demande de transfert de la participation dans une autre société
				$membership->setSociety ( new Society ( $_POST ['newsociety_id'] ) );
			}
			if (! is_a ( $membership->getIndividual (), 'Individual' ) && ! empty ( $_POST ['individual_lastName'] )) {
				// personne n'est déclaré comme participant
				$i = new Individual ();
				$i->feed ( $_POST, 'individual_' );
				if ($i->identifyFromName ()) {
					// nouvelle participation d'un individu déjà enregistré
					$membership->setIndividual ( $i );
				} else {
					// participation d'un nouvel individu					
					if ($i->toDB()) {
						$membership->setIndividual ( $i );	
					}
				}
			}
			if ($membership->toDB ()) {
				// enregistrement effectif !
				if (isset ( $_REQUEST ['serie'] ) && strcmp ( $_REQUEST ['serie'], 'none' ) != 0) {

					// enchaînement sur la déclaration d'une autre participation

					$lastSavedMembership = clone $membership;

					if (isset ( $applicant )) {
						switch (get_class ( $applicant )) {
							case 'Society' :
								$fb->addSuccessMessage ( 'La participation de ' . $membership->getHtmlLinkToIndividual () . ' est enregistrée.' );
								$membership = new Membership ();
								$membership->setSociety ( $applicant );
								break;
							case 'Individual' :
								$fb->addSuccessMessage ( 'La participation à ' . $membership->getHtmlLinkToSociety () . ' est enregistrée.' );
								$membership = new Membership ();
								$membership->setIndividual ( $applicant );
						}
					} else {
						$fb->addSuccessMessage ( 'Participation enregistrée.' );
						$membership = new Membership ();
					}

					switch ($_REQUEST ['serie']) {

						case 'similarMembershipInSociety' :

							// on demande à conserver les données pour la création d'une nouvelle participation à la même société sur le même modèle
							$membership->setSociety ( $lastSavedMembership->getSociety () );

							if ($lastSavedMembership->hasTitle ()) {
								$membership->setTitle ( $lastSavedMembership->getTitle () );
							}
							if ($lastSavedMembership->hasDepartment ()) {
								$membership->setDepartment ( $lastSavedMembership->getDepartment () );
							}
							if ($lastSavedMembership->hasInitYear ()) {
								$membership->setInitYear ( $lastSavedMembership->getInitYear () );
							}
							if ($lastSavedMembership->hasEndYear ()) {
								$membership->setEndYear ( $lastSavedMembership->getEndYear () );
							}
							if ($lastSavedMembership->hasDescription ()) {
								$membership->setDescription ( $lastSavedMembership->getDescription () );
							}
							if ($lastSavedMembership->hasUrl ()) {
								$membership->setUrl ( $lastSavedMembership->getUrl () );
							}
							break;

						case 'anotherMembershipInSociety' :
							$membership->setSociety ( $lastSavedMembership->getSociety () );
							break;

						case 'anotherIndividualMembership' :
							$membership->setIndividual ( $lastSavedMembership->getIndividual () );
							break;

						case 'anotherIndividualMembershipInSociety' :
							$membership->setSociety ( $lastSavedMembership->getSociety () );
							$membership->setIndividual ( $lastSavedMembership->getIndividual () );
							break;
					}
				} else {

					// pas de déclaration en série, redirection vers l'écran de l'entité à l'origine de la demande de déclaration
					if (isset ( $applicant )) {
						//*
						switch (get_class ( $applicant )) {
							case 'Society' :
								header ( 'location:society.php?society_id=' . $applicant->getId () );
								exit ();
							case 'Individual' :
								header ( 'location:individual.php?individual_id=' . $applicant->getId () );
								exit ();
						}
						//*/
					} else {
						$fb->addSuccessMessage ( 'Mise à jour de la participation effective.' );
					}
				}
			}
			break;
	}
}

// on détermine le titre de la page
if ($membership->isSocietyIdentified () && $membership->isIndividualIdentified ()) {
	$h1_content = $membership->getHtmlLinkToIndividual ('friendly') . ' <small>chez ' . $membership->getHtmlLinkToSociety () . '</small>';
} elseif ($membership->isIndividualIdentified ()) {
	$h1_content = 'Une participation de ' . $membership->getHtmlLinkToIndividual ();
} elseif ($membership->isSocietyIdentified ()) {
	$h1_content = 'Une participation à ' . $membership->getHtmlLinkToSociety ();
} else {
	$h1_content = 'Une participation';
}
?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo strip_tags($h1_content); ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>"	integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>"	crossorigin="anonymous" />
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
	<?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="js/society-name-autocomplete.js"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<main class="container-fluid">
	<h1><?php echo $h1_content ?></h1>
	<?php echo $fb->toHtml() ?>
	<section>
			<form id="membership_form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    		<?php
				if ($membership->hasId ()) {
					echo '<input name="membership_id" type="hidden" value="' . $membership->getId () . '" />';
				}
				if (isset ( $applicant )) {
					switch (get_class ( $applicant )) {
						case 'Society' :
							echo '<input id="society_id_i" name="society_id" type="hidden" value="' . $applicant->getId () . '" />';
							break;
						case 'Individual' :
							echo '<input id="individual_id_i" name="individual_id" type="hidden" value="' . $applicant->getId () . '" />';
							break;
					}
				}

				if (! $membership->isSocietyIdentified ()) {
					echo '<div class="form-group">';
					echo '<label for="s_name_i">Nom de la Société</label>';
					echo '<input id="s_name_i" name="society_name" is="society-name-autocomplete" type="text" class="form-control" maxlength="255" size="35"';
					echo '/>';
					echo '</div>';
				} else {
					if ($membership->isIndividualIdentified ()) {
						$relatedSocieties = $membership->getSociety ()->getRelatedSocieties ();
						if (count ( $relatedSocieties ) > 0) {
							echo '<div class="form-group">';
							echo '<label for="newsociety_id">Transférer dans une société liée</label>';
							echo '<select name="newsociety_id" class="form-control">';
							echo '<option value="">-- choisir --</option>';
							echo $membership->getSociety ()->getRelatedSocietiesOptionsTags ();
							echo '</select>';
							echo '</div>';
						}
					}
				}

				if (! $membership->isIndividualIdentified ()) {
					echo '<div id="individual-form-row" class="form-row">';
					echo '<div class="form-group col-md-4">';
					echo '<label for="individual_firstName_i">Prénom</label>';
					echo '<input id="individual_firstName_i" name="individual_firstName" type="text" maxlength="255" class="form-control" />';
					echo '</div>';
					echo '<div class="form-group col-md-6">';
					echo '<label for="individual_lastName_i">Nom</label>';
					echo '<input id="individual_lastName_i" name="individual_lastName" type="text" maxlength="255" class="form-control" />';
					echo '</div>';
					echo '</div>';
				}
			?>

			<div class="form-row">
				<div class="form-group col-md-4 col-sm-12">
					<label for="title_i">Fonction</label> <input id="title_i" name="title" type="text" list="title_list" value="<?php echo $membership->getTitle(); ?>" size="35" maxlength="255" class="form-control" />
					<datalist id="title_list">
					<?php
					foreach ( $system->getMembershipTitles () as $t ) {
						echo '<option>' . ToolBox::toHtml ( $t ['label'] ) . '</option>';
					}
					?>
					</datalist>
				</div>
				<div class="form-group col-md-4 col-sm-12">
					<label for="department_i">Service</label> <input id="department_i" name="department" type="text" value="<?php echo $membership->getDepartment(); ?>" size="35" maxlength="255" class="form-control" />
				</div>
				<div class="form-group col-md-2 col-sm-6">
					<label for="init_year_i">Année d'ouverture</label> <input id="init_year_i" name="init_year" type="text"	value="<?php echo $membership->getInitYear(); ?>" size="20"	class="form-control" />
				</div>
				<div class="form-group col-md-2 col-sm-6">
					<label for="end_year_i">Année de clôture</label> <input	id="end_year_i" name="end_year" type="text"	value="<?php echo $membership->getEndYear(); ?>" size="20" class="form-control" />
				</div>
			</div>
			
			<?php if ( empty($_REQUEST['serie']) || strcmp($_REQUEST['serie'], 'similarMembershipInSociety')!=0 || ($lastSavedMembership->hasPhone() || $lastSavedMembership->hasEmail()) ) : ?>
    		<div class="form-row">
					<div class="form-group col-md-4">
						<label for="phone_i">Téléphone</label> <input id="phone_i" name="phone" type="tel" value="<?php echo $membership->getPhone(); ?>" size="15"	maxlength="255" class="form-control" />
					</div>

					<div class="form-group col-md-8">
						<label for="email_i">Mél</label> <input id="email_i" name="email" type="email" value="<?php echo $membership->getEmail(); ?>" size="35" maxlength="255" class="form-control" />
					</div>
				</div>
			<?php endif; ?>

			<div class="form-group">
					<label for="comment_i">Notes</label>
					<textarea id="comment_i" name="description" cols="51" rows="5" class="form-control"><?php echo $membership->getDescription(); ?></textarea>
				</div>

				<div class="form-group">
					<label for="membership_url_i">Sur le web</label> <input	id="membership_url_i" name="url" type="url"	value="<?php echo $membership->getUrl(); ?>" size="35"	maxlength="255" class="form-control" onchange="javascript:checkUrlInput('membership_url_i', 'membership_url_link');" />
					<a id="membership_url_link" href="#" style="display: none">[voir]</a>
				</div>

				<?php
				$serie_options = array ();
	
				if (isset ( $applicant )) {
	
					$serie_options ['none'] = 'Ne pas enchaîner';
	
					switch (get_class ( $applicant )) {
						case 'Society' :
							$serie_options ['similarMembershipInSociety'] = 'Enchaîner avec une participation similaire';
							$serie_options ['anotherMembershipInSociety'] = 'Enchaîner avec une autre participation';
							break;
						case 'Individual' :
							$serie_options ['anotherIndividualMembership'] = 'Enchaîner avec ' . $membership->getHtmlLinkToIndividual ( 'friendly' ) . ' dans une autre société';
							$serie_options ['anotherIndividualMembershipInSociety'] = 'Enchaîner avec une autre participation';
							break;
					}
				}
	
				$toCheck = empty ( $_REQUEST ['serie'] ) ? 'none' : $_REQUEST ['serie'];
	
				if (count ( $serie_options ) > 1) {
					echo '<div class="form-group">';
					$i = 0;
					foreach ( $serie_options as $v => $l ) {
						$i ++;
						echo '<div class="form-check form-check-inline">';
						echo '<input class="form-check-input" type="radio" name="serie" id="serie_opt' . $i . '" value="' . $v . '"';
						if (strcmp ( $toCheck, $v ) == 0) {
							echo ' checked';
						}
						echo '>';
						echo '<label class="form-check-label" for="serie_opt' . $i . '">' . $l . '</label>';
						echo '</div>';
					}
					echo '</div>';
				}
				?>
			<div>
				<?php
				if (isset ( $applicant )) {
					switch (get_class ( $applicant )) {
						case 'Society' :
							echo '<a href="' . $applicant->getDisplayUrl () . '" class="btn btn-link">Quitter</a>';
							break;
						case 'Individual' :
							echo '<a href="' . $applicant->getDisplayUrl () . '" class="btn btn-link">Quitter</a>';
							break;
					}
				}
				?>
				<button name="task" type="submit" value="membership_submission"	class="btn btn-primary">Enregistrer</button>
			</div>
		</form>
		<?php
		if ($membership->hasId ()) {
			echo '<p>Tu veux oublier cette participation ? C\'est <a id="delete_a" href="#">ici</a>.</p>';
		}
		?>
	</section>
</main>

<script>
	const apiUrl = '<?php echo $system->getApiUrl() ?>';
	
	document.addEventListener("DOMContentLoaded", function() {
		customElements.define("society-name-autocomplete", SocietyNameAutocomplete, { extends: "input" });
					
		const ifni = document.getElementById('individual_firstName_i');
		const ilni = document.getElementById('individual_lastName_i');

		function displayAlert(id, value) {
			let i = document.getElementById(id);
			let aid = id+'_a';
			let a = document.getElementById(aid);
			
			if (value !== null && value !== undefined && value.length>0) {
		        if (a!==null) {
		        	a.remove();
		        }
		        let html = '<div id="'+aid+'" class="alert alert-info">'+value+'</div>';
		        i.insertAdjacentHTML('afterend', html);
			} else {
		        if (a!==null) {
		        	a.remove();
		        }
			}
		}

		function removeDisplayedAlert() {
			let alerts = document.querySelectorAll('.alert');
			
			if (alerts !== null) {
				for (let a of alerts) {
					a.remove();
				}
			}
		}

		function checkIndividualMemberships(e) {

			removeDisplayedAlert();

			let exe_condition = ifni.value.length>0 && ilni.value.length>0;
			if (exe_condition == false) {
				return false;
			}  

			event.preventDefault();
			
			let xhr = new XMLHttpRequest();
			xhr.open("GET", "api/memberships/?individual_firstName="+ifni.value+"&individual_lastName="+ilni.value+"&society_id="+document.getElementById('society_id_i').value, true);
			xhr.responseType = 'json';
			
			xhr.onreadystatechange = function () {
				if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
				
					let titles=[];
					for (let m of this.response) {
						titles.push(m.title);
					}
					if (titles.length>0) {
						let html = '';
						html+= ifni.value+" "+ilni.value+" est déjà ";
						for (i=0;i<titles.length;i++) {
							html+= titles[i];
							if (i < (titles.length-2)) {
								html+=', ';
							} else if(i < (titles.length-1)) {
								html+=' et ';
							}
						}
						html+='.<br>';
						displayAlert('individual-form-row', html);
					}
		    	}				  
			  };
			  xhr.send();
		}

		if (ifni !==null) {
			ifni.addEventListener('change', checkIndividualMemberships);
			ifni.focus();
		}
		
		if (ilni !==null) {
			ilni.addEventListener('change', checkIndividualMemberships);
		}
		
		<?php if($membership->hasId()): ?>
		document.getElementById('delete_a').addEventListener('click', function (event) {
			event.preventDefault();
			let xhr = new XMLHttpRequest();
			xhr.open("POST", "api/memberships/", true);
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
		  xhr.send("id=<?php echo $membership->getId() ?>&task=deletion");
		});
		<?php endif; ?>
	});
</script>
</body>
</html>