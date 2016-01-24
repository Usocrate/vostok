<?php
require_once 'config/main.inc.php';

session_start();
ToolBox::getDBAccess();

if (empty ($_SESSION['user_id'])) {
	header('Location:login.php');
	exit;
} else {
	$user = new User($_SESSION['user_id']);
	$user->feed();
}


$messages = array();

// nombre de individuals à afficher par page
$page_items_nb = 40;

if (isset($_REQUEST['individual_task_id'])) {
	// une tâche à effectuer
	switch ($_REQUEST['individual_task_id']){
		case 'deletion' :
			if (empty($_REQUEST['individual_id'])) break;
			$i = new Individual($_REQUEST['individual_id']);
			if ($i->deleteMemberships()) {
				if ($i->deleteLeadsInvolvement()){
					if ($i->delete()) $messages[] = 'L\'individu ne figure plus dans la base de données ...';
					else $messages[] = 'Echec de la suppression de l\'individu !';
				}
				else $messages[] = 'Echec de la suppression des implications de l\'individu dans les pistes.';
			}
			else $messages[] = 'Echec de la suppression des participations de l\'individu.';
			break;
	}
}
//	si ordre d'effectuer une nouvelle recherche
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
	$_SESSION['individual_search']['sort_key'] = 'individual_lastName';
	$_SESSION['individual_search']['sort_order'] = 'ASC';
}
if (!isset($_SESSION['individual_search'])) $_SESSION['individual_search'] = array();

//print_r($_SESSION);

//	critères de filtrage
$criteria = array();
if (!empty($_SESSION['individual_search']['lastName'])) {
	$criteria['individual_lastname_like_pattern'] = $_SESSION['individual_search']['lastName'];
}

//	nb de personnes correspondant aux critères
$individuals_nb = empty($_SESSION['individual_search']['toCheck']) ? $system->countIndividuals($criteria) : $system->countAloneIndividuals($criteria);

$pages_nb = ceil($individuals_nb/$page_items_nb);

//	changement de page
if (isset($_REQUEST['individual_search_page_index'])) $_SESSION['individual_search']['page_index'] = $_REQUEST['individual_search_page_index'];

//	sélection de individuals correspondant aux critères (dont le nombre dépend de la variable $page_items_nb)
$page_debut = ($_SESSION['individual_search']['page_index'] - 1) * $page_items_nb;

if (empty($_SESSION['individual_search']['toCheck'])){
	$statement = $system->getIndividualCollectionStatement($criteria, $_SESSION['individual_search']['sort_key'], $_SESSION['individual_search']['sort_order'], $page_debut, $page_items_nb);
} else {
	$statement = $system->getAloneIndividualCollectionStatement($criteria, $_SESSION['individual_search']['sort_key'], $_SESSION['individual_search']['sort_order'], $page_debut, $page_items_nb);
}

//	la collection d'individus à afficher
$individuals = new IndividualCollection($statement);
$i = $individuals->getIterator();

// si une seule personne redirection vers fiche individuelle.
if ($individuals->getSize()==1) {
	header('Location:individual.php?individual_id='.$i->current()->getId());
	exit;
}
$doc_title = 'Les individus';
?>
<!doctype html>
<html lang="fr">
<head>
<title><?php echo APPLI_NAME ?>: Liste des individus</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" type="text/css" href="<?php echo PURE_SEEDFILE_URI ?>">

<link rel="stylesheet" href="<?php echo SKIN_URL ?>main.css" type="text/css"><link rel="stylesheet" href="<?php echo SKIN_URL ?>pure-skin-vostok.css" type="text/css"></head>
<body class="pure-skin-vostok">
	<div class="pure-g-r">
		<div class="pure-u-1 ban">
			<header><div class="brand"><a href="<?php echo APPLI_URL?>"><?php echo ToolBox::toHtml(APPLI_NAME) ?></a></div><?php echo ToolBox::toHtml($doc_title); ?></header>
		</div>
		<?php if (count($messages)>0) echo '<div class="alerte pure-u-1">'.implode('<br />', $messages).'</div>' ?>
		<div class="pure-u-1">
			<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" class="pure-form">
				<label for="i_lastName_i">nom</label><input id="i_lastName_i" name="individual_lastName" type="text" value="<?php if (isset($_SESSION['individual_search']['lastName'])) echo $_SESSION['individual_search']['lastName'] ?>" /> <input for="i_toCheck_i" name="individual_toCheck" type="checkbox" value="1" <?php if (isset($_SESSION['individual_search']['toCheck'])) echo 'checked="checked" ' ?> /> <label for="i_toCheck_i">Les individus isolés seulement</label>
				<button type="submit" name="individual_newsearch" value="filtrer" class="pure-button">Filtrer</button>
			</form>
		</div>
		<div class="pure-u-1">
			<div>
			<?php
				if ($individuals->getSize()>0){
					echo '<ul class="pure-g-r">';
					do {
						echo '<li class="pure-u-1-4">';
						echo '<a href="individual.php?individual_id='.$i->current()->getId().'">'.$i->current()->getWholeName().'</a>';
						echo '</li>';
					}
					while ($i->next());
					echo '</ul>';
				}
			?>
			</div>
			<div>
				<?php 
					if ($pages_nb>1){
						$params = array();
						echo ToolBox::getHtmlPagesNav($_SESSION['individual_search']['page_index'], $pages_nb, $params, 'individual_search_page_index');
					}
				?>
			</div>
			<div>Enregistrer un <a href="individual_edit.php"><strong>nouvel individu</strong> </a></div>
		</div>
		<div class="pure-u-1">
			<footer><?php include 'menu.inc.php'; ?></footer>
		</div>
	</div>
</body>
</html>
