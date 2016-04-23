<?php
require_once 'config/main.inc.php';

session_start ();
ToolBox::getDBAccess ();

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
	$event->feed ();
	$society = $event->getSociety ();
} else {
	$event = new Event ();
	$society = empty ( $_REQUEST ['society_id'] ) ? new Society () : new Society ( $_REQUEST ['society_id'] );
}

// Formatage des données saisies par l'utilisateur
if (isset ( $_POST )) {
	ToolBox::formatUserPost ( $_POST );
	
	if (isset ( $_POST ['task_id'] )) {
		switch ($_POST ['task_id']) {
			case 'save' :
				$event->feed ( $_POST );
				$event->toDB ();
				header ( 'Location:society.php?society_id=' . $society->getId () );
				exit ();
		}
	}
}

$doc_title = 'Un évènement survient chez ' . $society->getNameForHtmlDisplay ();
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo ToolBox::toHtml(strip_tags($doc_title)); ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
    <link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
    <script type="text/javascript" src="js/controls.js"></script>
    <link rel="stylesheet" href="<?php echo SKIN_URL ?>main.css" type="text/css">
</head>
<body>
	<header>
		<div class="brand">
			<a href="<?php echo APPLI_URL?>"><?php echo ToolBox::toHtml(APPLI_NAME) ?></a>
		</div>
	</header>
	
	<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>
	
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<input name="task_id" type="hidden" value="save" />
		<?php if ($event->hasId()) : ?>
		<input name="event_id" type="hidden" value="<?php echo $event->getId() ?>" />
		<?php endif; ?>
		<?php if ($society->hasId()): ?>
		<input name="society_id" type="hidden" value="<?php echo $society->getId() ?>" />
		<?php endif; ?>
		<fieldset>
			<legend>Description de l&rsquo;évènement</legend>
			<p>
				<label>Quand ?</label><br /> <input name="datetime" type="text" value="<?php echo $event->hasDatetime()? $event->getDatetime() : date('Y-m-d h:i:s', time()) ?>" size="25" maxlength="19" />
			</p>
			<p>
				<label>Quelle est la nature de l&rsquo;évènement ?</label><br /> <select name="type">
					<?php echo $event->hasType() ? Event::getTypeOptionsTags($event->getType()) : Event::getTypeOptionsTags(); ?>
				</select>
			</p>
			<p>
				<label>Quelle est ici ta position ?</label><br />

				<?php
				if ($event->hasUserPosition () && strcmp ( $event->getUserPosition (), 'active' ) == 0) {
					echo '<div class="radio"><label><input name="user_position" type="radio" value="active" checked="checked" />active</label></div>';
					echo '<div class="radio"><label><input name="user_position" type="radio" value="passive" />passive</label></div>';
				} else {
					echo '<div class="radio"><label><input name="user_position" type="radio" value="active" />active</label></div>';
					echo '<div class="radio"><label><input name="user_position" type="radio" value="passive" checked="checked" />passive</label></div>';
				}
				?>
			</p>
			<p>
				<label>Quel est le média ?</label><br /> <select name="media">
					<?php echo $event->hasMedia() ? Event::getMediaOptionsTags($event->getMedia()) : Event::getMediaOptionsTags(); ?>
				</select>
			</p>
			<p>
				<label>Commentaire</label> <br />
				<textarea name="comment" cols="55" rows="10"><?php echo ToolBox::toHtml($event->getComment()) ?></textarea>
			</p>
		</fieldset>
		<button type="submit" class="btn btn-primary">Enregistrer</button>
	</form>
	<footer><?php include 'menu.inc.php'; ?></footer>
</body>
</html>