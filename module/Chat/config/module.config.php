<?php

return array(
    'router' => array(
        'routes' => array(
            'chat' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/chat[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9.-]*',
                        'id' => '[a-zA-Z0-9.-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Chat\Controller\Chat',
                        'action'     => 'index',
                        'moduleName' => 'chat'
                    ),
                ),
                'may_terminate' => true,
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Chat\Controller\Chat' => 'Chat\Controller\ChatController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'chat/chat/index' => __DIR__ . '/../view/chat/chat/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
