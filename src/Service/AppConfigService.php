<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AppConfigService
{
    public $app_name;

    public $app_logo;

    public function __construct(ParameterBagInterface $params)
    {
        $this->app_name = $params->get('app.name');
        $this->app_logo = $params->get('app.logo');
    }
}
