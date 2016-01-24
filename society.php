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

$society = new Society();
if (isset($_REQUEST['society_id'])) {
	$society->setId($_REQUEST['society_id']);
}
$society->initFromDB();

// participations
$memberships = $society->getMemberships();

// participations
$relationships = $society->getRelationships();

// pistes
$leads = $society->getLeads();

// évènements
$events = $society->getEvents();

$doc_title = $society->getNameForHtmlDisplay();
?>
<!doctype html>
<html lang="fr">
<head>
<title>Un des comptes (sa fiche détaillées)</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" type="text/css" href="<?php echo PURE_SEEDFILE_URI ?>">
<link rel="stylesheet" href="<?php echo SKIN_URL ?>main.css" type="text/css"><link rel="stylesheet" href="<?php echo SKIN_URL ?>pure-skin-vostok.css" type="text/css"></head>
<body id="societyDoc" class="pure-skin-vostok">
	<div class="pure-g-r">
		<div class="pure-u-1 ban">
			<header><div class="brand"><a href="<?php echo APPLI_URL?>"><?php echo ToolBox::toHtml(APPLI_NAME) ?></a></div><?php echo ToolBox::toHtml($doc_title); ?></header>
		</div>
		<div class="pure-u-1 dataSheet">
			<section>
				<?php 
				if ($society->hasUrl()) {
					echo $society->getHtmlThumbnailLink();
				}

				$line_elt = array();

				// affichage date de création et lien web.
				if ($society->getCreationDate()) {
					$line_elt[] = '<small >créée le '.ToolBox::toHtml($society->getCreationDateFr()).'</small>';
				}
				if (count($line_elt)) echo '<p>'.implode(' - ', $line_elt).'</p>';


				// affichage coordonnées.
				if ($society->getStreet() || $society->getCity() || $society->getPostalcode()){
					echo '<address>';
					echo ToolBox::toHtml($society->getAddress());
					if ($society->getCoordinates()) {
						echo ' <small>('.ToolBox::toHtml($society->getCoordinates()).')</small>';
					}
					echo '</address>';
				}
				if ($society->getPhone()) echo '<small>tél. : </small>'.ToolBox::toHtml($society->getPhone()).'<br />';

				echo '<div>';
				echo '<strong>activité(s) :</strong>';
				$industries = $society->getIndustries();
				if (count($industries)>0) {
					echo '<ul id="industries_list">';
					foreach ($industries as $i) {
						echo '<li>'.$i->getHtmlLink().'</li>';
					}
					echo '</ul>';
				} else {
					echo '<small>aucune activit�</small>';
				}
				echo '</div>';

				if ($society->getDescription()) {
					echo '<div><blockquote>'.ToolBox::toHtml($society->getDescription()).'</blockquote></div>';
				}
				?>
				<p>
					<a href="society_edit.php?society_id=<?php echo $society->getId() ?>" class="editlink">[édition]</a>
				</p>
			</section>
		</div>

		<div class="pure-u-1-4">
			<section>
				<h2>
					Les personnes de ma connaissance <small>(les participations)</small>
				</h2>
				<ul>
					<?php
					foreach ($memberships as $ms) {
						$i = $ms->getIndividual();
						echo '<li>';
						echo '<a href="individual.php?individual_id='.$i->getId().'">'.ToolBox::toHtml($i->getWholeName()).'</a>';
						$smallTag_elt = array();
						if ($ms->getDepartment()) $smallTag_elt[] = ToolBox::toHtml($ms->getDepartment());
						if ($ms->getTitle()) $smallTag_elt[] = ToolBox::toHtml($ms->getTitle());
						if (count($smallTag_elt)>0) {
							echo ' <small>(';
							echo implode(' / ', $smallTag_elt);
							echo ')</small>';
						}
						echo '&nbsp<a href="membership_edit.php?membership_id='.$ms->getId().'" class="editlink" title="éditer la participation de '.ToolBox::toHtml($i->getWholeName()).'">[�dition]</a>';
						echo '</li>';
					}
					?>
					<li><a href="membership_edit.php?society_id=<?php echo $society->getId() ?>">Nouvelle participation</a></li>
				</ul>
			</section>
		</div>
		<div class="pure-u-1-4">
			<section>
				<h2>Les pistes</h2>
				<ul>
					<?php
					foreach ($leads as $l) {
						echo '<li>';
						echo '<a href="lead_edit.php?lead_id='.$l->getId().'">';
						echo $l->getShortDescription() ? ToolBox::toHtml($l->getShortDescription()) : 'Piste n°'.$l->getId();
						echo '</a>';
						if ($l->getCreationDate()) echo ' <small>('.ToolBox::toHtml($l->getCreationDateFr()).')</small>';
						echo '</li>';
					}
					?>
					<li><a href="lead_edit.php?society_id=<?php echo $society->getId() ?>">Nouvelle piste</a></li>
				</ul>
			</section>
		</div>

		<div class="pure-u-1-4">
			<section>
				<h2>Évènements</h2>
				<ul>
					<?php
					foreach ($events as $e) {
						echo '<li>';
						echo '<a href="society_event_edit.php?event_id='.$e->getId().'">';
						echo ToolBox::toHtml($e->getLabel());
						echo '</a>';
						echo '<p>'.nl2br(ToolBox::toHtml($e->getComment())).'</p>';
						echo '</li>';
					}
					?>
					<li><a href="society_event_edit.php?society_id=<?php echo $society->getId() ?>">Nouvel évènement</a></li>
				</ul>
			</section>
		</div>

		<div class="pure-u-1-4">
			<section>
				<h2>Relation avec d'autres sociétés</h2>
				<ul>
					<?php
					$rowset = $society->getRelationshipsWithSocietyRowset();
					while ($row = mysql_fetch_array($rowset)) {
						echo '<li>';
						$s = new Society();
						$s->feed($row);
						echo '<a href="society.php?society_id='.$s->getId().'">'.$s->getNameForHtmlDisplay().'</a>';
						echo ' <small>(';
						echo '<a href="relationship_edit.php?relationship_id='.$row['relationship_id'].'">';
						echo empty($row['relatedsociety_role']) ? '?' : ToolBox::toHtml($row['relatedsociety_role']);
						echo '</a>';
						echo ')</small>';
						if (!empty($row['description'])) {
							echo '<p>';
							echo ToolBox::toHtml($row['description']);
							echo '</p>';
						}
						echo '</li>';
					}
					?>
					<li><a href="relationship_edit.php?item0_class=Society&amp;item0_id=<?php echo $society->getId() ?>">Nouvelle relation</a></li>
				</ul>
			</section>
		</div>
		<div class="pure-u-1">
			<footer><?php include 'menu.inc.php'; ?></footer>
		</div>
	</div>
</body>
</html>