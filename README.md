K3-EORM Module
==============

A Ko3 Module by [**John Hobbs**](http://twitter.com/jmhobbs)

Introduction
------------

This module provides some handy stuff that was left out of the ORM module.

Installation
------------

K3-EORM is a simple, standard module.

1. Drop the source in your MODPATH folder.
2. Add the module to Kohana::modules in your bootstrap.php

Usage
-----

### ActiveRecord-like aliases

The all() method is just and alias for find_all()

    ORM::factory( 'user' )->all();

The first() method is an alias for find()

    ORM::factory( 'user' )->first();

### Functions as properties

Additionally, you can add get methods to EORM models to access methods as properties.

    class Model_Post extends EORM {
    
      public function get_link () {
        return 'the-post-slug';
      }
    
    }
    
    $model = ORM::factory( 'post' );
    
    // Print's "the-post-slug"
    echo $model->get_link();
    
    // Print's "the-post-slug"
    echo $model->link;

### Action/Role based access control with EORM_Auth

The EORM_Auth class allows you to do some basic access controls in conjunction with Auth ORM

    class Model_Post extends EORM_Auth {
    
      protected $auth = array(
        'edit' => 'editor'
      );
    
      public function can_delete ( $user ) {
        return $this->author_id == $user->id;
      }

    }
    
    // In a view...
    if( $post->can( 'edit', Auth::instance()->get_user() ) ) {
      // show edit form
    }
    
    if( $post->can( 'delete', $user ) ) {
      // show delete form
    }

When you call "can( [action], [user] )" on an EORM_Auth object, it checks if the given user can dothe provided action by:

1. Checking for a method named "can_[action]" and calls it if found (it should return boolean )
2. Checking the $auth array for Auth ORM roles, and seeing if the user has them

Tips & Tricks
-------------

### Playing nice with ORM Auth

By default, EORM doesn't get included in the inheritance tree for Model_Auth_User when you are
using ORM Auth.  There is an easy fix for this!

Just create a file at application/classes/orm.php like this one:

    <?php
    
      class ORM extends EORM {}

This works because EORM sub-classes Kohana_ORM instead of just ORM, while Model_Auth_User just 
extends ORM.


