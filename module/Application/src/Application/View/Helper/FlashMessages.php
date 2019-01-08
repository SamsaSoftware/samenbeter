<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class FlashMessages extends AbstractHelper
{
    private $messagesType = array(
        'success',
        'error',
        'default'
    );

    private $flashMessages;

    public function __invoke ($flashMessages)
    {
        $this->flashMessages = $flashMessages;
        $this->messages();
    }
    private function messages()
    {
        foreach ($this->messagesType as $type) {
            $messages = $this->getMessages($type);
            if (!empty($messages)) {
                $type = $type === 'error' ? 'danger' : $type;
                echo $this->renderMessages($messages, $type);
            }
        }
    }

    private function getMessages($type)
    {
        $messages = array();
        $this->flashMessages->setNamespace($type);

        if ($this->flashMessages->hasMessages()) {
            $messages = $this->flashMessages->getMessages();
        }

        return $messages;
    }

    private function renderMessages($messages, $type)
    {
        $output = '';
        foreach ($messages as $message) {
            $output .= '<div id="alertContainer" class="alert alert-'.$type.'">';
                $output .= '<i class="icon-remove close" data-dismiss="alert"></i>';
                $output .= $message;
            $output .= '</div>';
        }
        return $output;
    }
}
