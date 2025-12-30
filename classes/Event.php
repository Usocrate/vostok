<?php
class Event {
	public $id;
	public $society;
	public $datetime;
	public $user;
	public $user_position;
	public $type;
	public $media;
	public $comment;
	public $warehouse;
	public $involvements;
	public function __construct($id = NULL) {
		$this->id = $id;
	}
	/**
	 * Indique si l'individu passé en paramètre est impliqué dans l'évènement.
	 *
	 * @param
	 *        	$individual
	 * @return EventInvolvement
	 */
	private function hasInvolvement(Individual $individual) {
		if (! isset ( $this->involvements )) {
			$this->getInvolvements ();
		}
	}
	/**
	 * Obtient les implications individuelles.
	 *
	 * @return EventInvolvementCollection
	 * @version 03/06/2017
	 */
	public function &getInvolvements() {
		global $system;
		if (! isset ( $this->involvements )) {
			if (! isset ( $this->id )) {
				return false;
			}
			$statement = $system->getPdo ()->prepare ( 'SELECT * FROM event_involvement WHERE event_id=:id' );
			$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
			$statement->execute ();
			$dataset = $statement->fetchAll ( PDO::FETCH_ASSOC );
			if ($dataset !== false) {
				$this->involvements = new EventInvolvementCollection ( $dataset );
			} else {
				$this->involvements = new EventInvolvementCollection ();
			}
		}
		return $this->involvements;
	}
	/**
	 * Fixe la valeur d'un attribut.
	 * mysql
	 */
	public function setAttribute($name, $value) {
		$value = trim ( $value );
		// $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
		return $this->{$name} = $value;
	}
	/**
	 *
	 * @return int
	 */
	public function getId() {
		return isset ( $this->id ) ? $this->id : NULL;
	}
	/**
	 * Obtient un label permettant d'indentifier l'évènement.
	 *
	 * @return string
	 */
	public function getLabel() {
		return $this->getType () . ' du ' . date ( "d/m/Y", ToolBox::mktimeFromMySqlDatetime ( $this->getDatetime () ) );
	}
	public function getName() {
		return $this->getLabel ();
	}
	/**
	 * Indique si l'évènement est identifié (comme étant enregistré en base de données).
	 *
	 * @return bool
	 */
	public function hasId() {
		return isset ( $this->id );
	}
	public function getAttribute($name) {
		return isset ( $this->$name ) ? $this->$name : NULL;
	}
	public function setDatetime($input) {
		if ($input) {
			$this->datetime = $input;
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Obtient la date de l'évènement si elle est connue.
	 *
	 * @return string
	 */
	public function getDatetime() {
		if (! isset ( $this->datetime ) && $this->hasId ()) {
			$dataset = $this->getDataFromBase ( array (
					'datetime'
			) );
			if (isset ( $dataset ['datetime'] )) {
				$this->datetime = $dataset ['datetime'];
			}
		}
		return isset ( $this->datetime ) ? $this->datetime : null;
	}
	/**
	 * Indique si la date de l'évènement est connue.
	 *
	 * @return bool
	 */
	public function hasDatetime() {
		return $this->getDatetime () !== null;
	}
	public function setUser($input) {
		if (is_a ( $input, 'User' )) {
			$this->user = $input;
		} else {
			return false;
		}
	}
	public function getUser() {
		return isset ( $this->user ) ? $this->user : NULL;
	}
	public function getUserAttribute($name) {
		return isset ( $this->user ) ? $this->user->getAttribute ( $name ) : NULL;
	}
	/**
	 * Obtient les valeurs posibles pour l'attribut user_position.
	 *
	 * @return array
	 */
	public static function getUserPositionsOptions() {
		return array (
				'active',
				'passive'
		);
	}
	/**
	 * Fixe la position de l'utilisateur.
	 *
	 * @param
	 *        	$input
	 * @return bool
	 */
	public function setUserPosition($input) {
		if (in_array ( $input, self::getUserPositionsOptions () )) {
			$this->user_position = $input;
			return true;
		} else {
			return false;
		}
	}
	public function getUserPosition() {
		if (! isset ( $this->user_position ) && $this->hasId ()) {
			$dataset = $this->getDataFromBase ( array (
					'user_position'
			) );
			if (isset ( $dataset ['user_position'] )) {
				$this->datetime = $dataset ['user_position'];
			}
		}
		return isset ( $this->user_position ) ? $this->user_position : null;
	}
	public function hasUserPosition() {
		return $this->getUserPosition () !== null;
	}
	/**
	 * Obtient les valeurs autorisées pour l'attribut media.
	 *
	 * @return array
	 */
	public static function getMediaOptions() {
		return array (
				'email',
				'rendez-vous',
				'appel téléphonique',
				'plateforme',
				'autre'
		);
	}
	public static function getMediaOptionsTags($selected = NULL) {
		$values = self::getMediaOptions ();
		sort ( $values );
		$html = '';
		foreach ( $values as $value ) {
			$html .= '<option ';
			$html .= 'value="' . ToolBox::toHtml ( $value ) . '"';
			if (strcasecmp ( $value, $selected ) == 0) {
				$html .= ' selected="selected"';
			}
			$html .= '>';
			$html .= ToolBox::toHtml ( $value );
			$html .= '</option>';
		}
		return $html;
	}
	public function setSociety($input) {
		if (is_a ( $input, 'Society' )) {
			$this->society = $input;
		}
	}
	public function getSociety() {
		return isset ( $this->society ) ? $this->society : NULL;
	}
	public function getSocietyAttribute($name) {
		return isset ( $this->society ) ? $this->society->getAttribute ( $name ) : NULL;
	}
	public static function getTypeOptions() {
		return array (
				'offre d\'emploi',
				'recommandation',
				'prise de contact',
				'relance',
				'contractualisation',
				'autre'
		);
	}
	/**
	 * Obtient les types d'évènements possibles sous forme de balises html <option>.
	 *
	 * @param
	 *        	$selected
	 * @return string
	 */
	public static function getTypeOptionsTags($selected = NULL) {
		$options = self::getTypeOptions ();
		$html = '';
		foreach ( $options as $o ) {
			$html .= '<option ';
			$html .= 'value="' . ToolBox::toHtml ( $o ) . '"';
			if (strcasecmp ( $o, $selected ) == 0) {
				$html .= ' selected="selected"';
			}
			$html .= '>';
			$html .= ToolBox::toHtml ( $o );
			$html .= '</option>';
		}
		return $html;
	}
	public function setType($input) {
		return $this->setAttribute ( 'type', $input );
	}
	public function getWarehouse($input) {
		return $this->warehouse;
	}
	public function setWarehouse($input) {
		$this->warehouse = $input;
	}
	public function getType() {
		if (! isset ( $this->type ) && $this->hasId ()) {
			$dataset = $this->getDataFromBase ( array (
					'type'
			) );
			if (isset ( $dataset ['type'] )) {
				$this->datetime = $dataset ['type'];
			}
		}
		return isset ( $this->type ) ? $this->type : null;
	}
	public function hasType() {
		return $this->getType () !== null;
	}
	public function setMedia($input) {
		return $this->media = $input;
	}
	public function getMedia() {
		if (! isset ( $this->media ) && $this->hasId ()) {
			$dataset = $this->getDataFromBase ( array (
					'media'
			) );
			if (isset ( $dataset ['media'] )) {
				$this->datetime = $dataset ['media'];
			}
		}
		return isset ( $this->media ) ? $this->media : null;
	}
	public function hasMedia() {
		return $this->getMedia () !== null;
	}
	/**
	 * Fixe les attributs de l'évènement
	 * à partir d'un tableau de valeur dont les clefs sont normalisées.
	 */
	public function feed($array = null, $prefix = null) {
		if (is_null ( $array )) {
			$dataset = $this->getDataFromBase ();
			return $this->feed ( $dataset );
		} else {
			foreach ( $array as $key => $value ) {
				if (is_null ( $value )) {
					continue;
				}
				if (isset ( $prefix )) {
					// on ne traite que les clés avec le préfixe spécifié
					if (strcmp ( iconv_substr ( $key, 0, iconv_strlen ( $prefix ) ), $prefix ) != 0) {
						continue;
					}
					// on retire le préfixe
					$key = iconv_substr ( $key, iconv_strlen ( $prefix ) );
				}
				switch ($key) {
					case 'id' :
						$this->id = $value;
						break;
					case 'society_id' :
						$this->society = new Society ( $value );
						$this->society->feed ( $array, 'society_' );
						break;
					case 'user_id' :
						$this->user = new User ( $value );
						break;
					case 'user_position' :
						$this->setUserPosition ( $value );
						break;
					case 'type' :
						$this->setType ( $value );
						break;
					case 'media' :
						$this->setMedia ( $value );
						break;
					case 'datetime' :
						$this->setDatetime ( $value );
						break;
					case 'comment' :
						$this->setComment ( $value );
						break;
					case 'warehouse' :
						$this->warehouse = $value;
						break;
				}
			}
			return true;
		}
	}
	/**
	 *
	 * @param
	 *        	$fields
	 * @return array
	 * @version 03/06/2017
	 */
	private function getDataFromBase($fields = NULL) {
		global $system;
		try {
			if (is_null ( $this->id )) {
				throw new Exception ( __METHOD__ . ' : l\'instance doit être identifiée.' );
			}
			if (is_null ( $fields )) {
				$fields = array (
						'*'
				);
			}
			$statement = $system->getPdo ()->prepare ( 'SELECT ' . implode ( ',', $fields ) . ' FROM event WHERE id=:id' );
			$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
			$statement->execute ();
			return $statement->fetch ( PDO::FETCH_ASSOC );
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
	/**
	 * Enregistre les données de l'instance en base de données.
	 *
	 * @version 08/04/2017
	 */
	public function toDB() {
		global $system;

		try {
			$new = ! $this->hasId ();

			$settings = array ();

			if ($this->society instanceof Society && $this->society->hasId ()) {
				$settings [] = 'society_id=:society_id';
			}
			if (isset ( $_SESSION[$systemIdInSession]['user_id'] )) {
				$settings [] = 'user_id=:user_id';
			}
			if (isset ( $this->datetime )) {
				$settings [] = 'datetime=:datetime';
				if ($new && empty ( $this->warehouse )) {
					$this->warehouse = ToolBox::mktimeFromMySqlDatetime ( $this->datetime ) > time () ? 'planning' : 'history';
				}
			}
			if (isset ( $this->warehouse )) {
				$settings [] = 'warehouse=:warehouse';
			}
			if (isset ( $this->user_position )) {
				$settings [] = 'user_position=:user_position';
			}
			if (isset ( $this->type )) {
				$settings [] = 'type=:type';
			}
			if (isset ( $this->media )) {
				$settings [] = 'media=:media';
			}
			if (isset ( $this->comment )) {
				$settings [] = 'comment=:comment';
			}

			$sql = $new ? 'INSERT INTO' : 'UPDATE';
			$sql .= ' event SET ';
			$sql .= implode ( ', ', $settings );
			if (! $new) {
				$sql .= ' WHERE id=:id';
			}
			$statement = $system->getPdo ()->prepare ( $sql );

			//
			// binding
			//
			if ($this->society instanceof Society && $this->society->hasId ()) {
				$statement->bindValue ( ':society_id', $this->society->getId (), PDO::PARAM_INT );
			}
			if (isset ( $_SESSION[$systemIdInSession]['user_id'] )) {
				$statement->bindValue ( ':user_id', $_SESSION[$systemIdInSession]['user_id'], PDO::PARAM_INT );
			}
			if (isset ( $this->datetime )) {
				$statement->bindValue ( ':datetime', $this->datetime, PDO::PARAM_STR );
			}
			if (isset ( $this->warehouse )) {
				$statement->bindValue ( ':warehouse', $this->warehouse, PDO::PARAM_STR );
			}
			if (isset ( $this->user_position )) {
				$statement->bindValue ( ':user_position', $this->user_position, PDO::PARAM_STR );
			}
			if (isset ( $this->type )) {
				$statement->bindValue ( ':type', $this->type, PDO::PARAM_STR );
			}
			if (isset ( $this->media )) {
				$statement->bindValue ( ':media', $this->media, PDO::PARAM_STR );
			}
			if (isset ( $this->comment )) {
				$statement->bindValue ( ':comment', $this->comment, PDO::PARAM_STR );
			}
			if (! $new) {
				$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
			}

			$result = $statement->execute ();
			if ($result && $new) {
				$this->id = $system->getPdo ()->lastInsertId ();
			}
			return $result;
		} catch ( Exception $e ) {
			$system->reportException ( $e, __METHOD__ );
		}
	}
	/**
	 * Supprime l'enregistrement de l'instance.
	 *
	 * @return boolean
	 * @version 08/04/2017
	 */
	public function delete() {
		global $system;
		$statement = $system->getPdo ()->prepare ( 'DELETE FROM event WHERE id=:id' );
		$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
		return $statement->execute ();
	}
	/**
	 * Fixe le commentaire associé à l'évènement.
	 */
	public function setComment($input) {
		if ($input) {
			$this->comment = $input;
		}
		return true;
	}
	/**
	 * Obtient le commentaire associé à l'évènement.
	 *
	 * @return string
	 */
	public function getComment() {
		if (! isset ( $this->comment ) && $this->hasId ()) {
			$dataset = $this->getDataFromBase ( array (
					'comment'
			) );
			$this->setComment ( $dataset ['comment'] );
		}
		return $this->comment;
	}
}
?>
