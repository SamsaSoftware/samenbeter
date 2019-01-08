<?php

namespace Application\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TemplateController extends AbstractActionController
{

    public function indexAction() {

        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        $templates = $dm->getRepository("\\Application\\Document\\ModelTemplate")->findAll();

        return new ViewModel(array(
            'flashMessages' => $this->flashMessenger(),
            'templates' => $templates
        ));
    }

    public function addAction() {
        try {
            $id = $this->params()->fromRoute('id', 0);
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            $template = $dm->getRepository("\\Application\\Document\\ModelTemplate")->findOneBy(array(
                "id" => $id
            ));
            if ($template === null && $id !== 0) {
                $this->flashMessenger()->addErrorMessage('Template do not exist!');
                $this->redirect()->toRoute('template', array(
                    'action' => 'index'
                ));
            }

            return new ViewModel(array(
                'template' => $template
            ));
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('An error has occurred!');
            $this->redirect()->toRoute('template', array(
                'action' => 'index'
            ));
        }
    }

    public function saveAction() {


        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $data = $request->getPost()->toArray();
                $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
                $template = null;
                if (isset($data['id']) && !empty($data['id'])) {
                    $template = $dm->getRepository("\\Application\\Document\\ModelTemplate")->findOneBy(array(
                        "id" => $data['id']
                    ));
                }
                if ($template !== null) {
                    $template->setName($data['name']);
                    $template->setClasspath($data['classpath']);

                } else {
                    $template = new \Application\Document\ModelTemplate();
                    $template->setName($data['name']);
                    $template->setClasspath($data['classpath']);
                }
                $dm->persist($template);
                $dm->flush();
                $this->flashMessenger()->addSuccessMessage('A template was successfully created !');
                return $this->redirect()->toRoute('template', array(
                    'action' => 'index'
                ));
            } catch(\Exception $e) {
                $this->flashMessenger()->addErrorMessage('An error has occurred!');
                $this->redirect()->toRoute('template', array(
                    'action' => 'add'
                ));
            }
        }

    }

    public function deleteAction() {
        try {
            $id = $this->params()->fromRoute('id', 0);
            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            $template = $dm->getRepository("\\Application\\Document\\ModelTemplate")->findOneBy(array(
                "id" => $id
            ));
            if ($template === null) {
                $this->flashMessenger()->addErrorMessage('Template do not exist!');
                $this->redirect()->toRoute('template', array(
                    'action' => 'index'
                ));
            }

            $dm->remove($template);
            $dm->flush();
            $this->flashMessenger()->addSuccessMessage('A template was successfully removed !');
            return $this->redirect()->toRoute('template', array(
                'action' => 'index'
            ));
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('An error has occurred!');
            $this->redirect()->toRoute('template', array(
                'action' => 'index'
            ));
        }
    }
}