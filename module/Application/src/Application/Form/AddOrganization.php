<?php

namespace Application\Form;

use Zend\Form\Form;

class AddOrganization extends Form
{

    private $serviceLocator;

    public function __construct($serviceLocator)
    {
        parent::__construct('addOrganization');
        $this->setAttribute('method', 'post');
        $this->serviceLocator = $serviceLocator;

        $this->add(
            array(
                'name' => 'id',
                'type' => 'Zend\Form\Element\Hidden'
            )
        );

        $this->add(
            array(
                'name' => 'name',
                'type' => 'Zend\Form\Element\Text',
                'id' => 'name',
                'options' => array(
                    'label' => 'Name:'
                ),
                'attributes' => array(
                    'class' => 'form-control input-width-xlarge required'
                )
            )
        );

        $this->add(
            array(
                'name' => 'email',
                'type' => 'Zend\Form\Element\Text',
                'id' => 'email',
                'options' => array(
                    'label' => 'Email:'
                ),
                'attributes' => array(
                    'class' => 'form-control input-width-xxlarge required email'
                )
            )
        );

        $this->add(
            array(
                'name' => 'classpath',
                'type' => 'Zend\Form\Element\Select',
                'id' => 'classpath',
                'options' => array(
                    'label' => 'Classpath:',
                    'empty_option' => 'Select',
                    'value_options' => array(
                    )

                ),
                'attributes' => array(
                    'class' => 'form-control input-width-xlarge required'
                )
            )
        );


        $this->add(
            array(
                'name' => 'description',
                'type' => 'Zend\Form\Element\Textarea',
                'id' => 'address',
                'attributes' => array(
                    'cols' => '50',
                    'rows' => '8'
                ),
                'options' => array(
                    'label' => 'Description:'
                ),
            )
        );


        $this->add(
            array(
                'name' => 'locale',
                'type' => 'Zend\Form\Element\Select',
                'options' => array(
                    'label' => 'Language:',
                    'empty_option' => 'Select',
                    'value_options' => array(
                        'de' => 'German',
                        'en' => 'English',
                        'nl' => 'Dutch',
                        'ro' => 'Romanian'
                    ),
                ),
                'attributes' => array(
                    'class' => 'form-control required'
                )
            )
        );

        $this->add(
            array(
                'name' => 'logo',
                'type' => 'Zend\Form\Element\File',
                'id' => 'image-file',
                'options' => array(
                    'label' => 'Logo:'
                ),
                'attributes' => array(
                    'data-style' => 'fileinput'
                )
            )
        );

        $this->add(
            array(
                'name' => 'deleted',
                'type' => 'Zend\Form\Element\Select',
                'id' => 'deleted',
                'options' => array(
                    'label' => 'Deleted',
                    'empty_option' => 'Select',
                    'value_options' => array(
                        '0' => 'False',
                        '1' => 'True',
                    ),
                ),
                'attributes' => array(
                    'class' => 'form-control required'
                )
            )
        );

        $this->add(
            array(
                'name' => 'submit',
                'attributes' => array(
                    'type' => 'submit',
                    'value' => 'Submit',
                    'id' => 'submit',
                    'class' => 'btn btn-primary pull-right'
                ),
            )
        );
    }
}