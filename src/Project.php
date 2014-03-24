<?php
namespace Phulner;

class Project {
    public function getAffectedFiles () {
        return $this->_affectedFiles;
    }

    public function getVulnerabilityConfigs () {
        return $this->_vulnerabilityConfigs;
    }

    public function getFiles () {
        return $this->_files;
    }

    public function __construct ($path) {
        $this->_path = $path;
        $this->_affectedFiles = [];
    }

    public function addConfig ($config) {
        $this->_vulnerabilityConfigs = [];
        $this->_name = $config->name;

        foreach ($config->vulnerabilities as $identifier => $vulnerabilityConfig) {
            $this->addVulnerabilityConfig($identifier, $vulnerabilityConfig);
        }
        $this->_affectedFiles = array_unique($this->_affectedFiles);
    }

    public function addVulnerabilityConfig ($identifier, $config) {
        $configClass = "Phulner\\Vulnerability\\Config\\" . ucfirst($config->type);
        if (class_exists($configClass)) {
            $this->_vulnerabilityConfigs[$identifier] = new $configClass($identifier, $config);
            $this->_affectedFiles = array_merge($this->_affectedFiles, $this->_vulnerabilityConfigs[$identifier]->getFiles());
        }
    }

    public function configFromFile ($file = "") {
        if ($file === "") {
            $file = "phulner.json";
        }
        $config = json_decode(file_get_contents($this->path($file)));
        $this->addConfig($config);
    }

    public function parseFiles () {
        $parser = new Parser;

        foreach ($this->_affectedFiles as $file) {
            $this->_files[$file] = $parser->parse($this->path($file));
        }
    }

    public function path ($sub = "") {
        return $this->_path . $sub;
    }


/*
    public function parse () {
        $vul = $this->_vulnerabilityConfigs["xss1"];
        $files = $vul->getFiles();

        $file = $this->_parser->parse($this->_path . $files[0]);
        //print_r($file);
        echo $file->toString();

    }
*/

    private $_name;
    private $_vulnerabilityConfigs;
    private $_path;
    private $_affectedFiles;
    private $_files;
    //private $_config;
}


?>