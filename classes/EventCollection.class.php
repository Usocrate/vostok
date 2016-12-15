<?php
Class EventCollection extends Collection {
	/**
	 * Constructeur
	 *
	 * @param PDOStatement|resource|array $dataset
	 */
	public function __construct($dataset = NULL) {
		parent::__construct('Event');
		/*
		 * les données sont constituées d'enregistrements issus de base de données.
		 */
		if (is_resource($dataset)) {
			while ($row = mysql_fetch_assoc($dataset)) {
				$element = new Event();
				$element->feed($row);
				$this->addElement($element);
			}
		}
		/*
		 * les données sont stockées dans un tableau.
		 */
		elseif (is_array($dataset)) {
			foreach ($dataset as $data) {
				if ($data instanceof Event) {
					// la case du tableau considérée contient un objet du type attendu
					$this->addElement($data);
				} elseif (is_array($data)) {
					// la case du tableau considérée contient un tableau de données
					$element = new Event();
					$element->feed($data);
					$this->addElement($element);
				}
			}
		}
	}
	/**
	 * Obtient la liste des derniers évènements enregistrés à l'historique.
	 * @param $size
	 * @return EventCollection
	 */
	public static function getLastHistoryEvents($size=7) {
		$criterias = array();
		$criterias[] = 't1.warehouse = "history"';
		return self::getEvents($criterias, 't1.datetime', 'DESC', $size);
	}
	/**
	 * Obtient la liste des prochains évènements enregistrés au planning.
	 * @param $size
	 * @return EventCollection
	 */
	public static function getNextPlanningEvents($size=7) {
		$criterias = array();
		$criterias[] = 't1.warehouse = "planning"';
		return self::getEvents($criterias, 't1.datetime', 'ASC', $size);
	}
	/**
	 * @version 01/08/2014
	 */	
	private static function getEvents($criterias=null, $sort_key=null, $sort_order='ASC', $size=null, $offset=0) {
		try {
			$fields = array();
			$fields[] = 't1.id';
			$fields[] = 't1.society_id';
			//$fields[] = 't1.user_id';
			$fields[] = 't1.user_position';
			$fields[] = 't1.media';
			$fields[] = 't1.type';
			$fields[] = 't1.datetime';
			$fields[] = 't1.comment';
			$fields[] = 't2.society_name';
			$sql = 'SELECT '.implode(',', $fields);
			$sql.= ' FROM event AS t1';
			$sql.= ' LEFT JOIN society AS t2 USING (society_id)';
			if (isset($criterias)) {
				$sql.= ' WHERE '.implode(' AND ',$criterias);
			}
			if (isset($sort_key)) {
				$sql.= ' ORDER BY '.$sort_key.' '.$sort_order;
			}
			if (isset($size)) {
				$sql.= ' LIMIT '.$offset.','.$size;
			}
			$dataset = mysql_query($sql);
			if ($dataset !== false) {
				return new EventCollection($dataset);
			} else {
				throw new Exception(__METHOD__.' : échec de la requête '.$sql);
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}
	public function toHtml() {
		if ($this->getSize()>0) {
			$i = $this->getIterator();
			$i->rewind();
			$pieces = array();
			$html = '<ul class="list-group">';
			while ($i->current()) {
				$html.= '<li class="list-group-item">';
				$html.=  '<h3>';
				$html.= '<div><small>'.$i->current()->getSociety()->getHtmlLinkToSociety().'</small></div>';
				$html.=  '<a href="society_event_edit.php?event_id='.$i->current()->getId().'">';
				$html.=  date("d/m/Y", ToolBox::mktimeFromMySqlDatetime($i->current()->getDatetime()));
				$html.=  ' <small>('.ToolBox::toHtml(ucfirst($i->current()->getType())).')</small>';
				$html.=  '</a>';
				$html.=  '</h3>';
				$html.= '<p>'.ToolBox::toHtml($i->current()->getComment()).'</p>';
				$html.= '</li>';
				$i->next();
			}
			$html.= '</ul>';
			return $html;
		}
	}
}
?>