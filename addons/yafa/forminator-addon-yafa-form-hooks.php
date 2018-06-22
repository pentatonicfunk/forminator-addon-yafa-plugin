<?php

class Forminator_Addon_Yafa_Form_Hooks extends Forminator_Addon_Form_Hooks_Abstract {

	/**
	 * Addon instance
	 *
	 * @var Forminator_Addon_Yafa
	 */
	protected $addon;

	/**
	 * Form settings instance
	 *
	 * @var null
	 *
	 */
	protected $form_settings_instance;

	/**
	 * @var WP_User
	 */
	protected $wp_user;

	/**
	 * Available fieds on the form
	 *
	 * @var array
	 */
	protected $form_fields;

	/**
	 * Which fields should be disabled on edit
	 * - you can make it dynamic or configurable using @see Forminator_Addon_Form_Settings_Abstract
	 *
	 * @var array
	 */
	protected $disabled_edit_fields
		= array(
			'name-1',
		);

	protected $is_edit_mode = false;

	/**
	 * @var bool|Forminator_Form_Entry_Model
	 */
	protected $entry_for_edit = false;

	/**
	 * On initialization we do :
	 * - Get Form
	 * - Get Form Fields
	 *
	 * @param Forminator_Addon_Abstract $addon
	 * @param                           $form_id
	 *
	 * @throws Forminator_Addon_Exception
	 */
	public function __construct( Forminator_Addon_Abstract $addon, $form_id ) {
		parent::__construct( $addon, $form_id );
		$this->wp_user                    = wp_get_current_user();
		$this->_submit_form_error_message = __( 'YAFA failed to process submitted data. Please check your form and try again', Forminator::DOMAIN );

		// getting form fields by API
		/** @var Forminator_Custom_Form_Model $custom_form */
		$custom_form          = Forminator_API::get_form( $form_id );
		$this->form_fields    = forminator_addon_format_form_fields( $custom_form );
		$this->entry_for_edit = $this->get_entry_for_edit();

	}

	/**
	 * - Protect Form
	 * - Display if the form correctly using this addon
	 * - wp_die when user not logged in
	 * - pre-fill input by JS
	 */
	public function on_before_render_form_fields() {
		$header_html = '';
		if ( ! $this->wp_user->ID ) {
			// die or you can redirect to login page, etc
			wp_die( 'Please make sure you are registered and logged in to view this form' );
		} else {
			// indicator only
			$header_html = "<h3>This form is using YAFA Addon</h3>";

			// enqueue script for pre-fill and disable fields
			wp_enqueue_script( 'yafa-inputs', forminator_addon_yafa_assets_url() . 'js/inputs.js', array( 'forminator-front-scripts' ), $this->addon->get_version() );
		}

		echo $header_html;
	}

	/**
	 * Render extra fields after all forms fields rendered
	 * what it generated :
	 * - WP_USER_ID : demonstrate capability to add custom fields / input
	 *
	 */
	public function on_after_render_form_fields() {
		$this->render_wp_user_id_field();
	}

	/**
	 * Create Pre-fill configuration for JS on a hidden div,
	 * then will be retrieved by $.data()
	 */
	public function on_after_render_form() {
		?>
		<div style="display: none;" class="forminator-yafa-inputs" data-is-edit="<?php echo esc_attr( $this->is_edit_mode ); ?>">
			<?php foreach ( $this->get_pre_filled_fields() as $field_id => $field_props ) : ?>
				<div class="forminator-yafa-field-pre-fill"
				     data-field-id="<?php echo esc_attr( $field_id ); ?>"
				     data-field-value="<?php echo esc_attr( $field_props['value'] ); ?>"
				     data-field-type="<?php echo esc_attr( $field_props['type'] ); ?>"></div>
			<?php endforeach; ?>

			<?php foreach ( $this->get_disabled_fields_config() as $disabled_field => $field_props ) : ?>
				<div class="forminator-yafa-field-disable"
				     data-field-id="<?php echo esc_attr( $disabled_field ); ?>"
				     data-field-value="<?php echo esc_attr( $field_props['value'] ); ?>"
				     data-field-type="<?php echo esc_attr( $field_props['type'] ); ?>"></div>
			<?php endforeach; ?>

		</div>
		<?php
	}

	/**
	 * Find Entry Model  of current user if exist, else return false
	 *
	 * @return bool|Forminator_Form_Entry_Model
	 */
	public function get_entry_for_edit() {
		global $wpdb;

		$entry_meta_table = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$prepared         = $wpdb->prepare(
			"SELECT entry_id from {$entry_meta_table} where meta_key = %s and meta_value = %s limit 1",
			array(
				'forminator_addon_my_yafa_find_pattern',
				$this->form_id . '|' . $this->wp_user->ID,
			)
		);
		$entry_id         = $wpdb->get_var( $prepared );
		if ( $entry_id ) {
			$entry = Forminator_API::get_entry( $this->form_id, $entry_id );
			if ( $entry instanceof Forminator_Form_Entry_Model ) {
				$this->is_edit_mode = true;

				return $entry;
			}
		}

		return false;
	}

