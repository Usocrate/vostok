<nav class="navbar navbar-default">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
				<span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?php echo $system->getAppliUrl() ?>"><?php echo ToolBox::toHtml($system->getAppliName()) ?></a>
		</div>

		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<ul class="nav navbar-nav">
				<li><a href="lead_edit.php">Nouvelle piste</a></li>
				<li><a href="leads.php?lead_newsearch_order=1">Les pistes</a></li>
				<li><a href="societies_list.php">Les sociétés</a></li>
				<li><a href="individuals.php">Les gens</a></li>	
				<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Plus <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li class="dropdown-header">Les sociétés</li>
						<li><a href="cities.php">Par ville</a></li>
						<li><a href="industries.php">Par activité</a></li>
						<li><a href="society_edit.php">Nouvelle société</a></li>
						<li role="separator" class="divider"></li>
						<li class="dropdown-header">Les pistes</li>
						<li><a href="leads.php?lead_newsearch_order=1&amp;lead_status=suivie">Suivies</a></li>
						<li><a href="leads.php">Dernière recherche</a></li>
						<li><a href="leads.php?lead_newsearch_order=1&amp;lead_status=<?php echo urlencode('à suivre') ?>">A suivre</a></li>
						<li><a href="lead_types_admin.php">Gérer les types</a></li>
					</ul>
				</li>
				</ul>
			<form class="navbar-form navbar-left" method="post" action="societies_list.php">
				<div class="form-group">
					<input id="navbar_s_name_i" name="society_name" type="text" class="form-control" placeholder="société..." />
				</div>
				<button type="submit" name="society_newsearch" value="filtrer" class="btn btn-default">Ok</button>
			</form>
			<ul class="nav navbar-nav navbar-right">
				<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Plus <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="login.php?anonymat_submission=1">Se déconnecter</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</nav>
<script type="text/javascript">
	$(document).ready(function(){
    $('#navbar_s_name_i').autocomplete({
		minLength: 2,
   		source: function( request, response ) {
            $.ajax({
				method:'GET',
                url:'society_names.json.php',
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
        }
   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
	    return $( "<li>" ).append(item.label).appendTo( ul );
    };
})
</script>