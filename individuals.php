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

if (empty($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $user = new User($_SESSION['user_id']);
    $user->feed();
}

$messages = array();

// nombre de individuals à afficher par page
$page_items_nb = 40;

if (isset($_REQUEST['individual_task_id'])) {
    // une tâche à effectuer
    switch ($_REQUEST['individual_task_id']) {
        case 'deletion':
            if (empty($_REQUEST['individual_id']))
                break;
            $i = new Individual($_REQUEST['individual_id']);
            if ($i->deleteMemberships()) {
                if ($i->deleteLeadsInvolvement()) {
                    if ($i->delete())
                        $messages[] = 'L\'individu ne figure plus dans la base de données ...';
                    else
                        $messages[] = 'Echec de la suppression de l\'individu !';
                } else
                    $messages[] = 'Echec de la suppression des implications de l\'individu dans les pistes.';
            } else
                $messages[] = 'Echec de la suppression des participations de l\'individu.';
            break;
    }
}
// si ordre d'effectuer une nouvelle recherche
// les données propres à la sélection de individuals courante sont réinitialisées
if (isset($_REQUEST['individual_newsearch']) || empty($_SESSION['individual_search'])) {
    $_SESSION['individual_search'] = array();
    if (isset($_REQUEST['individual_lastName'])) {
        $_SESSION['individual_search']['lastName'] = $_REQUEST['individual_lastName'];
    }
    if (isset($_REQUEST['individual_toCheck'])) {
        $_SESSION['individual_search']['toCheck'] = $_REQUEST['individual_toCheck'];
    }
    $_SESSION['individual_search']['page_index'] = 1;
    $_SESSION['individual_search']['sort'] = 'Name';
}
if (! isset($_SESSION['individual_search'])) {
    $_SESSION['individual_search'] = array();
}
    
// critères de filtrage
$criteria = array();
if (! empty($_SESSION['individual_search']['lastName'])) {
    $criteria['individual_lastname_like_pattern'] = $_SESSION['individual_search']['lastName'];
}

// nb de personnes correspondant aux critères
$individuals_nb = empty($_SESSION['individual_search']['toCheck']) ? $system->countIndividuals($criteria) : $system->countAloneIndividuals($criteria);

$pages_nb = ceil($individuals_nb / $page_items_nb);

// changement de page
if (isset($_REQUEST['individual_search_page_index']))
    $_SESSION['individual_search']['page_index'] = $_REQUEST['individual_search_page_index'];
    
    // sélection de individuals correspondant aux critères (dont le nombre dépend de la variable $page_items_nb)
$page_debut = ($_SESSION['individual_search']['page_index'] - 1) * $page_items_nb;

if (empty($_SESSION['individual_search']['toCheck'])) {
    $statement = $system->getIndividualCollectionStatement($criteria, $_SESSION['individual_search']['sort'], $page_debut, $page_items_nb);
} else {
    $statement = $system->getAloneIndividualCollectionStatement($criteria, $_SESSION['individual_search']['sort'], $page_debut, $page_items_nb);
}

// la collection d'individus à afficher
$individuals = new IndividualCollection($statement);
$i = $individuals->getIterator();

// si une seule personne redirection vers fiche individuelle.
if ($individuals->getSize() == 1) {
    header('Location:individual.php?individual_id=' . $i->current()->getId());
    exit();
}
$doc_title = 'Les gens';
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo ToolBox::toHtml($system->getAppliName()).' : '.ToolBox::toHtml($doc_title) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>main.css" type="text/css">
    <script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script>
    <script type="text/javascript" src="<?php echo JQUERY_UI_URI; ?>"></script>
    <script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	
	<h1><?php echo ToolBox::toHtml($doc_title); ?><small><a href="individual_edit.php"> <span class="glyphicon glyphicon-plus"></span></a></small></h1>
	
	<?php
        if (count($messages) > 0) {
            echo '<div class="alert alert-info" role="alert">';
            foreach ($messages as $m) {
                echo '<p>' . ToolBox::toHtml($m) . '</p>';
            }
            echo '</div>';
        }
    ?>
	
	<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" class="form-inline">
		<div class="form-group">
			<label for="individual_lastName_i">Nom</label>
			<input id="individual_lastName_i" name="individual_lastName" type="text" value="<?php if (isset($_SESSION['individual_search']['lastName'])) echo $_SESSION['individual_search']['lastName'] ?>" placeholder="nom de famille" class="form-control" />
		</div>
		<div class="checkbox">
            <label><input id="i_toCheck_i" name="individual_toCheck" type="checkbox" value="1" <?php if (isset($_SESSION['individual_search']['toCheck'])) echo 'checked="checked" ' ?> /> Sans société</label>
 		</div>
		<button type="submit" name="individual_newsearch" value="filtrer" class="btn btn-default">Filtrer</button>
		<?php if( count($criteria) > 0) echo ' <a href="individuals.php?individual_newsearch=1">Tous les gens</a>'  ?>
	</form>

	<section>
    	<?php
        if ($individuals->getSize() > 0) {
            echo '<ul class="list-group">';
            do {
                echo '<li class="list-group-item">';
                echo '<a href="individual.php?individual_id=' . $i->current()->getId() . '">' . $i->current()->getWholeName() . '</a>';
                echo '</li>';
            } while ($i->next());
            echo '</ul>';
         }
        ?>
    	<div>
    		<?php
            if ($pages_nb > 1) {
                $params = array();
                echo ToolBox::getHtmlPagesNav($_SESSION['individual_search']['page_index'], $pages_nb, $params, 'individual_search_page_index');
            }
            ?>
    	</div>
	</section>
</div>	
</body>
</html>
