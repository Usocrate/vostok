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

session_start();
ToolBox::getDBAccess();

if (empty($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
} else {
    $user = new User($_SESSION['user_id']);
    $user->feed();
}


$messages = array();

// première société
$s1 = new Society($_REQUEST['society_id'][0]);
$s1->feed();

// deuxième société
$s2 = new Society($_REQUEST['society_id'][1]);
$s2->feed();

// la société de référence (celle qu'on va mettre à jour) est la plus anciennement créée
// NB : on ne dispose pas toujours de la date de création
$cond1 = $s2->getCreationDate() > $s1->getCreationDate();
$cond2 = ! $s2->getCreationDate() && $s1->getCreationDate();
if ($cond1 || $cond2) {
    $oldest = & $s2;
    $newest = & $s1;
} else {
    $oldest = & $s1;
    $newest = & $s2;
}

//
// demande de fusion des données d'une Society
//
if (isset($_POST['fusion_submission'])) {
    if (isset($_POST['society_correctvalues_id']) && is_array($_POST['society_correctvalues_id'])) {
        /*
         * c'est la société dont l'enregistrement est le plus ancien qui sera conservée, modifications faites
         */
        foreach ($_POST['society_correctvalues_id'] as $key => $value) {
            /*
             * $value contient l'identifiant de la société dont la donnée doit prévaloir
             */
            if ( strcmp( $value, $oldest->getId() ) == 0 ) continue;
            switch ($key) {
                case 'name':
                    $oldest->setName($newest->getName());
                    break;
                case 'url':
                    $oldest->setUrl($newest->getUrl());
                    break;
                case 'phone':
                    $oldest->setPhone($newest->getPhone());
                    break;
                case 'street':
                    $oldest->setStreet($newest->getStreet());
                    break;
                case 'postalcode':
                    $oldest->setPostalCode($newest->getPostalCode());
                    break;
                case 'city':
                    $oldest->setCity($newest->getCity());
                    break;
                case 'description':
                    $oldest->setDescription($newest->getDescription());
                    break;
                default:
            }
        }
        $oldest->toDB();
    }
    
    // transfert des entités rattachées à la société la plus récente vers la société la plus ancienne
    echo $newest->transferMemberships($oldest) ? '' : 'participations non transférées';
    echo $newest->transferRelationships($oldest) ? '' : 'relations non transférées';
    echo $newest->transferLeads($oldest) ? '' : 'pistes non transférées';
    echo $newest->transferIndustries($oldest) ? '' : 'activités non transférées';
    
    // suppression du doublon
    echo $newest->delete() ? '' : 'no deletion';
    header('location:society.php?society_id=' . $oldest->getId());
    exit();
}
$doc_title = 'Fusion de deux sociétés existantes';
?>
<!doctype html>
<html lang="fr">
<head>
    <title><?php echo ToolBox::toHtml($doctitle); ?></title>
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
	
	<section>
	<p>La validation du formulaire ci-dessous permettra de fusionner les sociétés suivantes :</p>
	<ul>
		<li>
		<?php
            echo $s1->getNameForHtmlDisplay();
            if ($s1->getCreationDate()) echo ' <small>(créée le ' . $s1->getCreationDate() . ')</small>';
        ?>
		</li>
		<li>
		<?php
            echo $s2->getNameForHtmlDisplay();
            if ($s2->getCreationDate()) echo ' <small>(créée le ' . $s2->getCreationDate() . ')</small>';
        ?>
		</li>
	</ul>
	<br />
	<p>Merci d'indiquer la valeur que vous souhaitez conserver lorsque cela vous est demandé ...</p>
	
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    	<?php
        /**
         * ***************************
         * nom de la société
         * ***************************
         */
        if ($s1->getName() || $s2->getName()) {
            if (strcmp($s1->getName(), $s2->getName()) == 0) {
                echo '<h1>Nom <small>(en commun)</small></h1>';
                echo '<p>' . $s1->getNameForHtmlDisplay() . '</p>';
            } else {
                if (! $s2->getName()) {
                    echo '<h1>Nom</h1>';
                    echo '<p>' . $s1->getNameForHtmlDisplay() . '</p>';
                    echo '<input name="society_correctvalues_id[nom]" type="hidden" value="' . $s1->getId() . '">';
                } elseif (! $s1->getName()) {
                    echo '<h1>Nom</h1>';
                    echo '<p>' . $s2->getNameForHtmlDisplay() . '</p>';
                    echo '<input name="society_correctvalues_id[nom]" type="hidden" value="' . $s2->getId() . '">';
                } else {
                    echo '<h1>Quel nom ?</h1>';
                    echo '<div>';
                    echo '<input name="society_correctvalues_id[nom]" type="radio" value="' . $s1->getId() . '"';
                    if ($s1 === $oldest)
                        echo ' checked="checked"';
                    echo '/>';
                    echo $s1->getNameForHtmlDisplay();
                    echo '</div>';
                    echo '<div>';
                    echo '<input name="society_correctvalues_id[nom]" type="radio" value="' . $s2->getId() . '"';
                    if ($s2 === $oldest)
                        echo ' checked="checked"';
                    echo '/>';
                    echo $s2->getNameForHtmlDisplay();
                    echo '</div>';
                }
            }
        } else {
            echo '<h1>Nom</h1>';
            echo '<p><small>Aucun nom connu</small></p>';
        }
        echo '<br/>';
        
        /**
         * ***************************
         * site web
         * ***************************
         */
        if ($s1->getUrl() || $s2->getUrl()) {
            if (strcmp($s1->getUrl(), $s2->getUrl()) == 0) {
                echo '<h1>Site web <small>(en commun)</small></h1>';
                echo '<p>' . $s1->getUrl() . '<p>';
            } else {
                if (! $s2->getUrl()) {
                    echo '<h1>Site web</h1>';
                    echo '<p>' . $s1->getUrl() . '</p>';
                    echo '<input name="society_correctvalues_id[url]" type="hidden" value="' . $s1->getId() . '">';
                } elseif (! $s1->getUrl()) {
                    echo '<h1>Site web</h1>';
                    echo '<p>' . $s2->getUrl() . ' <small>(un seul site web connu)</small></p>';
                    echo '<input name="society_correctvalues_id[url]" type="hidden" value="' . $s2->getId() . '">';
                } else {
                    echo '<h1>Quel site web ?</h1>';
                    echo '<div>';
                    echo '<input name="society_correctvalues_id[url]" type="radio" value="' . $s1->getId() . '"';
                    if ($s1 === $oldest)
                        echo ' checked="checked"';
                    echo '/>';
                    echo $s1->getUrl();
                    echo '</div>';
                    echo '<div>';
                    echo '<input name="society_correctvalues_id[url]" type="radio" value="' . $s2->getId() . '"';
                    if ($s2 === $oldest)
                        echo ' checked="checked"';
                    echo '/>';
                    echo $s2->getUrl();
                    echo '</div>';
                }
            }
        } else {
            echo '<h1>Site web</h1>';
            echo '<p><small>Aucun site web connu</small><p>';
        }
        echo '<br/>';
        
        /**
         * ***************************
         * téléphone
         * ***************************
         */
        if ($s1->getPhone() || $s2->getPhone()) {
            if (strcmp($s1->getPhone(), $s2->getPhone()) == 0) {
                echo '<h1>Téléphone <small>(en commun)</small></h1>';
                echo '<p>' . $s1->getPhone() . '<p>';
            } else {
                if (! $s2->getPhone()) {
                    echo '<h1>Téléphone</h1>';
                    echo '<p>' . $s1->getPhone() . '</p>';
                    echo '<input name="society_correctvalues_id[phone]" type="hidden" value="' . $s1->getId() . '">';
                } elseif (! $s1->getPhone()) {
                    echo '<h1>Téléphone</h1>';
                    echo '<p>' . $s2->getPhone() . '</p>';
                    echo '<input name="society_correctvalues_id[phone]" type="hidden" value="' . $s2->getId() . '">';
                } else {
                    echo '<h1>Quel téléphone ?</h1>';
                    echo '<div>';
                    echo '<input name="society_correctvalues_id[phone]" type="radio" value="' . $s1->getId() . '"';
                    if ($s1 === $oldest)
                        echo ' checked="checked"';
                    echo '/>';
                    echo $s1->getPhone();
                    echo '</div>';
                    echo '<div>';
                    echo '<input name="society_correctvalues_id[phone]" type="radio" value="' . $s2->getId() . '"';
                    if ($s2 === $oldest)
                        echo ' checked="checked"';
                    echo '/>';
                    echo $s2->getPhone();
                    echo '</div>';
                }
            }
        } else {
            echo '<h1>Téléphone</h1>';
            echo '<p><small>Aucun téléphone connu</small><p>';
        }
        echo '<br/>';
        
        /**
         * ***************************
         * adresse postale
         * ***************************
         */
        // street
        if ($s1->getStreet() || $s2->getStreet()) {
            if ($s1->getStreet() == $s2->getStreet()) {
                echo '<h1>Rue <small>(en commun)</small></h1>';
                echo '<p>' . $s1->getStreet() . '<p>';
            } else {
                if (! $s2->getStreet()) {
                    echo '<h1>Rue</h1>';
                    echo '<p>' . $s1->getStreet() . '</p>';
                    echo '<input name="society_correctvalues_id[street]" type="hidden" value="' . $s1->getId() . '">';
                } elseif (! $s1->getStreet()) {
                    echo '<h1>Adresse postale</h1>';
                    echo '<p>' . $s2->getStreet() . '</p>';
                    echo '<input name="society_correctvalues_id[street]" type="hidden" value="' . $s2->getId() . '">';
                } else {
                    echo '<h1>Quelle adresse postale ?</h1>';
                    echo '<table>';
                    echo '<tr>';
                    echo '<td style="vertical-align:top">';
                    echo '<input name="society_correctvalues_id[street]" type="radio" value="' . $s1->getId() . '"';
                    if ($s1 === $oldest)
                        echo ' checked="checked"';
                    echo '/>';
                    echo '</td>';
                    echo '<td style="vertical-align:top">';
                    echo $s1->getStreet();
                    echo '</td>';
                    echo '<td style="vertical-align:top">';
                    echo '<input name="society_correctvalues_id[street]" type="radio" value="' . $s2->getId() . '"';
                    if ($s2 === $oldest)
                        echo ' checked="checked"';
                    echo '/>';
                    echo '</td>';
                    echo '<td style="vertical-align:top">';
                    echo $s2->getStreet();
                    echo '</td>';
                    echo '</tr>';
                    echo '</table>';
                }
            }
        } else {
            echo '<h1>Rue</h1>';
            echo '<p><small>Aucun rue indiquée</small><p>';
        }
        echo '<br/>';
        
        // postal code
        if ($s1->getPostalCode() || $s2->getPostalCode()) {
            if ($s1->getPostalCode() == $s2->getPostalCode()) {
                echo '<h1>code postal <small>(en commun)</small></h1>';
                echo '<p>' . $s1->getPostalCode() . '<p>';
            } else {
                if (! $s2->getPostalCode()) {
                    echo '<h1>Code postal</h1>';
                    echo '<p>' . $s1->getPostalCode() . '</p>';
                    echo '<input name="society_correctvalues_id[postalcode]" type="hidden" value="' . $s1->getId() . '">';
                } elseif (! $s1->getPostalCode()) {
                    echo '<h1>Code postal</h1>';
                    echo '<p>' . $s2->getPostalCode() . '</p>';
                    echo '<input name="society_correctvalues_id[postalcode]" type="hidden" value="' . $s2->getId() . '">';
                } else {
                    echo '<h1>Quelle adresse postale ?</h1>';
                    echo '<table>';
                    echo '<tr>';
                    echo '<td style="vertical-align:top">';
                    echo '<input name="society_correctvalues_id[postalcode]" type="radio" value="' . $s1->getId() . '"';
                    if ($s1 === $oldest)
                        echo ' checked="checked"';
                    echo '/>';
                    echo '</td>';
                    echo '<td style="vertical-align:top">';
                    echo $s1->getPostalCode();
                    echo '</td>';
                    echo '<td style="vertical-align:top">';
                    echo '<input name="society_correctvalues_id[postalcode]" type="radio" value="' . $s2->getId() . '"';
                    if ($s2 === $oldest)
                        echo ' checked="checked"';
                    echo '/>';
                    echo '</td>';
                    echo '<td style="vertical-align:top">';
                    echo $s2->getPostalCode();
                    echo '</td>';
                    echo '</tr>';
                    echo '</table>';
                }
            }
        } else {
            echo '<h1>Code postal</h1>';
            echo '<p><small>Aucun code postal indiqué</small><p>';
        }
        echo '<br/>';
        
        // city
        if ($s1->getCity() || $s2->getCity()) {
            if ($s1->getCity() == $s2->getCity()) {
                echo '<h1>ville <small>(en commun)</small></h1>';
                echo '<p>' . $s1->getCity() . '<p>';
            } else {
                if (! $s2->getCity()) {
                    echo '<h1>Ville</h1>';
                    echo '<p>' . $s1->getCity() . '</p>';
                    echo '<input name="society_correctvalues_id[city]" type="hidden" value="' . $s1->getId() . '">';
                } elseif (! $s1->getCity()) {
                    echo '<h1>Code postal</h1>';
                    echo '<p>' . $s2->getCity() . '</p>';
                    echo '<input name="society_correctvalues_id[city]" type="hidden" value="' . $s2->getId() . '">';
                } else {
                    echo '<h1>Quelle adresse postale ?</h1>';
                    echo '<table>';
                    echo '<tr>';
                    echo '<td style="vertical-align:top">';
                    echo '<input name="society_correctvalues_id[city]" type="radio" value="' . $s1->getId() . '"';
                    if ($s1 === $oldest)
                        echo ' checked="checked"';
                    echo '/>';
                    echo '</td>';
                    echo '<td style="vertical-align:top">';
                    echo $s1->getCity();
                    echo '</td>';
                    echo '<td style="vertical-align:top">';
                    echo '<input name="society_correctvalues_id[city]" type="radio" value="' . $s2->getId() . '"';
                    if ($s2 === $oldest)
                        echo ' checked="checked"';
                    echo '/>';
                    echo '</td>';
                    echo '<td style="vertical-align:top">';
                    echo $s2->getCity();
                    echo '</td>';
                    echo '</tr>';
                    echo '</table>';
                }
            }
        } else {
            echo '<h1>Ville</h1>';
            echo '<p><small>Aucune ville indiquée</small><p>';
        }
        echo '<br/>';
        
        /**
         * ***************************
         * commentaire
         * ***************************
         */
        if ($s1->getDescription() || $s2->getDescription()) {
            if (strcmp($s1->getDescription(), $s2->getDescription()) == 0) {
                echo '<h1>Description <small>(en commun)</small></h1>';
                echo '<p>' . nl2br($s1->getDescription()) . '</p>';
            } else {
                if (! $s2->getDescription()) {
                    echo '<h1>Description</h1>';
                    echo '<p>' . $s1->getDescription() . '</p>';
                    echo '<input name="society_correctvalues_id[description]" type="hidden" value="' . $s1->getId() . '">';
                } elseif (! $s1->getDescription()) {
                    echo '<h1>Description</h1>';
                    echo '<p>' . $s2->getDescription() . '</p>';
                    echo '<input name="society_correctvalues_id[commentaire]" type="hidden" value="' . $s2->getId() . '">';
                } else {
                    echo '<h1>Quelle description ?</h1>';
                    echo '<table>';
                    echo '<tr>';
                    echo '<td style="vertical-align:top">';
                    echo '<input name="society_correctvalues_id[commentaire]" type="radio" value="' . $s1->getId() . '"';
                    if ($s1 === $oldest)
                        echo ' checked="checked"';
                    echo '/>';
                    echo '</td>';
                    echo '<td style="vertical-align:top">';
                    echo $s1->getDescription();
                    echo '</td>';
                    echo '<td style="vertical-align:top">';
                    echo '<input name="society_correctvalues_id[commentaire]" type="radio" value="' . $s2->getId() . '"';
                    if ($s2 === $oldest)
                        echo ' checked="checked"';
                    echo '/>';
                    echo '</td>';
                    echo '<td style="vertical-align:top">';
                    echo $s2->getDescription();
                    echo '</td>';
                    echo '</tr>';
                    echo '</table>';
                }
            }
        } else {
            echo '<h1>Description</h1>';
            echo '<p><small>Aucune description indiquée</small></p>';
        }
        echo '<br />';
        ?>
		<button type="submit" name="fusion_submission" value="1" class="btn btn-primary">Valider</button>
		<input name="society_id[]" type="hidden" value="<?php echo $s1->getId() ?>" /> <input name="society_id[]" type="hidden" value="<?php echo $s2->getId() ?>" />
	</form>
	</section>
</div>	
</body>
</html>
