<?php

namespace App\Service;

class AppConfigService
{
    public $app_name;

    public $app_logo;

    public function __construct()
    {
        $this->app_name = getenv('APP_NAME') ? getenv('APP_NAME') : 'Member Directory';
        $this->app_logo = getenv('APP_LOGO');
    }
}
