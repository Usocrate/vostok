<?php
class IndividualCollection extends Collection {
	public function __construct($input = NULL) {
		parent::__construct ( 'Individual' );
		if ($input instanceof PDOStatement) {
			$input->execute ();
			$data = $input->fetchAll ();
			foreach ( $data as $datum ) {
				$i = new Individual ();
				$i->feed ( $datum );
				$this->addElement ( $i );
			}
		}
		if (is_array ( $input )) {
			foreach ( $input as $datum ) {
				if ($datum instanceof Individual) {
					// la case du tableau considérée contient un objet du type attendu
					$this->addElement ( $datum );
				} elseif (is_array ( $datum )) {
					// la case du tableau considérée contient un tableau de données
					$element = new Individual ();
					$element->feed ( $datum );
					$this->addElement ( $element );
				}
			}
		}
	}

	/**
	 *
	 * @since 07/2018
	 */
	public function setIndividualMemberships() {
		global $system;
		try {
			$sql = 'SELECT m.membership_id AS id, m.individual_id, m.society_id, m.title, m.department, m.description, m.init_year, m.end_year, m.timestamp';
			$sql .= ', s.society_name, s.society_city';
			$sql .= ' FROM membership AS m INNER JOIN society AS s USING (society_id)';
			$sql .= ' WHERE individual_id IN(' . $this->getCommaSeparatedIds () . ')';
			$sql .= ' ORDER BY m.init_year DESC';

			$statement = $system->getPdo ()->prepare ( $sql );
			$statement->setFetchMode ( PDO::FETCH_ASSOC );
			$statement->execute ();

			$memberships = array ();
			foreach ( $statement->fetchAll () as $data ) {

				// society
				$s = new Society ();
				$s->setId ( $data ['society_id'] );
				$s->setName ( $data ['society_name'] );
				$s->setCity ( $data ['society_city'] );

				// membership
				$m = new Membership ();
				$m->setId ( $data ['id'] );
				$m->setSociety ( $s );
				$m->setTitle ( $data ['title'] );
				$m->setDepartment ( $data ['department'] );
				$m->setDescription ( $data ['description'] );
				$m->setInitYear ( $data ['init_year'] );
				$m->setEndYear ( $data ['end_year'] );
				// $m->setTimestamp($data['timestamp']);

				if (! isset ( $memberships [$data ['individual_id']] )) {
					$memberships [$data ['individual_id']] = array ();
				}
				$memberships [$data ['individual_id']] [$data ['id']] = $m;
			}

			// affectation des participations à chaque individu de la collection
			foreach ( $memberships as $individual_id => $array ) {
				$this->getElementById ( $individual_id )->setMemberships ( $array );
			}
		} catch ( Exception $e ) {
			$system->reportException ( $e, __METHOD__ );
		}
	}
}
?>