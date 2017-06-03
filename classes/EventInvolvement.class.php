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
	/**
	 * @version 03/06/2017
	 */
	public function delete() {
		global $system;
		if (isset($this->id)) {
			$statement = $system->getPdo()->prepare('DELETE FROM event_involvement WHERE id=:id');
			$statement->bindValue(':id',$this->id,PDO::PARAM_INT);
			return $statement->execute();
		} else {
			return false;
		}
	}
	/**
	 * @version 03/06/2017
	 */
	public function save() {
		global $system;
		
		$new = empty ( $this->id );
		
		$settings = array();
		if (isset($this->event) && $this->event->hasId()) {
			$settings[] = 'event_id=:event_id';
		}
		if (isset($this->individual) && $this->individual->hasId()) {
			$settings[] = 'individual_id=:individual_id';
		}
		if (isset($this->role)) {
			$settings[] = 'role=:role';
		}
		if (isset($this->comment)) {
			$settings[] = 'comment=:comment';
		}
		
		$sql = $new ? 'INSERT INTO' : 'UPDATE';
		$sql .= ' event_involvement SET ';
		$sql .= implode ( ', ', $settings );
		if (! $new)	{
			$sql .= ' WHERE id=:id';
		}
		$statement = $system->getPdo()->prepare($sql);
		
		if (isset($this->event) && $this->event->hasId()) {
			$statement->bindValue(':event_id',$this->event->getId(), PDO::PARAM_INT);
		}
		if (isset($this->individual) && $this->individual->hasId()) {
			$statement->bindValue(':individual_id',$this->individual->getId(), PDO::PARAM_INT);
		}
		if (isset($this->role)) {
			$statement->bindValue(':role',$this->role, PDO::PARAM_STR);
		}
		if (isset($this->comment)) {
			$statement->bindValue(':comment',$this->comment, PDO::PARAM_STR);
		}
		if (! $new)	{
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		}

		$result = $statement->execute();

		if ($result && $new) {
	    	$this->id = $system->getPdo()->lastInsertId();
	    }
		return $result;		
	}
}
?>