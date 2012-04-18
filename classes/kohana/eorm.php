<?php defined('SYSPATH') or die('No direct script access.');

	/**
	 * Enhanced ORM
	 *
	 * @package    EORM
	 * @author     John Hobbs
	 * @copyright  (c) 2011-2012 John Hobbs
	 * @license    https://github.com/jmhobbs/K3-EORM/blob/master/LICENSE
	 */
	class Kohana_EORM extends Kohana_ORM {

		/**
		 * Place to hold errors.
		 *
		 *     $model->errors = ORM_Validation_Exception->errors();
		 */
		public $errors = array();

		/**
		 * Check if a field has an error on it.
		 *
		 * @param string The name of the column you want to check.
		 * @param string The name of the validation group you want to check
		 * 
		 * @return boolean True if there is an error for this field.
		 */
		public function has_error( $name, $set = null ) {
			return ! is_null( $this->get_error( $name, $set ) );
		}

		/**
		 * Get an error message for this object.
		 *
		 * @param string The name of the column you want to get an error for.
		 * @param string The name of the validation group you want to get an error for (optional)
		 * 
		 * @return mixed Returns the error messge if one exists, or null otherwise.
		 */
		public function get_error( $name, $set = null ) {
			if( ! is_null( $set ) ) {
				$set = arr::get( $this->errors, $set, array() );
				return arr::get( $set, $name, null );
			}
			else {
				return arr::get( $this->errors, $name, null );
			}
		}

		/*!
			Have any of the columns on this model been changed?

			\returns Boolean True if any column has changed.
		*/
		public function has_changed () { return 0 < count( $this->changed() ); }

		/** Anything in an array here will be included in as_array responses. */
		protected $_as_array_include = null;

		/** Anthing in an array here will be excluded from as_array responses. */
		protected $_as_array_exclude = null;

		/** Anything in this array can not be mass assigned with ORM::values */
		protected $_protect_from_mass_assignment = array();

		/**
		 * Alias for [ORM::find_all]
		 */
		public function all() { return $this->find_all(); }

		/**
		 * Alias for [ORM::find]
		 */
		public function first() { return $this->find(); }

		/**
		 * Get a field from the object. If the field does not exist in the object,
		 * this method attempts to call a "get_$field" method as a proxy for the value.
		 */
		public function __get ( $name ) {
			$method = "get_$name";

			if( method_exists( $this, $method ) ) { return $this->$method(); }

			return parent::__get( $name );
		}

		/**
		 * Set a field in the object. If the field does not exist in the object,
		 * this method attempts to call a "set_$field" method as a proxy for the value.
		 */
		public function __set ( $name, $value ) {
			$method = "set_$name";

			if( method_exists( $this, $method ) ) { return $this->$method( $value ); }

			return parent::__set( $name, $value );
		}

		public function __isset ( $name ) {
			$method = "isset_$name";

			if( method_exists( $this, $method ) ) { return $this->$method(); }

			return parent::__isset( $name );
		}

		public function __unset ( $name ) {
			$method = "unset_$name";

			if( method_exists( $this, $method ) ) { return $this->$method(); }

			return parent::__unset( $name );
		}


		/**
		 * Fields to include in all calls to [ORM::as_array].
		 */
		public function as_array_include () {
			return array();
		}

		/**
		 * Fields to exlude in all calls to [ORM::as_array].
		 */
		public function as_array_exclude () {
			return array();
		}

		/**
		 * Get the model represented as an array.
		 *
		 * @param	array	Only these keys will be returned. Overrides all other options.
		 * @param array Include specific data members. Overrides [EORM::as_array_include].
		 * @param array Strip these fields. Overrides [EORM::as_array_exclude].
		*/
		public function as_array ( $only = null, $include = null, $exclude = null ) { 
			$array = parent::as_array();

			$_as_array_include = $this->as_array_include();
			$_as_array_exclude = $this->as_array_exclude();

			if( is_array( $only ) ) {
				foreach( $array as $key => $value ) {
					if( in_array( $key, $only ) ) {
						unset( $only[$key] );
					}
					else {
						unset( $array[$key] );
					}
				}

				foreach( $only as $unfound_key ) {
					$array[$unfound_key] = $this->$unfound_key;
				}
			}
			else {
				if( is_null( $include ) and is_array( $_as_array_include ) ) {
					foreach( $_as_array_include as $key ) {
						$array[$key] = $this->$key;
					}
				}
				if( is_null( $exclude ) and is_array( $_as_array_exclude ) ) {
					foreach( $_as_array_exclude as $key ) {
						unset( $array[$key] );
					}
				}
				if( is_array( $include ) ) {
					foreach( $include as $key ) {
						$array[$key] = $this->$key;
					}
				}
				if( is_array( $exclude ) ) {
					foreach( $exclude as $key ) {
						unset( $array[$key] );
					}
				}
			}

			return $array;
		}

		/**
		 * Fields to prevent mass assignment through [ORM::values].
		 */
		public function protect_from_mass_assignment () {
			return array( $this->primary_key() );
		}

		/**
		 * Set values from an array with support for one-one relationships.  This method should be used
		 * for loading in post data, etc.
		 *
		 * @param  array $values   Array of column => val
		 * @param  array $expected Array of keys to take from $values
		 * @return ORM
		 */
		public function values ( array $values, array $expected = NULL ) {
			// Unless expected is specified, we want to protect the keys from mass assignment
			if( is_null( $expected ) ) {
				$_protect_from_mass_assignment = $this->protect_from_mass_assignment();
				foreach( $values as $key => $value ) {
					if( in_array( $key, $_protect_from_mass_assignment ) ) {
						unset( $values[$key] );
					}
				}
			}
			return parent::values( $values, $expected );
		}


		/**
		 * Scopes that have not been applied to the query.
		 * @var array
		 */
		protected $_scopes_pending = array();

		/**
		 * Names of the default scopes.
		 * @return array
		 */
		public function scopes () {
			return array();
		}

		/**
		 * Apply a scope.
		 * 
		 * @param string Scope name.
		 * @return ORM
		 */
		public function scope ( $name ) {
			$normalized = preg_replace( '/[^a-z_]/', '_', strtolower( $name ) );
			if( ! method_exists( $this, 'scope_' . $normalized ) ) { throw new Kohana_Exception( 'Scope "' . $normalized . '" does not exist in "' . get_class() . '"' ); }
			if( ! in_array( $normalized, $this->_scopes_pending ) ) { $this->_scopes_pending[] = $normalized; }
			return $this;
		}

		/**
		 * Remove a scope.
		 *
		 * @param string Scope name. If null, remove all scopes.
		 * @return ORM
		 */
		public function unscope ( $name = null ) {
			if( is_null( $name ) ) { $this->_scopes_pending = array(); }
			else {
				$normalized = preg_replace( '/[^a-z_]/', '_', strtolower( $name ) ); 
				$this->_scopes_pending[] = array_filter( $this->_scopes_pending, create_function( '$v', 'return $v == \'' . $normalized . '\';' ) );
			}
			return $this;
		}

		protected function _initialize() {
			$this->_scopes_pending = $this->scopes();
			return parent::_initialize();
		}

		protected function _build ( $type ) {
			foreach( $this->_scopes_pending as $scope ) {
				call_user_func( array( $this, 'scope_' . $scope ) );
			}
			$this->_scopes_pending = $this->scopes(); // Reset scopes
			return parent::_build( $type );
		}

	}
