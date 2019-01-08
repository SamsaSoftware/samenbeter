<?php
//users roles
return array(
    'resources' => array(
        'home',
        'auth',
        'organizationfixture',
        'user',
        'simulatorfixture',
        'updateusersfixture',
        'organization',
        'swagger-resources',
        'swagger-resource-detail',
        'swagger-ui',
        'cron',
        'chat',
        'chatserver',
        'startcron',
        'terms',
        'template'
    ),
    'usersRoles' => array(
        'anonymous' => array(
            'home' => array(
                'index',
                'pubnub'
            ),
            'auth' => array(
                'index',
                'logout',
                'login',
                'samenbeter',
                'signup',
                'createUser'
            ),
            'api' => array(
                'index',
                'login',
                'getMethodResultList',
                'getform',
                'view',
                'request',
                'content',
                'executeAction',
                'generateToken',
                'changePassword'
            ),
            'user' => array(
                'resetPassword',
                'sendMailReset',
                'generateToken',
                'changePasswordAfterReset',
                'createAccount'
            ),
            'terms' => array(
                'terms',
                'privacy',
                'copyright'
            ),
            'organizationfixture' => array(
                'run'
            ),
            'cron' => array(
                'run'
            ),
            'swagger-resources' => array(
                'display'
            ),
            'swagger-resource-detail' => array(
                'details'
            ),
            'swagger-ui' => array(
                'index'
            ),
            'chat' => array(
                'start',
                'index',
            ),
            'chatserver' => array(
                'start'
            ),
            'startcron' => array(
                'start'
            ),
            'simulatorfixture' => array(
                'run'
            ),
            'updateusersfixture' => array(
                'run'
            ),
        ),
        'superadmin' => array(
            'home' => array(
                'index',
                'list',
                'listnew',
                'getlist',
                'listchat',
                'getform',
                'types',
                "getinput",
                "listReference",
                "listOwningReference",
                "listOwningDoubleReference",
                "listOwningSixthReference",
                "getMethodResult",
                "getMethodResultList",
                "getMethodResultListReference",
                "getObject",
                "savefield",
                "view",
                "viewadmin",
                "saveobject",
                "deleteobjects",
                "savestate",
                "popup",
                "popuplist",
                "canvas",
                "export",
                "execute",
                "print",
                "exportgeneral",
                "importgeneral",
                "report",
                "listhtml",
                "listmap",
                "getTemplate",
                "getSchedulerData",
                "import",
                "exportprint"
            ),
            'user' => array(
                'list',
                'add',
                'save',
                'delete',
                'profile',
                'saveprofile',
                'password',
                'savepassword',
                'checkemail',
                'createAccount'
            ),
            'organization' => array(
                'list',
                'add',
                'save',
                'delete'
            ),
            'chat' => array(
                'start',
                'index',
            ),
            'template' => array(
                'index',
                'add',
                'save',
                'delete'
            ),
        ),
        'admin' => array(
            'auth' => array(
                'index',
                'logout',
                'login',
                'samenbeter',
                'signup',
                'createUser'
            ),
            'home' => array(
                'index',
                'list',
                'listnew',
                'getlist',
                'listchat',
                'getform',
                'types',
                "getinput",
                "listReference",
                "listOwningReference",
                "listOwningDoubleReference",
                "listOwningSixthReference",
                "getMethodResult",
                "getMethodResultList",
                "getMethodResultListReference",
                "getObject",
                "savefield",
                "view",
                "viewadmin",
                "saveobject",
                "deleteobjects",
                "savestate",
                "popup",
                "popuplist",
                "canvas",
                "export",
                "execute",
                "print",
                "exportgeneral",
                "importgeneral",
                "report",
                "listhtml",
                "listmap",
                "getTemplate",
                "getSchedulerData",
                "import",
                "exportprint"
            ),
            'user' => array(
                'list',
                'add',
                'save',
                'delete',
                'profile',
                'saveprofile',
                'password',
                'savepassword',
                'setDefaultSettings',
                'checkemail',
                'createAccount'
            ),
            'chat' => array(
                'start',
                'index',
            )

        ),
        'user' => array(
            'auth' => array(
                'index',
                'logout',
                'login',
                'samenbeter',
                'signup',
                'createUser'
            ),
            'home' => array(
                'index',
                'list',
                'listnew',
                'getlist',
                'listchat',
                'getform',
                'types',
                "getinput",
                "listReference",
                "listOwningReference",
                "listOwningDoubleReference",
                "listOwningSixthReference",
                "getMethodResult",
                "getMethodResultList",
                "getMethodResultListReference",
                "getObject",
                "savefield",
                "view",
                "saveobject",
                "deleteobjects",
                "savestate",
                "popup",
                "popuplist",
                "canvas",
                "export",
                "execute",
                "print",
                "exportgeneral",
                "importgeneral",
                "report",
                "listhtml",
                "listmap",
                "getTemplate",
                "getSchedulerData",
                "import"
            ),
            'user' => array(
                'profile',
                'saveprofile',
                'password',
                'savepassword',
                'setDefaultSettings',
                'createAccount'
            ),
            'chat' => array(
                'start',
                'index',
            )
        )
    )
);