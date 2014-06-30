<?php

namespace Clients\Form;

use Zend\InputFilter\InputFilter;

class EditLinkFilter extends InputFilter {

    public function __construct() {

        $this->add(array(
            'name' => 'url',
            'type' => 'Zend\Form\Element\Url',
            'options' => array(
                'label' => 'Program URL 1'
            ),
            'attributes' => array(
                'required' => 'required'
            )
        ));
    }

}
