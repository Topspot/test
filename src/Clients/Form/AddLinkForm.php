<?php

// filename : module/Users/src/Users/Form/LoginForm.php

namespace Clients\Form;

use Zend\Form\Form;

class AddLinkForm extends Form {

    public function __construct($name = null) {
        parent::__construct('Add');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/formdata');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('id', 'createLink');
        
        $this->add(array(
            'name' => 'website_id',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'date',
            'attributes' => array(
                'type' => 'text',
                'id' => 'datepicker',
                'class' => 'col-xs-10 col-sm-5',
                'placeholder' => 'Select Date',
            ),
            'options' => array(
                'label' => 'Date',
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Url',
            'name' => 'url',
             'attributes' => array(
                'class' => 'col-xs-10 col-sm-5',
                'id' => 'url',
                'placeholder' => 'URL',
             ),
            'options' => array(
//                'label' => 'Webpage URL'
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Submit',
                'id' => 'submitbutton',
                'class' => 'btn btn-info submit',
            ),
        ));
    }

}
