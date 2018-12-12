<?php
/**
 * @purpose: 模块配置
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/20
 * @version: 1.0
 */
return [
    'modules' => [
        'api' => [
            'className' => 'Apps\Modules\Api\Module',
            'path' => BASE_PATH . '/apps/Modules/Api/Module.php',
            'name' => 'api',
            'isDefault' => 0,
        ],
        'backend' => [
            'className' => 'Apps\Modules\Backend\Module',
            'path' => BASE_PATH . '/apps/Modules/Backend/Module.php',
            'name' => 'admin',
            'isDefault' => 0,
        ],
        'frontend' => [
            'className' => 'Apps\Modules\Frontend\Module',
            'path' => BASE_PATH . '/apps/Modules/Frontend/Module.php',
            'name' => '',
            'isDefault' => 1,
        ],
    ]
];