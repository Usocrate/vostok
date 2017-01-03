<?php
/**
 * @package usocrate.vostok
 * @author Florent Chanavat
 */
class User {
	public $id;
	public $name;
	public $password;
	public function __construct($id = NULL) {
		$this->id = $id;
	}
	public function feed($row = NULL) {
		global $system;
		if (is_array ( $row )) {
			// les données de l'initialisation sont transmises
			$this->id = $row ['user_id'];
			$this->name = $row ['username'];
			$this->password = $row ['password'];
			return true;
		} elseif ($this->id) {
			// on ne transmet pas les données de l'initialisation mais on connaît l'identifiant de l'utilisateur
			$sql = 'SELECT * FROM user WHERE user_id=:id';
			$statement = $system->getPdo()->prepare($sql);
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			$statement->execute();
			$row = $statement->fetch(PDO::FETCH_ASSOC);
			if (! $row)	return false;
			return $this->feed ( $row );
		}
		return false;
	}
	/**
	 * @version 23/11/2016 
	 */
	public function toDB() {
		global $system;

		$settings = array ();
		if ($this->name) {
			$settings [] = 'username = :name';
		}
		if ($this->password) {
			$settings [] = 'password = :password';
		}
		$sql = ($this->id) ? 'UPDATE' : 'INSERT INTO';
		$sql .= ' user SET '. implode ( ', ', $settings );
		
		if ($this->id) {
			$sql .= ' WHERE user_id = :id';
		}
		
		$statement = $system->getPdo()->prepare($sql);
		
		if ($this->name) {
			$statement->bindValue(':name', $this->name, PDO::PARAM_STR);
		}
		if ($this->password) {
			$statement->bindValue(':password', $this->password, PDO::PARAM_STR);
		}
		if ($this->id) {
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		}
		
		$result =  $statement->execute();

		if (! $this->id) {
			$this->id = $system->getPdo()->lastInsertId();
		}
		
		return $result;
	}
	/**
	 * Obtient le pseudonyme de l'utilisateur.
	 *
	 * @version 23/11/2016
	 */
	public function getName() {
		global $system;
		if (! isset ( $this->name )) {
			$statement = $system->getPdo()->prepare('SELECT username FROM user WHERE user_id = :id');
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			$statement->execute();
			$this->name = $statement->fetchColumn();
		}
		return $this->name;
	}
	public function identification($name, $password) {
		global $system;
		$statement = $system->getPdo ()->prepare ( 'SELECT * FROM user WHERE username = :name AND password = :password' );
		$statement->bindValue ( ':name', $name, PDO::PARAM_STR );
		$statement->bindValue ( ':password', $password, PDO::PARAM_STR );
		$statement->setFetchMode ( PDO::FETCH_ASSOC );
		$statement->execute();
		$this->feed ($statement->fetch());
		return $this->id;
	}
	/**
	 *
	 * @version 07/01/2006
	 */
	public function getId() {
		return isset ( $this->id ) ? $this->id : NULL;
	}
	/**
	 * Obtient le mot de passe de l'utilisateur.
	 *
	 * @since 26/02/2006
	 */
	public function getPassword() {
		return isset ( $this->password ) ? $this->password : NULL;
	}
}
?>