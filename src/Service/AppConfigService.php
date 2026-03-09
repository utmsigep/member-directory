<?php

namespace App\Service;

use SebastianBergmann\Version;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AppConfigService
{
    public $app_name;

    public $app_logo;

    public $version;

    public function __construct(ParameterBagInterface $params)
    {
        $this->app_name = $params->get('app.name');
        $this->app_logo = $params->get('app.logo');
        $this->version = $this->getVersion($params->get('kernel.project_dir'));
    }

    private function getVersion(string $projectDirectory): string
    {
        if ('prod' != $_ENV['APP_ENV']) {
            return (new Version(trim(file_get_contents($projectDirectory.'/VERSION')), $projectDirectory))->asString();
        }

        return trim(file_get_contents($projectDirectory.'/VERSION'));
    }
}
