<?php

namespace App\vStack\Fields;

class BelongsTo extends Field
{
    public $options = [];
    public $view = "";
    public function __construct($op = [])
    {
        $this->options = $op;
        $this->options["type"] = "text";
        parent::processFieldOptions();
        $this->makeView();
    }

    private function makeView()
    {
        $model       = $this->options["model"];
        $field       = @$this->options["field"];
        $label       = $this->options["label"];
        $route_list  = route("resource.inputs.option_list");
        $placeholder = $this->options["placeholder"];
        $view = "<v-select v-model='form.$field' list_model='$model' label='$label' 
                    placeholder='$placeholder' route_list='$route_list'
                />";
        return $this->view = $view;
    }
}
