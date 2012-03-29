<?php

class BackComponent extends Object {

    var $components = array('Session',
                            'RequestHandler');
    var $limit = 10;
    var $default = '/';

    function initialize(&$controller,$settings = array()) {
        $this->_set($settings);
        $this->_controller =& $controller;
        $this->params = $controller->params;
    }

    /**
     * beforeRender
     *
     * @param
     */
    function beforeRender(){
        $this->push();
    }

    /**
     * push
     *
     */
    function push(){
        if ($this->RequestHandler->isAjax()) {
            return;
        }
        if ($this->Session->check('Back.start')
            && $this->Session->read('Back.start') === $this->params) {
            $this->back();
        }
        $this->Session->delete('Back.start');
        $history = $this->Session->read('Back.history');
        if (empty($history)) {
            $history = array();
        }

        // Duplicate check
        if (count($history) === 0 || $history[count($history) - 1]  !== $this->params) {
            array_push($history, $this->params);
        }
        while (count($history)  > $this->limit) {
            array_shift($history);
        }
        $this->Session->write('Back.history', $history);
    }

    /**
     * back
     *
     */
    function back($back = 1){
        $this->Session->write('Back.start', $this->params);
        $history = $this->Session->read('Back.history');
        if (count($history) < $back + 1) {
            $this->Session->delete('Back.start');
            $this->_controller->redirect($this->default);
            return;
        }
        array_pop($history);
        for ($i = 0; $i < $back; $i++) {
            $redirect = array_pop($history);
        }
        $this->Session->write('Back.history', $history);
        if ($redirect === $this->params) {
            $this->back();
        }
        if (!empty($redirect['url']['url'])) {
            $url = '/' . $redirect['url']['url'];
        } else {
            unset($redirect['url']);
            $pass = empty($redirect['pass']) ? array() : $redirect['pass'];
            unset($redirect['pass']);
            unset($redirect['form']);
            $named = empty($redirect['named']) ? array() : $redirect['named'];
            unset($redirect['named']);
            $url = '/' . preg_replace('#' . Router::url('/') . '#', '', Router::url($redirect));
            foreach ($pass as $value) {
                $url .= '/' . $value;
            }
            foreach ($named as $key => $value) {
                $url .= '/' . $key . ':' . $value;
            }
        }
        $this->_controller->redirect($url);
    }
}