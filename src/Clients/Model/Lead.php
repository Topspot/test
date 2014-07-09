<?php

namespace Clients\Model;

class Lead {

    public $id;
    public $comments;
    public $caller_type;
    public $lead_date;
    public $lead_source;
    public $client_name;
    public $website;
    public $inc_phone;
    public $call_time;
    public $call_duration;
    public $lead_name;
    public $lead_email;
    public $website_id;

    function exchangeArray($data) {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->comments = (isset($data['comments'])) ? $data['comments'] : null;
        $this->caller_type = (isset($data['caller_type'])) ? $data['caller_type'] : null;
        $this->lead_date = (isset($data['lead_date'])) ? $data['lead_date'] : null;
        $this->lead_source = (isset($data['lead_source'])) ? $data['lead_source'] : null;
        $this->client_name = (isset($data['client_name'])) ? $data['client_name'] : null;
        $this->website = (isset($data['website'])) ? $data['website'] : null;
        $this->inc_phone = (isset($data['inc_phone'])) ? $data['inc_phone'] : null;
        $this->call_time = (isset($data['call_time'])) ? $data['call_time'] : null;
        $this->call_duration = (isset($data['call_duration'])) ? $data['call_duration'] : null;
        $this->lead_name = (isset($data['lead_name'])) ? $data['lead_name'] : null;
        $this->lead_email = (isset($data['lead_email'])) ? $data['lead_email'] : null;
        $this->website_id = (isset($data['website_id'])) ? $data['website_id'] : null;
    }
         // Add the following method:
     public function getArrayCopy()
     {
         return get_object_vars($this);
     }
}
