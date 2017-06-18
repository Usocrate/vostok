<?php

/**
 * @package usocrate.vostok
 * @author Florent Chanavat
 */
class Lead
{

    public $id;

    public $shortdescription;

    public $description;

    public $creation_date;

    public $type;

    public $status;

    public $source;

    public $source_description;

    public $individual;

    public $society;

    public function __construct($id = NULL)
    {
        $this->id = $id;
    }

    /**
     * Fixe la valeur d'un attribut.
     *
     * @version 04/03/2006
     */
    private function setAttribute($name, $value)
    {
        $value = trim($value);
        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
        return $this->{$name} = $value;
    }

    /**
     * Obtient la valeur d'un attribut de l'objet
     *
     * @version 09/08/2007
     */
    private function getAttribute($name)
    {
        return $this->{$name};
    }

    /**
     * Fixe l'identifiant de la piste.
     *
     * @param int $input            
     */
    public function setId($input)
    {
        return $this->setAttribute('id', $input);
    }

    public function getId()
    {
        return $this->getAttribute('id');
    }
    /**
     * @since 06/2017
     */
    public function hasId() {
        return !empty($this->id);
    }
    /**
     * Obtient le statut de la piste
     *
     * @since 09/08/2007
     */
    public function getStatus()
    {
        return $this->getAttribute('status');
    }

    /**
     * Fixe le statut de la piste
     *
     * @since 09/08/2007
     */
    public function setStatus($input)
    {
        return $this->setAttribute('status', $input);
    }

    /**
     * Obtient les options envisageables pour le statut de la piste
     *
     * @return String
     * @since 09/08/2007
     */
    public function getStatusOptionsTags($valueToSelect = NULL)
    {
        $values = array(
            'relevée',
            'à suivre',
            'suivie',
            'abandonnée'
        );
        if (empty($valueToSelect) || ! in_array($valueToSelect, $values)) {
            $valueToSelect = $this->getStatus();
        }
        $html = '';
        foreach ($values as $value) {
            $html .= '<option value="' . ToolBox::toHtml($value) . '"';
            if (strcmp($value, $valueToSelect) == 0) {
                $html .= ' selected="selected"';
            }
            $html .= '>';
            $html .= ToolBox::toHtml($value);
            $html .= '</option>';
        }
        return $html;
    }

    /**
     * Obtient la date d'enregistrement de la piste
     *
     * @since 01/01/2006
     * @version 09/08/2007
     */
    public function getCreationDate()
    {
        return $this->getAttribute('creation_date');
    }

    /**
     * Obtient le timestamp de l'enregistrement de la piste
     *
     * @since 01/01/2006
     * @version 09/08/2007
     */
    public function getCreationTimestamp()
    {
        if ($this->getCreationDate()) {
            list ($year, $month, $day) = explode('-', $this->getCreationDate());
            return mktime(0, 0, 0, (int) $month, (int) $day, (int) $year);
        } else {
            return NULL;
        }
    }

    /**
     * Obtient la date d'enregistrement de la piste au format français
     *
     * @since 01/01/2006
     * @version 09/08/2007
     */
    public function getCreationDateFr()
    {
        return date('d/m/Y', $this->getCreationTimestamp());
    }

    /**
     * Obtient la description de la piste.
     *
     * @return String
     * @version 09/08/2007
     */
    public function getDescription()
    {
        return $this->getAttribute('description');
    }

    /**
     * Fixe la description de la piste
     *
     * @version 09/08/2007
     */
    public function setDescription($input)
    {
        return $this->setAttribute('description', $input);
    }

    /**
     * Obtient la description résumée de la piste
     *
     * @return String
     * @version 09/08/2007
     */
    public function getShortDescription()
    {
        return $this->getAttribute('shortdescription');
    }

    /**
     * Fixe la description résumée de la piste
     *
     * @version 09/08/2007
     */
    public function setShortDescription($input)
    {
        return $this->setAttribute('shortdescription', $input);
    }

    /**
     * Obtient le type de la piste.
     *
     * @since 29/01/2006
     */
    public function getType()
    {
        return isset($this->type) ? $this->type : NULL;
    }