	/**
	 * Demonstrate the we can disable fields
	 * on this example,
	 * - get_entry_for_edit
	 * - we use $this->disabled_edit_fields as base configuration, although you can make it dynamic using option or addon form settings
	 * - field only disabled when it has value on our entry model, otherwise it will be editable
	 *
	 * @return array
	 */
	public function get_disabled_fields_config() {
		$entry = $this->entry_for_edit;

		$disabled_edits = array();
		if ( $entry ) {
			foreach ( $this->disabled_edit_fields as $disabled_edit_field ) {
				// make sure its exists on entry meta data
				if ( isset( $entry->meta_data[ $disabled_edit_field ] )
				     && isset( $entry->meta_data[ $disabled_edit_field ]['value'] )
				     && ! empty( $entry->meta_data[ $disabled_edit_field ] )
				     && ! empty( $entry->meta_data[ $disabled_edit_field ]['value'] )
				) {
					// only disable when value there, if its not there
					$disabled_field = $this->get_field( $disabled_edit_field );
					$field_type     = isset( $disabled_field['type'] ) ? $disabled_field['type'] : '';
					$field_label    = isset( $disabled_field['field_label'] ) ? $disabled_field['field_label'] : '';

					$disabled_edits[ $disabled_edit_field ] = array(
						'value' => $entry->meta_data[ $disabled_edit_field ]['value'],
						'type'  => $field_type,
						'label' => $field_label,
					);
				}

			}
		}

		return $disabled_edits;
	}

	/**
	 * Build pre filled fields which utilize get_entry_for_edit
	 *
	 * @return array
	 */
	public function get_pre_filled_fields() {
		$entry = $this->entry_for_edit;

		$pre_filled_values = array();
		if ( $entry ) {
			foreach ( $this->form_fields as $form_field ) {
				// make sure its exists on entry meta data
				$element_id = $form_field['element_id'];
				if ( isset( $entry->meta_data[ $element_id ] )
				     && isset( $entry->meta_data[ $element_id ]['value'] )
				     && ! empty( $entry->meta_data[ $element_id ] )
				     && ! empty( $entry->meta_data[ $element_id ]['value'] )
				) {
					$pre_filled_values[ $element_id ] = array(
						'value' => $entry->meta_data[ $element_id ]['value'],
						'type'  => $form_field['type'],
						'label' => $form_field['field_label'],
					);
				}

			}
		}

		return $pre_filled_values;
	}

	/**
	 * render hidden field of wp user id
	 */
	private function render_wp_user_id_field() {
		$html = '';
		if ( isset( $this->wp_user->ID ) ) {
			$html = '<input type="hidden" name="yafa_addon_wp_user_id" value="' . $this->wp_user->ID . '">';
		}


		echo $html;// WPCS: XSS ok. html output intended
	}

	/**
	 * Demonstrate custom validation / conditions
	 * - validate yafa_addon_wp_user_id
	 * - validate 'text-1' field
	 *
	 * @param $submitted_data
	 *
	 * @return bool
	 */
	public function on_form_submit( $submitted_data ) {
		try {
			if ( ! isset( $submitted_data['yafa_addon_wp_user_id'] ) || empty( $submitted_data['yafa_addon_wp_user_id'] ) ) {
				throw new Forminator_Addon_Yafa_Exception( 'Invalid User detected, robot ?' );
			}

			// validate
			if ( (int) $this->wp_user->ID !== (int) $submitted_data['yafa_addon_wp_user_id'] ) {
				throw new Forminator_Addon_Yafa_Exception( 'Invalid User detected, robot ?' );
			}

			/**
			 * its not REQUIRED on the BUILDER, but we can OVERRIDE it here
			 * - we can mark a lot of fields as not required on builder, so it can be saved anyway
			 * - this way, we can retrieve saved entry later by `yafa_addon_wp_user_id`
			 */

			if ( ! isset( $submitted_data['text-1'] ) || empty( $submitted_data['text-1'] ) ) {
				$field_label = $this->get_field_label( 'text-1' );
				throw new Forminator_Addon_Yafa_Exception( 'Please fill field : ' . $field_label );
			}


			return true;
		} catch ( Forminator_Addon_Yafa_Exception $e ) {
			// send this to Forminator Code
			$this->_submit_form_error_message = $e->getMessage();

			return false;
		}
	}

	/**
	 * The fun part
	 * - save yafa_addon_wp_user_id to Forminator Entry Meta Data for new entry
	 * - update entry meta data when its already exist
	 *
	 * @param array $submitted_data
	 *
	 * @return array
	 */
	public function add_entry_fields( $submitted_data ) {
		global $wpdb;

		$entry_meta_table = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );

