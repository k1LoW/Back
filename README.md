# Session base `history back' plugin for CakePHP #

## Install

First, Install 'DebugKitShortcut' by [recipe.php](https://github.com/k1LoW/recipe) , and set `CakePlugin::load('Back');`

Second, add the following code in AppController.php.

    <?php
       class AppController extends Controller {
           var $components = array('Back.Back');
       }

And see test case!

## License
MIT License
