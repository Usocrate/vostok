<?php
/**
 * @package usocrate.vostok
 * @author Florent Chanavat
 */
class Individual {
	protected $id;
	
	protected $firstName;
	protected $lastName;
	
	protected $street;
	protected $city;
	protected $postalcode;
	protected $state;
	protected $country;
	
	public function __construct($id = NULL) {
		$this->id = $id;
	}
	public function involveInEvent(Event $event, $role = null, $comment = null) {
		try {
			if (isset ( $this->id ) && $event->hasId ()) {
				$settings = array ();
				if (isset ( $role )) {
					$settings [] = 'role="' . mysql_real_escape_string ( $role ) . '"';
				}
				if (isset ( $comment )) {
					$settings [] = 'comment="' . mysql_real_escape_string ( $comment ) . '"';
				}
				if ($this->isEventInvolved ( $event )) {
					$sql = 'UPDATE event_involvement SET ' . implode ( ',', $settings ) . ' WHERE individual_id=' . $this->id . ' AND event_id=' . $event->getId ();
				} else {
					$settings [] = 'individual_id=' . $this->id;
					$settings [] = 'event_id=' . $event->getId ();
					$sql = 'INSERT INTO event_involvement SET ' . implode ( ',', $settings );
				}
				if (mysql_query ( $sql ) === true) {
					return true;
				} else {
					throw new Exception ( 'Échec de la requête : ' . $sql );
				}
			} else {
				throw new Exception ( 'Les identifiants de la personne et de l\'évènement doivent être connus' );
			}
		} catch ( Exception $e ) {
			trigger_error ( __METHOD__ . ' : ' . $e->getMessage () );
		}
	}
	/**
	 * @version 03/01/2017
	 **/
	public function isEventInvolved(Event $event) {
		global $system;
		try {
			if (isset ( $this->id ) && $event->hasId ()) {
				$statement = $system->getPdo()->prepare('SELECT COUNT(*) FROM event_involvement WHERE individual_id=:individual_id AND event_id=:event_id GROUP BY individual_id');
				$statement->bindValue(':individual_id', $this->id, PDO::PARAM_INT);
				$statement->bindValue(':event_id', $event->getId(), PDO::PARAM_INT);
				$statement->execute();
				return $statement->fetchColumn() > 0;
			} else {
				throw new Exception ( 'Les identifiants de la personne et de l\'évènement doivent être connus' );
			}
		} catch ( Exception $e ) {
			trigger_error ( __METHOD__ . ' : ' . $e->getMessage () );
		}
	}
	/**
	 * @version 03/01/2017
	 **/	
	public function deleteEventInvolvement(Event $event) {
		global $system;
		try {
			if (isset ( $this->id ) && $event->hasId ()) {
				$statement = $system->getPdo()->prepare('DELETE FROM event_involvement WHERE individual_id=:individual_id AND event_id=:event_id');
				$statement->bindValue(':individual_id', $this->id, PDO::PARAM_INT);
				$statement->bindValue(':event_id', $event->getId(), PDO::PARAM_INT);
				return $statement->execute();
			} else {
				throw new Exception ( 'Les identifiants de la personne et de l\'évènement doivent être connus' );
			}
		} catch ( Exception $e ) {
			trigger_error ( __METHOD__ . ' : ' . $e->getMessage () );
		}
	}
	/**
	 * Fixe la valeur d'un attribut.
	 */
	public function setAttribute($name, $value) {
		$value = trim ( $value );
		$value = html_entity_decode ( $value, ENT_QUOTES, 'UTF-8' );
		return $this->{$name} = $value;
	}
	public function getAttribute($name) {
		return isset ( $this->$name ) ? $this->$name : NULL;
	}
	/**
	 * Obtient le lieu de résidence de l'individu.
	 */
	public function getStreet() {
		return $this->getAttribute ( 'street' );
	}
	/**
	 * Obtient la ville de résidence de l'individu.
	 */
	public function getCity() {
		return $this->getAttribute ( 'city' );
	}
	/**
	 * Obtient le code postal de la résidence de l'individu.
	 */
	public function getPostalCode() {
		return $this->getAttribute ( 'postalCode' );
	}
	/**
	 * Obtient le pays de la résidence de l'individu.
	 */
	public function getCountry() {
		return $this->getAttribute ( 'country' );
	}
	/**
	 * Fixe l'identifiant de la personne.
	 */
	public function setId($input) {
		return $this->setAttribute ( 'id', $input );
	}
	public function getId() {
		return $this->getAttribute ( 'id' );
	}
	/**
	 * Indique si l'identifiant de l'individu est connu.
	 *
	 * @since 28/12/2010
	 * @return bool
	 */
	public function hasId() {
		return isset ( $this->id );
	}
	/**
	 * Obtient la date de naissance.
	 *
	 * @since 07/01/2006
	 */
	public function getBirthDate() {
		return isset ( $this->birth_date ) ? $this->birth_date : NULL;
	}
	/**
	 * Obtient la date de naissance au timestamp unix.
	 *
	 * @since 21/01/2007
	 */
	public function getBirthDateTimestamp() {
		if ($this->getBirthDate ()) {
			list ( $year, $month, $day ) = explode ( '-', $this->getBirthDate () );
			return mktime ( 0, 0, 0, $month, $day, $year );
		} else {
			return NULL;
		}
	}
	/**
	 * Obtient la date de naissance au format français.
	 *
	 * @since 21/01/2007
	 */
	public function getBirthDateFr() {
		return date ( 'd/m/Y', $this->getBirthDateTimestamp () );
	}
	public function identifyFromName() {
		if (empty ( $this->lastName ) || empty ( $this->firstName ))
			return false;
		$sql = 'SELECT individual_id FROM individual';
		$sql .= ' WHERE individual_lastName="' . mysql_real_escape_string ( $this->lastName ) . '"';
		$sql .= ' AND individual_firstName="' . mysql_real_escape_string ( $this->firstName ) . '"';
		$rowset = mysql_query ( $sql );
		$row = mysql_fetch_assoc ( $rowset );
		return $this->id = $row ['individual_id'];
	}
	/**
	 * Obtient la civilité.
	 *
	 * @since 24/09/2006
	 */
	public function getSalutation() {
		return $this->getAttribute ( 'salutation' );
	}
	/**
	 * Obtient les options pour le champ salutation, au format HTML.
	 *
	 * @version 24/05/2006
	 */
	public function getSalutationOptionsTags($valueToSelect = NULL) {
		$salutations = array (
				'mlle' => 'Mlle',
				'mme' => 'Mme',
				'mr' => 'Mr' 
		);
		if (is_null ( $valueToSelect ) && isset ( $this->salutation )) {
			$valueToSelect = $this->salutation;
		}
		$html = '';
		foreach ( $salutations as $key => $value ) {
			$html .= '<option ';
			$html .= 'value="' . $key . '"';
			if (isset ( $valueToSelect ) && strcmp ( $key, $valueToSelect ) == 0)
				$html .= ' selected="selected"';
			$html .= '>';
			$html .= $value;
			$html .= '</option>';
		}
		return $html;
	}
	/**
	 * Fixe le prénom.
	 */
	public function setFirstName($name) {
		if (! empty ( $name ))
			$this->firstName = $name;
	}
	/**
	 * Fixe le nom.
	 */
	public function setLastName($name) {
		if (! empty ( $name ))
			$this->lastName = $name;
	}
	/**
	 * Obtient le prénom de l'individu.
	 *
	 * @return string
	 * @since 19/11/2005
	 */
	public function getFirstName() {
		return $this->getAttribute ( 'firstName' );
	}
	/**
	 * Obtient le nom de famille de l'individu.
	 *
	 * @return string
	 * @since 19/11/2005
	 */
	public function getLastName() {
		return $this->getAttribute ( 'lastName' );
	}
	/**
	 * Obtenir le nom complet de l'individu, formatté pour l'affichage.
	 *
	 * @return string
	 */
	public function getWholeName() {
		if (! isset ( $this->salutation ) || ! isset ( $this->firstName ) || ! isset ( $this->lastName )) {
			$dataset = $this->getDataFromBase ( array (
					'individual_salutation',
					'individual_firstName',
					'individual_lastName' 
			) );
			$this->feed ( $dataset );
		}
		$pieces = array ();
		/*
		if (! empty ( $this->salutation )) {
			$pieces [] = ucfirst ( $this->salutation );
		}
		*/
		if (! empty ( $this->firstName )) {
			$pieces [] = ucfirst ( $this->firstName );
		}
		if (! empty ( $this->lastName )) {
			$pieces [] = strtoupper ( $this->lastName );
		}
		if (count ( $pieces ) == 0) {
			return 'XXX';
		}
		return implode ( ' ', $pieces );
	}
	/**
	 * Obtenir le lien vers l'écran dédié à l'individu.
	 *
	 * @return string
	 * @since 09/12/2016
	 */
	public function getHtmlLinkToIndividual() {
		return '<a href="individual.php?individual_id='.$this->getId().'">'.ToolBox::toHtml($this->getWholeName()).'</a>';
	}
	/**
	 * Obtient le commentaire.
	 */
	public function getDescription() {
		return $this->getAttribute ( 'description' );
	}
	/**
	 * Obtient l'adresse du site web perso.
	 */
	public function getWeb() {
		return $this->getAttribute ( 'web' );
	}
	public function getHtmlLinkToWeb() {
		if ( ! empty($this->web) ) return '<a href="'.$this->web.'">'.$this->web.'</a>';
	}
	/**
	 * Obtient le n° de tél.
	 * portable.
	 */
	public function getMobilePhoneNumber() {
		return $this->getAttribute ( 'mobile' );
	}
	/**
	 * Obtient le n° de tél.
	 * fixe.
	 *
	 * @since 24/09/2006
	 */
	public function getPhoneNumber() {
		return $this->getAttribute ( 'phone' );
	}
	/**
	 * Obtient l'adresse e-mail.
	 *
	 * @since 24/09/2006
	 */
	public function getEmailAddress() {
		return $this->getAttribute ( 'email' );
	}
	
