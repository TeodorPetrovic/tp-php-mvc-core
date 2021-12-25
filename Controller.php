<?php

namespace app\core;

use app\core\middlewares\BaseMiddleware;

class Controller
{
    public string $layout = 'main_layout'; //Default vrednost ce biti main_layout
    public string $action = '';
    /**
     * @var array \app\core\middlewares\BaseMiddleware[]
     */
    protected array $middleWares = [];

    public function setLayout($layout) { //On ce nam prikazivati layout jer imamo layout sa korisnike koji su vec ulogovani i za one koji nisi, ono koji imaju neke privilegije i one koje nemaju
        $this -> layout = $layout;
    }

    public static function render($view, $params = [])
    {
        return Application::$app -> view -> renderView($view, $params);
    }

    public function registerMiddleware(BaseMiddleware $middleware)
    {
        $this -> middleWares[] = $middleware;
    }

    /**
     * @return array
     */
    public function getMiddleWares(): array
    {
        return $this->middleWares;
    }
}