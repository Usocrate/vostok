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

session_start ();

//print_r($_POST);

if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {
	$user = new User ( $_SESSION ['user_id'] );
	$user->feed ();
}

$relationship = new Relationship();

// Formatage des données saisies par l'utilisateur
if (isset ( $_POST )) {
	ToolBox::formatUserPost ( $_POST );
}

if (! empty ( $_REQUEST ['relationship_id'] )) {
	// la participation à traiter est identifiée
	$relationship->setId ( $_REQUEST ['relationship_id'] );
	$relationship->feed ();
	
	if (isset ( $_POST ['relationship_deletion'] )) {
		// demande de suppression de la participation
		$item0 = $relationship->getItem(0);
		$relationship->delete();
		header('location:society.php?society_id='.$item0->getId());
		exit;
	} else {
		// récupération des données en base
		$item0 = $relationship->getItem ( 0 );
		if (is_object ( $item0 )) {
			$item0->feed ();
			$relationship->setItem($item0,0);
		}
		
		$item1 = $relationship->getItem ( 1 );
		if (is_object ( $item1 )) {
			$item1->feed ();
			$relationship->setItem($item1,1);
		}
	}
} else {
	// la relation est nouvelle
	// on récupère les identifiants, éventuellement fournis, des sociétés impliquées  
	if (isset ( $_REQUEST ['item0_id'] )) {
		$item0 = new Society ( $_REQUEST ['item0_id'] );
		$item0->feed();
		$relationship->setItem ($item0, 0);
	}
	if (isset ( $_REQUEST ['item1_id'] )) {
		$item1 = new Society ( $_REQUEST ['item1_id'] );
		$item1->feed();
		$relationship->setItem($item1, 1);
	}
}

