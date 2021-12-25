<?php

namespace app\core;

use JetBrains\PhpStorm\Pure;

abstract class Model
{
    public const RULE_REQUIRED = 'required'; //Ovde cemo pisati pravila koja ce nas model tj njegove vrednosti morati da postuju
    public const RULE_EMAIL = 'email';
    public const RULE_MIN = 'min';
    public const RULE_MAX = 'max';
    public const RULE_MATCH = 'match';
    public const RULE_UNIQUE = 'unique'; //svako novo pravilo koje zelis da implementiras napisaces ovde

    abstract public function rules(): array;

    public function labels(): array //Da imamo i polja za labels
    {
        return [];
    }

    #[Pure] public function getLabel($attribute)  //Nabali label preko parametra ako postoji, ako ne prosledi parametar
    {
        return $this -> labels()[$attribute] ?? $attribute;
    }

    public array $errors = [];

    public function loadData($data) //Oni su ovde jer ce se ove dve (ili vise) metode koristiti u svakom modelu i bolje je da se ovako napise
    {
        foreach ($data as $key => $value) {
            if(property_exists($this, $key)) {
                $this -> {$key} = $value;
            }
        }
    }

    public function validate(): bool
    {
        foreach ($this -> rules() as $attribute => $rules) { //Prodji kroz sva pravila koji si definisao // Objekat koji poziva rules() procitaj njegov array pravila i mapiraj atribute u $attributes a pravilo ili pravila tog atributa mapiraj u $rules
            $value = $this -> {$attribute}; //Vrednost atributa tj njegovo ime MOZDA NISAM SIGURAN NJEGOVA VREDNOST?
            foreach ($rules as $rule) { //Prodji kroz sva pravila tog jednog atributa
                $ruleName = $rule;

                if (!is_string($ruleName)) {
                    $ruleName = $rule[0]; //Uzmi prvi clan array-a u ovom slucaju lili uvek je prvi 0
                }
                if ($ruleName === self::RULE_REQUIRED && !$value) { //Ako je pravilo jednako required a nema vrednost izbaci gresku
                    $this -> addErrorForRule($attribute, self::RULE_REQUIRED);
                }
                if ($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) { //Proverava email
                    $this -> addErrorForRule($attribute, self::RULE_EMAIL);
                }
                if ($ruleName === self::RULE_MIN && strlen($value) < $rule['min']) { //Proverava password min sa zahtevom u pravilima
                    $this -> addErrorForRule($attribute, self::RULE_MIN, $rule);
                }
                if ($ruleName === self::RULE_MAX && strlen($value) > $rule['max']) { //Proverava password min sa zahtevom u pravilima
                    $this -> addErrorForRule($attribute, self::RULE_MAX, $rule);
                }
                if ($ruleName === self::RULE_MATCH && $value !== $this -> {$rule['match']}) { //Proverava password min sa zahtevom u pravilima
                    $rule['match'] = $this -> getLabel($rule['match']);
                    $this -> addErrorForRule($attribute, self::RULE_MATCH, $rule);
                }
                if ($ruleName === self::RULE_UNIQUE) {
                    $className = $rule['class']; //Procitaj koja je klasa
                    $uniqueAttr = $rule['attribute'] ?? $attribute; //Uzmi taj atribut koji je unique ako ga ima , ako ne onda samo ime atributa
                    $tableName = $className::tableName(); //na toj klassi pozovi tablename metodu
                    $statement = Application::$app -> db -> prepare("
                        SELECT * FROM $tableName WHERE $uniqueAttr = :attr;
                    ");
                    $statement -> bindValue(":attr", $value);
                    $statement -> execute(); //Napravi i izvrsi upit
                    $record = $statement -> fetchObject(); //Dohvati rezultat i ako ga ima uradi nesto
                    if ($record) {
                        $this -> addErrorForRule($attribute, self::RULE_UNIQUE, ['field' => $this -> getLabel($attribute)]); //Neprosljedjujem mu atribu nege vrednost label polja za taj atribut
                    }
                }
            }
        }

        return empty($this -> errors);
    }

    private function addErrorForRule(string $attribute, string $rule, $params = []) //privatni metod sa mo za ovde
    {
        $message = $this -> errorMessages()[$rule] ?? '';
        foreach ($params as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message); //Trazi u error poruci ovaj teks $key i zameni ga sa njegovom vrednoscu $value
        }//Mora dva {{}} jer ako je 1 ispisace {8}
        $this -> errors[$attribute][] = $message;
    }

    public function addError(string $attribute, string $message    ) //public metod bez rule polja
    {
        $this -> errors[$attribute][] = $message;
    }

    public function errorMessages(): array
    {
        return [
            self::RULE_REQUIRED => "This field is required",
            self::RULE_EMAIL => "This field must be a valid email address",
            self::RULE_MIN => "Min length of this field must be {min}",
            self::RULE_MAX => "Max length of this field must be {max}",
            self::RULE_MATCH => "This field must be the same as {match}",
            self::RULE_UNIQUE => "Record with this {field} already exists"
        ];
    }

    public function hasError($attribute): mixed
    {
        return $this -> errors[$attribute] ?? false;
    }

    public function getFirstError($attribute): mixed
    {
        return $this -> errors[$attribute][0] ?? false;
    }
}