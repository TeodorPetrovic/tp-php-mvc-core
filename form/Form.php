<?php

namespace app\core\form;

use app\core\Model;
use JetBrains\PhpStorm\Pure;

class Form
{
    public static function begin($action, $method): Form
    {
        echo sprintf('<form action="%s" method="%s">', $action, $method); ///sprintf like printf in java
        return new Form();
    }

    public static function end()
    {
        echo '</form>';
    }

    #[Pure] public function field(Model $model, $attribute): InputField
    {
        return new InputField($model, $attribute);
    }
}