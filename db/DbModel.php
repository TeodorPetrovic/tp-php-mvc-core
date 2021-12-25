<?php

namespace app\core\db;

use app\core\Application;
use app\core\Model;

abstract class DbModel extends Model
{
    abstract public static function tableName(): string; //slako ko implementira ovu klase mora da ima ove funkcije tj ove vrednosti mora da vrati

    abstract public function attributes(): array;

    abstract public static function primaryKey(): string;

    public function save(): bool
    {
        $tableName = $this -> tableName(); //Dobili smo ime tabel od abstraktne klase iz User
        $attributes = $this -> attributes(); //Ovo radimo i zbog brige za sql injection
        $params = array_map(fn($attr) => ":$attr", $attributes); //mapirali parametre da imaju :

        $statement = self::prepare("
            INSERT INTO $tableName (".implode(',', $attributes).") VALUES (".implode(',', $params).")
        ");

        foreach ($attributes as $attribute)
        {
            $statement -> bindValue(":$attribute", $this -> {$attribute}); //zami ti vrednost sa ovom vrednoscu
        }

        $statement -> execute();

        return true;
    }

    public static function findOne($where) // [email => zura@example.com, firstname => zura]
    {
        $tableName = static::tableName(); //Ako stavimo static onda na onom objectu gde koristimo ovaj metod uzece tu table name
        $attributes = array_keys($where);

        $sql = implode("AND ", array_map(fn($attr) => "$attr = :$attr", $attributes));
        $statement = self::prepare("
            SELECT * FROM $tableName WHERE $sql
        ");

        foreach ($where as $key => $item) {
            $statement -> bindValue(":$key", $item);
        }
        $statement -> execute();
        return $statement -> fetchObject(static::class); //Vraca ne samo korisnika iz baze nego castuje taj podatak u classu od objecta koji ga poziva

    }

    public static function prepare($SQL): bool|\PDOStatement //Napisali smo samo da bi lakse ucitavali kod da nepisemo ovo dole
    {
        return Application::$app -> db -> pdo -> prepare($SQL);
    }

}