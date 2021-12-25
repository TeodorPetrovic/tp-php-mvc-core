<?php

namespace app\core; //Dodali smo mu namespace. da bi ga posle autoloader automatski ucitao

use app\core\db\Database;
use app\core\db\DbModel;

class Application
{
    public static string $ROOT_DIR;
    public Router $router; //Morao sam da predjem na php 7.4 model teksta da bi aplikacija razumela sta da radi
    public Request $request;
    public Response $response;
    public Session $session; //Pravlja i sesije
    public Database $db; //Pravimo konekciju ka bazu
    public ?UserModel $user; //User might be null, when just browsing the website
    public string $userClass;
    public string $layout = 'main_layout';
    public View $view;

    public static Application  $app;
    public ?Controller $controller = null;

    public function __construct($rootPath, array $config) //Koristimo ga da bi prosledili parametre za povezivanje na bazu ili bilo koje druge parametre u buducnosti
    {
        self::$ROOT_DIR = $rootPath; //Napravili smo staticki zapis naseg root direktorijuma naseg projekta da ne bismo stalno koristili __DIR__ funkciju
        self::$app = $this; //Ja aplikaciju prosledjujem samoj sebe da bi posle u Route clasi kod metode resolvera direktno aplikaciji prosledio respone sa error codom 404
        $this -> request = new Request();//Prvo napravimo jedan request, a onda ga prosledjujemo routeru
        $this -> response = new Response();
        $this -> session = new Session();
        $this -> router = new Router($this -> request, $this ->response); //Zada samo ne znam da li mozemo da izbacimo ovu staticu instancu same aplikacije ali vidi posle

        $this -> db = new Database($config['db']);
        $this -> userClass = $config['userClass'];
        $primaryValue = $this -> session -> get('user');
        if ($primaryValue) {
            $primaryKey = $this-> userClass::primaryKey();
            $this -> user = $this -> userClass::findOne([$primaryKey => $primaryValue]);
        } else {
            $this -> user = null;
        }

        $this -> view = new View();
    }

    public function run()
    {
        //$this -> router -> resolver(); //izmenili smo u metodi resolver da je return a ovde echo, da tamo nebismo pisali echo sto puta
        try {
            echo $this -> router -> resolver();
        } catch (\Exception $e) {
            $this -> response -> setStatusCode($e -> getCode());
            echo $this -> view -> renderView('_error', [
                'exception' => $e
            ]);
        }
    }

    /**
     * @return Controller
     */
    public function getController(): Controller
    {
        return $this->controller;
    }

    /**
     * @param Controller $controller
     */
    public function setController(Controller $controller): void
    {
        $this->controller = $controller;
    }

    public function login(UserModel $user): bool
    {
        $this -> user = $user;
        $primaryKey = $user::primaryKey();
        $primaryValue = $user -> {$primaryKey};
        $this -> session -> set('user', $primaryValue);//Namesti u sessiju korisnika

        return true;
    }

    public function logout()
    {
        $this -> user = null;
        $this -> session -> remove('user');
    }

    public static function isGuest(): bool
    {
        return !self::$app -> user;
    }
}