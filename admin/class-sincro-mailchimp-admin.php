<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * @package    Sincro_Mailchimp
 * @subpackage Sincro_Mailchimp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sincro_Mailchimp
 * @subpackage Sincro_Mailchimp/admin
 * @author     Dario <dm@madaritech.com>
 */
class Sincro_Mailchimp_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	/**
	 * Configurazione Plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private $smc;

	/**
	 * Richiama l'API get_lists dal Plugin MailChimp for WP.
	 *
	 * @since    1.0.0
	 * @param    array    $args    Parametri per l'API MailChimp.
	 */
	protected function get_lists( $args ) {
		global $mc4wp;
		return ($mc4wp['api']->get_lists($args));
	} 
	
	/**
	 * Richiama l'API get_list_member dal Plugin MailChimp for WP.
	 *
	 * @since    1.0.0
	 * @param    string    $list_id    	Id della Mailing List.
	 * @param    string    $user_email  Email dell'utente.
	 */
	protected function get_list_member( $list_id, $user_email ) {
		global $mc4wp;
		return ($mc4wp['api']->get_list_member($list_id, $user_email));
	} 

	/**
	 * Richiama l'API add_list_member dal Plugin MailChimp for WP.
	 *
	 * @since    1.0.0
	 * @param    string   $list_id    	Id della Mailing List.
	 * @param    array    $args  		Parametri per l'API MailChimp.
	 */
	protected function add_list_member( $list_id, $args ) {
		global $mc4wp;
		return ($mc4wp['api']->add_list_member($list_id, $args));
	}

	/**
	 * Richiama l'API delete_list_member dal Plugin MailChimp for WP.
	 *
	 * @since    1.0.0
	 * @param    string   $list_id    	Id della Mailing List.
	 * @param    string   $user_email  	Email dell'utente.
	 */
	protected function delete_list_member( $list_id, $user_email ) {
		global $mc4wp;
		return ($mc4wp['api']->delete_list_member($list_id, $user_email));
	}

	/**
	 * Richiama la configurazione del plugin relativa ad un certo ruolo.
	 *
	 * @since    1.0.0
	 * @param    string   $user_role    	Ruolo dell'utente.
	 */
	protected function get_config_role( $user_role ) {
		$this->smc = unserialize(SINCRO_MAILCHIMP_CONFIG);
		return($this->smc[$user_role]);
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/plugin-name-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/plugin-name-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * The field on the editing screens.
	 *
	 * @param $user WP_User user object
	 * @since    1.0.0
	 */
	public function form_field_iscrizione_mailing_list($user)
	{
		$checked = 0;

		// Estrazione dati utente
		$user_email = $user->user_email;
		$user_role = $user->roles[0];

		$subscription_status = $this->check_subscription_status($user_email, $user_role);

		if ($subscription_status == 2) $checked = 1;

	    require_once('partials/sincro-mailchimp-admin-display.php');

	    $this->esegui_iscrizione_javascript($user);
	}
 
	/**
	 * Javascript for manage checkbox change event.
	 *
	 * @param $user WP_User user object
	 * @since    1.0.0
	 */
	private function esegui_iscrizione_javascript($user) { ?>
		<script type="text/javascript" >
		jQuery("#mc_subscribe").change(function($) {

			var checked = 0;

			if(this.checked) {
	        	checked = 1;
	    	}

			var data = {
				'action': 'esegui_iscrizione',
				'check_status': checked,
				'user_email' : '<?php echo $user->user_email; ?>',
				'user_role' : '<?php echo $user->roles[0]; ?>',
				'_wpnonce' : '<?php  $nonce = wp_create_nonce( 'esegui_iscrizione' );
									echo $nonce; ?>'
			};

			jQuery.post(ajaxurl, data, function(response) {
				alert(response.data);
			});
		});
		</script> <?php
	}

	/**
	 * In base ai parametri ricevuti via post esegue o meno l'iscrizione.
	 *
	 * @since    1.0.0
	 */
	public function esegui_iscrizione() {

		check_admin_referer('esegui_iscrizione', '_wpnonce');

		$check_status = intval( $_POST['check_status'] );
		$user_email = strval( $_POST['user_email'] );
		$user_role = strval( $_POST['user_role'] );
		$ut = isset($_POST['ut']) ?  intval($_POST['ut']) : 0;

		if ($ut) wp_send_json_success( 'Verifica Unit Test' );
			
		$subscription_status = $this->check_subscription_status($user_email, $user_role);

		if ($check_status) $this->subscribe_process($subscription_status, $user_email, $user_role);
		else $this->unsubscribe_process($subscription_status, $user_email, $user_role);

		if ($ut) wp_send_json_success( 'Operazione eseguita' );
	}

	/**
	 * Implementa la logica del processo di sottoscrizione.
	 *
	 * @since    1.0.0
	 */
	public function subscribe_process($subscription_status, $user_email, $user_role) {	

		$res = false;

		switch ($subscription_status) {
			case 0:
				// Configurazione vuota: non eseguo nulla
				break;
			case 1:
				// Procedo con l'iscrizione

				// Estrazione parametri configurazione
				$smc = $this->get_config_role($user_role);
				
				$res = $this->subscribe_user($user_email, $smc);

				break;
			case 2:
				// Utente già iscritto correttamente
				break;
			case 3:
				// Utente iscritto parzialmente o in modo diverso rispetto la configurazione

				// Estrazione parametri configurazione
				$smc = $this->get_config_role($user_role);

				//Reset iscrizione parziale
				if ($this->unsubscribe_user_mailchimp($user_email)) {
					// Procedo con iscrizione da configurazione
					$res = $this->subscribe_user($user_email, $smc);
				}

				break;

			default:
				break;
		}

		return($res);

	}

	/**
	 * Implementa la logica del processo di cancellazione della sottoscrizione.
	 *
	 * @since    1.0.0
	 */
	public function unsubscribe_process($subscription_status, $user_email, $user_role) {

		$res = false;

		switch ($subscription_status) {
			case 0:
				// Configurazione vuota: non eseguo nulla
				break;
			case 1:
				// Utente non iscritto
				break;
			case 2:
				// Utente iscritto secondo configurazione

				// Estrazione parametri configurazione
				$smc = $this->get_config_role($user_role);

				$res = $this->unsubscribe_user_config($user_email, $smc);

				break;
			case 3:
				// Utente iscritto parzialmente o in modo diverso rispetto la configurazione

				// Estrazione parametri configurazione
				$smc = $this->get_config_role($user_role);

				$res = $this->unsubscribe_user_mailchimp($user_email);

				break;			
			default:
				break;
		}

		return($res);

	}

	/**
	 * Verifica lo stato dell'iscrizione. Valori ritornati:
	 * 0 - la configurazione è vuota 
	 * 1 - l'utente non è iscritto e la configurazione non è vuota
	 * 2 - l'utente è già iscritto e rispetta la configurazione
	 * 3 - l'utente è iscritto parzialmente o in modo diverso rispetto la configurazione
	 *
	 * @param    $user_email
	 * @param    $user_role
	 * @since    1.0.0
	 */
	public function check_subscription_status($user_email, $user_role) {
		
		// Estrazione parametri configurazione
		$smc = $this->get_config_role($user_role);

		// Estrazione List associate all'utente e verifica allineamento rispetto la configurazione
		$args['email'] = $user_email;
		$res_user_lists = $this->get_lists($args);

		$num_list_mailchimp = count((array)$res_user_lists);
		$num_list_config = count($smc);

		if ($num_list_config != 0 && $num_list_mailchimp == 0) return(1); //unchecked

		if ($num_list_config == 0 ) return(0); //unchecked

		if ($num_list_config != 0 && $num_list_mailchimp != 0) { 

			//Controllo se il numero di liste associate in configurazione e su Mailchimp è uguale
			if ($num_list_config == $num_list_mailchimp) {

				foreach ($res_user_lists as $list) {

					//Verifico che gli id lista coincidano con la configurazione
					if (array_key_exists($list->id, $smc)) { 

						//Estrazione interests da Mailchimp
						$res_user_list_interests = $this->get_list_member($list->id, $user_email);
						
						$interest_ids = (array) $res_user_list_interests->interests;
						
						foreach ($interest_ids as $key => $value) {
							if ($smc[$list->id][$key] !== $value) {
								return(3);
				    		}
						}

					} else {

				    	return(3);
					}
				}

			}
			else {
				return(3);
			}

			return(2);
		}
	}

	/**
	 * Eseguo l'iscrizione dell'utente.
	 *
	 * @since    1.0.0
	 */
	public function subscribe_user($user_email, $smc) {
		$args['email_address'] = $user_email;
		$args['status'] = 'subscribed';

		foreach ($smc as $list_id => $interests ) {
			$args['interests'] = $interests;
			$add_status = $this->add_list_member($list_id, $args);
		}

		return(true);
	}

	/**
	 * Elimino l'iscrizione basandomi sullo stato della configurazione locale.
	 *
	 * @since    1.0.0
	 */
	public function unsubscribe_user_config($user_email, $smc) {

		$reset_args['email'] = $user_email;

		foreach ($smc as $list_id => $interests) {
			$reset_status = $this->delete_list_member($list_id, $user_email);
		}

		return(true);
	}

	/**
	 * Elimino l'iscrizione basandomi sullo stato di configurazione di mailchimp.
	 *
	 * @since    1.0.0
	 */
	public function unsubscribe_user_mailchimp($user_email) {

		// Reset iscrizione incompleta
		$reset_args['email'] = $user_email;
		$res_user_lists = $this->get_lists($reset_args);

		foreach ($res_user_lists as $list ) {
			$reset_status = $this->delete_list_member($list->id, $user_email);
		}

		return(true);
	}
}