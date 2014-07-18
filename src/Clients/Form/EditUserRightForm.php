<?php

// filename : module/Users/src/Users/Form/LoginForm.php

namespace Clients\Form;

use Zend\Form\Form;

class EditUserRightForm extends Form {

    public function __construct($name = null) {
        parent::__construct('edit');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/formdata');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('id', 'updateUserRight');

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'user_id',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'crud_user',
            'attributes' => array(
                'id' => 'crud_user',
                'class' => 'col-xs-10 col-sm-2',
            ),
            'options' => array(
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'crud_client',
            'attributes' => array(
                'id' => 'crud_client',
                'class' => 'col-xs-10 col-sm-2',
            ),
            'options' => array(
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'crud_lead',
            'attributes' => array(
                'id' => 'crud_lead',
                'class' => 'col-xs-10 col-sm-2',
            ),
            'options' => array(
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'crud_link',
            'attributes' => array(
                'id' => 'crud_link',
                'class' => 'col-xs-10 col-sm-2',
            ),
            'options' => array(
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'crud_traffic',
            'attributes' => array(
                'id' => 'crud_traffic',
                'class' => 'col-xs-10 col-sm-2',
            ),
            'options' => array(
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'crud_transcript',
            'attributes' => array(
                'id' => 'crud_transcript',
                'class' => 'col-xs-10 col-sm-2',
            ),
            'options' => array(
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'crud_book',
            'attributes' => array(
                'id' => 'crud_book',
                'class' => 'col-xs-10 col-sm-2',
            ),
            'options' => array(
            )
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