if (isset ( $_POST ['relationship_submission'] )) {
	// enregistrement des données de la participation
	$relationship->feed ( $_POST );
	if (empty($item0) && !empty($_POST['item0_name'])) {
		$item0 = new Society ();
		$item0->setName ( $_POST ['item0_name'] );
		if (! $item0->identifyFromName()) {
			$item0->toDB ();
		}
		$relationship->setItem($item0,0);
	}
	if (empty($item1) && !empty($_POST['item1_name'])) {
		$item1 = new Society();
		$item1->setName($_POST['item1_name']);
		if(!$item1->identifyFromName()) {
			$item1->toDB();
		}
		$relationship->setItem($item1,1);
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
	$h1_content = 'Une relation entre ' . $item0->getHtmlLinkToSociety() . ' et ' . $item1->getHtmlLinkToSociety();
} else {
	$h1_content = $relationship->isItemKnown(0) ? 'Une relation de ' . $relationship->getItem(0)->getHtmlLinkToSociety() : 'Une relation';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo strip_tags($h1_content) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css"></link>
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
	<section>
		<form id="relationship_form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<input name="item0_class" type="hidden" value="Society" />
			<input name="item1_class" type="hidden" value="Society" />
			<?php
			if ($relationship->getId ())
				echo '<input name="relationship_id" type="hidden" value="' . $relationship->getId () . '" />';

			if (!$relationship->isItemKnown(0)) {
				// la première société est à définir
				echo '<div class="form-row">';
				echo '<div class="form-group col-md-6">';
			    echo '<label for="s1_name_i">Nom de la première société</label>';
				echo '<input id="s1_name_i" name="item0_name" type="text" maxlength="255" class="form-control" />';
				echo '</div>';
				echo '<div class="form-group col-md-6">';
    			echo '<label for="s1_role_i">Son rôle</label>';
    			echo '<input id="s1_role_i" name="item0_role" type="text" value="'.$relationship->getItemRole(0).'" size="20" class="form-control" />';
				echo '</div>';
				echo '</div>';
			} else {
				// la première société est définie
				echo '<div class="form-group">';
				echo '<input name="item0_id" type="hidden" value="' . $relationship->getItem(0)->getId () . '"/>';
    			echo '<label for="s1_role_i">Rôle '. $relationship->getItem(0)->getHtmlLinkToSociety() . '</label>';
    			echo '<input id="s1_role_i" name="item0_role" type="text" value="'.$relationship->getItemRole(0).'" size="20" class="form-control" />';
				echo '</div>';
			}
			if (!$relationship->isItemKnown(1)) {
				// la deuxième société est à définir
				echo '<div class="form-row">';
				echo '<div class="form-group col-md-6">';
				echo '<label for="s2_name_i">Nom de la deuxième société</label>';
				echo '<input id="s2_name_i" name="item1_name" type="text" maxlength="255" class="form-control" />';
				echo '</div>';
				echo '<div class="form-group col-md-6">';
    			echo '<label for="s2_role_i">Son rôle</label>';
    			echo '<input id="s2_role_i" name="item1_role" type="text" value="'.$relationship->getItemRole(1).'" size="20" class="form-control" />';
				echo '</div>';
				echo '</div>';
			} else {
				// la deuxième société est définie
				echo '<div class="form-group">';
				echo '<input name="item1_id" type="hidden" value="' . $relationship->getItem(1)->getId () . '"/>';
    			echo '<label for="s2_role_i">Rôle '. $relationship->getItem(1)->getHtmlLinkToSociety() . '</label>';
    			echo '<input id="s2_role_i" name="item1_role" type="text" value="'.$relationship->getItemRole(1).'" size="20" class="form-control" />';
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
    			<label>Commentaire</label>
    			<textarea name="description" cols="51" rows="5" class="form-control"><?php echo $relationship->getAttribute('description'); ?></textarea>
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
			
			<button name="relationship_submission" type="submit" value="1" class="btn btn-primary">Enregistrer</button>
			<?php if ($relationship->getId()) : ?>
				<button name="relationship_deletion" type="submit" value="1" class="btn btn-secondary">Supprimer</button>
			<?php endif; ?>
			
			<?php
    			if ($relationship->isItemKnown(0)) {
					echo '<a href="'.$relationship->getItem(0)->getDisplayUrl().'" class="btn btn-secondary">Quitter</a>';
    			}
			?>
		</form>
	</section>
</div>
<script type="text/javascript">
	$(document).ready(function(){
	    $('#s1_name_i').autocomplete({
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
	   		}
	   	});
	    $('#s2_name_i').autocomplete({
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
	   		}
	   	});	    
	    $('#s1_role_i').autocomplete({
			minLength: 2,
	   		source: function( request, response ) {
	            $.ajax({
					method:'GET',
	                url:'api/relationship_roles.json.php',
	                dataType: 'json',
	                data:{
	                    'searchPattern': request.term,
	                    'rolePlayerClass': 'society'
	                 },
	                 dataFilter: function(data,type){
	                     return JSON.stringify(JSON.parse(data).roles);
	                 },
	                 success : function(data, textStatus, jqXHR){
						response(data);
	                 }
	         	})
	   		},
	        focus: function( event, ui ) {
				$('#s1_role_i').val( ui.item.role);
	        	return false;
	        },
	        select: function( event, ui ) {
	        	$('#s1_role_i').val( ui.item.role);
	        	return false;
	        }
	   	}).autocomplete("instance")._renderItem = function( ul, item ) {
		   	var content = '<div>'+item.role+' <small>('+item.nb+')</small></div>';
		   	return $( "<li>" ).append(content).appendTo( ul );
		};
	    $('#s2_role_i').autocomplete({
			minLength: 2,
	   		source: function( request, response ) {
	            $.ajax({
					method:'GET',
	                url:'api/relationship_roles.json.php',
	                dataType: 'json',
	                data:{
	                    'searchPattern': request.term,
	                    'rolePlayerClass': 'society'
	                 },
	                 dataFilter: function(data,type){
	                     return JSON.stringify(JSON.parse(data).roles);
	                 },
	                 success : function(data, textStatus, jqXHR){
						response(data);
	                 }
	         	})
	   		},
            focus: function( event, ui ) {
    			$('#s2_role_i').val( ui.item.role);
            	return false;
            },
            select: function( event, ui ) {
            	$('#s2_role_i').val( ui.item.role);
            	return false;
            }
	   	}).autocomplete("instance")._renderItem = function( ul, item ) {
		   	var content = '<div>'+item.role+' <small>('+item.nb+')</small></div>';
		   	return $( "<li>" ).append(content).appendTo( ul );
		};	    
	})
</script>
</body>
</html>