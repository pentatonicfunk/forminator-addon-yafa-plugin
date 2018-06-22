<?php

final class Forminator_Addon_Yafa extends Forminator_Addon_Abstract {

	private static $_instance = null;

	protected $_slug                   = 'my_yafa';
	protected $_version                = FORMINATOR_ADDON_YAFA_VERSION;
	protected $_min_forminator_version = '1.2';
	protected $_short_title            = 'YetAnotherForminatorAddon';
	protected $_title                  = 'YetAnotherForminatorAddon';
	protected $_url                    = 'http://premium.wpmudev.org';
	protected $_full_path              = __FILE__;
	protected $_icon                   = '';
	protected $_icon_x2                = '';
	protected $_image                  = '';
	protected $_image_x2               = '';

	/**
	 * Class name of form hooks
	 *
	 * @var string
	 */
	protected $_form_hooks = 'Forminator_Addon_Yafa_Form_Hooks';

	/**
	 * Make FORM with ID 257 have this addon auto connected
	 * it should go on form settings
	 */
	const FORM_ID_TO_ACTIVATE = 260;

	public function __construct() {
		// late init to allow translation
		$this->_description                = __( 'Make your form YAFA-able', Forminator::DOMAIN );
		$this->_activation_error_message   = __( 'Sorry but we failed to activate YAFA Integration, don\'t hesitate to contact us', Forminator::DOMAIN );
		$this->_deactivation_error_message = __( 'Sorry but we failed to deactivate YAFA Integration, please try again', Forminator::DOMAIN );

		$this->_update_settings_error_message = __(
			'Sorry, we are failed to update settings, please check your form and try again',
			Forminator::DOMAIN
		);
	}

	/**
	 * @return self|null
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Flag for check if and addon connected (global settings suchs as api key complete)
	 *
	 * @return bool
	 */
	public function is_connected() {
		// Lets mark it as available for all forms, although you can go crazy with it
		if ( ! $this->is_active() ) {
			Forminator_Addon_Loader::get_instance()->activate_addon( $this->_slug );
		}

		return true;
	}

	/**
	 * Flag for check if and addon connected to a form(form settings suchs as list name completed)
	 *
	 * @param $form_id
	 *
	 * @return bool
	 */
	public function is_form_connected( $form_id ) {
		if ( self::FORM_ID_TO_ACTIVATE === (int) $form_id ) {
			return true;
		}

		return false;

	}
}
