<?php

// filename : module/Users/src/Users/Form/LoginForm.php

namespace Clients\Form;

use Zend\Form\Form;

class EditBookForm extends Form {

    public function __construct($name = null) {
        parent::__construct('Edit');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('id', 'createBook');

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
