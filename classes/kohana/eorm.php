<?php defined('SYSPATH') or die('No direct script access.');

	/**
		This class allows for the addition of get_[name] and set_[name]
		elements to be used automatically for non-extant attributes.

		For example:

			// In a view
			echo $user->link;

			// In an EORM class:
			public function get_link () { return '/users/' . $this->username; }

		Get it?

	**/
	class Kohana_EORM extends Kohana_ORM {

		//! Anything in an array here will be included in as_array responses.
		protected $_as_array_include = null;
		//! Anthing in an array here will be excluded from as_array responses.
		protected $_as_array_exclude = null;

		public function all() { return $this->find_all(); }

		public function first() { return $this->find(); }

		public function __get ( $name ) {
			$method = "get_$name";

			if( method_exists( $this, $method ) ) { return $this->$method(); }

			return parent::__get( $name );
		}

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

		/*!
			This is the old as_array function, but using some custom options.

			\param $only If set to an array, only those keys will be returned. Overrides all other options.
			\param $include Set to an array to include specific data members (essentially only used for get_method members). Overrides _as_array_include.
			\param $exclude Set to an array of key values to strip from the result. Overrides _as_array_exclude.
		*/
		public function as_array ( $only = null, $include = null, $exclude = null ) { 
			$array = parent::as_array();

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
				if( is_null( $include ) and is_array( $this->_as_array_include ) ) {
					foreach( $this->_as_array_include as $key ) {
						$array[$key] = $this->$key;
					}
				}
				if( is_null( $exclude ) and is_array( $this->_as_array_exclude ) ) {
					foreach( $this->_as_array_exclude as $key ) {
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

	}
