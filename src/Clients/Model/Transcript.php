<?php

namespace Clients\Model;

class Transcript {

    public $id;
    public $name;
    public $date_received;
    public $date_posted;
    public $date_revised;
    public $fileupload;
    public $created_at;
    public $updated_at;   
    public $website_id;   


    function exchangeArray($data) {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->name = (isset($data['name'])) ? $data['name'] : null;
        $this->date_received = (isset($data['date_received'])) ? $data['date_received'] : null;
        $this->date_posted = (isset($data['date_posted'])) ? $data['date_posted'] : null;
        $this->date_revised = (isset($data['date_revised'])) ? $data['date_revised'] : null;
        $this->website_id = (isset($data['website_id'])) ? $data['website_id'] : null;
        $this->fileupload = (isset($data['fileupload'])) ? $data['fileupload'] : null;
        $this->created_at = (isset($data['created_at'])) ? $data['created_at'] : null;

    }
         // Add the following method:
     public function getArrayCopy()
     {
         return get_object_vars($this);
     }


}
