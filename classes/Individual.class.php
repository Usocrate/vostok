<?php
/**
 * @package usocrate.vostok
 * @author Florent Chanavat
 */
class Individual {
	protected $id;
	
	protected $twitter_id;
	protected $Linkedin_id;
	
	protected $firstName;
	protected $lastName;
	
	protected $street;
	protected $city;
	protected $postalcode;
	protected $state;
	protected $country;
	
	protected $lastPin_date;
	
	protected $memberships;
	
	public function __construct($id = NULL) {
		$this->id = $id;
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
			$system->reportException($e, __METHOD__);
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
			$system->reportException($e, __METHOD__);
		}
	}
	/**
	 * Fixe la valeur d'un attribut.
	 * @version 05/2018
	 */
	public function setAttribute($name, $value) {
		$value = trim ( $value );
		$value = html_entity_decode ( $value, ENT_QUOTES, 'UTF-8' );

		switch($name) {
			case 'birth_date' :
				if ( strcmp($value, '0000-00-00') == 0 ) {
					return false;	
				}
			case 'individual_salutation' :
				$salutations = array('mr', 'mme', 'mlle');
				if (!in_array($value, $salutations)) {
					return false;
				}
			default :
				// après gestion des cas particuliers, règle générale
				return $this->{$name} = $value;			
		}
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
	 * @since 08/2018
	 */
	public function getTwitterId() {
		return isset($this->twitter_id) ? $this->twitter_id : null;
	}
	/**
	 * @since 08/2018
	 */
	public function hasTwitterId() {
		return !empty($this->twitter_id);
	}
	/**
	 * @since 08/2018
	 */
	public function getLinkedinId() {
		return isset($this->linkedin_id) ? $this->linkedin_id : null;
	}
	/**
	 * @since 08/2018
	 */
	public function hasLinkedinId() {
		return !empty($this->linkedin_id);
	}	
	/**
	 * Indique si l'identifiant de l'individu est connu.
	 *
	 * @since 12/2010
	 * @return bool
	 */
	public function hasId() {
		return isset ( $this->id );
	}
	/**
	 * Obtient la date de naissance.
	 *
	 * @since 01/2006
	 * @version 05/03/2017
	 */
	public function getBirthDate() {
		return isset ( $this->birth_date ) ? $this->birth_date : NULL;
	}
	/**
	 * Obtient la date de naissance au timestamp unix.
	 *
	 * @since 21/01/2007
	 * @version05/03/2017
	 */
	public function getBirthDateTimestamp() {
		if ( isset ( $this->birth_date ) ) {
			list ( $year, $month, $day ) = explode ( '-', $this->birth_date );
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
	/**
	 * @version 05/03/2017
	 */
	public function identifyFromName() {
		global $system;
		if (empty ( $this->lastName ) || empty ( $this->firstName )) return false;
		$statement = $system->getPdo()->prepare('SELECT individual_id FROM individual WHERE individual_lastName=:lastName AND individual_firstName=:firstName');
		$statement->bindValue(':lastName', $this->getLastName(), PDO::PARAM_STR);
		$statement->bindValue(':firstName', $this->getFirstName(), PDO::PARAM_STR);
		$statement->execute();
		return $this->id = $statement->fetchColumn();
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
	 * @since 07/2018
	 */
	private static function getKnownLastNames($substring = NULL) {
	    global $system;
		$sql = 'SELECT individual_lastName FROM individual WHERE individual_lastName IS NOT NULL';
		if (isset ( $substring )) {
			$sql .= ' AND individual_lastName LIKE :pattern';
		}
		$statement = $system->getPdo()->prepare($sql);
		if (isset ( $substring )) {
		    $statement->bindValue(':pattern', '%'.$substring.'%', PDO::PARAM_STR);
		}
		$statement->execute();
		return $statement->fetchAll(PDO::FETCH_COLUMN);
	}
	/**
	 * @since 07/2018
	 */
	public static function knownLastNamesToJson($substring = NULL) {
		$output = '{"lastnames":' . json_encode ( self::getKnownLastNames ( $substring ) ) . '}';
		return $output;
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
		return '<a href="individual.php?individual_id='.$this->getId().'/">'.ToolBox::toHtml($this->getWholeName()).'</a>';
	}
	/**
	 * @since 08/2018
	 */
	public function getHtmlLinkToTwitter() {
		return '<a href="https://twitter.com/'.$this->getTwitterId().'" target="_blank"><i class="fab fa-twitter"></i> '.$this->getTwitterId().'</a>';		
	}
	/**
	 * @since 08/2018
	 */
	public function getHtmlLinkToLinkedin() {
		return '<a href="https://Linkedin.com/in/'.$this->getLinkedinId().'/" target="_blank"><i class="fab fa-linkedin"></i> '.$this->getLinkedinId().'</a>';		
	}
	/**
	 * @since 08/2018
	 */
	public function embedTwitterTimeline() {
		return '<a class="twitter-timeline" href="https://twitter.com/'.$this->getTwitterId().'?ref_src=twsrc%5Etfw">Tweets by '.$this->getTwitterId().'</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
	}
	/**
	 * Obtient le commentaire.
	 * @version 08/2018 
	 */
	public function getDescription() {
		return isset($this->description) ? $this->description : null;
	}
	/**
	 * @since 08/2018
	 */
	public function hasDescription() {
		return !empty($this->description);
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
	 * @version 06/03/2017
	 * @return string
	 */
	public function getPhotoUrl() {
		global $system;
		try {
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
				if ( !isset ($this->lastName) && !isset ($this->firstName) ) {
					throw New Exception ('Il nous manque le nom de la personne pour trouver sa photo');
				}
				$file_basename = ToolBox::formatForFileName ( $this->lastName . '_' . $this->firstName );
				foreach ( $file_extensions as $e ) {
					$file_name = $file_basename . '.' . $e;
					if (is_file ( $system->getTrombiDirPath () . DIRECTORY_SEPARATOR . $file_name )) {
						return $this->photo_url = $system->getTrombiUrl () . $file_name;
					}
				}
			}
		} catch (Exception $e) {
			$system->reportException($e, __METHOD__);	
		}
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
	 * @since 08/2014
	 */
	public function filePhoto(array $uploadedFile) {
		global $system;
		try {
			if ($uploadedFile['size'] > 0) {
				$a = explode('.', $uploadedFile ['name']);
				$ext = end($a);
				$targetFilePath = $system->getTrombiDirPath() . DIRECTORY_SEPARATOR . $this->getId () . '.' . $ext;
				if (is_file ( $targetFilePath )) {
					unlink ( $targetFilePath );
				}
				return copy ( $uploadedFile ['tmp_name'], $targetFilePath );
			}
		} catch ( Exception $e ) {
			$system->reportException($e, __METHOD__);
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
			return '<img src="' . $this->getPhotoUrl () . '" />';
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
		if (isset($street)) {
			$this->street = $street['number'].' '.$street['route'];
		}
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
			$system->reportException($e, __METHOD__);
		}
	}
	/**
	 * Obtient les participations associées à l'individu.
	 *
	 * @return array
	 * @since 01/2006
	 * @since 07/2017
	 */
	public function getMemberships($society = NULL) {
		global $system;
		try {
			if (!isset($this->memberships)) {			
				if (empty ( $this->id ))
					throw new Exception('Récupérer les participations d\'un individu exige que celui-ci soit identifié.');
				
				$criteria = array ();
				
				$criteria[] = 'm.individual_id=:id';
				
				if (isset ( $society )) {
					$criteria[] = 'm.society_id=:society_id';
				}
				
				$sql = 'SELECT m.membership_id AS id, m.title, m.department, m.description, m.init_year, m.end_year';
				$sql.= ', s.society_id, s.society_name, s.society_city';				
				$sql .= ' ,DATE_FORMAT(s.society_creation_date, "%d/%m/%Y") as society_creation_date';
				$sql .= ' FROM membership AS m LEFT OUTER JOIN society AS s USING (society_id)';
				$sql .= ' WHERE '.implode(' AND ', $criteria);
				$sql .= ' ORDER BY m.init_year DESC, m.end_year DESC';
				
				$statement = $system->getPdo()->prepare($sql);
				$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
				if (isset ( $society )) {
					$statement->bindValue(':society_id', $society->getId, PDO::PARAM_INT);
				}
				$statement->setFetchMode(PDO::FETCH_ASSOC);
				$statement->execute();
	
				$this->memberships = array();
				foreach ( $statement->fetchAll() as $data ) {
					// society
					$s = new Society();
					$s->setId($data['society_id']);
					$s->setName($data['society_name']);
					$s->setCity($data['society_city']);
	
					// membership
					$m = new Membership();
					$m->setId($data['id']);
					$m->setSociety($s);
					$m->setTitle($data['title']);
					$m->setDepartment($data['department']);
					$m->setDescription($data['description']);
					$m->setInitYear($data['init_year']);
					$m->setEndYear($data['end_year']);
					
					$this->memberships[$m->getId()] = $m;
				}
			}
			return $this->memberships;			
		} catch (Exception $e) {
			$system->reportException($e, __METHOD__);
		}
	}
	/**
	 * @since 07/2018
	 */
	public function setMemberships(array $input) {
		$this->memberships = $input;
	}
	/**
	 * @version 01/2017
	 */
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
			$system->reportException($e, __METHOD__);
		}
	}
	/**
	 * Efface toutes les participations de cet individu en base de données.
	 *
	 * @return boolean
	 * @version 01/2017
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
			$system->reportException($e, __METHOD__);			
		}		
	}
	/**
	 * @since 07/2018
	 */
	public function getRelatedIndividuals() {
		global $system;

		if (empty ( $this->id )) {
			return NULL;
		}

		$output = array();

		$sql = 'SELECT i.*, r.relationship_id, r.item1_role AS relatedindividual_role, r.description, r.init_year, r.end_year';
		$sql .= ' FROM relationship AS r INNER JOIN individual AS i ON(r.item1_id=i.individual_id)';
		$sql .= ' WHERE item0_class="individual" AND item0_id=:item0_id AND item1_class="individual"';
		$sql .= ' UNION';
		$sql .= ' SELECT i.*, r.relationship_id, r.item0_role AS relatedindividual_role, r.description, r.init_year, r.end_year';
		$sql .= ' FROM relationship AS r INNER JOIN individual AS i ON(r.item0_id=i.individual_id)';
		$sql .= ' WHERE item1_class="individual" AND item1_id=:item1_id AND item0_class="individual"';
		$sql .= ' ORDER BY init_year DESC, end_year DESC, individual_lastName ASC';

		$statement = $system->getPdo()->prepare($sql);
		$statement->bindValue(':item0_id', $this->id, PDO::PARAM_INT);
		$statement->bindValue(':item1_id', $this->id, PDO::PARAM_INT);
		$statement->execute();
		$data = $statement->fetchAll(PDO::FETCH_ASSOC);

		foreach ( $data as $row ) {
			$i = new Individual();
			$i->feed($row);
			$output[] = array($i, $row['relationship_id'], $row['relatedindividual_role'], $row['description'], new Period($row['init_year'], $row['end_year']));
		}
		return $output;
	}
	
	/**
	 * Indique si l'individu participe à une société donnée.
	 *
	 * @return boolean
	 * @param int $society_id
	 * @version 01/2017
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
			$system->reportException($e, __METHOD__);
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
			$system->reportException($e, __METHOD__);
		}
	}
	/**
	 * Demande de focus sur l'individu
	 * @since 07/2018
	 */
	public function Pin() {
		global $system;
		try {
			if (empty ( $this->id )) {
				throw new Exception('Tentative d\'épinglage sur un individu non identifié.');
			}
			$statement = $system->getPdo()->prepare('UPDATE individual SET individual_lastPin_date=:datetime WHERE individual_id=:id');
			//var_dump($statement);
			$statement->bindValue(':datetime', date('Y-m-d H:i:s', time()), PDO::PARAM_STR);
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			return $statement->execute();			
		}
		catch (Exception $e) {
			$system->reportException($e, __METHOD__);
		}
	}
	/**
	 * Enregistre les données de l'individu en base de données.
	 * @version 08/2018
	 */
	public function toDB() {
		global $system;
		
		$new = empty ( $this->id );
		
		$settings = array ();
		
		if (isset ( $this->twitter_id )) {
			$settings [] = 'individual_twitter_id=:twitter_id';
		}
		if (isset ( $this->linkedin_id )) {
			$settings [] = 'individual_linkedin_id=:linkedin_id';
		}
		
		if (isset ( $this->firstName )) {
			$settings [] = 'individual_firstName=:firstName';
		}
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
		
		if (isset ( $this->twitter_id )) {
			$statement->bindValue(':twitter_id', $this->twitter_id, PDO::PARAM_STR);
		}
		if (isset ( $this->linkedin_id )) {
			$statement->bindValue(':linkedin_id', $this->linkedin_id, PDO::PARAM_STR);
		}
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
			empty ($this->birth_date) ? $statement->bindValue(':birth_date', NULL, PDO::PARAM_NULL) : $statement->bindValue(':birth_date', $this->birth_date, PDO::PARAM_STR);
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

		if ($new) {
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
				// NB : stricte correspondance entre les noms d'attribut de la classe une fois préfixe retiré
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