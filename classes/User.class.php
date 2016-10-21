<?php
/**
 * @package usocrate.exomemory.vostok
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
		if (is_array ( $row )) {
			// les données de l'initialisation sont transmises
			$this->id = $row ['user_id'];
			$this->name = $row ['username'];
			$this->password = $row ['password'];
			return true;
		} elseif ($this->id) {
			// on ne transmet pas les données de l'initialisation
			// mais on connaît l'identifiant de l'utilisateur
			$sql = 'SELECT * FROM user WHERE user_id=' . $this->id;
			$rowset = mysql_query ( $sql );
			$row = mysql_fetch_array ( $rowset );
			mysql_free_result ( $rowset );
			if (! $row)
				return false;
			return $this->feed ( $row );
		}
		return false;
	}
	public function toDB() {
		// si l'utilisateur ne possède pas d'id il est considéré comme nouveau
		$settings = array ();
		if ($this->name)
			$settings [] = 'username=\'' . mysql_real_escape_string ( $this->name ) . '\'';
		if ($this->password)
			$settings [] = 'password=\'' . mysql_real_escape_string ( $this->password ) . '\'';
		
		$sql = ($this->id) ? 'UPDATE' : 'INSERT INTO';
		$sql .= ' user SET ';
		$sql .= implode ( ', ', $settings );
		if ($this->id)
			$sql .= ' WHERE user_id=' . $this->id;
			// echo '<p>'.$sql.'< /p>';
		$result = mysql_query ( $sql );
		if (! $this->id)
			$this->id = mysql_insert_id ();
		return $result;
	}
	/**
	 * Obtient le pseudonyme de l'utilisateur.
	 *
	 * @version 26/02/2006
	 */
	public function getName() {
		if (! isset ( $this->name )) {
			$sql = 'SELECT username FROM user WHERE user_id =' . $this->id;
			$rowset = mysql_query ( $sql );
			$row = mysql_fetch_assoc ( $rowset );
			return $row ['username'];
		}
		return $this->name;
	}
	public function identification($name, $password) {
		global $system;
		
		$sql = "SELECT * FROM user WHERE username=:name AND password=:password";
		
		$statement = $system->getPdo ()->prepare ( $sql );
		
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