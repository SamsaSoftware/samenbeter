<?php

namespace Application\Form;

use Zend\Form\Form;

class AddUser extends Form
{

    private $serviceLocator;

    public function __construct($serviceLocator)
    {
        parent::__construct('addUser');
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'adduser');
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
                'name' => 'lastname',
                'type' => 'Zend\Form\Element\Text',
                'id' => 'lastname',
                'options' => array(
                    'label' => 'Last Name:'
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
                'attributes' => array(
                    'id' => 'emailElement',
                    'class' => 'form-control input-width-xlarge required email'
                ),
                'options' => array(
                    'label' => 'Email:'
                )
            )
        );
        $this->add(
            array(
                'name' => 'phone',
                'type' => 'Zend\Form\Element\Text',
                'id' => 'phone',
                'options' => array(
                    'label' => 'Telephone:'
                ),
                'attributes' => array(
                    'class' => 'form-control input-width-xlarge'
                )
            )
        );
        $this->add(
            array(
                'name' => 'address',
                'type' => 'Zend\Form\Element\Textarea',
                'id' => 'address',
                'attributes' => array(
                    'cols' => '50',
                    'rows' => '8'
                ),
                'options' => array(
                    'label' => 'Address:'
                ),
            )
        );
        $this->add(
            array(
                'name' => 'password',
                'type' => 'Zend\Form\Element\Password',
                'id' => 'password',
                'options' => array(
                    'label' => 'Password:'
                ),
                'attributes' => array(
                    'class' => 'form-control input-width-xlarge required'
                )
            )
        );

        $this->add(
            array(
                'name' => 'role',
                'type' => 'Zend\Form\Element\Select',
                'id' => 'role',
                'options' => array(
                    'label' => 'Role',
                    'empty_option' => 'Select',
                    'value_options' => $this->getUserRoles(),
                ),
                'attributes' => array(
                    'class' => 'form-control required'
                )

            )
        );
        $this->add(
            array(
                'name' => 'organization',
                'type' => 'Zend\Form\Element\Select',
                'id' => 'organization',
                'options' => array(
                    'label' => 'Organization',
                    'empty_option' => 'Select',
                    'value_options' => $this->getOrganizations(),
                ),
                'attributes' => array(
                    'class' => 'form-control required'
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

    private function getUserRoles()
    {

        $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
        $session = $this->serviceLocator->get('Zend\Authentication\AuthenticationService');
        $identity = $session->getIdentity();
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));


        $userRoles = $dm->getRepository("\\Application\\Document\\UserRole")->findAll();
        $rolesArray = array();
        foreach ($userRoles as $userRole){
            if ($user->getUserRole()->getRole() == \Application\Document\User::SUPER_ADMIN &&
                $userRole->getRole() != \Application\Document\User::USER) {
                $rolesArray[$userRole->getId()] = $userRole->getRole();
            } elseif ($user->getUserRole()->getRole() == \Application\Document\User::ADMIN &&
                $userRole->getRole() != \Application\Document\User::SUPER_ADMIN) {
                $rolesArray[$userRole->getId()] = $userRole->getRole();
            }
        }
        return $rolesArray;
    }
    private function getOrganizations()
    {

        $dm = $this->serviceLocator->get('doctrine.documentmanager.odm_default');
        $organizations = $dm->getRepository("\\Application\\Document\\Organization")->findAll();
        $session = $this->serviceLocator->get('Zend\Authentication\AuthenticationService');
        $identity = $session->getIdentity();
        $user = $dm->getRepository("\\Application\\Document\\User")->findOneBy(array(
            "id" => $identity['id']
        ));
        $organizationArray = array();
        if ($user->getUserRole()->getRole() == \Application\Document\User::SUPER_ADMIN) {
            foreach ($organizations as $organization) {
                $organizationArray[$organization->getId()] = $organization->getName();
            }
        } else {
            $organizationArray[$user->getOrganization()->getId()] = $user->getOrganization()->getName();
        }
        return $organizationArray;
    }
}