<?php

namespace Clients\Model;

class Link {

    public $id;
    public $date;
    public $url;
    public $website_id;
    public $created_at;
    public $updated_at;   


    function exchangeArray($data) {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->date = (isset($data['date'])) ? $data['date'] : null;
        $this->url = (isset($data['url'])) ? $data['url'] : null;
        $this->website_id = (isset($data['website_id'])) ? $data['website_id'] : null;

    }
         // Add the following method:
     public function getArrayCopy()
     {
         return get_object_vars($this);
     }


}
