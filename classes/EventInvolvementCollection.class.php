<?php
Class EventInvolvementCollection extends Collection {
	/**
	 * Constructeur
	 *
	 * @param PDOStatement|resource|array $dataset
	 */
	public function __construct($dataset=NULL) {
		parent::__construct('EventInvolvement');
		/*
		 * les données sont constituées d'enregistrements issus de base de données.
		 */
		if (is_resource($dataset)) {
			while ($row = mysql_fetch_assoc($dataset)) {
				$element = new EventInvolvement();
				$element->feed($row);
				$this->addElement($element);
			}
		}
		/*
		 * les données sont stockées dans un tableau.
		 */
		elseif (is_array($dataset)) {
			foreach ($dataset as $data) {
				if ($data instanceof EventInvolvement) {
					// la case du tableau considérée contient un objet du type attendu
					$this->addElement($data);
				} elseif (is_array($data)) {
					// la case du tableau considérée contient un tableau de données
					$element = new eventInvolvement();
					$element->feed($data);
					$this->addElement($element);
				}
			}
		}
	}
	/**
	 * Obtient les personnes impliquées dans au moins un évènement de la collection.
	 *
	 * @return IndividualCollection
	 */
	public function getIndividuals() {
		$output = new IndividualCollection();
		foreach ($this->elements as $element) {
			$i = $element->getIndividual();
			if ($i instanceof Individual) {
				$output->addElement($i);
			}
		}
		return $output;
	}
}
?>