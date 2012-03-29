<?php

App::import('Controller', array('Component', 'Controller'), false);
App::import('Component', 'Back.Back');

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

    function start() {
        parent::start();
        ClassRegistry::config(array('table' => false));
        $this->__loadController();
        $this->Controller->Back->Session->delete('Back');
    }

    function end() {
        $this->Controller->Back->Session->delete('Back');
        $this->__shutdownController();
        parent::end();
    }

    /**
     * startTest
     *
     */
    public function startTest(){
        $this->Controller->Back->Session->delete('Back');
    }

    function __loadController($params = array()) {
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
        $Controller = new $controllerName();
        $Controller->params = array(
                                    'controller' => $Controller->name,
                                    'action' => 'test_action',
                                    );
        $Controller->params = array_merge($Controller->params, $params);
        $Controller->constructClasses();
        $Controller->Component->initialize($Controller);
        $Controller->beforeFilter();
        $Controller->Component->startup($Controller);
        $this->Controller =& $Controller;
    }

    function __shutdownController() {
        $this->Controller->Component->shutdown($this->Controller);
    }

    /**
     * testPush
     *
     */
    public function testPush(){
        $this->__loadController(array(
                                      'action' => 'test_action',
                                      ));
        $this->Controller->Back->push();
        $result = $this->Controller->Back->Session->read('Back.history');
        $expected = array(array(
                                'controller' => $this->Controller->name,
                                'action' => 'test_action',
                                ));
        $this->assertIdentical($result, $expected);

        $this->__loadController(array(
                                      'action' => 'test_action2',
                                      ));
        $this->Controller->Back->push();
        $result = $this->Controller->Back->Session->read('Back.history');
        $expected = array(array(
                                'controller' => $this->Controller->name,
                                'action' => 'test_action',
                                ),
                          array(
                                'controller' => $this->Controller->name,
                                'action' => 'test_action2',
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
        $expected = array(array(
                                'controller' => $this->Controller->name,
                                'action' => 'test_action',
                                ));
        $this->assertIdentical($result, $expected);

        $this->__loadController(array(
                                      'action' => 'test_action',
                                      ));
        $this->Controller->Back->push();
        $result = $this->Controller->Back->Session->read('Back.history');
        $expected = array(array(
                                'controller' => $this->Controller->name,
                                'action' => 'test_action',
                                ),
                          );
        $this->assertIdentical($result, $expected);

        $this->__loadController(array(
                                      'action' => 'test_action2',
                                      ));
        $this->Controller->Back->push();
        $result = $this->Controller->Back->Session->read('Back.history');
        $expected = array(array(
                                'controller' => $this->Controller->name,
                                'action' => 'test_action',
                                ),
                          array(
                                'controller' => $this->Controller->name,
                                'action' => 'test_action2',
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
        $expected = array(array(
                                'controller' => $this->Controller->name,
                                'action' => 'test_action',
                                ));
        $this->assertIdentical($result, $expected);

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->__loadController(array(
                                      'action' => 'test_ajax',
                                      ));
        $this->Controller->Back->push();
        $result = $this->Controller->Back->Session->read('Back.history');
        $expected = array(array(
                                'controller' => $this->Controller->name,
                                'action' => 'test_action',
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