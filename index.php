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

session_start ();
ToolBox::getDBAccess ();

if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {
	$user = new User ( $_SESSION ['user_id'] );
	$user->feed ();
}

$doc_title = 'Accueil';
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
		<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>
		<div class="row">
			<div class="col-md-6">
				<section>
					<form method="post" action="/societies_list.php">
						<div class="form-group">
							<label for="s_name_i">Une société</label>
							<input id="s_name_i" name="society_name" type="text" class="form-control" placeholder="nom" />
						</div>
						<button type="submit" name="society_newsearch" value="filtrer" class="btn btn-default">Chercher</button>
					</form>
				</section>
				<section>
				<form method="post" action="/individuals.php">
					<div class="form-group">
						<label for="individual_lastName_i">Un individu</label> <input id="individual_lastName_i" name="individual_lastName" type="text" placeholder="nom de famille" class="form-control" />
					</div>
					<button type="submit" name="individual_newsearch" value="filtrer" class="btn btn-default">Chercher</button>
				</form>
				</section>
			</div>
			<div class="col-md-6">
				<section>
					<h2>Les prochains évènements planifiés</h2>
    				<?php echo EventCollection::getNextPlanningEvents()->toHtml(); ?>
    			</section>
				<section>
					<h2>Les derniers évènements enregistrés</h2>
    				<?php echo EventCollection::getLastHistoryEvents()->toHtml(); ?>
    			</section>
			</div>
		</div>
	</div>
<script type="text/javascript">
	$(document).ready(function(){
	    $('#s_name_i').autocomplete({
			minLength: 3,
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
				$('#s_name_i').val( ui.item.value );
	        	return false;
	        },
	        select: function( event, ui ) {
				$('#s_name_i').val( ui.item.value );
	        	return false;
	        }
	   	}).autocomplete( "instance" )._renderItem = function( ul, item ) {
		    //alert(JSON.stringify(item));
		    return $( "<li>" ).append(item.label).appendTo( ul );
	    };
	})
</script>	
</body>
</html>