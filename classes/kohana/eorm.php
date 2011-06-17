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
	class Kohana_EORM extends ORM {

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

	}
