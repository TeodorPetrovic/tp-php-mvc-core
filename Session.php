<?php

namespace app\core;

class Session //Sve vezano za pokretanje i koriscenje sessije
{
    const FLASH_KEY = 'flash_messages'; //bilo protecres

    public function __construct()
    {
        session_start(); //Ovde nesto neradi jer program samo radi ako stavim session sta
        $flashMessages = $_SESSION[self::FLASH_KEY] ?? []; //ako nepostoji session parametar sa tim imenom selije je onda prazni skup

        foreach ($flashMessages as $key => &$flashMessage) { //Obrisi ih ali kada na KRAJU!!!!! REQUEST-a &by refence tako se stavlja
            $flashMessage['remove'] = true; // Marked to be removed
        }

        $_SESSION[self::FLASH_KEY] = $flashMessages;

    }

    public function setFlash($key, $message) //flash sessija koja traje za samo jedan request
    {
        //session_start();
        $_SESSION[self::FLASH_KEY][$key] = [ //svali flash message ima parametre remove i poruku
            'remove' => false,
            'value' => $message
        ];

        /*echo '<pre sf>'; //Ovo radi vraca ono sto bi trebalo
        var_dump($_SESSION[self::FLASH_KEY]);
        echo '</pre>';
        exit;*/
    }

    public function getFlash($key)
    {
        return $_SESSION[self::FLASH_KEY][$key]['value'] ?? false;
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function get($key)
    {
        return $_SESSION[$key] ?? false;
    }

    public function remove($key)
    {
        unset($_SESSION[$key]);
    }

    public function  __destruct()
    {
        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];

        foreach ($flashMessages as $key => &$flashMessage) {
            if($flashMessage['remove']) {
                unset($flashMessages[$key]);
            }
        }

        $_SESSION[self::FLASH_KEY] = $flashMessages;
    }
}