<?php
/**
 * permet de gérer des messages à fournir aux utilisateur
 *
 * @since 03/2008
 */
class UserFeedBack {
	private array $messages;
	
	public function __construct() {
		$this->messages = array ();
		$this->messages['success'] = array ();
		$this->messages['info'] = array ();
		$this->messages['warning'] = array ();
		$this->messages['danger'] = array ();
	}
	
	/**
	 * ajoute un message au feedback à fournir à l'utilisateur
	 *
	 * @since 03/2008
	 */
	private function addMessage($message, $type = 'notice') {
		$this->messages [$type] [] = $message;
	}
	public function addSuccessMessage($message) {
		$this->addMessage ( $message, 'success' );
	}
	public function addInfoMessage($message) {
		$this->addMessage ( $message, 'info' );
	}
	public function addWarningMessage($message) {
		$this->addMessage ( $message, 'warning' );
	}
	public function addErrorMessage($message) {
		$this->addMessage ( $message, 'error' );
	}
	public function addDangerMessage($message) {
		$this->addMessage ( $message, 'danger' );
	}
	public function SuccessMessagesToHtml() {
		return $this->messagesToHtml ( 'success' );
	}
	public function InfoMessagesToHtml() {
		return $this->messagesToHtml ( 'info' );
	}
	public function WarningMessagesToHtml() {
		return $this->messagesToHtml ( 'warning' );
	}
	public function DangerMessagesToHtml() {
		return $this->messagesToHtml ( 'danger' );
	}
	
	private function MessagesToHtml($type) {
		if (isset($this->messages[$type]) && count($this->messages[$type])>0) {
			switch ($type) {
				case 'success' :
					$css_class = 'alert alert-success';
					break;
				case 'info' :
					$css_class = 'alert alert-info';
					break;
				case 'warning' :
					$css_class = 'alert alert-warning';
					break;
				case 'danger' :
					$css_class = 'alert alert-danger';
					break;
			}
			$html = '<div class="' . $css_class . '">';
			foreach ( $this->messages [$type] as $m ) {
				$html .= $m.'<br>';
			}
			$html .= '</div>';
			return $html;
		}
	}
	public function AllMessagesToHtml() {
		$html = $this->DangerMessagesToHtml ();
		$html .= $this->WarningMessagesToHtml ();
		$html .= $this->SuccessMessagesToHtml ();
		$html .= $this->InfoMessagesToHtml ();
		return $html;
	}
	/**
	 * @since 12/2018
	 */
	public function toHtml() {
		return $this->AllMessagesToHtml();
	}
}
?>