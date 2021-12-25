<?php

namespace app\core;
//Pravimo zato sto nezelimo da odmah ide do glavnog request handlera nego prvoda dodje do naseg handlera
use JetBrains\PhpStorm\Pure;

class Request
{
    public function getPath()
    {
        $path = $_SERVER['REQUEST_URI'] ?? "/"; // ?? It returns its first operand if it exists and is not NULL; otherwise, it returns its second operand
        $position = strpos($path, '?'); //Trazi neki karakter u string, ako ga nenadje daje BOOLEAN FALSE
        if ($position === false) {
            return $path;
        }

        return $path = substr($path, 0, $position);

        /*echo "<pre>";
        var_dump($position);
        echo "</pre>";
        exit;*/
    }

    public function method(): string //Mora izbacije neku gresku
    {
        return strtolower($_SERVER['REQUEST_METHOD']); // Nabavi koji tip/metod jeste request
    }

    #[Pure] public function isGet(): bool
    {
        return $this -> method() === 'get';
    }

    #[Pure] public function isPost(): bool
    {
        return $this -> method() === 'post';
    }

    #[Pure] public function getBody(): array //On ce uzimati bilo joi request $_POST parametre i obradjivace ih za maliciozne stvari
    {
        $body = [];
        if ($this -> method() === 'get') { //Filtriranje svi $_GET zahteva
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        if ($this -> method() === 'post') { //Filtriranje svi $_POST zahteva
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }

        return $body;
    }
}