<?php

namespace app\core\form;

use app\core\Model;
use JetBrains\PhpStorm\Pure;

class InputField extends BaseField
{
    public const  TYPE_TEXT = "text";
    public const  TYPE_PASSWORD = "password";
    public const  TYPE_NUMBER = "number";

    public string $type;

    /**
     * @param Model $model
     * @param string $attribute
     */
    #[Pure] public function __construct(Model $model, string $attribute)
    {
        parent::__construct($model, $attribute);
        $this -> type = self::TYPE_TEXT;
    }

    public function passwordField(): static
    {
        $this -> type = self::TYPE_PASSWORD;
        return $this;
    }

    public function renderInput(): string
    {
        return sprintf('<input type="%s" name="%s" id="%s" value="%s" class="form-control%s">',
            $this -> type,
            $this -> attribute,
            $this -> attribute,
            $this -> model -> {$this -> attribute},
            $this -> model -> hasError($this -> attribute) ? ' is-invalid' : ''
        );
    }
}