<?php
require_once 'config/boot.php';
require_once 'classes/System.class.php';
$system = new System( 'config/host.json' );

session_start ();

if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {
	$user = new User ( $_SESSION ['user_id'] );
	$user->feed ();
}

if (isset ( $_POST ['task'] )) {
	ToolBox::formatUserPost($_POST);
	//print_r($_POST);
	
	$fb = new UserFeedBack();
	
	switch ($_POST ['task']) {
		case 'newindustry' :
			if (! empty ( $_POST ['newindustry_name'] )) {
				$i = new Industry ();
				$i->setName ( $_POST ['newindustry_name'] );
				$i->toDB ();
			}
			break;
		case 'industries_merge' :
			if (isset ( $_POST ['industries_ids'] )) {
				$industriesToMerge = $system->getIndustriesFromIds($_POST['industries_ids']);
				$nb = count($industriesToMerge);
				$result = null;
				while (count($industriesToMerge) > 1 ) {
					$result = $system->mergeIndustries(current($industriesToMerge), next($industriesToMerge));
					array_splice($industriesToMerge, 0, 2, array ($result));
				}
				$fb->addSuccessMessage('Les '.$nb.' activités sélectionnées sont fusionnées ('.$industriesToMerge[0]->getName().')');
			}
			break;
		default :
			trigger_error ( 'La tâche à exécuter est inconnue' );
	}
}
/*
 * gestion du tri de la liste des activités
 */
if (isset($_REQUEST['newsort']) || empty($_SESSION['industries_list_sort'])) {

    if (isset($_REQUEST['newsort'])) {
        switch ($_REQUEST['newsort']) {
            case 'alpha':
                $_SESSION['industries_list_sort'] = 'Alphabetical';
                break;
            case 'count':
                $_SESSION['industries_list_sort'] = 'Most used first';
        }
    } else {
        $_SESSION['industries_list_sort'] = 'Most used first';
    }
}

$doc_title = 'Les activités exercées';
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo ToolBox::toHtml($system->getAppliName()) ?>: Répartition des sociétés par activité</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
    <script src="<?php echo JQUERY_URI; ?>"></script>
    <script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1 class="bd-title"><?php echo ToolBox::toHtml($doc_title); ?></h1>
	<?php 
	if (isset($fb)) {
		echo $fb->toHtml(); 
	}
	?>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<div class="row">
			<div class="col-md-6">
				<section>
				<table class="table">
					<thead>
						<tr>
							<th></th>
							<th><?php echo strcmp($_SESSION['industries_list_sort'], 'Alphabetical')==0 ? 'Activité' : 'Activité <a href="'.$_SERVER['PHP_SELF'].'?newsort=alpha"><small><i class="fas fa-filter"></i></small></a>' ?></th>
							<th><?php echo strcmp($_SESSION['industries_list_sort'], 'Most used first')==0 ? 'Nombre' : 'Nombre <a href="'.$_SERVER['PHP_SELF'].'?newsort=count"><small><i class="fas fa-filter"></i></small></a>' ?></th>
						</tr>
					</thead>
					<tbody>
        				<?php
        				
        				$mainIndustryMinWeight = $system->getMainIndustryMinWeight();
        				$notMarginalIndustryMinWeight = $system->getNotMarginalIndustryMinWeight();
        				
        				foreach ( $system->getIndustries(null, $_SESSION['industries_list_sort']) as $i ) {
      				    
        				    $weight = $i->getSocietiesNb();
        				    
        				    if ($weight >= $mainIndustryMinWeight) {
        				        $status = 'main';
        				    } elseif($weight < $notMarginalIndustryMinWeight) {
        				        $status = 'marginal';
        				    } else {
        				        $status = 'normal';
        				    }
        				    
        					echo '<tr>';
        					echo '<td><input name="industries_ids[]" type="checkbox" value="'.$i->getId().'" /></td>';
        					echo '<td>';
        					echo '<a href="societies.php?society_newsearch=1&amp;industry_id='.$i->getId().'">';
        					switch ($status) {
        					    case 'main':
        					        echo '<strong>'.ToolBox::toHtml($i->getName()).'</strong>';
        					        break;
        					    case 'marginal' :
        					        echo '<small>'.ToolBox::toHtml($i->getName()).'</small>';
        					        break;
        					    default :
        					        echo ToolBox::toHtml($i->getName());
        					}
        					echo ' <small><a href="industry_edit.php?id='.$i->getId().'"><i class="fas fa-edit"></i></a></small>';
        					echo '</a>';
        					echo '</td>';
        					echo '<td>';
        					echo '<span class="badge badge-secondary">';
        					echo '<a href="societies.php?society_newsearch=1&amp;industry_id=' . $i->getId () . '">';
        					echo $weight;
        					echo '</span>';
        					echo '</td>';
        					echo '</tr>';
        				}
        				?>
        			</tbody>
					<tfoot>
						<tr>
							<td colspan="3">
								<button type="submit" name="task" value="industries_merge" class="btn btn-primary">Fusionner</button>
							</td>
						</tr>
					</tfoot>
				</table>
				</section>
			</div>
			<div class="col-md-6">
				<section>
    				<label id="newindustry_name_i">Nouvelle activité</label> <input id="newindustry_name_i" name="newindustry_name" type="text" size="15" />
    				<button name="task" type="submit" value="newindustry" class="btn btn-secondary">Déclarer</button>
				</section>
			</div>
		</div>
	</form>
</div>	
</body>
</html>