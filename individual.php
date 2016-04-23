<?php
require_once 'config/main.inc.php';

session_start();
ToolBox::getDBAccess();

if (empty ($_SESSION['user_id'])) {
	header('Location:login.php');
	exit;
} else {
	$user = new User($_SESSION['user_id']);
	$user->feed();
}


$individual = new Individual($_REQUEST['individual_id']);
$individual->feed();
$memberships = $individual->getMemberships();

$doc_title = $individual->getWholeName();
?>
<!doctype html>
<html lang="fr">
<head>
<title><?php echo $doc_title ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_URI ?>" type="text/css" />
<link rel="stylesheet" href="<?php echo BOOTSTRAP_CSS_THEME_URI ?>" type="text/css" />
<script language="javascript" type="application/javascript">
	<!-- <![CDATA[
	function SocietyLink(society_id, department, title, individual_phone, individual_email, description){
		this.society_id = society_id;
		this.department = department;
		this.title = title;
		this.individual_phone = individual_phone;
		this.individual_email = individual_email;
		this.description = description;
	}
	
	Society.prototype.setSocietyId = new Function(id){
		this.society_id = id;
	}
	
	function fillSocietyLinkForm(society_id){
		form = document.getElementById('societyLink_form');
	}
	]]>-->
	</script>
<link rel="stylesheet" href="<?php echo SKIN_URL ?>main.css" type="text/css"><script type="text/javascript" src="<?php echo JQUERY_URI; ?>"></script><script type="text/javascript" src="<?php echo BOOTSTRAP_JS_URI; ?>"></script></head>
<body>
<?php include 'navbar.inc.php'; ?>
<div class="container-fluid">
	<h1><?php echo ToolBox::toHtml($doc_title); ?> <small><a href="individual_edit.php?individual_id=<?php echo $individual->getId() ?>"><span class="glyphicon glyphicon-edit"></span></a></small></h1>
	<div class="row">
		<div class="col-md-8">
			<section>
			<div class="card">	
				<?php
				if ($individual->getPhotoUrl()) {
					echo '<div class="photo">';
					echo $individual->getPhotoHtml();
					echo '</div>';
				}
				?>
				<div class="data">
					<small>naissance : </small>
					<?php echo $individual->getBirthDate() ? $individual->getBirthDate() : 'n.c.'; ?>
					<br /> <small>description : </small>
					<?php echo $individual->getAttribute('description') ? $individual->getAttribute('description') : 'n.c.' ?>
					<br /> <small>autre tél. : </small>
					<?php echo $individual->getAttribute('phone') ? $individual->getAttribute('phone') : 'n.c.' ?>
					<br /> <small>mobile : </small>
					<?php echo $individual->getAttribute('mobile') ? $individual->getAttribute('mobile') : 'n.c.' ?>
					<br /> <small>email : </small>
					<?php echo $individual->getEmailHtml() ? $individual->getEmailHtml() : 'n.c.' ?>
					<br /> <small>web : </small>
					<?php echo $individual->getAttribute('web') ? '<a href="'.$individual->getAttribute('web').'">'.$individual->getAttribute('web').'</a>' : 'n.c.'; ?>
					<br /> <small>adresse : </small>
					<?php echo $individual->getAttribute('street') ? $individual->getAttribute('street') : 'n.c.' ?>
					<br /> <small>ville : </small>
					<?php echo $individual->getAttribute('city') ? $individual->getAttribute('city') : 'n.c.' ?>
					<br /> <small>cp : </small>
					<?php echo $individual->getAttribute('postalCode') ? $individual->getAttribute('postalCode') : 'n.c.' ?>
					<br /> <small>pays : </small>
					<?php echo $individual->getAttribute('country') ? $individual->getAttribute('country') : 'n.c.' ?>
					<?php
					if ($individual->getCvUrl()) {
						echo '<p><a href="'.$individual->getCvUrl().'">cv</a></p>';
					}
					if ($individual->getGoogleQueryUrl()) {
						echo '<p><a href="'.$individual->getGoogleQueryUrl().'">'.$individual->getWholeName().' dans Google</a></p>';
					}
					?>
				</div>
			</div>
			</section>
		</div>
		<div id="participations_div" class="col-md-4">
			<section>
    			<h2>Participations</h2>
    			<?php
    			if (isset($memberships)){
    				echo '<ul>';
    				foreach ($memberships as $ms) {
    					$s = $ms->getSociety();
    					echo '<li>';
    					echo '<p>';
    					echo '<a href="society.php?society_id='.$s->getId().'">'.$s->getName().'</a>';
    					if ($ms->getDepartment()) echo ' ('.$ms->getDepartment().')';
    					if ($ms->getTitle()) echo '<br \><small>'.$ms->getTitle().'</small>';
    					if ($ms->getUrl()) echo ' '.$ms->getWebHtmlLink();
    					echo '</p>';
    					$data = array();
    					if ($ms->getPhone()) $data[] = $ms->getPhone();
    					if ($ms->getEmail()) {
    						$data[] = '<a href="mailto:'.ToolBox::toHtml($individual->getFirstName()).'%20'.ToolBox::toHtml($individual->getLastName()).'%20<'.$ms->getEmail().'>">'.$ms->getEmail().'</a>';
    					}
    					if (count($data)>0) {
    						echo '<p><small>'.implode(' - ', $data).'</small></p>';
    					}
    					if ($ms->getDescription()) echo '<p>'.$ms->getDescription().'</p>';
    					echo '<a href="membership_edit.php?membership_id='.$ms->getId().'"><span class="glyphicon glyphicon-edit"></span> édition</a>';
    					echo '</li>';
    				}
    				echo '<li><a href="membership_edit.php?individual_id='.$individual->getId().'">nouvelle participation</a></li>';
    				echo '</ul><br/>';
    			}
    			?>
			</section>
		</div>
	</div>
</div>	
</body>
</html>