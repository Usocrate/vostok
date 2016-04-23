<nav class="navbar navbar-default">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
				<span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo APPLI_URL ?>"><?php echo ToolBox::toHtml(APPLI_NAME) ?></a>
		</div>

		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<ul class="nav navbar-nav">
				<li><a href="/lead_edit.php">Nouvelle piste</a></li>
				<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Les pistes <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="/leads.php?lead_newsearch_order=1">Toutes</a></li>
						<li><a href="/leads.php?lead_newsearch_order=1&amp;lead_status=suivie">Suivie</a></li>
						<li><a href="/leads.php?lead_newsearch_order=1&amp;lead_status=<?php echo urlencode('à suivre') ?>">A suivre</a></li>
						<li role="separator" class="divider"></li>
						<li><a href="/lead_types_admin.php">Types de piste</a></li>
					</ul></li>
				<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Les sociétés <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="/societies_list.php?society_newsearch=1">Toutes</a></li>
						<li><a href="/cities.php">Par ville</a></li>
						<li><a href="/industries.php">Par activité</a></li>
						<li><a href="/societies_list.php?society_newsearch=1&amp;society_city=">Avec ville n.c.</a></li>
						<li><a href="/society_edit.php">Nouvelle société</a></li>
					</ul>
				</li>
				<li><a href="/individuals.php?individual_newsearch=1">Les individus</a></li>
			</ul>
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Plus <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="http://todo.usocrate.fr/index.php?sProject=Vostok">TodoList</a></li>
						<li><a href="/login.php?anonymat_submission=1">Se déconnecter</a></li>
					</ul></li>
			</ul>
		</div>
	</div>
</nav>