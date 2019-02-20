<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="<?php echo $system->getAppliUrl() ?>"><?php echo ToolBox::toHtml($system->getAppliName()) ?></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse d-lg-flex" id="navbarSupportedContent">
    <ul class="navbar-nav flex-lg-fill">
		<li class="nav-item"><a class="nav-link" href="lead_edit.php">Nouvelle piste</a></li>
		<li class="nav-item"><a class="nav-link" href="leads.php?lead_newsearch">Les pistes</a></li>
		<li class="nav-item"><a class="nav-link" href="societies_list.php?society_newsearch">Les sociétés</a></li>
		<li class="nav-item"><a class="nav-link" href="individuals.php?individual_newsearch">Les gens</a></li>	
		<li class="nav-item dropdown">
			<a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Plus</a>
			<div class="dropdown-menu" aria-labelledby="navbarDropdown">
				<h6 class="dropdown-header">Les sociétés</h6>
				<a class="dropdown-item" href="cities.php">Par ville</a>
				<a class="dropdown-item" href="industries.php">Par activité</a>
				<a class="dropdown-item" href="society_edit.php">Nouvelle société</a>
				<div class="dropdown-divider"></div>
				<h6 class="dropdown-header">Les gens</h6>
				<a class="dropdown-item" href="individual_edit.php">Introduire un nouvel individu</a>
				<a class="dropdown-item" href="titles.php">Les rôles</a>
				<div class="dropdown-divider"></div>
				<h6 class="dropdown-header">Les pistes</h6>
				<a class="dropdown-item" href="leads.php?lead_newsearch_order=1&amp;lead_status=suivie">Suivies</a>
				<a class="dropdown-item" href="leads.php">Dernière recherche</a>
				<a class="dropdown-item" href="leads.php?lead_newsearch_order=1&amp;lead_status=<?php echo urlencode('à suivre') ?>">A suivre</a>
				<a class="dropdown-item" href="lead_types_admin.php">Gérer les types</a>
				<div class="dropdown-divider"></div>
				<a class="dropdown-item" href="login.php?anonymat_submission=1">Se déconnecter</a>
			</div>
		</li>    	
    </ul>
	<form class="form-inline flex-lg-fill mx-lg-2" method="post" action="societies_list.php">
		<input id="navbar_s_name_i" name="society_name" type="search" class="form-control flex-lg-fill mx-lg-1" placeholder="société..." />
		<button type="submit" name="society_newsearch" value="filtrer" class="btn btn-outline-primary mx-lg-1">Ok</button>
	</form>    
  </div>
</nav>

<script type="text/javascript">
	$(document).ready(function(){
    $('#navbar_s_name_i').autocomplete({
		minLength: 2,
   		source: function( request, response ) {
            $.ajax({
				method:'GET',
                url:'api/society_names.json.php',
                dataType: 'json',
                data:{
                    'query': request.term
                 },
                 dataFilter: function(data,type){
                     return JSON.stringify(JSON.parse(data).names);
                 },
                 success : function(data, textStatus, jqXHR){
					response(data);
                 }
         	})
   		},
        focus: function( event, ui ) {
			$('#navbar_s_name_i').val( ui.item.value );
        	return false;
        },
        select: function( event, ui ) {
			$('#navbar_s_name_i').val( ui.item.value );
        	return false;
        },
        _renderItem: function( ul, item ) {
		    return $( "<li>" ).append(item.label).appendTo( ul );
	    }
   	});
})
</script>