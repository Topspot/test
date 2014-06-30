<?php

// filename : module/Users/src/Users/Form/LoginForm.php

namespace Clients\Form;

use Zend\Form\Form;

class AddForm extends Form {
    
    public function __construct($name = null) {
        parent::__construct('Add');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/formdata');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('id', 'createClient');

        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type' => 'text',
                'class' => 'col-xs-10 col-sm-5',
                'id' => 'name',
                'placeholder' => 'Name',
            ),
            'options' => array(
//                'label' => 'Full Name',
            ),
        ));
        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type' => 'email',
                'class' => 'col-xs-10 col-sm-5',
                'id' => 'email',
                'placeholder' => 'Email',
            ),

        ));
        $this->add(array(
            'name' => 'phone',
            'attributes' => array(
                'type' => 'text',
                'class' => 'col-xs-10 col-sm-5 input-mask-phone',
                'id' => 'phone',
                'placeholder' => 'Phone',
            ),
            'options' => array(
//                'label' => 'Phone',
            ),
        ));
         $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'calltracking',
            'options' => array(
                'label' => 'Call Tracking',
                'value_options' => array(
//                    '1' => 'Select',
                    '1' => 'Yes',
                    '0' => 'No'
                ),
            ),
            'attributes' => array(
                'value' => '1', //set selected to '1'
                'class' => 'col-xs-10 col-sm-5'
            )
        ));
         
        $this->add(array(
            'name' => 'website',
            'attributes' => array(
                'type' => 'text',
                'id'    => 'website',
                'class' => 'col-xs-10 col-sm-5',
                
            ),
            'options' => array(
//                'label' => 'Website',
            ),
        ));
       
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Add Now',
                'id' => 'submitbutton',
                'class' => 'btn btn-info submit',
            ),
        ));
    

    }
}
