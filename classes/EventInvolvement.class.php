<?php
/**
 * L'implication d'un individu dans un évènement.
 *
 * @author Florent
 * @package usocrate.vostok
 */
class EventInvolvement extends Involvement implements CollectibleElement {
	private $id;
	private $event;

	public function getId() {
		return $this->id;	
	}
	public function getName() {
		return 'Implication n°'.$this->id;	
	}
	public function feed($input = null)	{
		if (is_array($input)) {
			$this->individual = new Individual();
			$this->individual->feed($input, 'individual_');
			$this->event = new Event();
			$this->event->feed($input, 'event_');
			if (isset($input['id'])) {
				$this->id = $input['id'];
			}
			if (isset($input['role'])) {
				$this->setRole($input['role']);
			}
			if (isset($input['comment'])) {
				$this->setComment($input['comment']);
			}
		}
		return false;
	}
	public function delete() {
		if (isset($this->id)) {
			return mysql_query('DELETE FROM event_involvement WHERE id='.$this->id);
		} else {
			return false;
		}
	}
	public function save() {
		$settings = array();
		if (isset($this->event) && $this->event->hasId()) {
			$settings[] = 'event_id='.$this->event->getId();
		}
		if (isset($this->individual) && $this->individual->hasId()) {
			$settings[] = 'individual_id='.$this->individual->getId();
		}
		if (isset($this->role)) {
			$settings[] = 'role="'.mysql_real_escape_string($this->role).'"';
		}
		if (isset($this->comment)) {
			$settings[] = 'comment="'.mysql_real_escape_string($this->comment).'"';
		}
		if (isset($this->id)) {
			// mise à jour
			$result = mysql_query('UPDATE INTO event_involvement SET '.implode(',',$settings).' WHERE id='.$this->id);
			return $result;
		} else {
			// création
			$result = mysql_query('INSERT INTO event_involvement SET '.implode(',',$settings));
			if ($result === false) {
				return false;
			} else {
				$this->id = mysql_insert_id();
				return true;
			}
		}
	}
}
?>