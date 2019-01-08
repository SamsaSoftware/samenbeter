<?php
namespace Application\Service;

use Application\Controller\ServiceLocatorFactory;
use Application\Controller\MongoObjectFactory;
use Application\Document\Template;

class MailService extends Service
{

    const RESET_PASSWORD_EMAIL = "resetPassword";

    public static $generalEmails = array(
        Template::RESET_PASSWORD => Template::RESET_PASSWORD,
        Template::REGISTERED_TO_ORGANIZATION => Template::REGISTERED_TO_ORGANIZATION,
        Template::ALREADY_PART_OF_ORGANIZATION => Template::ALREADY_PART_OF_ORGANIZATION,
        Template::GENERATE_PASSWORD => Template::GENERATE_PASSWORD,
        Template::CONFIRM_ACCOUNT => Template::CONFIRM_ACCOUNT
    );

    public function callEmail($data)
    {
        $to = $data['to'];
        $from = $data['from'];
        $fromName = $data['fromName'];
        $subject = $data['subject'];
        $htmlContent = $data['htmlContent'];
        $cc = $data['cc'];
        $this->sendEmail($to, $from, $fromName, $subject, $htmlContent, $cc);
    }

    public function sendEmailService($data)
    {
        $laf = new MongoObjectFactory();
        
        $template = $data['template'];
        $objectRef = $data['objectType'];
        $objectId = $data['id']['$id'];
        $method = $data['method'];
        $workspaceId = $data['workspaceId'];
        // $data = array(
        // "name" => "CustomerId",
        // "referencelink" => "getParent.getParent._id"
        // );
        if (is_array($objectId)) {
            $objectId = $objectId[0];
        }
        $itemNew = array();
        if (isset($data['params'])) {
            $paths = explode("+", $data['params']);
            foreach ($paths as $path) {
                $methodsRef = explode("=", $path);
                $methodsRefernces = explode(".", $methodsRef[1]);
                $object = $laf->findObject($objectRef, $objectId);
                $collectionObj1 = array();
                $collectionObj1[] = $object;
                $itemNew[$methodsRef[0]] = $this->getCollectionRef($methodsRefernces, $collectionObj1);
            }
        }
        if (! empty($itemNew)) {
            if (isset($itemNew['to'])) {
                if (is_array($itemNew['to'])) {
                    $to = $itemNew['to'][0];
                    unset($itemNew['to'][0]);
                    $ccArray = $itemNew['to'];
                }
            }
            
            $from = (isset($itemNew['from']) && ! is_array($itemNew['from'])) ? $itemNew['from'] : '';
            $fromName = (isset($itemNew['fromName']) && ! is_array($itemNew['fromName'])) ? $itemNew['fromName'] : '';
            $ccArray[] = (isset($itemNew['cc']) && ! is_array($itemNew['cc'])) ? $itemNew['cc'] : null;
            
            $criteria = array(
                'name' => $template,
                'parent.$id' => $workspaceId
            );
            $resultTemplate = $laf->findObjectByCriteria("Template", $criteria);
            $resultTemplateMessage = '';
            if (isset($resultTemplate['messageTemplate']) && $resultTemplate['messageTemplate'] != '') {
                $criteriaMessage = array(
                    'name' => $resultTemplate['messageTemplate'],
                    'parent.$id' => $workspaceId
                );
                $resultTemplateObject = $laf->findObjectByCriteria("Template", $criteriaMessage);
                $resultTemplateMessage = $resultTemplateObject['text'];
            }
            
            if (isset($itemNew['subject'])) {
                $subject = isset($resultTemplate['subject']) ? $resultTemplate['subject'] . ' ' . $itemNew['subject'] : 'Subject';
            } else {
                $subject = isset($resultTemplate['subject']) ? $resultTemplate['subject'] : 'Subject';
            }
            $mainArrayResult = array();
            $mainArrayResult = $laf->findObjectJSON($objectRef, $objectId);
            
            // get instance from object
            $classObject = $laf->findObjectInstance($objectRef, $objectId);
            
            $collectionObj = array();
            $collectionObj[] = $classObject;
            $collectionInstances = array();
            // extract the last method name : getOrder -> orders
            // $methods = explode(".", $method);
            $collectionInstances = $this->getMultiCollectionRef($method, $collectionObj);
            $data["main"] = $mainArrayResult;
            
            $collection = array();
            $data["lists"] = $collectionInstances;
            
            /*
             * ///we should change reportBuilder to be as main method
             * $reportingService = new ReportingService();
             * if (! empty($resultTemplate)) {
             * $template = $reportingService->reportBuilder($resultTemplate['text'], $data);
             * } else {
             * $template = "No template!";
             * }
             */
            \Application\Controller\Log::getInstance()->AddRow(' REPORT->generate Email ' . $template . " - " . json_encode($data) . " - " . $itemNew['subject']);
            $reportingService = new ReportingService();
            $resultGenerateInvoice = $reportingService->formatDocumentCollection($workspaceId, $template, $data, $itemNew['subject']);
            \Application\Controller\Log::getInstance()->AddRow(' REPORT->generate Email result ' . $template . " - " . json_encode($resultGenerateInvoice) . " - ");
            
            $pathInvoice = isset($resultGenerateInvoice['path']) ? $resultGenerateInvoice['path'] : '';
            $this->sendEmail($to, $from, $fromName, $subject, $resultTemplateMessage, $ccArray, $pathInvoice);
        }
    }

