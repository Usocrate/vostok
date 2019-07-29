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

$role = empty($_REQUEST['role']) ? null : $_REQUEST['role'];

if (isset($_REQUEST['newsort']) || empty($_SESSION['societiesHavingThatRole_list_sort'])) {
    if (isset($_REQUEST['newsort'])) {
        switch ($_REQUEST['newsort']) {
            case 'alpha':
                $_SESSION['societiesHavingThatRole_list_sort'] = 'Alphabetical';
                break;
            case 'count':
                $_SESSION['societiesHavingThatRole_list_sort'] = 'Most used first';
        }
    } else {
        $_SESSION['societiesHavingThatRole_list_sort'] = 'Most used first';
    }
}

$doc_title = $role;

?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php ToolBox::toHtml($doc_title) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />    
    <link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo MASONRY_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo IMAGESLOADED_URI; ?>"></script>
	<script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<nav>
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="societies_roles.php">Les relations entre sociétés</a></li>
    <li class="breadcrumb-item active"><?php echo ToolBox::toHtml($role) ?></li>
  </ol>
</nav>

<div class="container-fluid">
	<h1 class="bd-title"><?php echo ToolBox::toHtml(ucfirst($doc_title)); ?></h1>
    <div class="row">
        <div class="col-md-8">
        	<table class="table">
        	<thead>
    			<tr>
    				<th style="display:none"></th>
    				<th><?php echo strcmp($_SESSION['societiesHavingThatRole_list_sort'], 'Alphabetical')==0 ? 'Nom de la société' : 'Nom de la société <a href="'.$_SERVER['PHP_SELF'].'?role='.$role.'&newsort=alpha"><small><i class="fas fa-filter"></i></small></a>' ?></th>
    				<th><?php echo strcmp($_SESSION['societiesHavingThatRole_list_sort'], 'Most used first')==0 ? 'Nombre de sociétés auprès desquelles le rôle est assumé' : 'Nombre de sociétés auprès desquelles le rôle est assumé <a href="'.$_SERVER['PHP_SELF'].'?role='.$role.'&newsort=count"><small><i class="fas fa-filter"></i></small></a>' ?></th>
    			</tr>
    		</thead>
        	<tbody>
            <?php
            foreach ($system->getSocietiesHavingThatRole($role, $_SESSION['societiesHavingThatRole_list_sort']) as $item) {
                echo '<tr>';
                echo '<td>';
                echo $item['society']->getHtmlLinkToSociety();
                echo '</td>';
                echo '<td>';
                if (isset($item['count'])) echo $item['count'];
                echo '</td>';
                echo '</tr>';
            }
            ?>
            </tbody>
            </table>
        </div>
        <div class="col-md-4">
        	<div class="card">
        	<div class="card-body">
        	<h2 class="card-title">Les rôles associés</h2>
        	<?php
        	   $matchingRoles = Relationship::getMatchingRoles($role);
        	   if ($matchingRoles > 0) {
        	       echo '<ol>';
        	       foreach($matchingRoles as $item) {
        	           echo '<li><a href="'.$_SERVER['PHP_SELF'].'?role='.$item['role'].'">'.ToolBox::toHtml($item['role']).'</a> <small>('.$item['nb'].')</small></li>';
        	       }
        	       echo '</ol>';
        	   }
        	?>
        	</div>
        	</div>
        </div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
	});
</script>
</body>
</html>