	public function getAddress() {
		$elements = array ();
		if ($this->getStreet ()) {
			$elements [] = $this->getStreet ();
		}
		if ($this->getPostalCode ()) {
			$elements [] = $this->getPostalCode ();
		}
		if ($this->getCity ()) {
			$elements [] = $this->getCity ();
		}
		if (count ( $elements ) > 0) {
			return implode ( ' ', $elements );
		}
	}
	
	/**
	 * Obtient le lien 'mailTo:' de la personne.
	 */
	public function getEmailHtml() {
		if ($this->getAttribute ( 'email' ))
			return '<a href="mailto:' . $this->getAttribute ( 'email' ) . '">' . $this->getAttribute ( 'email' ) . '</a>';
	}
	/**
	 * Obtient l'Url de la photographie de la personne.
	 *
	 * @return string
	 */
	public function getPhotoUrl() {
		global $system;
		if (isset ( $this->photo_url )) {
			return $this->photo_url;
		} else {
			$file_extensions = array (
					'jpg',
					'jpeg',
					'gif',
					'png' 
			);
			
			// recherche d'un fichier construit à partir de l'id de l'individu.
			foreach ( $file_extensions as $e ) {
				$file_name = $this->getId () . '.' . $e;
				if (is_file ( $system->getTrombiDirPath () . DIRECTORY_SEPARATOR . $file_name )) {
					return $this->photo_url = $system->getTrombiUrl () . $file_name;
				}
			}
			
			// recherche d'un fichier construit à partir du nom de l'individu.
			$file_basename = ToolBox::formatForFileName ( $this->lastName . '_' . $this->firstName );
			foreach ( $file_extensions as $e ) {
				$file_name = $file_basename . '.' . $e;
				if (is_file ( $system->getTrombiDirPath () . DIRECTORY_SEPARATOR . $file_name )) {
					return $this->photo_url = $system->getTrombiUrl () . $file_name;
				}
			}
		}
		return NULL;
	}
	/**
	 * Obtient le chemin d'accès au fichier.
	 *
	 * @return string
	 */
	public function getPhotoFilePath() {
		global $system;
		$file_extensions = array (
				'jpg',
				'jpeg',
				'gif',
				'png' 
		);
		$file_basename = ToolBox::formatForFileName ( $this->lastName . '_' . $this->firstName );
		foreach ( $file_extensions as $e ) {
			$file_name = $file_basename . '.' . $e;
			if (is_file ( $system->getTrombiDirPath () . DIRECTORY_SEPARATOR_ . $file_name )) {
				return $system->getTrombiDirPath () . DIRECTORY_SEPARATOR_ . $file_name;
			}
			$file_name = $this->getId () . '.' . $e;
			if (is_file ( $system->getTrombiDirPath () . DIRECTORY_SEPARATOR_ . $file_name )) {
				return $system->getTrombiDirPath () . DIRECTORY_SEPARATOR_ . $file_name;
			}
		}
	}
	/**
	 * Supprime le fichier photo.
	 *
	 * @since 02/12/2006
	 */
	private function deletePhotoFile() {
		return unlink ( $this->getPhotoFilePath () );
	}
	/**
	 *
	 * @since 07/08/2014
	 * @param array $uploadedFile        	
	 * @return boolean
	 */
	public function filePhoto(array $uploadedFile) {
		global $system;
		try {
			if ($uploadedFile ['size'] > 0) {
				$ext = end ( explode ( '.', $uploadedFile ['name'] ) );
				$targetFilePath = $system->getTrombiDirPath() . DIRECTORY_SEPARATOR . $this->getId () . '.' . $ext;
				if (is_file ( $targetFilePath )) {
					unlink ( $targetFilePath );
				}
				return copy ( $uploadedFile ['tmp_name'], $targetFilePath );
			}
		} catch ( Exception $e ) {
			System::reportException ( $e );
			return false;
		}
	}
	/**
	 * Indique si une photo est associée à l'individu.
	 *
	 * @since 02/12/2006
	 */
	private function hasPhoto() {
		return is_file ( $this->getPhotoFilePath () );
	}
	public function getPhotoHtml() {
		if ($this->getPhotoUrl ())
			return '<img src="' . $this->getPhotoUrl () . '" class="thumbnail" />';
	}
	/**
	 * Obtient l'Url du CV la personne.
	 *
	 * @return String
	 */
	public function getCvUrl() {
		global $system;
		if (isset ( $this->cv_url )) {
			return $this->cv_url;
		} else {
			$file_extensions = array (
					'odt',
					'pdf',
					'rtf',
					'txt',
					'doc' 
			);
			$file_basename = ToolBox::formatForFileName ( $this->lastName . '_' . $this->firstName );
			foreach ( $file_extensions as $e ) {
				$file_name = $file_basename . '.' . $e;
				if (is_file ( $system->getCvDirPath () . DIRECTORY_SEPARATOR . $file_name )) {
					return $this->cv_url = $system->getCvUrl () . '/' . $file_name;
				}
			}
		}
		return NULL;
	}
	/**
	 * Obtient l'URL permettant de googliser la personne.
	 *
	 * @return string
	 * @since 29/10/2007
	 */
	public function getGoogleQueryUrl($type = 'search') {
		$params = array ();
		if (isset ( $this->firstName )) {
			$params [] = $this->firstName;
		}
		if (isset ( $this->lastName )) {
			$params [] = $this->lastName;
		}
		if (count ( $params ) > 0) {
			$query = '?q=' . urlencode ( implode ( '+', $params ) );
			switch ($type) {
				case 'images' :
					$url = 'http://images.google.com/images';
					break;
				case 'news' :
					$url = 'http://news.google.com/news';
					break;
				case 'groups' :
					$url = 'http://groups.google.com/groups';
					break;
				default :
					$url = 'http://www.google.com/search';
			}
			return $url . $query;
		} else {
			return NULL;
		}
	}
	public function getAddressFromGoogle($input = NULL) {
		global $system;
		if ( empty($input) ) $input = $this->getAddress();
		$json = $system->getGoogleGeocodeAsJson($input);
		$data = json_decode($json);
		$street = array();
		foreach ($data->{'results'}[0]->{'address_components'} as $c) {
			if (in_array('street_number', $c->types)) {
				$street['number'] = $c->long_name;
			}
			if (in_array('route', $c->types)) {
				$street['route'] = $c->long_name;
			}
			if (in_array('locality', $c->types)) {
				$this->city = $c->long_name;
			}
			if (in_array('postal_code', $c->types)) {
				$this->postalCode = $c->long_name;
			}
			if (in_array('administrative_area_level_1', $c->types)) {
				$this->state = $c->long_name;
			}
			if (in_array('country', $c->types)) {
				$this->country = $c->short_name;
			}			
		}
		$this->street = $street['number'].' '.$street['route'];
	}
	/**
	 * Obtient le nombre de participations enregistrées en base de données.
	 *
	 * @since 15/08/2006
	 * @version 03/01/2017
	 */
	public function getMembershipsRowsNb() {
		global $system;
		try {
			if (empty ( $this->id ))
				throw new Exception('On ne peut compter les participations d\'un individu non identifié.');

			$statement = $system->getPdo()->prepare('SELECT COUNT(*) FROM membership WHERE individual_id=:id GROUP BY individual_id');
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			$statement->execute();
			return $statement->fetchColumn();
			
		} catch (Exception $e) {
			trigger_error(__METHOD__.$e->getMessage());
		}
	}
	/**
	 * Obtient les participations associées à l'individu.
	 *
	 * @return array
	 * @since 21/01/2006
	 * @version 03/01/2017
	 */
	public function getMemberships($society = NULL) {
		global $system;
		try {
			if (empty ( $this->id ))
				throw new Exception('Récupérer les participations d\'un individu exige que celui-ci soit identifié.');
			
			$memberships = array ();
			$criteria = array ();
			
			$criteria[] = 'ac.individual_id=:id';
			
			if (isset ( $society )) {
				$criteria[] = 'ac.society_id=:society_id';
			}
			
			$sql = 'SELECT *,';
			$sql .= ' DATE_FORMAT(a.society_creation_date, "%d/%m/%Y") as society_creation_date';
			$sql .= ' FROM membership AS ac LEFT OUTER JOIN society AS a ON ac.society_id = a.society_id';
			$sql .= ' WHERE '.implode(' AND ', $criteria);
			$sql .= ' ORDER BY init_date DESC';
			
			$statement = $system->getPdo()->prepare($sql);
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			if (isset ( $society )) {
				$statement->bindValue(':society_id', $society->getId, PDO::PARAM_INT);
			}
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$statement->execute();

			foreach ( $statement->fetchAll() as $row ) {
				$ms = new Membership ();
				$ms->feed ( $row );
				$s = $ms->getSociety ();
				$s->feed ( $row );
				$memberships [] = $ms;
			}
			return $memberships;			
		} catch (Exception $e) {
			trigger_error(__METHOD__.$e->getMessage());
		}
	}
	/**
	 * @version 03/01/2017
	 **/
	public function addMembershipRow($society_id, $department = NULL, $title = NULL, $phone = NULL, $email = NULL, $description = NULL) {
		global $system;
		try {
			if (empty ( $this->id ) || empty ( $society_id )) {
				throw new Exception('Il faut un individu et une société identifiée pour ajouter une participation.');
			}
			$settings = array ();
			if (isset ( $department )) {
				$settings [] = 'department=:department';
			}
			if (isset ( $title )) {
				$settings [] = 'title=:title';
			}
			if (isset ( $phone )) {
				$settings [] = 'phone=:phone';
			}
			if (isset ( $email )) {
				$settings [] = 'email=:email';
			}
			if (isset ( $description )) {
				$settings [] = 'description=:description';
			}
			if ($this->isMember ( $society_id )) {
				// il s'agit d'une mise à jour d'un lien existant
				$sql = 'UPDATE membership SET '.implode ( ', ', $settings ).' WHERE individual_id=:individual_id AND society_id=:society_id';
			} else {
				// il s'agit d'un nouveau lien
				$settings [] = 'society_id=:society_id';
				$settings [] = 'individual_id=:individual_id';
				$sql = 'INSERT INTO membership SET '.implode ( ', ', $settings );
			}
			$statement = $system->getPdo()->prepare($sql);
			
			$statement->bindValue(':society_id', $society_id, PDO::PARAM_INT);
			$statement->bindValue(':individual_id', $this->id, PDO::PARAM_INT);
			
			if (isset ( $department )) {
				$statement->bindValue(':department', $department, PDO::PARAM_STR);
			}
			if (isset ( $title )) {
				$statement->bindValue(':title', $title, PDO::PARAM_STR);
			}
			if (isset ( $phone )) {
				$statement->bindValue(':phone', $phone, PDO::PARAM_STR);
			}
			if (isset ( $email )) {
				$statement->bindValue(':email', $email, PDO::PARAM_STR);
			}
			if (isset ( $description )) {
				$statement->bindValue(':description', $description, PDO::PARAM_STR);
			}
			return $statement->execute();
		} catch (Exception $e) {
			trigger_error(__METHOD__.$e->getMessage());
		}
	}
	/**
	 * Efface toutes les participations de cet individu en base de données.
	 *
	 * @return boolean
	 * @version 03/01/2017
	 */
	public function deleteMemberships() {
		global $system;
		try {
			if (empty ( $this->id ))
				throw new Exception('La suppression des participations enregistrées n\'est possible que pour un individu identifié.');
			
			$statement = $system->getPdo()->prepare('DELETE FROM membership WHERE individual_id=:id');
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			return $statement->execute();
		}
		catch (Exception $e) {
			trigger_error ( __METHOD__ . ' : ' . $e->getMessage () );			
		}		
	}
	/**
	 * Indique si l'individu participe à une société donnée.
	 *
	 * @return boolean
	 * @param int $society_id
	 * @version 03/01/2017
	 */
	public function isMember($society_id) {
		global $system;
		try {
			if (empty ( $this->id ))
				throw new Exception('On ne peut tester l\'appartenance d\'un individu à une société que s\'il est identifié.');
				
			$statement = $system->getPdo()->prepare('SELECT * FROM membership WHERE individual_id=:id AND society_id=society_id');
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			$statement->bindValue(':society_id', $society_id, PDO::PARAM_INT);
			$statement->execute();
			$rowset = $statement->fetchAll();
			return count($rowset) > 0 ? true : false;			
		} catch (Exception $e) {
			trigger_error ( __METHOD__ . ' : ' . $e->getMessage () );			
		}
	}
	/**
	 * Efface, en base de données, toutes les relations de cet individu aux pistes.
	 *
	 * @return boolean
	 * @since 19/11/2005
	 * @version 03/01/2017
	 */
	public function deleteLeadsInvolvement() {
		global $system;
		try {
			if (empty ( $this->id ))
				throw new Exception('La suppression des pistes enregistrées n\'est possible que pour un individu identifié.');
			
			$statement = $system->getPdo()->prepare('DELETE FROM membership WHERE individual_id=:id');
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			return $statement->execute();
			
		} catch (Exception $e) {
			trigger_error ( __METHOD__ . ' : ' . $e->getMessage () );
		}
	}
	/**
	 * Enregistre les données de l'individu en base de données.
	 */
	public function toDB() {
		global $system;
		
		$new = empty ( $this->id );
		
		$settings = array ();
		
		if (isset ( $this->salutation )) {
			$settings [] = 'individual_salutation=:salutation';
		}
		if (isset ( $this->firstName )) {
			$settings [] = 'individual_firstName=:firstName';
		}
		if (isset ( $this->lastName )) {
			$settings [] = 'individual_lastName=:lastName';
		}
		if (isset ( $this->birth_date )) {
			$settings [] = 'individual_birth_date=:birth_date';
		}
		if (isset ( $this->description )) {
			$settings [] = 'individual_description=:description';
		}
		if (isset ( $this->mobile )) {
			$settings [] = 'individual_mobile=:mobile';
		}
		if (isset ( $this->phone )) {
			$settings [] = 'individual_phone=:phone';
		}
		if (isset ( $this->email )) {
			$settings [] = 'individual_email=:email';
		}
		if (isset ( $this->web )) {
			$settings [] = 'individual_web=:web';
		}
		if (isset ( $this->street )) {
			$settings [] = 'individual_street=:street';
		}
		if (isset ( $this->city )) {
			$settings [] = 'individual_city=:city';
		}
		if (isset ( $this->postalCode )) {
			$settings [] = 'individual_postalCode=:postalCode';
		}
		if (isset ( $this->state )) {
			$settings [] = 'individual_state=:state';
		}
		if (isset ( $this->country )) {
			$settings [] = 'individual_country=:country';
		}

		if ($new) {
			$settings [] = 'individual_creation_date=NOW()';
			if (isset ( $_SESSION ['user_id'] )) {
				$settings [] = 'individual_creation_user_id=:user_id';
			}
		}

		$sql = $new ? 'INSERT INTO' : 'UPDATE';
		$sql .= ' individual SET ';
		$sql .= implode ( ', ', $settings );
		if (! $new)
			$sql .= ' WHERE individual_id=:id';
			
	
		$statement = $system->getPdo()->prepare($sql);
		
		if (isset ( $this->salutation )) {
			$statement->bindValue(':salutation', $this->salutation, PDO::PARAM_STR);
		}
		if (isset ( $this->firstName )) {
			$statement->bindValue(':firstName', $this->firstName, PDO::PARAM_STR);
		}
		if (isset ( $this->lastName )) {
			$statement->bindValue(':lastName', $this->lastName, PDO::PARAM_STR);
		}
		if (isset ( $this->birth_date )) {
			$statement->bindValue(':birth_date', $this->birth_date, PDO::PARAM_STR);
		}
		if (isset ( $this->description )) {
			$statement->bindValue(':description', $this->description, PDO::PARAM_STR);
		}
		if (isset ( $this->mobile )) {
			$statement->bindValue(':mobile', $this->mobile, PDO::PARAM_STR);
		}
		if (isset ( $this->phone )) {
			$statement->bindValue(':phone', $this->phone, PDO::PARAM_STR);
		}
		if (isset ( $this->email )) {
			$statement->bindValue(':email', $this->email, PDO::PARAM_STR);
		}
		if (isset ( $this->web )) {
			$statement->bindValue(':web', $this->web, PDO::PARAM_STR);
		}
		if (isset ( $this->street )) {
			$statement->bindValue(':street', $this->street, PDO::PARAM_STR);
		}
		if (isset ( $this->city )) {
			$statement->bindValue(':city', $this->city, PDO::PARAM_STR);
		}
		if (isset ( $this->postalCode )) {
			$statement->bindValue(':postalCode', $this->postalCode, PDO::PARAM_INT);
		}
		if (isset ( $this->state )) {
			$statement->bindValue(':state', $this->state, PDO::PARAM_STR);
		}
		if (isset ( $this->country )) {
			$statement->bindValue(':country', $this->country, PDO::PARAM_STR);
		}
		
		if (! $new)	{
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		}
		
		if ($new) {
			if (isset ( $_SESSION ['user_id'] )) {
				$statement->bindValue(':user_id', $_SESSION ['user_id'], PDO::PARAM_INT);
			}
		}
		
		$result = $statement->execute();
		
		if ($result && ! isset($this->id)) {
            $this->id = $system->getPdo()->lastInsertId();
        }
		
		return $result;
	}
	public function delete() {
		global $system;
		if (! empty ( $this->id )) {
			
			// effacement des liens avec Comptes
			$statement = $system->getPdo()->prepare('DELETE FROM membership WHERE individual_id=:id');
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			
			if ( $statement->execute() ) {
				if ($this->hasPhoto())	$this->deletePhotoFile ();
					
				// effacement du Individual proprement dit
				$statement = $system->getPdo()->prepare('DELETE FROM individual WHERE individual_id=:id');
				$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
				return $statement->execute();
			}
		}
		return false;
	}
	private function getDataFromBase($fields = NULL) {
		global $system;
		if (is_null ( $this->id )) {
			throw new Exception ( 'l\'instance doit être identifiée.' );
		}
		if (is_null ( $fields )) {
			$fields = array (
					'*' 
			);
		}
		$statement = $system->getPdo()->prepare('SELECT ' . implode ( ',', $fields ) . ' FROM individual WHERE individual_id=:id');
		$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		$statement->execute();
		return $statement->fetch(PDO::FETCH_ASSOC);
	}
	public function feed($array = NULL) {
		if (is_array ( $array )) {
			// les données de l'initialisation sont transmises
			foreach ( $array as $key => $value ) {
				// NB : stricte correspondance entre les noms d'attribut de la classe
				$items = explode ( '_', $key );
				switch ($items [0]) {
					case 'individual' :
						// pour les champs préfixés 'individual_', on supprime le préfixe
						array_shift ( $items );
						$this->setAttribute ( implode ( '_', $items ), stripslashes ( $value ) );
						break;
					default :
					// $this->setAttribute($key, $value);
				}
			}
			// print_r($this);
			return true;
		} elseif (! empty ( $this->id )) {
			// on ne transmet pas les données de l'initialisation
			// mais on connaît l'identifiant de la personne
			$row = $this->getDataFromBase();
			return $this->feed ( $row );
		}
		return false;
	}
}
?>