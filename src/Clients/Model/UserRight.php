<?php

namespace Clients\Model;

class UserRight {

    public $id;
    public $user_id;
    public $crud_user;
    public $crud_client;
    public $crud_lead;
    public $crud_link;
    public $crud_traffic;
    public $crud_transcript;
    public $crud_book;  


    function exchangeArray($data) {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
        $this->crud_user = (isset($data['crud_user'])) ? $data['crud_user'] : null;
        $this->crud_client = (isset($data['crud_client'])) ? $data['crud_client'] : null;
        $this->crud_lead = (isset($data['crud_lead'])) ? $data['crud_lead'] : null;
        $this->crud_link = (isset($data['crud_link'])) ? $data['crud_link'] : null;
        $this->crud_traffic = (isset($data['crud_traffic'])) ? $data['crud_traffic'] : null;
        $this->crud_transcript = (isset($data['crud_transcript'])) ? $data['crud_transcript'] : null;
        $this->crud_book = (isset($data['crud_book'])) ? $data['crud_book'] : null;

    }
         // Add the following method:
     public function getArrayCopy()
     {
         return get_object_vars($this);
     }


}
