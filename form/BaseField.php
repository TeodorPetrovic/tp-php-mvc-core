<?php

namespace app\core\form;

use app\core\Model;

abstract class BaseField
{
    abstract public function renderInput(): string;

    public Model $model;
    public string $attribute;

    /**
     * @param Model $model
     * @param string $attribute
     */
    public function __construct(Model $model, string $attribute)
    {
        //$this -> type = self::TYPE_TEXT;
        $this -> model = $model;
        $this -> attribute = $attribute;
    }


    public function __toString()
    {
        return sprintf('
            <div class="form-group">
                <label for="%s" class="form-label">%s</label>
                %s
                <div class="invalid-feedback">
                    %s
                </div>
            </div>
        ',  $this -> attribute,
            $this -> model -> getLabel($this -> attribute),
            $this -> renderInput(),
            $this -> model -> getFirstError($this -> attribute)
        );
        /*return sprintf('
            <div class="form-group needs-validation">
                <label for="%s" class="form-label">%s</label>
                <input type="text" class="form-control" name="%s" id="%s" value="%s"%s>
                <div class="invalid-feedback">
                    %s
                </div>
            </div>
        ',  $this -> attribute,
            $this -> attribute,
            $this -> attribute,
            $this -> attribute,
            $this -> model -> {$this -> attribute},
            $this -> model -> hasError($this -> attribute) ? ' required' : '',
            $this -> model -> getFirstError($this -> attribute)
        );*/
    }
}