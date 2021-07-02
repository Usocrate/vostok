<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="<?php echo $system->getAppliUrl() ?>"><?php echo ToolBox::toHtml($system->getAppliName()) ?></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse d-lg-flex" id="navbarSupportedContent">
    <ul class="navbar-nav flex-lg-fill">
		<li class="nav-item"><a class="nav-link" href="lead_edit.php">Nouvelle piste</a></li>
		<li class="nav-item"><a class="nav-link" href="leads.php?lead_newsearch">Les pistes</a></li>
		<li class="nav-item"><a class="nav-link" href="societies.php?society_newsearch">Les sociétés</a></li>
		<li class="nav-item"><a class="nav-link" href="individuals.php?individual_newsearch">Les gens</a></li>	
		<li class="nav-item dropdown">
			<a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Plus</a>
			<div class="dropdown-menu" aria-labelledby="navbarDropdown">
				<h6 class="dropdown-header">Les sociétés</h6>
				<a class="dropdown-item" href="societies_cities.php">Les villes d'implantation</a>
				<a class="dropdown-item" href="societies_industries.php">Les activités exercées</a>
				<a class="dropdown-item" href="societies_roles.php">Les relations entre sociétés</a>
				<a class="dropdown-item" href="society_edit.php">Nouvelle société</a>
				<div class="dropdown-divider"></div>
				<h6 class="dropdown-header">Les gens</h6>
				<a class="dropdown-item" href="titles.php">Les rôles</a>
				<a class="dropdown-item" href="individual_edit.php">Introduire un nouvel individu</a>
				<div class="dropdown-divider"></div>
				<h6 class="dropdown-header">Les pistes</h6>
				<a class="dropdown-item" href="leads.php?lead_newsearch_order=1&amp;lead_status=suivie">Suivies</a>
				<a class="dropdown-item" href="leads.php">Dernière recherche</a>
				<a class="dropdown-item" href="leads.php?lead_newsearch_order=1&amp;lead_status=<?php echo urlencode('à suivre') ?>">A suivre</a>
				<a class="dropdown-item" href="lead_types_admin.php">Gérer les types</a>
				<div class="dropdown-divider"></div>
				<a class="dropdown-item" href="./admin/config.php">Admin</a>
				<a class="dropdown-item" href="login.php?anonymat_submission=1">Se déconnecter</a>
			</div>
		</li>    	
    </ul>
	<form class="form-inline flex-lg-fill mx-lg-2" method="post" action="entities.php">
		<input id="entity_search_i" name="query" type="search" class="form-control flex-lg-fill mx-lg-1  my-1" placeholder="nom, prénom, société" />
		<button type="submit" name="entity_newsearch" value="filtrer" class="btn btn-outline-primary mx-lg-1 my-1">Ok</button>
	</form>    
  </div>
</nav>

<script>
	$(document).ready(function(){

	    $('#entity_search_i').autocomplete({
			minLength: 2,
	   		source: function( request, response ) {
	            $.ajax({
					method:'GET',
	                url:'api/entities_names.json.php',
	                dataType: 'json',
	                data:{
	                    'query': request.term
	                 },
	                 success : function(data, textStatus, jqXHR){
	                 	//console.log(JSON.stringify(data));
						response(data);
	                 }
	         	})
	   		},
	   		select: function( event, ui ) {
	   			switch(ui.item.type){
	   				case 'individual':
	   					document.location.href = "individual.php?individual_id="+ ui.item.id;
	   					break;
					case 'society':
	   					document.location.href = "society.php?society_id="+ ui.item.id;
	   					break;
	   			}
	   			return false;
	   		},
	   		focus: function( event, ui ) {
	   			$('#entity_search_i').val(ui.item.name);
	   			return false;
	   		}
	   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
	   		if (item.type == "individual") {
	   			var icon = '<i class="fas fa-user-circle colored"></i>';
	   		} else if (item.type == "society") {
	   			var icon = '<i class="fas fa-users colored"></i>';
	   		}
	   		var nameWithSmallTerm = String(item.name).replace(new RegExp(this.term, "gi"),"<small>$&</small>");
	   		return $("<li>").append("<div>"+icon+" "+nameWithSmallTerm+"</div>").appendTo(ul);
	    };
	})
</script>