<?php

namespace Clients\Model;

class Book {

    public $id;
    public $name;
    public $website_id;   

    function exchangeArray($data) {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->name = (isset($data['name'])) ? $data['name'] : null;
        $this->website_id = (isset($data['website_id'])) ? $data['website_id'] : null;
    }
         // Add the following method:
     public function getArrayCopy()
     {
         return get_object_vars($this);
     }


}
