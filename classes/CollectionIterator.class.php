<?php
class CollectionIterator implements Iterator {
 private $var = array();

/**
 * @version 06/2017
 */
 public function __construct($array) {
  if (is_array($array) ) {
   $this->var = $array;
  }
 }

 /**
 * Remet le focus sur le premier élément de la collection
 */
 public function rewind() {
 return reset($this->var);
 }
 
 /**
 * Obtient l'object sur lequel est le focus.
 */
 public function current() {
 return current($this->var);
 }
 
 public function key() {
 return key($this->var);
 }
 /**
 * Obtient l'objet suivant dans la collection et déplace le focus.
 */
 public function next() {
 return next($this->var);
 }
 
 public function valid() {
 $var = $this->current() !== false;
 return $var;
 }
}
?>