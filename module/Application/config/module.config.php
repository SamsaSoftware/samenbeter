<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

//print __DIR__ . '/../src/' . __NAMESPACE__ . 'Document';exit;
date_default_timezone_set('UTC');
return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/application[/:action][/:id][?mode=:mode]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9.-]*',
                        'id' => '[a-zA-Z0-9.-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                        'moduleName' => 'application',
                    ),
                ),
                'may_terminate' => true,
            ),
            'user' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/user[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action'     => 'index',
                    ),
                ),
            ),
            'organization' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/organization[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Organization',
                        'action'     => 'index',
                    ),
                ),
            ),
            'terms' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/terms[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Terms',
                        'action'     => 'terms',
                    ),
                ),
            ),
            'template' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/template[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Template',
                        'action'     => 'index',
                    ),
                ),
            ),

        ),
    ),
   'doctrine' => array(
        'driver' => array(
            'odm_driver' => array(
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . 'Document')
            ),
            'odm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Document' => 'odm_driver'
                )
            )
        )
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
        'factories' => array(
            'Navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
        )
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type' => 'phparray',
                'base_dir' => __DIR__ . '/../../../lang',
                'pattern' => '%s.php',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\User' => 'Application\Controller\UserController',
            'Application\Controller\Organization' => 'Application\Controller\OrganizationController',
            'Application\Controller\Cron' => 'Application\Controller\CronController',
            'Application\Controller\Terms' => 'Application\Controller\TermsController',
            'Application\Controller\Template' => 'Application\Controller\TemplateController'
        ),
    ),
    'navigation' => array(
        'default' => array(
            array(
                'label' => 'Home',
                'route' => 'home',
                'action' => 'view',
                'resource' => 'home',
                'privilege' => 'view'

            ), array(
                'label' => 'Admin',
                'route' => 'home',
                'action' => 'viewadmin',
                'query' => array('mode' => 'admin'),
                'resource' => 'home',
                'privilege' => 'viewadmin'

            ), array(
                'label' => 'Organizations',
                'route' => 'organization',
                'action' => 'list',
                'resource' => 'organization',
                'privilege' => 'list'

            ), array(
                'label' => 'Users',
                'route' => 'user',
                'action' => 'list',
                'resource' => 'user',
                'privilege' => 'list'

            ), array(
                'label' => 'Templates',
                'route' => 'template',
                'action' => 'index',
                'resource' => 'template',
                'privilege' => 'index'

            )

        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
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
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
                'cron' => array(
                    'options' => array(
                        'route' => 'run cron [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Cron',
                            'action' => 'run',
                            'moduleName' => 'application'
                        )
                    )
                )
            ),
        ),
    )

);
