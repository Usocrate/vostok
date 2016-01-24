<?php
/**
 * Classe permettant de gérer un ensemble d'objet de même type
 */
abstract class Collection implements IteratorAggregate {
  /**
   * un intitulé de la collection
   *
   * @var string
   */
  private $name;
  /**
   * Le type d'objet collectionné.
   *
   * @var string
   */
  private $element_type;
  /**
   * Un tableau stockant les objets collectionnés
   *
   * @var array
   */
  protected $elements = array();

  /**
   * constructeur
   *
   * @param string $element_type
   */
  public function __construct($element_type, $name = NULL) {
    $this->setName($name);
    $this->element_type = $element_type;
  }
  /**
   * Définition requise de l'interface IteratorAggregate
   *
   * @return CollectionIterator
   */
  public function getIterator() {
    return new CollectionIterator($this->elements);
  }
  /**
   * Ajoute un élément à la collection
   *
   * @return boolean
   */
  public function addElement($element) {
    try {
      if (is_null($element)) {
        return true;
      }
      if ($element instanceof $this->element_type) {
        if ($element->getId()) {
          $this->elements[$element->getId()] = $element;
          return true;
        } else {
          throw new Exception('Un élément doit présenter un identifiant pour pouvoir être ajouté à une collection');
        }
      } else {
        throw new Exception('L\'élément que vous tentez d\'ajouter à la collection "'.$this->getName().'" n\'est pas du bon type : '.get_class($element));
      }
    } catch (Exception $e) {
      echo '<p>'.get_class($this).' : '.htmlentities($e->getMessage()).'</p>';
      ToolBox::html_dump($element);
      exit;
    }
  }
  /**
   * supprime le dernier élément de la collection.
   */
  public function removeLastElement() {
    return array_pop($this->elements);
  }
  /**
   * Obtient la taille de la collection
   *
   * @return int
   */
  public function getSize() {
    return count($this->elements);
  }
  /**
   * Obtient l'intitulé de la collection
   *
   * @return string
   */
  public function getName() {
    return isset($this->name) ? $this->name : NULL;
  }
  /**
   * Fixe l'intitulé de la collection.
   *
   * @return string
   */
  public function setName($name) {
    if (!empty($name)) {
      $this->name = $name;
    }
  }
  /**
   * Obtient une représentation de la collection au format json
   *
   * @return string
   */
  public function toJson() {
    return $this->toJson4Yui();
  }
  /**
   * Obtient une représentation de la collection au format json adaptée à Yahoo! UI Library
   *
   * @return string
   */
  public function toJson4Yui() {
    $i = $this->getIterator();
    $i->rewind();
    $jsonPieces = array();
    while ($i->current()) {
      $jsonPieces[] = $i->current()->toJson();
      $i->next();
    }
    return '{'.ToolBox::stringToJson(get_class($this)).':{"elements":['.implode(',',$jsonPieces).']}}';
  }
  /**
   * Renvoie les éléments de la collection sous forme de balises Html <option>.
   *
   * @param string $value_to_select
   */
  public function toHtmlOptionTags($value_to_select=NULL) {
    if ($this->getSize()>0) {
      $i = $this->getIterator();
      $i->rewind();
      while ($i->current()) {
        $html.= '<option value="'.$i->current()->getId().'"';
        if (strcmp($i->current()->getId(), $value_to_select)==0) {
          $html.= ' selected="selected"';
        }
        $html.= '>';
        $html.= htmlentities($i->current()->getName());
        $html.= '</option>';
        $i->next();
      }
      return $html;
    }
  }
  /**
   * Retourne le premier élément de la collection.
   */
  public function getFirstElement() {
    $ids = $this->getIds();
    if (count($ids)>0) {
      return $this->getElementById($ids[0]);
    } else {
      return NULL;
    }
  }
  /**
   * Obtient la liste des identifiants des objets de la collection.
   *
   * @return array
   */
  public function getIds() {
    return array_keys($this->elements);
  }
  /**
   * Obtient les identifiants des descendants directs (sous-chapitres) des chapitres de la collection.
   *
   * @return array
   */
  public function getChildIds() {
    $output = array();
    $i = $this->getIterator();
    $i->rewind();
    while ($i->current()) {
      $output = array_merge($output, $i->current()->getChildIds());
      $i->next();
    }
    return $output;
  }
  /**
   * Obtient les noms des éléments de la collection
   *
   * @return array
   */
  public function getNames() {
    $names = array();
    if ($this->getSize()>0) {
      $i = $this->getIterator();
      $i->rewind();
      while ($i->current()) {
        $names[] = $i->current()->getName();
        $i->next();
      }
    }
    return $names;
  }
  /**
   * Si un élément est passé en paramètre, indique si celui-ci est déjà dans la collection, sinon indique si la collection poss�de des éléments.
   *
   * @param object $element | NULL
   * @return boolean
   */
  public function hasElement($element=NULL) {
    try {
      /**
       * si pas d'élément passé en paramètre on indique simplement si la collection possède des éléments
       */
      if (is_null($element)) {
        return $this->getSize()>0;
      }
      /**
       * sinon on indique si l'élément fait partie de la collection
       */
      if ($element instanceof $this->element_type) {
        return in_array($element->getId(), $this->getIds());
      } else {
        return false;
      }
    } catch(Exception $e) {
      echo '<p>'.__METHOD__.' : '.htmlentities($e->getMessage()).'</p>';
      exit;
    }
  }
  /**
   * Renvoie un des éléments de la collection, retrouvé par son identifiant.
   *
   * @param string $id
   * @return object
   */
  public function &getElementById($id) {
    return isset($this->elements[$id]) ? $this->elements[$id] : NULL;
  }
  /**
   * Renvoie les éléments de la collection portant le nom passé en paramètre
   *
   * @param string $name
   * @return Collection
   */
  public function getElementsByName($name) {
    $class = get_class($this);
    $selection = new $class('les éléments portant le nom '.$name.' parmi la collection '.$this->getName());
    $names = $this->getNames();
    $ids = $this->getIds();
    /**
     * recherche des éléments portant le nom passé en paramètre
     */
    while (current($names)) {
      //echo '<p>'.current($names).' : '.key($names).'</p>';
      if (strcmp($name, current($names))==0) {
        $id = $ids[key($names)];
        $selection->addElement($this->getElementById($id));
      }
      next($names);
    }
    return $selection;
  }
  /**
   * Renvoie le premier élément de la collection portant le nom passé en paramètre.
   *
   * @param string $name
   * @return Object
   */
  public function &getElementByName($name) {
    $selection = $this->getElementsByName($name);
    return $selection->getSize() > 0 ? $selection->getFirstElement() : NULL;
  }
  /**
   * Renvoie le nom d'un élément de la collection retrouvé par son identifiant.
   *
   * @param string $id
   * @return string
   */
  public function getElementName($id) {
    return isset($this->elements[$id]) ? $this->elements[$id]->getName() : NULL;
  }
  /**
   * Obtient un sous-ensemble d'élements de la collection, retrouvés par leur identifiant.
   *
   * @param array $ids
   * @return Collection
   */
  public function getSelectionByIds(Array $ids) {
    $class = get_class($this);
    $selection = new $class('Sélection d\'éléments parmi la collection '.$this->getName());
    if ($this->getSize()>0) {
      $i = $this->getIterator();
      $i->rewind();
      while ($i->current()) {
        if (in_array($i->current()->getId(), $ids)) {
          $selection->addElement($i->current());
        }
        $i->next();
      }
    }
    return $selection;
  }
  /**
   * Obtient la liste des identifiants des éléments de la collection au format CSV
   *
   * @return string
   */
  public function getCommaSeparatedIds() {
    return isset($this->elements) ? implode(',',$this->getIds()) : NULL;
  }
  /**
   * Obtient les noms des éléments de la collection au format CSV
   *
   * @return string
   */
  public function getCommaSeparatedNames() {
    return is_array($this->elements) ? implode(',',$this->getNames()) : NULL;
  }
  /**
   * tri le tableau de sous-rubriques filles par intitulé
   *
   */
  public function sortElementsByName() {
    if ($this->getSize()>0) {
      $ids = $this->getIds();
      for ($i = count($ids) - 1; $i >= 0; $i--) {
        /**
         * on tri la liste des identifiants selon l'ordre alphabetique des éléments associés
         */
        for ($j = 0; $j < $i; $j++) {
          if (self::areAlphabeticallyOrdered($this->getElementById($ids[$j]), $this->getElementById($ids[$j+1]))) {
            $tmp = $ids[$j];
            $ids[$j] = $ids[$j+1];
            $ids[$j+1] = $tmp;
          }
        }
      }
      /**
       * un deuxième temps on reconstitue la liste des éléments de la collection suivant cet ordre
       */
      $orderedElements = array();
      foreach ($ids as $id) {
        $orderedElements[$id] = $this->elements[$id];
      }
      $this->elements = $orderedElements;
    }
  }
  /**
   * Indique si l'ordre alphabétique est respecté dans la liste d'éléments passé en paramètre.
   *
   * @return boolean
   */
  public static function areAlphabeticallyOrdered($element1, $element2)  {
    for ($i=1; $i<iconv_strlen($element1->getName()) || $i<iconv_strlen($element2->getName()); $i++) {
      $s1 = iconv_substr($element1->getName(), 0, $i);
      $s2 = iconv_substr($element2->getName(), 0, $i);
      //echo $s1.' vs '.$s2.' : '.strcasecmp($s1, $s2).'<br/>';
      if (strcasecmp($s1, $s2)!=0) {
        return strcasecmp($s1, $s2)>0;
      }
    }
  }
  /**
   * Met en commun deux collections (union).
   *
   * @param Collection $c
   * @return Collection
   */
  public function mergeWith(Collection $c) {
    try {
      if (strcmp(get_class($this), get_class($c))!=0) {
        throw new Exception('Deux collections doivent être de même type pour pouvoir être fusionnées. Ici la collection "'.$this->getName().'" est de type "'.get_class($this).'" alors que la collection "'.$c->getName().'" est de type '.get_class($c).')');
      }
      $class = get_class($this);
      /**
       * les 2 collections comportent au moins un élément
       */
      if ($this->getSize()>0 && $c->getSize()>0) {
        //echo '<p>les 2 collections comportent au moins un élément</p>';
        $ids = array_merge($this->getIds(), $c->getIds()); // les identifiants des éléments présents dans au moins une des deux collections
        $output = new $class('Conglomérat de collections de type '.$class);
        foreach ($ids as $id) {
          $element_to_add = in_array($id, $this->getIds()) ? $this->getElementById($id) : $c->getElementById($id);
          $output->addElement($element_to_add);
        }
        return $output;
      }
      /**
       * seule la collection passée en paramètre comporte un élément au moins
       */
      elseif ($this->getSize()==0) {
        return $c;
      }
      /**
       * seule la collection courante comporte un élément au moins
       */
      elseif ($c->getSize()==0) {
        return $this;
      }
      /**
       * aucune des 2 collections à fusionner ne comporte d'éléments
       */
      else {
        return new $class();
      }

    } catch (Exception $e) {
      echo '<p>'.__METHOD__.' : '.htmlentities($e->getMessage()).'</p>';
    }
  }
  /**
   * Renvoie les éléments communs que possède la collection avec la collection passée en paramètre.
   *
   * @param Collection $c
   * @return Collection
   */
  public function getIntersectionWith(Collection $c) {
    try {
      if (strcmp(get_class($this), get_class($c))!=0) {
        throw new Exception('Deux collections doivent être de même type pour pouvoir en extraire les éléments en commun');
      }
      $ids = array_intersect($this->getIds(), $c->getIds()); // les identifiants des éléments en commun
      $class = get_class($this);
      $output = new $class();
      foreach ($ids as $id) {
        $element_to_add = in_array($id, $this->getIds()) ? $this->getElementById($id) : $c->getElementById($id);
        $output->addElement($element_to_add);
      }
      return $output;
    } catch (Exception $e) {
      echo '<p>'.__METHOD__.' : '.htmlentities($e->getMessage()).'</p>';
    }
  }
  /**
   * Retourne les éléments de la collection amputés des éléments de la collection passée en paramêtre.
   *
   * @param Collection $c
   * @return Collection
   */
  public function getDifference(Collection $c) {
    try {
      if (strcmp(get_class($this), get_class($c))!=0) {
        throw new Exception('Deux collections doivent être de même type pour pouvoir en faire la différence');
      }
      $ids = array_diff($this->getIds(), $c->getIds()); // les identifiants des éléments en commun
      $class = get_class($this);
      $output = new $class();
      foreach ($ids as $id) {
        $output->addElement($this->getElementById($id));
      }
      return $output;
    } catch (Exception $e) {
      echo '<p>'.__METHOD__.' : '.htmlentities($e->getMessage()).'</p>';
    }
  }
}
?>