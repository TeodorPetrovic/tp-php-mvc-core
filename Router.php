<?php

namespace app\core; //Dodali smo mu namespace. da bi ga posle autoloader automatski ucitao

use app\core\exception\NotFoundException;

class Router
{
    protected array $routes = []; //Prazan Array
    public Request $request;
    public Response $response;

    /**
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this -> request = $request;
        $this -> response = $response;
    }

    public function get($path, $callback)
    {
        $this -> routes['get'][$path] = $callback; //Dobicemo zahtev za neku putanju ovde smo difinisali za get
    }                                              //A callback function is a function that is passed as an argument to another function

    public function post($path, $callback)
    {
        $this -> routes['post'][$path] = $callback;
    }

    /**
     * @throws NotFoundException
     */
    public function resolver()
    {
        $path = $this -> request -> getPath(); //Nabavlja koja je putanja
        $method = $this -> request -> method(); //nabavlja koji je metod
        $callback = $this -> routes[$method][$path] ?? false; //Pravi callback funkciju i stavlja request u routes array, i ako ruta ne postoji stavi false
        if ($callback === false) {
            //Application::$app -> response -> setStatusCode(404); //Objasnjeno u Application.php u konstruktoru
            //return $this -> renderView("_404");
            throw new NotFoundException();
        }

        //ovo nam vraca string a ako bismo koristili controlere treba da nam vrati jedan objekat ove klase
        if (is_string($callback)) {
            return Application::$app -> view -> renderView($callback); //Ovo ce nas callback pretvoriti u view i prikazati korisniku
        }
        if (is_array($callback)) {
            //$callback[0] = new $callback[0](); // To je ime kontrolera tj njegova klassa | VRACA MAN OBJEKAT KLASE
            /** @var Controller $controller */
            $controller = new  $callback[0]();
            Application::$app -> controller = $controller;
            $controller -> action = $callback[1]; //Dodajemo nasem kontroleru i akciju koju radi

            foreach ($controller -> getMiddleWares() as $middleware) {
                $middleware -> execute();
            }

            $callback[0] = $controller;
        } //Ovo bi trabalo podesiti da bismo u siteControleru mogle da koristimo $this -> render// Ali kod mene ne radi

        return call_user_func($callback, $this -> request, $this -> response); //izvrsava ovaj callback funkciju //Dodalismo i $this -. request da nismo prosedili handleContact controleru
        /*echo "<pre>"; // Ovde mozemo da koristimo da bismo ispisali podatke o php serveru koji radi u pozadini sa nekim informacijama
        var_dump($path ili $_SERVER);
        echo "</pre>";
        exit;*/
    }

}