<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System( 'config/host.json' );

session_start();

if (empty ($_SESSION['user_id'])) {
	header('Location:login.php');
	exit;
} else {
	$user = new User($_SESSION['user_id']);
	$user->feed();
}

$role = empty($_REQUEST['role']) ? null : $_REQUEST['role'];

if (isset($_POST)) {
    ToolBox::formatUserPost($_POST);
    
    if (isset($_POST['task_id'])) {
        $fb = new UserFeedBack();
        ToolBox::formatUserPost($_POST);
        switch ($_POST['task_id']) {
            case 'replaceRole':
                if (!empty($role) && !empty($_POST['newRole'])) {
                    if (Relationship::replaceRole($role, $_POST['newRole'], 'society')) {
                        //$fb->addSuccessMessage('Changement de label effectif pour le rôle '.$role.' ('.$_POST['newRole'].').');
                        header ( 'Location:relationshipSocietyRole.php?role='.$_POST['newRole'] );
                        exit();
                    }
                }
                break;
        }
    }
}

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

$societiesHavingThatRole = $system->getSocietiesHavingThatRole($role, $_SESSION['societiesHavingThatRole_list_sort']);

$doc_title = $role;

?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php ToolBox::toHtml($doc_title) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <script src="<?php echo FONTAWESOME_KIT_URI ?>" crossorigin="anonymous"></script>    
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="<?php echo POPPER_JS_URI ?>" integrity="<?php echo POPPER_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
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
	<header>
        <h1 class="bd-title"><?php echo ToolBox::toHtml(ucfirst($doc_title)); ?>
        <small>(<?php echo count($societiesHavingThatRole) ?>)</small>
        <div class="dropdown" style="display:inline-block">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="sortOptionButton" data-toggle="dropdown"><small><i class="fas fa-filter"></i></small> Trier par</button>
            <div class="dropdown-menu">
                <?php
                $options = array();
                $options[] = array('label'=>'Nom','sort'=>'alpha','active'=>strcmp($_SESSION['societiesHavingThatRole_list_sort'], 'Alphabetical')==0);
                $options[] = array('label'=>'Nombre de sociétés auprès desquelles le rôle est assumé','sort'=>'count','active'=>strcmp($_SESSION['societiesHavingThatRole_list_sort'], 'Most used first')==0);
              
                foreach ($options as $o) {
                    $class = $o['active'] ? 'dropdown-item active' : 'dropdown-item';
                    echo '<a class="'.$class.'" href="'.$_SERVER['PHP_SELF'].'?role='.$role.'&newsort='.$o['sort'].'">'.ToolBox::toHtml($o['label']).'</a>';
                }
                ?>
            </div>
        </div>
        <a class="btn btn-secondary" href="relationshipSocietyRole_edit.php?role=<?php echo $role ?>"><small><i class="fas fa-edit"></i></small> Éditer</a>
        </h1>
    </header>
    
    <?php
    if (isset($fb)) {
        echo '<div>';
        echo $fb->AllMessagesToHtml();
        echo '</div>';
    }
    ?>
    
    <div class="row">
        <div class="col-md-8">
        	<div class="table-responsive">
            	<table class="table">
            	<thead>
        			<tr>
        				<th style="display:none"></th>
        				<th>Société</th>
        				<th>Rôle assumé auprès de</th>
        			</tr>
        		</thead>
            	<tbody>
                <?php
                foreach ($societiesHavingThatRole as $item) {
                    echo '<tr>';
                    echo '<td>';
                    echo $item['society']->getHtmlLinkToSociety();
                    echo ' <small>('.$item['count'].')</small>';
                    echo '</td>';
                    echo '<td>';
                    echo '<ul>';
                    foreach ($item['relatedSocieties'] as $id=>$name){
                        $s = new Society($id);
                        $s->setName($name);
                        echo '<li>'.$s->getHtmlLinkToSociety().'</li>';
                    }
                    echo '</ul>';
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
                </tbody>
                </table>
            </div>
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
</body>
</html>