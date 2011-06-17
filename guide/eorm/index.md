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

The all() method is just and alias for find_all()

    ORM::factory( 'user' )->all();

The first() method is an alias for find()

    ORM::factory( 'user' )->first();

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

