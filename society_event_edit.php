<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System( 'config/host.json' );

session_start ();

if (empty ( $_SESSION ['user_id'] )) {
	header ( 'Location:login.php' );
	exit ();
} else {
	$user = new User ( $_SESSION ['user_id'] );
	$user->feed ();
}

// messages à délivrer
$messages = array ();

if (! empty ( $_REQUEST ['event_id'] )) {
	$event = new Event ( $_REQUEST ['event_id'] );
	$event->feed();
	$society = $event->getSociety ();
} else {
	$event = new Event ();
	$society = empty ( $_REQUEST ['society_id'] ) ? new Society () : new Society ( $_REQUEST ['society_id'] );
}

// Formatage des données saisies par l'utilisateur
if (isset ( $_POST )) {
	ToolBox::formatUserPost ( $_POST );

	if (isset ( $_REQUEST ['task_id'] )) {
		switch ($_REQUEST ['task_id']) {
			case 'save' :
				$event->feed ( $_REQUEST );
				$event->toDB();
				header ( 'Location:society.php?society_id=' . $society->getId () );
				exit();
			case 'markAsDone' :
				$event->setWarehouse('history');
				$event->toDB();
				header('Location:'.$system->getAppliUrl());
				exit();
			case 'markAsCancelled' :
				$event->setWarehouse('trashcan');
				$event->toDB();
				header('Location:'.$system->getAppliUrl());
				exit();
		}
	}
}

$doc_title = 'Un évènement chez ' . $society->getName();
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo ToolBox::toHtml(strip_tags($doc_title)); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="<?php echo PHOSPHOR_URI ?>"></link>    
    <link type="text/css" rel="stylesheet" href="<?php echo $system->getSkinUrl() ?>theme.css"></link>
    <?php echo $system->writeHtmlHeadTagsForFavicon(); ?>
	<script src="<?php echo JQUERY_URI; ?>"></script>
	<script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
</head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1>Un évènement survient chez <?php echo '<a href="society.php?society_id='.$society->getId().'">'.ToolBox::toHtml($society->getName()).'</a>'; ?></h1>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<input name="task_id" type="hidden" value="save" />

		<?php if ($event->hasId()) : ?>
			<input name="event_id" type="hidden" value="<?php echo $event->getId() ?>" />
		<?php endif; ?>

		<?php if ($society->hasId()): ?>
			<input name="society_id" type="hidden" value="<?php echo $society->getId() ?>" />
		<?php endif; ?>

		<div class="form-group">
			<label for="datetime_i">Quand ?</label>
			<input id="datetime_i" name="datetime" type="text" value="<?php echo $event->hasDatetime()? $event->getDatetime() : date('Y-m-d h:i:s', time()) ?>" size="25" maxlength="19" class="form-control" />
		</div>
		<div class="form-group">
			<label for="type_i">Quelle est la nature de l&rsquo;évènement ?</label><br />
			<select id="type_i" name="type" class="form-control">
				<?php echo $event->hasType() ? Event::getTypeOptionsTags($event->getType()) : Event::getTypeOptionsTags(); ?>
			</select>
		</div>
		<?php
			if ($event->hasUserPosition () && strcmp ( $event->getUserPosition (), 'active' ) == 0) {
				echo '<div class="radio"><label><input name="user_position" type="radio" value="active" checked="checked" />Actif</label></div>';
				echo '<div class="radio"><label><input name="user_position" type="radio" value="passive" />Passif</label></div>';
			} else {
				echo '<div class="radio"><label><input name="user_position" type="radio" value="active" />Actif</label></div>';
				echo '<div class="radio"><label><input name="user_position" type="radio" value="passive" checked="checked" />Passif</label></div>';
			}
		?>
		<div class="form-group">
			<label for="media_i">Quel est le média ?</label>
			<select id="media_i" name="media" class="form-control">
				<?php echo $event->hasMedia() ? Event::getMediaOptionsTags($event->getMedia()) : Event::getMediaOptionsTags(); ?>
			</select>
		</div>
		<div class="form-group">
			<label for="comment_i">Commentaire</label>
			<textarea id="comment_i" name="comment" cols="55" rows="10" class="form-control"><?php echo $event->getComment() ?></textarea>
		</div>
		
		<button type="submit" class="btn btn-primary">Enregistrer</button>
	</form>
</div>
</body>
</html>
