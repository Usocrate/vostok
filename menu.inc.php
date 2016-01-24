<?php
$closeToBirthDateIndividuals = $system->getCloseToBirthDateIndividuals();
if ($closeToBirthDateIndividuals->getSize()>0) {
	echo '<section class="flat">';
	echo '<h1>Les anniversaires</h1>';
	echo '<ul>';
	$i = $closeToBirthDateIndividuals->getIterator();
	do {
		echo '<li><a href="individual.php?individual_id='.$i->current()->getId().'">'.$i->current()->getWholeName().'</a> <small>('.$i->current()->getBirthDateFr().')</small></li>';
	} while ($i->next());
	echo '</ul>';
	echo '</section>';
}
?>
<nav>
	<section>
		<h1>Les pistes</h1>
		<ul>
			<li><a href="/leads.php?lead_newsearch_order=1">Toutes</a></li>
			<li><a href="/leads.php?lead_newsearch_order=1&amp;lead_status=suivie">Suivie</a></li>
			<li><a href="/leads.php?lead_newsearch_order=1&amp;lead_status=<?php echo urlencode('à suivre') ?>">A suivre</a></li>
			<li><a href="/lead_types_admin.php">Types de piste</a></li>
			<li><a href="/lead_edit.php">Nouvelle piste</a></li>
		</ul>
	</section>
	<section>
		<h1>Les individus</h1>
		<ul>
			<li><a href="/individuals.php?individual_newsearch=1">Tous</a></li>
			<li>
				<form method="post" action="/individuals.php" class="pure-form">
					<label for="individual_lastName_i">Nom<input id="individual_lastName_i" name="individual_lastName" type="text" value="<?php if (isset($_SESSION['individual_search']['lastName'])) echo $_SESSION['individual_search']['lastName'] ?>" placeholder="nom de famille" />
					</label>
					<button type="submit" name="individual_newsearch" value="filtrer" class="pure-button">ok</button>
				</form>
			</li>
			<li><a href="/individuals.php?individual_toCheck=1&amp;individual_newsearch=1">Les individus isolés</a></li>
			<li><a href="/individual_edit.php">Nouvel individu</a></li>
		</ul>
	</section>
	<section>
		<h1>Les sociétés</h1>
		<ul>
			<li><a href="/societies_list.php?society_newsearch=1">Toutes</a></li>
			<li>
				<form method="post" action="/societies_list.php" class="pure-form">
					<label>Nom<input id="society_name_i" name="society_name" type="text" value="<?php if (isset($_SESSION['society_search']['name'])) echo $_SESSION['society_search']['name'] ?>" size="5" />
					</label>
					<button type="submit" name="society_newsearch" value="filtrer" class="pure-button">ok</button>
					<input type="hidden" name="society_newsearch" value="1" />
				</form>
			</li>
			<li><a href="/cities.php">Par ville</a></li>
			<li><a href="/industries.php">Par activité</a></li>
			<li><a href="/societies_list.php?society_newsearch=1&amp;society_city=">Avec ville n.c.</a></li>
			<li><a href="/society_edit.php">Nouvelle société</a></li>
		</ul>
	</section>
	<section>
		<h1>Divers</h1>
		<ul>
			<li><a href="/index.php">Accueil</a></li>
			<li><a href="http://todo.usocrate.fr/index.php?sProject=Vostok">TodoList</a></li>
			<li><a href="/login.php?anonymat_submission=1">Se déconnecter</a></li>
		</ul>
	</section>
</nav>