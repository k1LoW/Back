# Session base `history back' plugin for CakePHP #

[![Build Status](https://secure.travis-ci.org/k1LoW/Back.png?branch=master)](http://travis-ci.org/k1LoW/Back)

## Install

First, Install 'Back' by [recipe.php](https://github.com/k1LoW/recipe) , and set `CakePlugin::load('Back');`

Second, add the following code in AppController.php.

    <?php
       class AppController extends Controller {
           public $components = array('Back.Back');
       }

And see test case!

## License
MIT License