    public function sendEmailCustomData($data, $templateName, $workspaceId)
    {
        $laf = new MongoObjectFactory();
        $criteria = array(
            'name' => $templateName,
            'parent.$id' => $workspaceId
        );
        $resultTemplate = $laf->findObjectByCriteria("Template", $criteria);
        // backupo
        $backupOrg = isset($_SESSION['organization']) ? $_SESSION['organization'] : null;
        $backupDbname = isset($_SESSION['dbname']) ? $_SESSION['dbname'] : null;
        if ($resultTemplate === null && $this->checkGeneralEmail($templateName)) {
            $_SESSION['organization'] = 'Samsa';
            $_SESSION['dbname'] = 'Samsa';
            $criteria = array(
                'name' => self::$generalEmails[$templateName]
            );
            $resultTemplate = $laf->findObjectByCriteria("Template", $criteria);
        }
        $subject = isset($resultTemplate['subject']) ? $resultTemplate['subject'] : 'Subject';

        if (! empty($resultTemplate)) {
            $template = $this->reportBuilder($resultTemplate['text'], $data);
        } else {
            $template = "No template!";
        }
        $this->sendEmail($data['to'], $data['from'], $data['fromName'], $subject, $template);
        $_SESSION['organization'] = $backupOrg;
        $_SESSION['dbname'] = $backupDbname;

    }

    private function checkGeneralEmail($name)
    {
        return isset(self::$generalEmails[$name]);
    }

    /**
     *
     * @param string $to            
     * @param string $from            
     * @param string $fromName            
     * @param string $subject            
     * @param string $htmlContent            
     * @param string $ccEmail            
     * @param string $attachment
     *            - pathToAttachment
     * @throws \Exception
     */
    public function sendEmail($to, $from, $fromName, $subject, $htmlContent, $ccEmail = array(), $attachment = '')
    {
        $serviceLocator = ServiceLocatorFactory::getInstance();
        $mongoObjectFactory = new MongoObjectFactory();
        $criteria = array(
            'name' => 'emailConfiguration'
        );
        $configuration = $mongoObjectFactory->findObjectInstanceByCriteria('Configuration', $criteria);
        if ($configuration != null) {
            $smtpConfig = json_decode($configuration->valuestr, true);
        } else {
            $config = $serviceLocator->get('config');
            if (isset($config['smtp_transport'])) {
                $smtpConfig = $config['smtp_transport'];
            } else {
                return "no config";
            }
        }
        $addressList = new \Zend\Mail\AddressList();
        $addressList->add($from);
        if (count($ccEmail) > 0) {
            foreach ($ccEmail as $cc) {
                if ($cc != '') {
                    $addressList->add($cc);
                }
            }
        }
        try {
            $transport = new \Zend\Mail\Transport\Smtp();
            $options = new \Zend\Mail\Transport\SmtpOptions($smtpConfig);
            $transport->setOptions($options);
            $message = new \Zend\Mail\Message();
            $message->setEncoding("UTF-8");
            $message->addFrom($from, $fromName)
                ->addTo($to)
                ->addReplyTo($addressList)
                ->setSubject($subject);
            if (count($ccEmail) > 0) {
                foreach ($ccEmail as $cc) {
                    if ($cc != '') {
                        $message->addCc($cc);
                    }
                }
            }
            // make a header as html
            
            $html = new \Zend\Mime\Part($htmlContent);
            $html->type = "text/html";
            $body = new \Zend\Mime\Message();
            /*
             * $body->setParts(array(
             * $html
             * ));
             */
            if ($attachment != '') {
                $attachmentPart = new \Zend\Mime\Part(file_get_contents($attachment));
                $attachmentPart->type = \Zend\Mime\Mime::TYPE_OCTETSTREAM;
                $attachmentPart->filename = basename($attachment);
                $attachmentPart->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
                $attachmentPart->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
                $body->setParts(array(
                    $html,
                    $attachmentPart
                ));
            } else {
                $body->setParts(array(
                    $html
                ));
            }
            
            $message->setBody($body);
            $transport->send($message);
        } catch (\Exception $e) {
            // throw $e;
        }
    }

    public function sendEmailPhp($to, $from, $fromName, $subject, $htmlContent, $ccEmail = null)
    {
        $serviceLocator = ServiceLocatorFactory::getInstance();
        $config = $serviceLocator->get('config');
        
        /*
         * ini_set("SMTP","ssl://smtp.gmail.com");
         * ini_set("smtp_port","465");
         */
        
        /*
         * ini_set('auth_username', 'testoohmihai@gmail.com');
         * ini_set('auth_password', 'testeazaemail');
         */
        
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= "X-Priority: 1 (Highest)\n";
        $headers .= "X-MSMail-Priority: High\n";
        $headers .= "Importance: High\n";
        
        // $headers .= 'To: Mihai <codymihai@yahoo.com>' . "\r\n";
        $headers .= 'From: ' . $fromName . ' <' . $from . '>' . "\r\n";
        if ($ccEmail != null) {
            $headers .= 'Cc: <' . $ccEmail . '>' . "\r\n";
        }
        
        try {
            $result = mail($to, $subject, $htmlContent, $headers);
        } catch (\Exception $e) {
            print_r($e->getMessage());
            exit();
        }
    }
}
