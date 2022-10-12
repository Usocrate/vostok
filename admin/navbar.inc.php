<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<span class="navbar-brand"><a href="<?php echo $system->getAppliUrl() ?>"><?php echo ToolBox::toHtml($system->getAppliName()) ?></a>&nbsp;<a href="<?php echo $system->getAppliUrl() ?>/admin"><small>Admin</small></a></span>
  
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    	<span class="navbar-toggler-icon"></span>
  	</button>

	<div class="collapse navbar-collapse d-lg-flex" id="navbarSupportedContent">
    	<ul class="navbar-nav flex-lg-fill">
			<li class="nav-item"><a class="nav-link" href="config.php">Configuration</a></li>
			<li class="nav-item"><a class="nav-link" href="<?php echo $system->getAppliUrl() ?>">Quitter</a></li>
    	</ul>
    </div>
</nav>