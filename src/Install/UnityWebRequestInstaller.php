<?php
namespace Cmrweb\UnityWebRequest\Install;

use Symfony\Component\Yaml\Yaml;

class UnityWebRequestInstaller 
{ 
 
    
    public static function install()
    {
        $servicesPath = __DIR__.'/../../../config/services.yaml'; 
        $services = Yaml::parseFile($servicesPath); 
        $services["parameters"]["unity_web_token"] = "%env(UNITY_REQUEST_TOKEN)%";
        $yaml = Yaml::dump($services); 
        file_put_contents($servicesPath, $yaml);

        $envPath = __DIR__.'/../../../.env';
        $env = file_get_contents($envPath);
        $envKey = "\n###> cmrweb/unity_web_request ###\nUNITY_REQUEST_TOKEN='mySuperSecretToken'\n###< cmrweb/unity_web_request ### ";
        file_put_contents($envPath, $env.$envKey);

    } 
}