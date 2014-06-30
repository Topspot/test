<?php

namespace Clients\Model;

class Client {

    public $id;
    public $name;
    public $email;
//    public $website;
    public $phone;
    public $calltracking;

    function exchangeArray($data) {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->name = (isset($data['name'])) ? $data['name'] : null;
        $this->email = (isset($data['email'])) ? $data['email'] : null;
    //    $this->website = (isset($data['website'])) ? $data['website'] : null;
        $this->phone = (isset($data['phone'])) ? $data['phone'] : null;
        $this->calltracking = (isset($data['calltracking'])) ? $data['calltracking'] : null;
    }
         // Add the following method:
     public function getArrayCopy()
     {
         return get_object_vars($this);
     }


}
