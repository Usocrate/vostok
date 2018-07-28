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


$individual = new Individual($_REQUEST['individual_id']);
$individual->feed();

// participations
$memberships = $individual->getMemberships();

// individus liés
$relatedIndividuals = $individual->getRelatedIndividuals();

$doc_title = $individual->getWholeName();

//var_dump($_SESSION);

if (!empty($_SESSION['preferences']['individual']['focus'])) {
	$focus = $_SESSION['preferences']['individual']['focus'];
} else {
	$focus = 'onMemberships';
}
?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo $doc_title ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>" type="text/css" />
	<link rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css" type="text/css">
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1>
	<?php echo ToolBox::toHtml($doc_title); ?> <small><a href="individual_edit.php?individual_id=<?php echo $individual->getId() ?>"><span class="glyphicon glyphicon-edit"></span></a> <a href="index.php?individual_task_id=pin&individual_id=<?php echo $individual->getId() ?>"><span class="glyphicon glyphicon-pushpin"></span></a></small></h1>
	<section>
	<div>	
		<?php
		if ($individual->getPhotoUrl()) {
			echo $individual->getPhotoHtml();
		}
		?>
		<div>
			<?php
				$contact_data = array();
				if ($individual->getPhoneNumber()) {
					$contact_data['phone'] = $individual->getPhoneNumber();
				}
				if ($individual->getMobilePhoneNumber()) {
					$contact_data['mobile'] = $individual->getMobilePhoneNumber();
				}
				if ($individual->getEmailAddress()) {
					$contact_data['email'] = $individual->getEmailHtml();
				}

				if ($individual->getDescription()) {
					echo '<p>'.$individual->getDescription().'</p>';
				}
				if ($individual->getWeb()) {
					echo '<p>'.$individual->getHtmlLinkToWeb().'</p>';
				}
				if (count($contact_data) >0) {
					echo '<p>'.implode('<span> | </span>', $contact_data).'</p>';
				}
				if ($individual->getBirthDate()) {
					echo '<p><small>naissance : </small>'.$individual->getBirthDate().'</p>';
				}
				if ($individual->getAddress()) {
					echo '<p>'.$individual->getAddress().'</p>';
				}
				if ($individual->getCvUrl()) {
					echo '<p><a href="'.$individual->getCvUrl().'">cv</a></p>';
				}
				if ($individual->getGoogleQueryUrl()) {
					echo '<p><a href="'.$individual->getGoogleQueryUrl().'" target="_blank">'.$individual->getWholeName().' dans Google</a></p>';
				}
			?>
		</div>
	</div>
	</section>
	
	<div>
	  <!-- Nav tabs -->
	  <ul class="nav nav-tabs" role="tablist">
	    <li role="presentation" <?php if (strcmp($focus,'onMemberships')==0) echo 'class="active"' ?>><a id="membershipsTabSelector" href="#memberships-tab" data-toggle="tab">Participations <span class="badge"><?php echo count($memberships) ?></span></a></li>
	    <li role="presentation" <?php if (strcmp($focus,'onRelatedIndividuals')==0) echo  'class="active"' ?>><a id="relationsTabSelector" href="#relations-tab" aria-controls="relations-tab" role="tab" data-toggle="tab">Relations <span class="badge"><?php echo count($relatedIndividuals) ?></span></a></li>
	  </ul>
	
	  <!-- Tab panes -->
	  <div class="tab-content">
	    <div role="tabpanel" class="tab-pane <?php if (strcmp($focus,'onMemberships')==0) echo 'active' ?>" id="memberships-tab">
			<h2>Participations <small><a href="membership_edit.php?individual_id=<?php echo $individual->getId() ?>."><span class="glyphicon glyphicon-plus"></span></a></small></h2>
			<?php
			if (isset($memberships)){
				echo '<ul class="list-group">';
				foreach ($memberships as $ms) {
					$s = $ms->getSociety();
					echo '<li class="list-group-item">';
					echo '<h3>';
					echo $s->getHtmlLinkToSociety();
					
					echo ' <small>';
					if ($ms->getTitle()) {
						echo ' (<a href="membership_edit.php?membership_id='.$ms->getId().'">'.ToolBox::toHtml(ucfirst($ms->getTitle())).')</a>';
					}
					echo ' <a href="membership_edit.php?membership_id='.$ms->getId().'"><span class="glyphicon glyphicon-edit"></span></a>';	
					echo ' </small>';
					
					echo '</h3>';
					
					$more = array();
					if ($ms->getPeriod()) {
						$more[] = $ms->getPeriod();
												
					}
					if ($ms->getDepartment()) {
						$more[] = $ms->getDepartment();
					}
					if (count($more)>0) {
						echo '<div><small>'.implode(' - ', $more).'</small></div>';
					}					
					
					if ($ms->getDescription()) {
						echo '<p>'.$ms->getDescription().'</p>';
					}
					
					if ($ms->getUrl()) {
						echo '<p>'.$ms->getHtmlLinkToWeb().'</p>';
					}
					$data = array();
					if ($ms->getPhone()) $data[] = $ms->getPhone();
					if ($ms->getEmail()) {
						$data[] = '<a href="mailto:'.ToolBox::toHtml($individual->getFirstName()).'%20'.ToolBox::toHtml($individual->getLastName()).'%20<'.$ms->getEmail().'>">'.$ms->getEmail().'</a>';
					}
					if (count($data)>0) {
						echo '<p>'.implode('<span> | </span>', $data).'</p>';
					}
					echo '</li>';
				}
				echo '</ul>';
			}
			?>
	    </div>
	    <div role="tabpanel" class="tab-pane <?php if (strcmp($focus,'onRelatedIndividuals')==0) echo 'active' ?>" id="relations-tab">
			<h2>Relations <small><a href="individualToIndividualRelationship_edit.php?item0_id=<?php echo $individual->getId() ?>"> <span class="glyphicon glyphicon-plus"></span></a></a></small></h2>
			<?php if (isset($relatedIndividuals)): ?>
			<ul class="list-group">
				<?php
				foreach ($relatedIndividuals as $item) {
					// $item[0] : Individu
					// $item[1] : Identifiant de la relation;
					// $item[2] : Rôle
					// $item[3] : Description
					// $item[4] : Period object
					echo '<li class="list-group-item">';
					echo '<h3>';
					echo '<a href="individual.php?individual_id='.$item[0]->getId().'">'.ToolBox::toHtml($item[0]->getWholeName()).'</a>';
					echo ' <small>(';
					echo '<a href="individualToIndividualRelationship_edit.php?relationship_id='.$item[1].'">';
					echo empty($item[2]) ? '?' : ToolBox::toHtml(ucfirst($item[2]));
					echo '</a>';
					echo ')';
					echo ' <a href="individualToIndividualRelationship_edit.php?relationship_id='.$item[1].'"><span class="glyphicon glyphicon-edit"></span></a>';
					echo '</small>';
					echo '</h3>';
					if ($item[4]->isDefined()) {
						echo '<div><small>'.$item[4]->toString().'</small></div>';
					}
					if (!empty($item[3])) {
						echo '<p>';
						echo ToolBox::toHtml($item[3]);
						echo '</p>';
					}
					echo '</li>';
				}
				?>
			</ul>	    	
			<?php endif; ?>
	    </div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
	    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var focus;
			var scope = 'individual';

			switch(e.target.id) {
			  	case 'membershipsTabSelector':
			  		focus = 'onMemberships';
			  		break;
			  	case 'relationsTabSelector':
			  		focus = 'onRelatedIndividuals';
			  		break;
			}

			$.ajax({
				  url: 'session.ws.php?focus='+focus+'&scope='+scope,
				  beforeSend: function( xhr ) {
				    xhr.overrideMimeType( "text/plain; charset=x-user-defined" );
				  }
			});
		});
	});
</script>
</body>
</html>