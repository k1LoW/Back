<?php
App::uses('Component', 'Controller');
class BackComponent extends Component {

    public $components = array(
        'Session',
        'RequestHandler'
    );
    public $limit = 10;
    private $default = array(
        'defaultRedirect' => '/',
        'blacklist' => array()
    );

    /**
     * __construct
     *
     */
    public function __construct(ComponentCollection $collection, $settings = array()) {
        $this->settings = Set::merge($this->default, $this->settings);
        $this->_set($settings);
        $this->Controller = $collection->getController();
        $this->params = $this->Controller->params->params;
        $this->params['url'] = $this->Controller->params->url;
        parent::__construct($collection, $settings);
    }

    /**
     * beforeRender
     *
     * @param
     */
    public function beforeRender(Controller $controller){
        $this->push();
    }

    /**
     * push
     *
     */
    public function push(){
        if ($this->RequestHandler->isAjax()) {
            return;
        }
        // Blacklist check
        foreach ($this->settings['blacklist'] as $list) {
            foreach ($list as $key => $value) {
                if (empty($this->params[$key])) {
                    continue;
                }
                if ($this->params[$key] === $value) {
                    return;
                }
            }
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
    public function back($back = 1){
        $this->Session->write('Back.start', $this->params);
        $history = $this->Session->read('Back.history');
        if (count($history) < $back + 1) {
            $this->Session->delete('Back.start');
            $this->Controller->redirect($this->settings['defaultRedirect']);
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
        if (!empty($redirect['url'])) {
            $url = '/' . $redirect['url'];
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
        $this->Controller->redirect($url);
    }

    /**
     * clear
     *
     *
     */
    public function clear(){
        $this->Session->delete('Back');
    }
}