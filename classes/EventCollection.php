<?php
class EventCollection extends Collection {
	/**
	 * Constructeur
	 *
	 * @param PDOStatement|resource|array $dataset
	 */
	public function __construct($dataset = NULL) {
		parent::__construct ( 'Event' );
		/*
		 * les données sont stockées dans un tableau.
		 */
		if (is_array ( $dataset )) {
			foreach ( $dataset as $data ) {
				if ($data instanceof Event) {
					// la case du tableau considérée contient un objet du type attendu
					$this->addElement ( $data );
				} elseif (is_array ( $data )) {
					// la case du tableau considérée contient un tableau de données
					$element = new Event ();
					$element->feed ( $data );
					$this->addElement ( $element );
				}
			}
		}
	}
	/**
	 * Obtient la liste des derniers évènements enregistrés à l'historique.
	 *
	 * @param
	 *        	$size
	 * @return EventCollection
	 * @version 01/06/2017
	 */
	public static function getLastHistoryEvents($nb = 7) {
		$criteria = array ();
		$criteria ['warehouse'] = 'history';
		return self::getEvents ( $criteria, 'Last created first', $nb );
	}
	/**
	 * Obtient la liste des prochains évènements enregistrés au planning.
	 *
	 * @param
	 *        	$size
	 * @return EventCollection
	 * @version 01/06/2017
	 */
	public static function getNextPlanningEvents($nb = 7) {
		$criteria = array ();
		$criteria ['warehouse'] = 'planning';
		return self::getEvents ( $criteria, 'Last created first', $nb );
	}
	/**
	 *
	 * @since 01/06/2017
	 */
	public static function getEvents($criteria = NULL, $sort = 'Last created first', $nb = NULL, $offset = 0) {
		$output = array ();
		foreach ( self::getEventsData ( $criteria, $sort, $nb, $offset ) as $data ) {
			$e = new Event ();
			$e->feed ( $data );
			$output [] = $e;
		}
		return $output;
	}
	/**
	 *
	 * @version 01/06/2017
	 */
	private static function getEventsData($criteria = null, $sort = 'Last created first', $nb = NULL, $offset = 0) {
		global $system;
		try {
			$fields = array ();
			$fields [] = 't1.id';
			$fields [] = 't1.society_id';
			// $fields[] = 't1.user_id';
			$fields [] = 't1.user_position';
			$fields [] = 't1.media';
			$fields [] = 't1.type';
			$fields [] = 't1.datetime';
			$fields [] = 't1.comment';
			$fields [] = 't2.society_name';
			$sql = 'SELECT ' . implode ( ',', $fields ) . ' FROM event AS t1 LEFT JOIN society AS t2 USING (society_id)';

			if (isset ( $criteria ) && count ( $criteria ) > 0) {
				$sql_criteria = array ();
				if (isset ( $criteria ['warehouse'] )) {
					$sql_criteria [] = 't1.warehouse = :warehouse';
				}
				$sql .= ' WHERE ' . implode ( ' AND ', $sql_criteria );
			}
			switch ($sort) {
				case 'Last created first' :
					$sql .= ' ORDER BY t1.datetime DESC';
					break;
				case 'First created first' :
					$sql .= ' ORDER BY t1.datetime ASC';
					break;
			}

			if (isset ( $nb )) {
				$sql .= ' LIMIT :offset, :nb';
			}

			$statement = $system->getPdo ()->prepare ( $sql );

			if (isset ( $criteria ) && count ( $criteria ) > 0) {
				if (isset ( $criteria ['warehouse'] )) {
					$statement->bindValue ( ':warehouse', $criteria ['warehouse'], PDO::PARAM_STR );
				}
			}
			if (isset ( $nb )) {
				$statement->bindValue ( ':offset', $offset, PDO::PARAM_INT );
				$statement->bindValue ( ':nb', $nb, PDO::PARAM_INT );
			}

			$statement->execute ();
			return $statement->fetchAll ( PDO::FETCH_ASSOC );
		} catch ( Exception $e ) {
			error_log ( $e->getMessage () );
		}
	}
	public function toHtml() {
		if ($this->getSize () > 0) {
			$i = $this->getIterator ();
			$i->rewind ();
			$pieces = array ();
			$html = '<ul class="list-group">';
			while ( $i->current () ) {
				$html .= '<li class="list-group-item">';
				$html .= '<h3>';
				$html .= '<div><small>' . $i->current ()->getSociety ()->getHtmlLinkToSociety () . '</small></div>';
				$html .= '<a href="society_event_edit.php?event_id=' . $i->current ()->getId () . '">';
				$html .= date ( "d/m/Y", ToolBox::mktimeFromMySqlDatetime ( $i->current ()->getDatetime () ) );
				$html .= ' <small>(' . ToolBox::toHtml ( ucfirst ( $i->current ()->getType () ) ) . ')</small>';
				$html .= '</a>';
				$html .= '</h3>';
				$html .= '<p>' . ToolBox::toHtml ( $i->current ()->getComment () ) . '</p>';
				$html .= '</li>';
				$i->next ();
			}
			$html .= '</ul>';
			return $html;
		}
	}
}
?>