		// its ensured to be exist by on_form_submit
		$wp_user_id = $this->wp_user->ID;

		$entry = $this->entry_for_edit;

		/**
		 * // ITS EDIT MODE!!!
		 */
		if ( $entry ) {
			// UPDATE OLD META
			foreach ( $this->form_fields as $form_field ) {
				$element_id = $form_field['element_id'];
				if ( isset( $submitted_data[ $element_id ] ) && ! empty( $submitted_data[ $element_id ] ) ) {
					// new field value introduced, probably not filled on previous submission
					if ( ! isset( $entry->meta_data[ $element_id ] ) ) {
						$wpdb->insert(
							$entry_meta_table,
							array(
								'entry_id'     => $entry->entry_id,
								'meta_key'     => $element_id,
								'meta_value'   => $submitted_data[ $element_id ],
								'date_created' => date_i18n( 'Y-m-d H:i:s' ),
							)
						);
					}
				}
			}

			foreach ( $entry->meta_data as $field_id => $meta ) {
				// update existing meta data
				if ( ! in_array( $field_id, $this->disabled_edit_fields, true ) ) {
					$meta_id = $meta['id'];
					if ( isset( $submitted_data[ $field_id ] ) && ! empty( $submitted_data[ $field_id ] ) ) {
						$value = wp_unslash( $submitted_data[ $field_id ] );
						$value = maybe_serialize( $value );
						$entry->update_meta( $meta_id, $field_id, $value ); // probably performance issue
					}
				}
			}

			return array();

		} else {
			// new ENTRY!!
			// save the user!
			// we can save multiple info here
			return array(
				array(
					'name'  => 'wp_user_id',
					'value' => $wp_user_id,
				),
				array(
					'name'  => 'form_id', // for future reference
					'value' => $this->form_id,
				),
				array(
					'name'  => 'find_pattern', // for future reference on finding entry
					'value' => $this->form_id . '|' . $wp_user_id,
				),
				// example add another info
				//			array(
				//				'name'  => 'is_approved',
				//				'value' => false,
				//			),
			);
		}


	}

	/**
	 * Another fun part
	 * - Display wp User on entry page (wp-admin/admin.php?page=forminator-entries&form_type=forminator_forms&form_id=260)
	 *
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 * @param array                       $addon_meta_data
	 *
	 * @return array
	 */
	public function on_render_entry( Forminator_Form_Entry_Model $entry_model, $addon_meta_data ) {
		$wp_user_id = 0;

		// we have multiple data here
		foreach ( $addon_meta_data as $addon_meta_datum ) {
			if ( isset( $addon_meta_datum['name'] ) && 'wp_user_id' === $addon_meta_datum['name'] ) {
				$wp_user_id = $addon_meta_datum['value'];
			}
		}

		return array(
			array(
				'label'       => 'YAFA Addon',
				'value'       => '-',
				'sub_entries' => array(
					array(
						'label' => 'WP user ID',
						'value' => $wp_user_id,
					),
					array(
						'label' => 'WP username',
						'value' => $this->get_user_property_from_id( $wp_user_id, 'user_login', 'Not Found' ),
					),
					array(
						'label' => 'WP user nice name',
						'value' => $this->get_user_property_from_id( $wp_user_id, 'user_nicename', 'Not Found' ),
					),
					array(
						'label' => 'WP user email',
						'value' => $this->get_user_property_from_id( $wp_user_id, 'user_email', 'Not Found' ),
					),
				),
			),

		);
	}

	/**
	 * More fun part :
	 * - Delete new entry when its EDIT Mode, so it wont duplicate
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 */
	public function after_entry_saved( Forminator_Form_Entry_Model $entry_model ) {
		$old_entry = $this->entry_for_edit;

		if ( $old_entry ) {
			// delete new entry, so it wont duplicate
			$entry_model->delete();
		}

	}

	/**
	 * Helper get user property from id
	 *
	 * @param        $user_id
	 * @param string $property
	 * @param string $fallback
	 *
	 * @return mixed|string
	 */
	public function get_user_property_from_id( $user_id, $property = 'user', $fallback = '' ) {
		$user = get_user_by( 'ID', $user_id );
		if ( false === $user ) {
			return $fallback;
		}

		if ( ! isset( $user->$property ) ) {
			return $fallback;
		}

		return $user->$property;
	}

	/**
	 * helper get field
	 *
	 * @param $field_id
	 *
	 * @return array
	 */
	private function get_field( $field_id ) {
		foreach ( $this->form_fields as $field ) {
			if ( $field['element_id'] === $field_id ) {
				return $field;
			}
		}

		return array();
	}

	/**
	 * helper get field label
	 *
	 * @param $field_id
	 *
	 * @return mixed|string
	 */
	private function get_field_label( $field_id ) {
		$field = $this->get_field( $field_id );
		if ( isset( $field['field_label'] ) ) {
			return $field['field_label'];
		}

		return '';
	}
}
