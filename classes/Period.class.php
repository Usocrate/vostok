<?php
/**
 * @since 07/2018
 */
class Period {
    
    public $init_year;
    public $end_year;
    
	public function __construct($init_year = null, $end_year = null) {
	    $this->init_year = $init_year;
	    $this->end_year = $end_year;
	}
	
	public function toString() {
	    if (! empty($this->init_year) && ! empty($this->end_year) ) {
			return $this->init_year.'-'.$this->end_year;
		} elseif (! empty($this->init_year) ) {
			return 'depuis '.$this->init_year;
		} elseif (! empty($this->end_year)) {
		    return 'jusqu\'en '.$this->end_year;
		}
	}
	
	public function isDefined(){
	    return !(empty($this->init_year) && empty($this->end_year));
	}
}