    /**
     * Récupère l'ensemble des types déjà présents en base de données
     *
     * @param
     *            $label_substring
     * @return return array
     * @since 13/07/2009
     */
    private static function getKnownTypes($label_substring = NULL)
    {
        global $system;
        $sql = 'SELECT lead_type AS value, COUNT(*) AS count FROM lead WHERE lead_type IS NOT NULL';
        if (isset($label_substring)) {
            $sql .= ' AND lead_type LIKE :pattern';
        }
        $sql .= ' GROUP BY lead_type ORDER BY COUNT(*) DESC';
        $statement = $system->getPdo()->prepare($sql);
        if (isset($label_substring)) {
            $statement->bindValue(':pattern', '%' . $label_substring . '%', PDO::PARAM_STR);
        }
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtient la liste des différents types de piste présents en base de données, sous la forme de balises HTML <options>
     *
     * @return String
     * @since 29/01/2006
     * @version 13/07/2009
     */
    public static function getKnownTypesAsOptionsTags($valueToSelect = NULL)
    {
        $html = '';
        $items = self::getKnownTypes();
        foreach ($items as $item) {
            $html .= '<option value="' . ToolBox::toHtml($item['value']) . '"';
            if (isset($valueToSelect) && strcmp($item['value'], $valueToSelect) == 0) {
                $html .= ' selected="selected"';
            }
            $html .= '>';
            $html .= ToolBox::toHtml(ucfirst($item['value']));
            $html .= ' (' . $item['count'] . ')';
            $html .= '</option>';
        }
        return $html;
    }

    /**
     * Obtient la liste des types déjà présents en base de données sous format json.
     *
     * @param
     *            $label_substring
     * @return return string
     * @since 13/07/2009
     */
    public static function knownTypesToJson($label_substring = NULL)
    {
        $output = '{"types":[';
        $items = self::getKnownTypes($label_substring);
        for ($i = 0; $i < count($items); $i ++) {
            $output .= '{"value":' . ucfirst(json_encode($items[$i]['value'])) . ',"count":' . $items[$i]['count'] . '}';
            if ($i < count($items) - 1) {
                $output .= ',';
            }
        }
        $output .= ']}';
        return $output;
    }

    /**
     * Fixe le type la piste.
     *
     * @since 29/01/2006
     */
    public function setType($input)
    {
        if (empty($input)) {
            return false;
        } else {
            $this->type = $input;
            return true;
        }
    }

    /**
     * Obtient l'origine de la piste.
     *
     * @since 03/2006
     */
    public function getSource()
    {
        return $this->getAttribute('source');
    }

    /**
     * Récupère l'ensemble des sources déjà présentes en base de données
     *
     * @param
     *            $label_substring
     * @return return array
     * @since 13/07/2009
     */
    private static function getKnownSources($label_substring = NULL)
    {
        global $system;
        $sql = 'SELECT lead_source AS value, COUNT(*) AS count FROM lead WHERE lead_source IS NOT NULL';
        if (isset($label_substring)) {
            $sql .= ' AND lead_source LIKE :pattern';
        }
        $sql .= ' GROUP BY lead_source ORDER BY COUNT(*) DESC';
        $statement = $system->getPdo()->prepare($sql);
        if (isset($label_substring)) {
            $statement->bindValue(':pattern', '%' . $label_substring . '%', PDO::PARAM_STR);
        }
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtient la liste des sources déjà présentes en base de données sous format json.
     *
     * @param
     *            $label_substring
     * @return return string
     * @since 13/07/2009
     */
    public static function knownSourcesToJson($label_substring = NULL)
    {
        $output = '{"sources":[';
        $items = self::getKnownSources($label_substring);
        for ($i = 0; $i < count($items); $i ++) {
            $output .= '{"value":' . json_encode($items[$i]['value']) . ',"count":' . $items[$i]['count'] . '}';
            if ($i < count($items) - 1) {
                $output .= ',';
            }
        }
        $output .= ']}';
        return $output;
    }

    /**
     *
     * @since 16/01/2007
     * @version 09/09/2007
     */
    public static function getKnownSourcesAsOptionsTags($valueToSelect = NULL)
    {
        $html = '';
        $items = self::getKnownSources();
        foreach ($items as $item) {
            $html .= '<option value="' . ToolBox::toHtml($item['value']) . '"';
            if (isset($valueToSelect) && strcmp($item['value'], $valueToSelect) == 0) {
                $html .= ' selected="selected"';
            }
            $html .= '>';
            $html .= ToolBox::toHtml(ucfirst($item['value']));
            $html .= '(' . $item['count'] . ')';
            $html .= '</option>';
        }
        return $html;
    }

    /**
     * Obtient les commentaires sur l'origine de la piste.
     *
     * @since 09/08/2006
     */
    public function getSourceDescription()
    {
        return $this->getAttribute('source_description');
    }

    /**
     * Fixe la description de l'origine de la piste
     *
     * @since 09/08/2007
     */
    public function setSourceDescription($input)
    {
        return $this->setAttribute('source_description', $input);
    }

    /**
     * Fixe l'origine de la piste
     *
     * @since 03/2006
     */
    public function setSource($input)
    {
        return $this->setAttribute('source', $input);
    }

    /**
     * Fixe la personne liée à la piste
     *
     * @param Individual $input            
     * @version 29/01/2006
     */
    public function setIndividual($input)
    {
        if (is_a($input, 'Individual'))
            $this->individual = $input;
    }

    /**
     * Obtient la personne liée à la piste
     *
     * @return Individual
     */
    public function getIndividual()
    {
        return isset($this->individual) && is_a($this->individual, 'Individual') ? $this->individual : NULL;
    }

    /**
     * Fixe la société liée à la piste
     *
     * @param Society $input            
     * @version 09/04/2006
     */
    public function setSociety($input)
    {
        if (is_a($input, 'Society'))
            $this->society = $input;
    }

    /**
     * Obtient le compte auquel est liée la piste
     *
     * @return Society
     * @version 09/08/2007
     */
    public function getSociety()
    {
        return isset($this->society) && is_a($this->society, 'Society') ? $this->society : NULL;
    }

    /**
     *
     * @version 09/08/2007
     */
    public function toDB()
    {
        global $system;
        
        $new = ! isset($this->id) || empty($this->id);
        
        /*
         * Construction requête sql
         */
        $settings = array();
        if (isset($this->individual) && $this->individual->getId()) {
            $settings[] = 'individual_id=:individual_id';
        }
        if (! empty($this->society->id)) {
            $settings[] = 'society_id=:society_id';
        }
        if ($this->getShortDescription()) {
            $settings[] = 'lead_shortdescription=:shortdescription';
        }
        if (! empty($this->description)) {
            $settings[] = 'lead_description=:description';
        }
        if (isset($this->type)) {
            $settings[] = 'lead_type=:type';
        }
        if (isset($this->status)) {
            $settings[] = 'lead_status=:status';
        }
        if (! empty($this->source)) {
            $settings[] = 'lead_source=:source';
        }
        if (! empty($this->source_description)) {
            $settings[] = 'lead_source_description=:source_description';
        }
        if ($new) {
            $settings[] = 'lead_creation_date=NOW()';
            $settings[] = 'lead_creation_user_id=:user_id';
        } else {
            $settings[] = 'lead_lastModification_user_id=:user_id';
        }
        
        $sql = $new ? 'INSERT INTO' : 'UPDATE';
        $sql .= ' lead SET ';
        $sql .= implode(', ', $settings);
        if (! $new) {
            $sql .= ' WHERE lead_id=:id';
        }
        //echo '<p>'.$sql.'< /p>';
        
        $statement = $system->getPdo()->prepare($sql);
        /*
         * Binding
         */
        if (isset($this->individual) && $this->individual->getId()) {
            $statement->bindValue(':individual_id', $this->individual->getId(), PDO::PARAM_STR);
        }
        if (! empty($this->society->id)) {
            $statement->bindValue(':society_id', $this->society->id, PDO::PARAM_STR);
        }
        if ($this->getShortDescription()) {
            $statement->bindValue(':shortdescription', $this->getShortDescription(), PDO::PARAM_STR);
        }
        if (! empty($this->description)) {
            $statement->bindValue(':description', $this->getDescription(), PDO::PARAM_STR);
        }
        if (! empty($this->source_description)) {
            $statement->bindValue(':source_description', $this->source_description, PDO::PARAM_STR);
        }
        if (isset($this->type)) {
            $statement->bindValue(':type', $this->type, PDO::PARAM_STR);
        }
        if (isset($this->status)) {
            $statement->bindValue(':status', $this->status, PDO::PARAM_STR);
        }
        if (! empty($this->source)) {
            $statement->bindValue(':source', $this->source, PDO::PARAM_STR);
        }
        if (! empty($this->source_description)) {
            $statement->bindValue(':source_description', $this->source_description, PDO::PARAM_STR);
        }
        if (! $new) {
            $statement->bindValue(':id', (int) $this->id, PDO::PARAM_INT);
        }
        $statement->bindValue(':user_id', (int) $_SESSION['user_id'], PDO::PARAM_INT);
        $result = $statement->execute();
        if ($new) {
            $this->id = $system->getPdo()->lastInsertId();
        }
        return $result;
    }

    public function delete()
    {
        global $system;
        if (! $this->id) return false;
        $statement = $system->getPdo()->prepare('DELETE FROM lead WHERE lead_id=:id');
        $statement->bindValue(':id', (int) $this->id, PDO::PARAM_INT);        
        return $statement->execute();
    }

    /**
     *
     * @version 03/01/2017
     */
    public function feed($data = NULL) {
        global $system;
        if (is_array($data)) {
            // compte
            $this->society = new Society();
            $this->society->feed($data);
            
            // individual
            $this->individual = new Individual();
            $this->individual->feed($data);
            
            // les données de l'initialisation sont transmises
            foreach ($data as $key => $value) {
                // NB : stricte correspondance entre les noms d'attribut de la classe
                $items = explode('_', $key);
                switch ($items[0]) {
                    case 'lead':
                        // pour les champs préfixés 'lead_', on supprime le préfixe
                        array_shift($items);
                        $this->setAttribute(implode('_', $items), $value);
                        break;
                    default:
                }
            }
            return true;
        } elseif (! empty($this->id)) {
            $sql = 'SELECT * FROM lead AS l';
            $sql .= ' LEFT JOIN individual AS c ON l.individual_id=c.individual_id';
            $sql .= ' LEFT JOIN society AS a ON l.society_id=a.society_id';
            $sql .= ' WHERE lead_id=:id';
            // echo $sql.'<br/>';
            $statement = $system->getPdo()->prepare($sql);
            $statement->bindValue(':id', $this->id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            return is_array($data) ? $this->feed($data) : false;
        }
        return false;
    }
}
?>