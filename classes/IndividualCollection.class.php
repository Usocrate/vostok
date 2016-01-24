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
		if (is_resource ( $input )) {
			while ( $row = mysql_fetch_assoc ( $input ) ) {
				$element = new Individual ();
				$element->feed ( $row );
				$this->addElement ( $element );
			}
		} elseif (is_array ( $input )) {
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
}
?>