<?php

// filename : module/Users/src/Users/Form/LoginForm.php

namespace Clients\Form;

use Zend\Form\Form;

class EditLeadForm extends Form {

    public function __construct($name = null) {
        parent::__construct('Edit');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/formdata');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('id', 'createLead');

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));

       $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'caller_type',
            'options' => array(
                'label' => 'Type',
                'value_options' => array(
                     '' => 'Select',
                    '1' => 'Poten Newclient',
                    '2' => 'Non-Client',
                    '3' => 'Soliciter',
                    '4' => 'Current Client',
                    '5' => 'Repeated',
                    '6' => 'Web Form',
                    '7' => 'Test Call',
                    '8' => 'No Recording'
                ),
            ),
            'attributes' => array(
                'value' => '', //set selected to '1'
                'class' => 'col-xs-10 col-sm-5',
                
            )
        ));
        $this->add(array(
            'name' => 'lead_date',
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
            'type' => 'Zend\Form\Element\Select',
            'name' => 'lead_source',
            'options' => array(
                'label' => 'Source',
                'value_options' => array(
                    '' => 'Select',
                    '1' => 'Phone Call',
                    '2' => 'Contact Form',
                    '3' => 'Book Download'

                ),
            ),
            'attributes' => array(
                'value' => '', //set selected to '1'
                'class' => 'col-xs-10 col-sm-5'
            )
        ));
         $this->add(array(
            'name' => 'inc_phone',
            'attributes' => array(
                'type' => 'text',
                'class' => 'col-xs-10 col-sm-5 input-mask-phone',
                'id' => 'inc_phone',
                'placeholder' => 'Phone',
      
            ),
            'options' => array(
//                'label' => 'Phone',
            ),
        )); 
         
         $this->add(array(
            'name' => 'call_duration',
            'attributes' => array(
                'type' => 'text',
                'class' => 'col-xs-10 col-sm-5',
                'id' => 'call_duration',
                'placeholder' => 'Call Duration',
                
       
            ),
            'options' => array(
//                'label' => 'Full Name',
            ),
        ));
         $this->add(array(
            'name' => 'lead_name',
            'attributes' => array(
                'type' => 'text',
                'class' => 'col-xs-10 col-sm-5',
                'id' => 'lead_name',
                'placeholder' => 'Name',
            ),
            'options' => array(
//                'label' => 'Full Name',
            ),
        ));
         $this->add(array(
            'name' => 'lead_email',
            'attributes' => array(
                'type' => 'text',
                'class' => 'col-xs-10 col-sm-5',
                'id' => 'lead_email',
                'placeholder' => 'Email',
            ),
            'options' => array(
//                'label' => 'Full Name',
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
