<?php

namespace app\core;

class View
{
    public string $title = '';

    //Ne mozemo ovo ovako jednostavno da namestimo jer nasi view-evi ce imati nekoliko Layouts i jos neke dodatne parametre
    public function renderView($view, $params = []) //Naknado dodajemo params kao prazan array []
    {
        $viewContent = $this -> renderOnlyView($view, $params);
        $layoutContent = $this -> layoutContent(); //Ucitavamo prvo layout pa onda nase parametre za tu specificnu aplikaciju
        return str_replace('{{content}}', $viewContent, $layoutContent);
        //include_once __DIR__."/../views/$view.php";
        //include_once Application::$ROOT_DIR."/views/$view.php"; // Ovo nam ne treba sada jer smo ubacili return str_replace
    }

    public function renderContent($viewContent) // Prenutno nekorisna koristili smo je 10 sek za prikazivanje 404 layouta sa porukom ali smo presli na view _404
    {
        $layoutContent = $this -> layoutContent();
        return str_replace('{{content}}', $viewContent, $layoutContent);
    }

    protected function layoutContent() //The class member declared as Protected are inaccessible outside the class but they can be accessed by any subclass(derived class) of that class
    {
        $layout = Application::$app -> layout;
        if (Application::$app -> controller) {
            $layout = Application::$app -> controller -> layout; //Iz aplikacije cita instancu kontrolera koji ima promenljivu public tipa layout cija je default vrednost main_layout
        }

        ob_start(); //pocinje output ali ne prikazuje korisniku(kao da ga kesira i cega neku obradu)
        include_once Application::$ROOT_DIR."/views/layouts/$layout.php"; // Umesto da ima main_layout mi smo definisali metodu koja ce to raditi
        return ob_get_clean(); //return this to browser and clear cache
    }

    protected function renderOnlyView($view, $params) //Prosledjujem mu dodatne parametre koje ce on prikazati nastranici
    {
        foreach ($params as $key => $value) { //Ovde samo ulazimo u nase parametre koju su array citamo ih i pravimo da bismo ih posle u view iskoristili u nasem slucaju citacemo parametar $name u home view-u
            $$key = $value;
        }

        ob_start();
        include_once Application::$ROOT_DIR."/views/$view.php";
        return ob_get_clean();
    }
}