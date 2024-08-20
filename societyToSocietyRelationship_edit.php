<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System( 'config/host.json' );

session_start ();

if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {
	$user = new User ( $_SESSION ['user_id'] );
	$user->feed ();
}

$relationship = new Relationship();

if (! empty ( $_GET ['relationship_id'] )) {
	// la participation à traiter est identifiée
	$relationship->setId ( $_GET ['relationship_id'] );
	$relationship->feed ();
	$relationship->feedItems ();
} else {
	if (! empty ( $_GET ['item0_id'] )) {
		$s0 = new Society($_GET ['item0_id']);
		$s0->feed();
		$relationship->setItem($s0,0);
	}
	if (! empty ( $_GET ['item1_role'] )) {
		$relationship->setItemRole($_GET ['item1_role'],1);
	}
}

// Formatage des données saisies par l'utilisateur
if (isset ( $_POST )) {
	ToolBox::formatUserPost ( $_POST );
}

if (isset ( $_POST ['relationship_submission'] )) {
	// enregistrement des données de la participation
	$relationship->feed ( $_POST );
	if (!$relationship->isItemKnown(0) && !empty($_POST['item0_name'])) {
		$s0 = new Society ();
		$s0->setName ( $_POST ['item0_name'] );
		if (! $s0->identifyFromName()) {
			$s0->toDB ();
		}
		$relationship->setItem($s0,0);
	}
	if (!$relationship->isItemKnown(1) && !empty($_POST['item1_name'])) {
		$s1 = new Society();
		$s1->setName($_POST['item1_name']);
		if(!$s1->identifyFromName()) {
			$s1->toDB();
		}
		$relationship->setItem($s1,1);
	}
	if ($relationship->toDB ()) {
		if (isset($_POST['serie']) && strcmp($_POST['serie'],'none')!=0) {
			$lastSavedRelationship = clone $relationship;
			$relationship = new Relationship();

			switch ($_POST['serie']) {
				case 'similarRelationship':
					$relationship->setItem ($lastSavedRelationship->getItem(0), 0);
					$relationship->setItemRole($lastSavedRelationship->getItemRole(0),0);
					$relationship->setItemRole($lastSavedRelationship->getItemRole(1),1);
					$relationship->setInitYear($lastSavedRelationship->getInitYear());
					$relationship->setEndYear($lastSavedRelationship->getEndYear());
					$relationship->setDescription($lastSavedRelationship->getDescription());
					$relationship->setUrl($lastSavedRelationship->getUrl());
					break;
				case 'anotherRelationship':
					$relationship->setItem ($lastSavedRelationship->getItem(0), 0);
			}
		} else {
			header ( 'location:society.php?society_id=' . $relationship->getItem(0)->getId () );
			exit;
		}
	}
}
if ($relationship->areItemsBothKnown()) {
	$h1_content = 'Une relation entre ' . $relationship->getItem(0)->getHtmlLinkToSociety() . ' et ' . $relationship->getItem(1)->getHtmlLinkToSociety();
} else {
	$h1_content = $relationship->isItemKnown(0) ? 'Une relation de ' . $relationship->getItem(0)->getHtmlLinkToSociety() : 'Une relation';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo strip_tags($h1_content) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="js/society-name-autocomplete.js"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1 class="bd-title"><?php echo $h1_content ?></h1>
	<section>
		<form id="relationship_form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			
			<input name="item0_class" type="hidden" value="Society" />
			<input name="item1_class" type="hidden" value="Society" />
			<datalist id="role_list">
				<?php
				foreach (Relationship::getKnownRoles() as $r) {
					echo '<option value="'.ToolBox::toHtml($r['role']).'"/>';
				}
				?>
			</datalist>
			<?php
			if ($relationship->getId ())
				echo '<input name="relationship_id" type="hidden" value="' . $relationship->getId () . '" />';

			if (!$relationship->isItemKnown(0)) {
				// la première société est à définir
				echo '<div class="form-row">';
				echo '<div class="form-group col-md-6">';
			    echo '<label for="s1_name_i">Nom de la première société</label>';
				echo '<input is="society-name-autocomplete" id="s1_name_i" name="item0_name" type="text" maxlength="255" class="form-control" />';
				echo '</div>';
				echo '<div class="form-group col-md-6">';
    			echo '<label for="s1_role_i">Son rôle</label>';
    			echo '<input id="s1_role_i" name="item0_role" type="text" list="role_list" value="'.$relationship->getItemRole(0).'" size="20" class="form-control" />';
				echo '</div>';
				echo '</div>';
			} else {
				// la première société est définie
				echo '<div class="form-group">';
				echo '<input name="item0_id" type="hidden" value="' . $relationship->getItem(0)->getId () . '"/>';
    			echo '<label for="s1_role_i">Rôle '. $relationship->getItem(0)->getHtmlLinkToSociety() . '</label>';
    			echo '<input id="s1_role_i" name="item0_role" type="text" list="role_list" value="'.$relationship->getItemRole(0).'" size="20" class="form-control" />';
				echo '</div>';
			}
			if (!$relationship->isItemKnown(1)) {
				// la deuxième société est à définir
				echo '<div class="form-row">';
				echo '<div class="form-group col-md-6">';
				echo '<label for="s2_name_i">Nom de la deuxième société</label>';
				echo '<input is="society-name-autocomplete" id="s2_name_i" name="item1_name" type="text" maxlength="255" class="form-control" />';
				echo '</div>';
				echo '<div class="form-group col-md-6">';
    			echo '<label for="s2_role_i">Son rôle</label>';
    			echo '<input id="s2_role_i" name="item1_role" type="text" list="role_list" value="'.$relationship->getItemRole(1).'" size="20" class="form-control" />';
				echo '</div>';
				echo '</div>';
			} else {
				// la deuxième société est définie
				echo '<div class="form-group">';
				echo '<input name="item1_id" type="hidden" value="' . $relationship->getItem(1)->getId () . '"/>';
    			echo '<label for="s2_role_i">Rôle '. $relationship->getItem(1)->getHtmlLinkToSociety() . '</label>';
    			echo '<input id="s2_role_i" name="item1_role" type="text" list="role_list" value="'.$relationship->getItemRole(1).'" size="20" class="form-control" />';
				echo '</div>';
			}
			?>
			<div class="form-row">
				<div class="form-group col-md-3">
	    			<label for="init_year_i">Année de démarrage</label>
	    			<input id="init_year_i" name="init_year" type="text" value="<?php echo $relationship->getAttribute('init_year'); ?>" size="4" class="form-control" />
				</div>
				
				<div class="form-group col-md-3">
	    			<label for="end_year_i">Année de clôture</label>
	    			<input id="end_year_i" name="end_year" type="text" value="<?php echo $relationship->getAttribute('end_year'); ?>" size="4" class="form-control" />
				</div>
			</div>
			
			<div class="form-group">
    			<label for="description_area">Commentaire</label>
    			<textarea id="description_area" name="description" cols="51" rows="5" class="form-control"><?php echo $relationship->getAttribute('description'); ?></textarea>
			</div>
			
			<div class="form-group">
    			<label for="relationship_url_input">url</label>
    			<input id="relationship_url_input" name="url" type="url" size="35" class="form-control" onchange="javascript:checkUrlInput('relationship_url_input', 'relationship_web_link');" value="<?php echo $relationship->getUrl(); ?>" />
    			<a id="relationship_web_link" href="#" style="display: none">[voir]</a>
			</div>
			
			<?php
			    $serie_options = array();
			    
		        $serie_options['none'] = 'Ne pas enchaîner';
                $serie_options['similarRelationship'] = 'Enchaîner avec une relation similaire';
                $serie_options['anotherRelationship'] = 'Enchaîner avec une autre relation';
		        
			    $toCheck = empty($_POST['serie']) ? 'none' : $_POST['serie'];
			    
			    if (count($serie_options)>1) {
			        echo '<div class="form-group">';
			        $i = 0;
			        foreach ($serie_options as $v=>$l) {
			            $i++;
			            echo '<div class="form-check form-check-inline">';
			            echo '<input class="form-check-input" type="radio" name="serie" id="serie_opt'.$i.'" value="'.$v.'"';
			            if (strcmp($toCheck,$v)==0) {
			                echo ' checked';
			            }
			            echo '>';
			            echo '<label class="form-check-label" for="serie_opt'.$i.'">'.$l.'</label>';
			            echo '</div>';
			        }
			        echo '</div>';
			    }
			?>
			
			<div>
				<?php
	    			if ($relationship->isItemKnown(0)) {
						echo '<a href="'.$relationship->getItem(0)->getDisplayUrl().'" class="btn btn-link">Quitter</a>';
	    			}
				?>
				<button name="relationship_submission" type="submit" value="1" class="btn btn-primary">Enregistrer</button>
			</div>
		</form>
		<?php
		if ($relationship->getId()) {
			echo '<p>Tu veux oublier cette relation ? C\'est <a id="delete_a" href="#">ici</a>.</p>';
		}
		?>		
	</section>
</div>

<script>
	const apiUrl = '<?php echo $system->getApiUrl() ?>';
	
	document.addEventListener("DOMContentLoaded", function() {
		customElements.define("society-name-autocomplete", SocietyNameAutocomplete, { extends: "input" });	

		<?php if($relationship->hasId()): ?>		
		const delete_a = document.getElementById('delete_a');
		delete_a.addEventListener('click', function (event) {
		  event.preventDefault();
		  var xhr = new XMLHttpRequest();
		  xhr.open("POST", "api/relationships/", true);
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
		  xhr.send("id=<?php echo $relationship->getId() ?>&task=deletion");
		});
		<?php endif; ?>
	});
</script>

</body>
</html>