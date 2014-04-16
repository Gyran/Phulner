<?php
namespace Phulner;

use Phulner\Vulnerability\VulnerabilityAbstract;
use Phulner\File;
use Phulner\File\Part;

class Generator {
    public function __construct() {
        $this->_vulnerabilities = [];
        $this->_outPath = "";
    }

    public function fromConfig($config) {
        $projectPath = $config->project;
        if (!is_dir($projectPath)) {
            die(stringColor(sprintf("No project found at %s\n", $projectPath), "0;31"));
        }
        $project = new Project($projectPath);
        $project->configFromFile();
        $this->addProject($project);

        $this->_vulnerabilities = $config->vulnerabilities;

        $this->setOutPath($config->out);
    }

    public function setOutPath ($path) {
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                die(stringColor(sprintf("Can't create output path %s\n", $path), "0;31"));
            }
        }

        $this->_outPath = $path;
    }

    public function addProject (Project $project) {
        $this->_project = $project;
    }

    public function addVulnerability (VulnerabilityAbstract $vulnerability) {
        $this->_vulnerabilities[] = $vulnerability;
    }

    public function generate () {
        $source = $this->_project->basePath();
        $destination = $this->_outPath;
        echo stringColor("Copying project to " . $destination . "\n", "1;34");
        $copyCommand = "cp -r '" . $this->_project->basePath() . "'* '" . $this->_outPath . "'";
        exec($copyCommand);

        $this->_project->parseFiles();
        $files = $this->_project->getFiles();

        foreach ($files as $file) {
            $newFile = $this->_handleFile($file);
            $this->_writeFile($this->_outPath, $newFile);
        }
    }

    private function _handleFile (File $file) {
        $newFile = new File($file->getPath());

        foreach ($file as $part) {
            $newPart = $this->_handlePart($part);
            $newFile->addPart($newPart);
        }

        return $newFile;
    }

    private function _handlePart (Part $part) {
        $method = "_handlePart_" . $part->getType();
        if (method_exists($this, $method)) {
            return $this->$method($part);
        }
        throw new \Exception(sprintf("Can't handle part type %s", $part->getType()));
    }

    private function _handlePart_Vulnerability (Part\Vulnerability $part) {
        $partConfig = $part->getConfig();
        $vulnerability = $this->_getVulnerability($partConfig->identifier);

        if ($vulnerability === null) { // shall not be injected
            $newPart = new Part\Block($part->toString());
            return $newPart;
        }

        $vulnerabilityConfig = $this->_project->getVulnerabilityConfig($partConfig->identifier);

        $injector = $vulnerabilityConfig->getInjector($partConfig);

        $injectedCode = $injector->inject($part->getCode(), $vulnerability);

        $newPart = new Part\Block($injectedCode);

        return $newPart;
    }

    private function _handlePart_Block (Part\Block $part) {
        return $part;
    }

    private function _writeFile ($path, $file) {
        echo stringColor("writing file " . $file->getPath() . "\n", "1;34");
        file_put_contents($path . $file->getPath(), $file->toString());
    }

    private function _getVulnerability ($identifier) {
        if (isset($this->_vulnerabilities->$identifier) && $this->_vulnerabilities->$identifier->inject) {
            return $this->_vulnerabilities->$identifier;
        }
        return null;
    }

    private $_project;
    private $_vulnerabilities;
    private $_outPath;
}
