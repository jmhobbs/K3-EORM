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

### Methods as properties

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

### A more capable as_array

As array has been given three new, optional arguments: $only, $include, and $exclude.

If you call as_array on an object with $only set to an array of property names, only those 
properties will be returned in the array. This also applies to properties implemented with
get_method's.  Providing this option overrides all the other output.

If you call as_array with $include set to an array, you will add those properties to the array.
This is mostly only useful to include properties implemented by methods.

If you call as_array with $exclude set to an array, those properties will be excluded.

Additionally, there are two new properties, $_as_array_include and $_as_array_exclude.  These
behave as default values for $include and $exclude respectively.

The order of priority for processing is as follows, with the most authoratative on top:

1. $only
2. $exclude
3. $include
4. $this->_as_array_exclude
5. $this->_as_array_include

#### Examples

    //  CREATE TABLE `posts` (
    //    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    //    `title` varchar(255) NOT NULL,
    //    `body` text NOT NULL,
    //    PRIMARY KEY  ( `id` )
    //  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    class Model_Post extends EORM {

      protected $_as_array_exclude = array( 'id' );

      public function get_slug () { return url::title( $this->title ); }

    }

    $post = ORM::factory( 'post' );
    $post->title = 'Test Post';
    $post->body = 'Hello, world!';
    $post->save();
    $post->reload();

    // [ 'title' => 'Test Post', 'body' => 'Hello, world!' ]
    $post->as_array();

    // [ 'id' => 1 ]
    $post->as_array( array( 'id' ) );

    // [ 'id' => 1, 'slug' => 'test-post', 'title' => 'Test Post', 'body' => 'Hello, world!' ]
    $post->as_array( null, array( 'id', 'slug' ) );

    // [ 'slug' => 'test-post', 'title' => 'Test Post' ]
    $post->as_array( null, array( 'slug' ), array( 'body' ) );

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


