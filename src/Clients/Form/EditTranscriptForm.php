<?php

// filename : module/Users/src/Users/Form/LoginForm.php

namespace Clients\Form;

use Zend\Form\Form;

class EditTranscriptForm extends Form {

    public function __construct($name = null) {
        parent::__construct('Edit');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('id', 'updateTranscript');

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));

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
            'name' => 'date_received',
            'attributes' => array(
                'type' => 'text',
                'id' => 'date_received',
                'class' => 'col-xs-10 col-sm-5',
                'placeholder' => 'Select Received Date',
            ),
            'options' => array(
//                'label' => 'Date',
            ),
        ));
        $this->add(array(
            'name' => 'date_posted',
            'attributes' => array(
                'type' => 'text',
                'id' => 'date_posted',
                'class' => 'col-xs-10 col-sm-5',
                'placeholder' => 'Select Posted Date',
            ),
            'options' => array(
//                'label' => 'Date',
            ),
        ));
        $this->add(array(
            'name' => 'date_revised',
            'attributes' => array(
                'type' => 'text',
                'id' => 'date_revised',
                'class' => 'col-xs-10 col-sm-5',
                'placeholder' => 'Select Revised',
            ),
            'options' => array(
//                'label' => 'Date',
            ),
        ));
        $this->add(array(
            'name' => 'fileupload',
            'attributes' => array(
                'type' => 'file',
                'class' => 'col-xs-10 col-sm-3',
//         'multiple' => true,   
                'id' => 'fileupload',
            ),
            'options' => array(
//        'label' => 'File Upload',
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
