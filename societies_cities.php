<?php
require_once 'config/boot.php';
require_once 'classes/System.php';
$system = new System( 'config/host.json' );

session_start();

if (empty($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $user = new User($_SESSION['user_id']);
    $user->feed();
}
$doc_title = 'Les villes d\'implantation';
?>
<!doctype html>
<html lang="fr">
<head>
<title><?php echo ToolBox::toHtml($system->getAppliName()) ?>: Répartition des sociétés par ville</title>
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
<main class="container-fluid">
	<h1><?php echo ToolBox::toHtml($doc_title); ?></h1>
	<table class="table">
		<thead>
			<tr>
				<th>Ville</th>
				<th>Nombre de sociétés</th>
			</tr>
		</thead>
		<tbody>
    	<?php
        foreach ($system->getSocietyCountByCity() as $row) {
            echo '<tr>';
            echo '<td>';
            echo empty($row['city']) ? '<small>n.c.</small>' : '<a href="societies.php?society_newsearch=1&amp;society_city=' . $row['city'] . '">' . $row['city'] . '</a>';
            echo '</td>';
            echo '<td>';
            echo '<span class="badge badge-secondary">';
            echo '<a href="societies.php?society_newsearch=1&amp;society_city=' . $row['city'] . '">';
            echo $row['count'];
            echo '</a>';
            echo '</span>';
            echo '</td>';
            echo '</tr>';
        }
        ?>
    	</tbody>
	</table>
</main>
</body>
</html>
