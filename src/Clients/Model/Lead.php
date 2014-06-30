<?php

namespace Clients\Model;

class Lead {

    public $id;
    public $name;

    function exchangeArray($data) {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->name = (isset($data['name'])) ? $data['name'] : null;
    }
         // Add the following method:
     public function getArrayCopy()
     {
         return get_object_vars($this);
     }
}
