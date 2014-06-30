<?php

namespace Clients\Form;

use Zend\InputFilter\InputFilter;

class AddTranscriptFilter extends InputFilter {

    public function __construct() {
//        $this->add(array(
//            'name' => 'url',
//            'attributes' => array(
//                'type' => 'Zend\Form\Element\Url',
//                'error_msg' => 'Enter Valid Program URL 1',
//                'label_msg' => 'Program URL 1 *'
//            ),
//            'validation' => array(
//                'required' => true,
//                'filters' => array(
//                    array(
//                        'name' => 'StripTags'
//                    ),
//                    array(
//                        'name' => 'StringTrim'
//                    )
//                ),
//                'validators' => array(
//                    array(
//                        'name' => 'Regex',
//                        'options' => array(
//                            'pattern' => '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'
//                        )
//                    )
//                )
//            )
//        ));
//        $this->add(array(
//            'name' => 'url',
//            'type' => 'Zend\Form\Element\Url',
//            'options' => array(
//                'label' => 'Program URL 1'
//            ),
//            'attributes' => array(
//                'required' => 'required'
//            )
//        ));
    }

}
