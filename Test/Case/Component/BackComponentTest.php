<?php

App::uses('Controller', 'Controller');
App::uses('Component', 'Back.Back');
App::uses('SessionComponent', 'Controller/Component');
App::uses('Router', 'Routing');
session_start(); // http://mindthecode.com/using-sessions-in-phpunit-tests-with-cakephp/
class BackComponentTestController extends Controller {
    var $name = 'BackComponentTest';
    var $components = array('Back.Back');
    var $uses = array();

    var $redirectTo  =  null;

    function redirect($url) {
        $this->redirectTo = Router::url($url);
        return true;
    }
}

class BackComponentTest extends CakeTestCase {
    var $Controller = null;
    var $fixtures = array();

    function setUp() {
        App::build();
        Router::reload();
        $this->__loadController();
        $this->Controller->Back->Session->delete('Back');
    }

    function tearDown() {
        App::build();
        $this->Controller->Back->Session->delete('Back');
        $this->__shutdownController();
    }

    /**
     * startTest
     *
     */
    public function startTest($method = null){
        parent::startTest($method);
        $this->__loadController();
        $this->Controller->Back->Session->delete('Back');
    }

    /**
     * endTest
     *
     */
    public function endTest($method = null){
        $this->__shutdownController();
        parent::endTest($method);
    }

    /**
     * __loadController
     *
     */
    private function __loadController($params = array()) {
        $this->ComponentCollection = new ComponentCollection();

        if ($this->Controller !== null) {
            $this->__shutdownController();
            unset($this->Controller);
        }

        $controllerName = 'Test';
        if (!empty($params['controller'])) {
            $controllerName = $params['controller'];
            unset($params['controller']);
        }

        $controllerName = 'BackComponent' . $controllerName . 'Controller';
        $Request = new CakeRequest(null, false);
        $Controller = new $controllerName($Request);
        $Request->addParams(array(
                                  'controller' => $Controller->name,
                                  'action' => 'test_action',
                                  ))->addParams($params);
        $Controller = new $controllerName($Request);

        $Controller->constructClasses();
        $Controller->Components->trigger('initialize', array($Controller));
        $this->Controller = $Controller;
        $this->sessionBaseKey = "Back." . Inflector::underscore($Controller->name);
    }

    /**
     * __shutdownController
     *
     */
    function __shutdownController() {
        $this->Controller->shutdownProcess();
    }

    /**
     * testPush
     *
     * jpn:BackComponent::push()でページ表示履歴をセッション保存できる
     */
    public function testPush(){
        $this->__loadController(array(
                                      'action' => 'test_action',
                                      ));
        $this->Controller->Back->push();
        $result = $this->Controller->Back->Session->read('Back.history');
        unset($result[0]['url']);
        $expected = array(array(
                                'plugin' => null,
                                'controller' => $this->Controller->name,
                                'action' => 'test_action',
                                'named' => array(),
                                'pass' => array(),
                                ));
        $this->assertIdentical($result, $expected);

        $this->__loadController(array(
                                      'action' => 'test_action2',
                                      ));
        $this->Controller->Back->push();
        $result = $this->Controller->Back->Session->read('Back.history');
        unset($result[0]['url']);
        unset($result[1]['url']);
        $expected = array(array(
                                'plugin' => null,
                                'controller' => $this->Controller->name,
                                'action' => 'test_action',
                                'named' => array(),
                                'pass' => array(),
                                ),
                          array(
                                'plugin' => null,
                                'controller' => $this->Controller->name,
                                'action' => 'test_action2',
                                'named' => array(),
                                'pass' => array(),
                                ),
                          );
        $this->assertIdentical($result, $expected);
    }

    /**
     * testDuplicatePush
     *
     * jpn:直近と同じ履歴は保持しない
     */
    public function testDuplicatePush(){
        $this->__loadController(array(
                                      'action' => 'test_action',
                                      ));
        $this->Controller->Back->push();
        $result = $this->Controller->Back->Session->read('Back.history');
        unset($result[0]['url']);
        $expected = array(array(
                                'plugin' => null,
                                'controller' => $this->Controller->name,
                                'action' => 'test_action',
                                'named' => array(),
                                'pass' => array(),
                                ));
        $this->assertIdentical($result, $expected);

        $this->__loadController(array(
                                      'action' => 'test_action',
                                      ));
        $this->Controller->Back->push();
        $result = $this->Controller->Back->Session->read('Back.history');
        unset($result[0]['url']);
        $expected = array(array(
                                'plugin' => null,
                                'controller' => $this->Controller->name,
                                'action' => 'test_action',
                                'named' => array(),
                                'pass' => array(),
                                ),
                          );
        $this->assertIdentical($result, $expected);

        $this->__loadController(array(
                                      'action' => 'test_action2',
                                      ));
        $this->Controller->Back->push();
        $result = $this->Controller->Back->Session->read('Back.history');
        unset($result[0]['url']);
        unset($result[1]['url']);
        $expected = array(array(
                                'plugin' => null,
                                'controller' => $this->Controller->name,
                                'action' => 'test_action',
                                'named' => array(),
                                'pass' => array(),
                                ),
                          array(
                                'plugin' => null,
                                'controller' => $this->Controller->name,
                                'action' => 'test_action2',
                                'named' => array(),
                                'pass' => array(),
                                ),
                          );
        $this->assertIdentical($result, $expected);
    }

    /**
     * testAjaxPush
     *
     * jpn: Ajaxアクションは履歴に加えない
     */
    public function testAjaxPush(){
        $this->__loadController(array(
                                      'action' => 'test_action',
                                      ));
        $this->Controller->Back->push();
        $result = $this->Controller->Back->Session->read('Back.history');
        unset($result[0]['url']);
        $expected = array(array(
                                'plugin' => null,
                                'controller' => $this->Controller->name,
                                'action' => 'test_action',
                                'named' => array(),
                                'pass' => array(),
                                ));
        $this->assertIdentical($result, $expected);

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->__loadController(array(
                                      'action' => 'test_ajax',
                                      ));
        $this->Controller->Back->push();
        $result = $this->Controller->Back->Session->read('Back.history');
        unset($result[0]['url']);
        $expected = array(array(
                                'plugin' => null,
                                'controller' => $this->Controller->name,
                                'action' => 'test_action',
                                'named' => array(),
                                'pass' => array(),
                                ));
        $this->assertIdentical($result, $expected);
    }

    /**
     * testBack
     *
     */
    public function testBack(){
        $this->__loadController(array(
                                      'action' => 'test_action',
                                      ));
        $this->Controller->Back->push();
        $this->__loadController(array(
                                      'action' => 'test_action2',
                                      ));
        $this->Controller->Back->push();

        $this->Controller->Back->back();

        $result = $this->Controller->redirectTo;
        $expected = '/' . $this->Controller->name . '/test_action';
    }
}