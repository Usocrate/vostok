<?php
require_once 'config/boot.php';
require_once 'classes/System.class.php';
$system = new System( 'config/host.json' );

session_start();

// print_r($_POST);

if (empty($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $user = new User($_SESSION['user_id']);
    $user->feed();
}

$role = empty($_REQUEST['role']) ? null : $_REQUEST['role'];

if (isset($_POST)) {
    ToolBox::formatUserPost($_POST);
    
    if (isset($_POST['task_id'])) {
        $fb = new UserFeedBack();
        ToolBox::formatUserPost($_POST);
        switch ($_POST['task_id']) {
            case 'replaceRole':
                if (!empty($role) && !empty($_POST['newRole'])) {
                    if (Relationship::replaceRole($role, $_POST['newRole'], 'society')) {
                        header ( 'Location:relationshipSocietyRole.php?role='.$_POST['newRole'] );
                        exit();
                    }
                }
                break;
        }
    }
}


?>
<!doctype html>
<html lang="fr">
<head>
	<title><?php echo strip_tags($h1_content) ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="<?php echo FONTAWESOME_CSS_URI ?>" integrity="<?php echo FONTAWESOME_CSS_URI_INTEGRITY ?>" crossorigin="anonymous" />
	<link type="text/css" rel="stylesheet" href="<?php echo JQUERY_UI_CSS_THEME_URI ?>"></link>
	<link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="<?php echo JQUERY_UI_URI; ?>"></script>
	<script src="<?php echo BOOTSTRAP_JS_URI ?>" integrity="<?php echo BOOTSTRAP_JS_URI_INTEGRITY ?>" crossorigin="anonymous"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<nav>
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="societies_roles.php">Les relations entre sociétés</a></li>
    <li class="breadcrumb-item"><a href="relationshipSocietyRole.php?role=<?php echo $role ?>"><?php echo ToolBox::toHtml($role) ?></a></li>
    <li class="breadcrumb-item active">éditer</li>
  </ol>
</nav>
<div class="container-fluid">
	<h1 class="bd-title"><?php echo ToolBox::toHtml($role) ?></h1>
		
	<?php
    if (isset($fb)) {
        echo '<div>';
        echo $fb->AllMessagesToHtml();
        echo '</div>';
    }
    ?>
		
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<input name="role" type="hidden" value="<?php echo $role ?>">
		<div class="form-group">
			<label for="newRole_i">Nouveau nom</label> <input id="newRole_i" name="newRole" type="text" maxlength="255" class="form-control" />
		</div>
		<button name="task_id" type="submit" value="replaceRole" class="btn btn-primary">enregistrer</button>
	</form>
</div>
<script>
$(document).ready(function(){
	$('#newRole_i').autocomplete({
		minLength: 2,
   		source: function( request, response ) {
            $.ajax({
				method:'GET',
                url:'api/relationships/roles.php',
                dataType: 'json',
                data:{
                    'searchPattern': request.term,
                    'rolePlayerClass': 'society'
                 },
                 dataFilter: function(data,type){
                     return JSON.stringify(JSON.parse(data).roles);
                 },
                 success : function(data, textStatus, jqXHR){
					response(data);
                 }
         	})
   		},
        focus: function( event, ui ) {
			$('#newRole_i').val( ui.item.role);
        	return false;
        },
        select: function( event, ui ) {
        	$('#newRole_i').val( ui.item.role);
        	return false;
        }
   	}).autocomplete("instance")._renderItem = function( ul, item ) {
	   	var content = '<div>'+item.role+' <small>('+item.nb+')</small></div>';
	   	return $( "<li>" ).append(content).appendTo( ul );
	};		
})
</script>
</body>
</html>