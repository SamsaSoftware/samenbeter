<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'DataFixture\Controller\DataFixture' => 'DataFixture\Controller\DataFixtureController',
            'DataFixture\Controller\OrganizationFixture' => 'DataFixture\Controller\OrganizationFixtureController',
            'DataFixture\Controller\ILCDataFixture' => 'DataFixture\Controller\ILCDataFixtureController',
            'DataFixture\Controller\ILCUpdateFixture' => 'DataFixture\Controller\ILCUpdateFixtureController',
            'DataFixture\Controller\ILCDemoFixture' => 'DataFixture\Controller\ILCDemoFixtureController',
            'DataFixture\Controller\AutoplanFixture' => 'DataFixture\Controller\AutoplanFixtureController',
            'DataFixture\Controller\CaseManagementFixture' => 'DataFixture\Controller\CaseManagementFixtureController',
            'DataFixture\Controller\ETCDataFixture' => 'DataFixture\Controller\ETCDataFixtureController',
            'DataFixture\Controller\ETCUpdateFixture' => 'DataFixture\Controller\ETCUpdateFixtureController',
            'DataFixture\Controller\EBCDataFixture' => 'DataFixture\Controller\EBCDataFixtureController',
            'DataFixture\Controller\TCSDataFixture' => 'DataFixture\Controller\TCSDataFixtureController',
            'DataFixture\Controller\VECODataFixture' => 'DataFixture\Controller\VECODataFixtureController',
            'DataFixture\Controller\VECOUpdateFixture' => 'DataFixture\Controller\VECOUpdateFixtureController',
            'DataFixture\Controller\CbmnDataFixture' => 'DataFixture\Controller\CbmnDataFixtureController',
            'DataFixture\Controller\issuefixture' => 'DataFixture\Controller\IssueFixtureController',
            'DataFixture\Controller\NwDataFixture' => 'DataFixture\Controller\NwDataFixtureController',
            'DataFixture\Controller\ChatServer' => 'DataFixture\Controller\ChatServerController',
            'DataFixture\Controller\OrderTappDataFixture' => 'DataFixture\Controller\OrderTappDataFixtureController',
            'DataFixture\Controller\MihaiDataFixture' => 'DataFixture\Controller\MihaiDataFixtureController',
            'DataFixture\Controller\IngrooveDataFixture' => 'DataFixture\Controller\IngrooveDataFixtureController',
            'DataFixture\Controller\StartCron' => 'DataFixture\Controller\StartCronController',
            'DataFixture\Controller\PnaDataFixture' => 'DataFixture\Controller\PnaDataFixtureController',
            'DataFixture\Controller\RallocatorDataFixture' => 'DataFixture\Controller\RallocatorDataFixtureController',
            'DataFixture\Controller\ElsigDataFixture' => 'DataFixture\Controller\ElsigDataFixtureController',
            'DataFixture\Controller\SimulatorFixture' => 'DataFixture\Controller\SimulatorFixtureController',
            'DataFixture\Controller\UpdateUsersFixture' => 'DataFixture\Controller\UpdateUsersFixtureController',
        )
        
    ),
    'router' => array(
        'routes' => array()

        
    ),
    
    'console' => array(
        'router' => array(
            'routes' => array(
                'datafixture' => array(
                    'options' => array(
                        'route' => 'run datafixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\DataFixture',
                            'action' => 'run',
                            'moduleName' => 'datafixture'
                        )
                    )
                ),
                'organizationfixture' => array(
                    'options' => array(
                        'route' => 'run organizationfixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\OrganizationFixture',
                            'action' => 'run',
                            'moduleName' => 'datafixture'
                        )
                    )
                ),
                
                'ilcdatafixture' => array(
                    'options' => array(
                        'route' => 'run ilcdatafixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\ILCDataFixture',
                            'action' => 'run',
                            'moduleName' => 'ilcdatafixture'
                        )
                    )
                ), 
                'nwdatafixture' => array(
                    'options' => array(
                        'route' => 'run nwdatafixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\NwDataFixture',
                            'action' => 'run',
                            'moduleName' => 'nwdatafixture'
                        )
                    )
                ),
                'cbmndatafixture' => array(
                    'options' => array(
                        'route' => 'run cbmndatafixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\CbmnDataFixture',
                            'action' => 'run',
                            'moduleName' => 'cbmndatafixture'
                        )
                    )
                ),
                'ilcupdatefixture' => array(
                    'options' => array(
                        'route' => 'run ilcupdatefixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\ILCUpdateFixture',
                            'action' => 'run',
                            'moduleName' => 'ilcupdatefixture'
                        )
                    )
                ),
                'ilcdemofixture' => array(
                    'options' => array(
                        'route' => 'run ilcdemofixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\ILCDemoFixture',
                            'action' => 'run',
                            'moduleName' => 'ilcdemofixture'
                        )
                    )
                ),
                'autoplanfixture' => array(
                    'options' => array(
                        'route' => 'run autoplanfixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\AutoplanFixture',
                            'action' => 'run',
                            'moduleName' => 'autoplanfixture'
                        )
                    )
                ),
                'casemanagementfixture' => array(
                    'options' => array(
                        'route' => 'run casemanagementfixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\CaseManagementFixture',
                            'action' => 'run',
                            'moduleName' => 'casemanagementfixture'
                        )
                    )
                ),
                'etcdatafixture' => array(
                    'options' => array(
                        'route' => 'run etcdatafixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\ETCDataFixture',
                            'action' => 'run',
                            'moduleName' => 'etcdatafixture'
                        )
                    )
                ),
                'etcupdatefixture' => array(
                    'options' => array(
                        'route' => 'run etcupdatefixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\ETCUpdateFixture',
                            'action' => 'run',
                            'moduleName' => 'etcupdatefixture'
                        )
                    )
                ),
                'ebcdatafixture' => array(
                    'options' => array(
                        'route' => 'run ebcdatafixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\EBCDataFixture',
                            'action' => 'run',
                            'moduleName' => 'ebcdatafixture'
                        )
                    )
                ),
                'tcsdatafixture' => array(
                    'options' => array(
                        'route' => 'run tcsdatafixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\TCSDataFixture',
                            'action' => 'run',
                            'moduleName' => 'tcsdatafixture'
                        )
                    )
                ),
                'vecodatafixture' => array(
                    'options' => array(
                        'route' => 'run vecodatafixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\VECODataFixture',
                            'action' => 'run',
                            'moduleName' => 'vecodatafixture'
                        )
                    )
                ),
                'vecoupdatefixture' => array(
                    'options' => array(
                        'route' => 'run vecoupdatefixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\VECOUpdateFixture',
                            'action' => 'run',
                            'moduleName' => 'vecoupdatefixture'
                        )
                    )
                ),
                'issuefixture' => array(
                    'options' => array(
                        'route' => 'run issuefixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\issuefixture',
                            'action' => 'run',
                            'moduleName' => 'issuefixture'
                        )
                    )
                ),
                'chatserver' => array(
                    'options' => array(
                        'route' => 'run chatserver [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\ChatServer',
                            'action' => 'start',
                            'moduleName' => 'chatserver'
                        )
                    )
                ),
                'ordertappdatafixture' => array(
                    'options' => array(
                        'route' => 'run ordertappdatafixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\OrderTappDataFixture',
                            'action' => 'run',
                            'moduleName' => 'ordertappdatafixture'
                        )
                    )
                ),
                'mihaidatafixture' => array(
                    'options' => array(
                        'route' => 'run mihaidatafixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\MihaiDataFixture',
                            'action' => 'run',
                            'moduleName' => 'mihaidatafixture'
                        )
                    )
                ),
                'ingroovedatafixture' => array(
                    'options' => array(
                        'route' => 'run ingroovedatafixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\IngrooveDataFixture',
                            'action' => 'run',
                            'moduleName' => 'ingroovedatafixture'
                        )
                    )
                ),
                
                'startcron' => array(
                    'options' => array(
                        'route' => 'run startcron [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\StartCron',
                            'action' => 'start',
                            'moduleName' => 'startcron'
                        )
                    )
                ),

                'pnadatafixture' => array(
                    'options' => array(
                        'route' => 'run pnadatafixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\PnaDataFixture',
                            'action' => 'run',
                            'moduleName' => 'pnadatafixture'
                        )
                    )
                ),

                'rallocatordatafixture' => array(
                    'options' => array(
                        'route' => 'run rallocatordatafixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\RallocatorDataFixture',
                            'action' => 'run',
                            'moduleName' => 'rallocatordatafixture'
                        )
                    )
                ),
                'elsigdatafixture' => array(
                    'options' => array(
                        'route' => 'run elsigdatafixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\ElsigDataFixture',
                            'action' => 'run',
                            'moduleName' => 'elsigdatafixture'
                        )
                    )
                ),
                'simulatorfixture' => array(
                    'options' => array(
                        'route' => 'run simulatorfixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\SimulatorFixture',
                            'action' => 'run',
                            'moduleName' => 'simulatorfixture'
                        )
                    )
                ),
                'updateusersfixture' => array(
                    'options' => array(
                        'route' => 'run updateusersfixture [--verbose|-v]',
                        'defaults' => array(
                            'controller' => 'DataFixture\Controller\UpdateUsersFixture',
                            'action' => 'run',
                            'moduleName' => 'updateusersfixture'
                        )
                    )
                )
            )
        )
    )
);