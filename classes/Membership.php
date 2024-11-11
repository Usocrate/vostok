<?php
class Membership {
	public int $id;
	public Society $society;
	public Individual $individual;
	public string $title;
	public string $department;
	public string $phone;
	public string $email;
	public string $url;
	public string $description;
	public int $weight;
	public $init_year;
	public $end_year;
	public function __construct($id = NULL) {
		if (isset ( $id ))
			$this->id = $id;
	}
	/**
	 * Fixe la valeur d'un attribut.
	 *
	 * @since 01/2006
	 */
	public function setAttribute(string $name, $value) {
		$value = trim ( $value );
		$value = html_entity_decode ( $value, ENT_QUOTES, 'UTF-8' );
		$this->{$name} = $value;
	}
	/**
	 *
	 * @since 05/2018
	 */
	public function setTitle(string $input = '') {
		$this->title = $input;
	}
	/**
	 *
	 * @since 05/2018
	 */
	public function setDepartment(string $input = '') {
		$this->department = $input;
	}
	/**
	 *
	 * @since 05/2018
	 */
	public function setDescription(string $input = '') {
		$this->description = $input;
	}
	/**
	 *
	 * @since 04/2022
	 * @param int $input
	 */
	public function setWeight(int $input) {
		$this->weight = $input;
	}
	/**
	 *
	 * @since 04/2022
	 */
	public function getWeight() {
		return isset ( $this->weight ) ? $this->weight : null;
	}
	/**
	 *
	 * @since 01/2017
	 */
	public function setInitYear($input) {
		if (is_numeric ( $input ) && strlen ( $input ) == 4) {
			$this->init_year = $input;
		} elseif (empty ( $input )) {
			$this->init_year = '';
		} else {
			return false;
		}
	}
	/**
	 *
	 * @since 03/2019
	 * @return boolean
	 */
	public function getInitYear() {
		return isset ( $this->init_year ) ? $this->init_year : null;
	}
	/**
	 *
	 * @since 03/2019
	 * @return boolean
	 */
	public function hasInitYear() {
		return ! empty ( $this->init_year );
	}
	/**
	 *
	 * @since 01/2017
	 */
	public function setEndYear($input) {
		if (is_numeric ( $input ) && strlen ( $input ) == 4) {
			$this->end_year = $input;
		} elseif (empty ( $input )) {
			$this->end_year = '';
		} else {
			return false;
		}
	}
	/**
	 *
	 * @since 03/2019
	 * @return boolean
	 */
	public function getEndYear() {
		return isset ( $this->end_year ) ? $this->end_year : null;
	}
	/**
	 *
	 * @since 03/2019
	 * @return boolean
	 */
	public function hasEndYear() {
		return ! empty ( $this->end_year );
	}
	public function getAttribute($name) {
		return isset ( $this->$name ) ? $this->$name : NULL;
	}
	/**
	 * Obtient l'identifiant de la participation.
	 *
	 * @return int
	 */
	public function getId() {
		return $this->getAttribute ( 'id' );
	}
	/**
	 * Fixe l'identifiant de la participation.
	 *
	 * @param int $input
	 * @since 11/2005
	 */
	public function setId(int $input) {
		return $this->setAttribute ( 'id', $input );
	}
	/**
	 *
	 * @since 12/2018
	 */
	public function hasId() {
		return isset ( $this->id );
	}
	/**
	 * Renvoie la fonction exercée dans le cadre de cette participation.
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->getAttribute ( 'title' );
	}
	/**
	 *
	 * @since 03/2019
	 * @return boolean
	 */
	public function hasTitle() {
		return ! empty ( $this->title );
	}
	/**
	 *
	 * @since 12/2021
	 * @return boolean
	 */
	public function hasPhone() {
		return ! empty ( $this->phone );
	}
	/**
	 *
	 * @since 12/2021
	 * @return boolean
	 */
	public function hasEmail() {
		return ! empty ( $this->email );
	}
	/**
	 *
	 * @since 10/2012
	 */
	private static function getKnownTitles(string $substring = '') {
		global $system;
		$sql = 'SELECT title AS value, COUNT(*) AS count FROM membership';
		$sql .= ' WHERE title IS NOT NULL';
		if (! empty ( $substring )) {
			$sql .= ' AND title LIKE :pattern';
		}
		$sql .= ' GROUP BY title ORDER BY COUNT(*) DESC';
		$statement = $system->getPdo ()->prepare ( $sql );
		if (! empty ( $substring )) {
			$statement->bindValue ( ':pattern', '%' . $substring . '%', PDO::PARAM_STR );
		}
		$statement->execute ();
		return $statement->fetchAll ( PDO::FETCH_ASSOC );
	}
	/**
	 *
	 * @since 10/2012
	 */
	public static function knownTitlesToJson(string $substring = '') {
		$output = '{"titles":[';
		$items = self::getKnownTitles ( $substring );
		for($i = 0; $i < count ( $items ); $i ++) {
			$output .= '{"value":' . ucfirst ( json_encode ( $items [$i] ['value'] ) ) . ',"count":' . $items [$i] ['count'] . '}';
			if ($i < count ( $items ) - 1) {
				$output .= ',';
			}
		}
		$output .= ']}';
		return $output;
	}
	/**
	 * Renvoie la description de cette participation.
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->getAttribute ( 'description' );
	}
	/**
	 * N'affiche que la première phrase de la description, le reste de la description est fournie mais sera masqué par défaut
	 *
	 * @since 08/2022
	 */
	public function getHtmlExpandableDescription() {
		if (! empty ( $this->description )) {
			preg_match ( '/^(.*[\.|?|!]{1})/', $this->description, $result );
			$html = '<div class="card-text membership-description-area">';
			if (count ( $result ) > 1 && count ( $result ) < strlen ( $this->description )) {
				$html .= '<span>' . ToolBox::toHtml ( $result [1] ) . '</span><span class="more">' . substr ( $this->description, strlen ( $result [1] ) ) . '</span>';
				$html .= '<div>...</div>';
			} else {
				$html .= ToolBox::toHtml ( $this->description );
			}
			$html .= '</div>';
			return $html;
		}
	}
	/**
	 *
	 * @since 03/2019
	 * @return boolean
	 */
	public function hasDescription() {
		return ! empty ( $this->description );
	}
	/**
	 *
	 * @since 01/2017
	 */
	public function getPeriod() {
		$p = new Period ( $this->init_year, $this->end_year );
		return ucfirst ( $p->toString () );
	}
	/**
	 * Renvoie l'email utilisé dans le cadre de cette participation.
	 *
	 * @return string
	 * @since 01/2006
	 */
	public function getEmail() {
		return $this->getAttribute ( 'email' );
	}
	public function setEmail(string $input) {
		return $this->setAttribute ( 'email', strtolower ( $input ) );
	}
	/**
	 * Renvoie le numéro de téléphone utilisé dans le cadre de cette participation.
	 *
	 * @return string
	 */
	public function getPhone() {
		return $this->getAttribute ( 'phone' );
	}
	/**
	 * Renvoie le service dans lequel se situe cette participation.
	 *
	 * @return string
	 */
	public function getDepartment() {
		return $this->getAttribute ( 'department' );
	}
	/**
	 *
	 * @since 03/2019
	 * @return boolean
	 */
	public function hasDepartment() {
		return ! empty ( $this->department );
	}
	/**
	 *
	 * @since 10/2012
	 */
	private static function getKnownDepartments(string $substring = '') {
		global $system;
		$sql = 'SELECT department AS value, COUNT(*) AS count FROM membership WHERE department IS NOT NULL';
		if (! empty ( $substring )) {
			$sql .= ' AND department LIKE :pattern';
		}
		$sql .= ' GROUP BY department ORDER BY COUNT(*) DESC';
		$statement = $system->getPdo ()->prepare ( $sql );
		if (! empty ( $substring )) {
			$statement->bindValue ( ':pattern', '%' . $substring . '%', PDO::PARAM_STR );
		}
		$statement->execute ();
		return $statement->fetchAll ( PDO::FETCH_ASSOC );
	}
	/**
	 *
	 * @since 10/2012
	 */
	public static function knownDepartmentsToJson(string $substring = '') {
		$output = '{"departments":[';
		$items = self::getKnownDepartments ( $substring );
		for($i = 0; $i < count ( $items ); $i ++) {
			$output .= '{"value":' . ucfirst ( json_encode ( $items [$i] ['value'] ) ) . ',"count":' . $items [$i] ['count'] . '}';
			if ($i < count ( $items ) - 1) {
				$output .= ',';
			}
		}
		$output .= ']}';
		return $output;
	}
	/**
	 *
	 * @since 11/2024
	 */
	public function getIndividualIdentityFromDB() {
		global $system;

		try {
			if (isset ( $this->id )) {
				$sql = 'SELECT m.individual_id AS id, i.individual_firstName AS firstName, i.individual_lastName AS lastName';
				$sql .= ' FROM membership AS m INNER JOIN individual AS i ON i.individual_id = m.individual_id';
				$sql .= ' WHERE m.membership_id = :id';

				$statement = $system->getPdo ()->prepare ( $sql );
				$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
				$statement->execute ();
				$data = $statement->fetch ( PDO::FETCH_ASSOC );

				return new Individual ( $data );
			}
			return null;
		} catch ( Exception $e ) {
			$system->reportException ( $e, __METHOD__ );
		}
	}
	/**
	 *
	 * @since 11/2024
	 */
	public function getSocietyIdentityFromDB() {
		global $system;

		try {
			if (isset ( $this->id )) {
				$sql = 'SELECT m.society_id AS id, s.society_name';
				$sql .= ' FROM membership AS m INNER JOIN society AS s ON s.society_id = m.society_id';
				$sql .= ' WHERE m.membership_id = :id';

				$statement = $system->getPdo ()->prepare ( $sql );
				$statement->bindValue ( ':id', $this->id, PDO::PARAM_INT );
				$statement->execute ();
				$data = $statement->fetch ( PDO::FETCH_ASSOC );

				return new Society ( $data );
			}
			return null;
		} catch ( Exception $e ) {
			$system->reportException ( $e, __METHOD__ );
		}
	}
	/**
	 * Renvoie la personne impliquée.
	 *
	 * @version 11/2024
	 * @return Individual|NULL
	 */
	public function getIndividual() {
		if (! isset ( $this->individual )) {
			$i = $this->getIndividualIdentityFromDB ();
			$this->setIndividual ( $i );
		}
		return isset ( $this->individual ) ? $this->individual : NULL;
	}
	/**
	 * Renvoie la société concernée.
	 *
	 * @version 11/2024
	 * @return Society|NULL
	 */
	public function getSociety() {
		if (! isset ( $this->society )) {
			$s = $this->getSocietyIdentityFromDB ();
			$this->setSociety ( $s );
		}
		return isset ( $this->society ) ? $this->society : NULL;
	}
	/**
	 * Fixe la personne impliquée.
	 *
	 * @param Individual $input
	 */
	public function setIndividual(Individual $input) {
		if (is_a ( $input, 'Individual' )) {
			$this->individual = $input;
			return true;
		}
		return false;
	}
	/**
	 * Fixe la société concernée.
	 *
	 * @param Society $input
	 * @version 11/2024
	 */
	public function setSociety(Society $input) {
		if (is_a ( $input, 'Society' )) {
			$this->society = $input;
			return true;
		}
		return false;
	}
	/**
	 * Renvoie l'id de la personne impliquée.
	 */
	public function getIndividualId() {
		if (! isset ( $this->individual )) {
			$this->individual = $this->getIndividualIdentityFromDB ();
		}
		return isset ( $this->individual ) ? $this->individual->getId () : NULL;
	}
	/**
	 *
	 * @since 12/2018
	 * @version 11/2024
	 */
	public function isSocietyIdentified() {
		if (! isset ( $this->society )) {
			$this->society = $this->getSocietyIdentityFromDB ();
		}
		return isset ( $this->society ) && ! empty ( $this->society->getId () );
	}
	/**
	 *
	 * @since 12/2018
	 * @version 11/2024
	 */
	public function isIndividualIdentified() {
		if (! isset ( $this->individual )) {
			$this->individual = $this->getIndividualIdentityFromDB ();
		}

		return isset ( $this->individual ) && ! empty ( $this->individual->getId () );
	}
	/**
	 * Obtient l'Url décrivant la participation de la personne.
	 *
	 * @since 01/2006
	 */
	public function getUrl() {
		return isset ( $this->url ) ? $this->url : NULL;
	}
	/**
	 *
	 * @since 03/2019
	 * @return boolean
	 */
	public function hasUrl() {
		return ! empty ( $this->url );
	}
	/**
	 *
	 * @since 08/2018
	 */
	public function setUrl(string $input) {
		$this->url = $input;
	}
	/**
	 * Obtient un lien HTML vers un contenu web décrivant la participation.
	 *
	 * @since 12/2006
	 */
	public function getHtmlLinkToWeb() {
		return $this->getUrl () ? '<a href="' . $this->getUrl () . '" title="' . $this->getUrl () . '">[web]</a>' : NULL;
	}
	/**
	 *
	 * @since 12/2018
	 * @version 11/2024*
	 */
	public function getHtmlLinkToIndividual(string $mode = 'normal') {
		if ($this->isIndividualIdentified ()) {
			return $this->individual->getHtmlLinkToIndividual ( $mode );
		} else {
			if ($this->hasId ()) {
				$i = $this->getIndividualIdentityFromDB ();
				if ($this->setIndividual ( $i )) {
					return $this->individual->getHtmlLinkToIndividual ( $mode );
				}
			}
			return null;
		}
	}
	/**
	 *
	 * @since 12/2018
	 * @version 11/2024
	 */
	public function getHtmlLinkToSociety() {
		if ($this->isSocietyIdentified ()) {
			return $this->society->getHtmlLinkToSociety ();
		} else {
			if ($this->hasId ()) {
				$s = $this->getSocietyIdentityFromDB ();
				if ($this->setSociety ( $s )) {
					return $this->society->getHtmlLinkToSociety ();
				}
			}
			return null;
		}
	}
	/**
	 * Enregistre en base de données les attributs de la participation.
	 */
	public function toDB() {
		global $system;
		$new = empty ( $this->id );

		$settings = array ();
		if (isset ( $this->individual ) && $this->individual->getId ())
			$settings [] = 'individual_id=:individual_id';
		if (isset ( $this->society ) && $this->society->getId ())
			$settings [] = 'society_id=:society_id';
		if (isset ( $this->title ))
			$settings [] = 'title=:title';
		if (isset ( $this->department ))
			$settings [] = 'department=:department';
		if (isset ( $this->phone ))
			$settings [] = 'phone=:phone';
		if (isset ( $this->email ))
			$settings [] = 'email=:email';
		if (isset ( $this->url ))
			$settings [] = 'url=:url';
		if (isset ( $this->description ))
			$settings [] = 'description=:description';
		if (isset ( $this->weight ))
			$settings [] = 'weight=:weight';
		if (isset ( $this->init_year ))
			$settings [] = 'init_year=:init_year';
		if (isset ( $this->end_year ))
			$settings [] = 'end_year=:end_year';

		$sql = $new ? 'INSERT INTO' : 'UPDATE';
		$sql .= ' membership SET ';
		$sql .= implode ( ', ', $settings );
		if (! $new)
			$sql .= ' WHERE membership_id=:membership_id';

		$statement = $system->getPdo ()->prepare ( $sql );
		if (isset ( $this->individual ) && $this->individual->getId ())
			$statement->bindValue ( ':individual_id', $this->individual->getId (), PDO::PARAM_INT );
		if (isset ( $this->society ) && $this->society->getId ())
			$statement->bindValue ( ':society_id', $this->society->getId (), PDO::PARAM_INT );
		if (isset ( $this->title ))
			$statement->bindValue ( ':title', $this->title, PDO::PARAM_STR );
		if (isset ( $this->department ))
			$statement->bindValue ( ':department', $this->department, PDO::PARAM_STR );
		if (isset ( $this->phone ))
			$statement->bindValue ( ':phone', $this->phone, PDO::PARAM_STR );
		if (isset ( $this->email ))
			$statement->bindValue ( ':email', $this->email, PDO::PARAM_STR );
		if (isset ( $this->url ))
			$statement->bindValue ( ':url', $this->url, PDO::PARAM_STR );
		if (isset ( $this->description ))
			$statement->bindValue ( ':description', $this->description, PDO::PARAM_STR );
		if (isset ( $this->weight )) {
			$statement->bindValue ( ':weight', $this->weight, PDO::PARAM_INT );
		}
		if (isset ( $this->init_year )) {
			empty ( $this->init_year ) ? $statement->bindValue ( ':init_year', NULL, PDO::PARAM_NULL ) : $statement->bindValue ( ':init_year', $this->init_year, PDO::PARAM_INT );
		}
		if (isset ( $this->end_year )) {
			empty ( $this->end_year ) ? $statement->bindValue ( ':end_year', NULL, PDO::PARAM_NULL ) : $statement->bindValue ( ':end_year', $this->end_year, PDO::PARAM_INT );
		}
		if (! $new) {
			$statement->bindValue ( ':membership_id', $this->id, PDO::PARAM_INT );
		}
		$result = $statement->execute ();
		if ($new)
			$this->id = $system->getPdo ()->lastInsertId ();
		return $result;
	}
	/**
	 * Supprime la participation en base de données.
	 *
	 * @return boolean
	 * @version 01/2017
	 */
	public function delete() {
		global $system;
		if (empty ( $this->id ))
			return false;
		$statement = $system->getPdo ()->prepare ( 'DELETE FROM membership WHERE membership_id=:id' );
		$statement->bindValue ( ':id', $this->id, PDO::PARAM_STR );
		return $statement->execute ();
	}
	public function feed($array = NULL, $prefix = NULL) {
		if (is_array ( $array )) {
			// les données de l'initialisation sont transmises
			foreach ( $array as $key => $value ) {
				if (is_null ( $value ))
					continue;
				if (isset ( $prefix )) {
					// on ne traite que les clés avec le préfixe spécifié
					if (strcmp ( iconv_substr ( $key, 0, iconv_strlen ( $prefix ) ), $prefix ) != 0)
						continue;
					// on retire le préfixe
					$key = iconv_substr ( $key, iconv_strlen ( $prefix ) );
				}
				switch ($key) {
					case 'membership_id' :
						$this->setId ( $value );
						break;
					case 'individual_id' :
						$this->setIndividual ( new Individual ( $value ) );
						break;
					case 'society_id' :
						$this->setSociety ( new Society ( $value ) );
						break;
					case 'title' :
						$this->setAttribute ( 'title', $value );
						break;
					case 'department' :
						$this->setAttribute ( 'department', $value );
						break;
					case 'phone' :
						$this->setAttribute ( 'phone', $value );
						break;
					case 'email' :
						$this->setEmail ( $value );
						break;
					case 'url' :
						$this->setAttribute ( 'url', $value );
						break;
					case 'description' :
						$this->setAttribute ( 'description', $value );
						break;
					case 'weight' :
						$this->setWeight ( $value );
						break;
					case 'init_year' :
						$this->setInitYear ( $value );
						break;
					case 'end_year' :
						$this->setEndYear ( $value );
						break;
				}
			}
			return true;
		} elseif (isset ( $this->id )) {
			// on ne transmet pas les données de l'initialisation mais on connaît l'identifiant de la participation
			global $system;
			$statement = $system->getPdo ()->prepare ( 'SELECT * FROM membership WHERE membership_id=:id' );
			$statement->bindValue ( ':id', $this->id, PDO::PARAM_STR );
			$statement->execute ();
			$data = $statement->fetch ( PDO::FETCH_ASSOC );
			return $data ? $this->feed ( $data ) : false;
		}
		return false;
	}
}
?>