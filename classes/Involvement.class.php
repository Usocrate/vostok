<?php
/**
 * L'implication d'une personne.
 *
 * @package usocrate.vostok
 */
abstract class Involvement {

	protected $individual;
	protected $role;
	protected $comment;
	abstract function delete();
	abstract function feed();
	abstract function save();

	public function __construct() {
	}
	public function setIndividual(Individual $input) {
		$this->individual = $input;
	}
	public function &getIndividual(){
		return $this->individual;
	}
	public function getIndividualId(){
		return isset($this->individual) ? $this->individual->getId() : null;
	}
	public function setRole($input) {
		$this->role = $input;
	}
	public function getRole(){
		return $this->role;
	}	
	public function setComment($input) {
		$this->comment = $input;
	}
	public function getComment(){
		return $this->comment;
	}
}
?>