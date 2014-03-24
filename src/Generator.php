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
        $projectPath = $config->project . "/";
        $project = new Project($projectPath);
        $project->configFromFile();
        $this->addProject($project);

        /*$vulnerabilityConfigs = $this->_project->getVulnerabilityConfigs();
        $this->_project->parseFiles();
        $files = $this->_project->getFiles();*/

        $this->_vulnerabilities = $config->vulnerabilities;

        $this->_outPath = $config->out . "/";

        //foreach ($config->vulnerabilities as $vulnerability) {

        //}

    }

    public function addProject (Project $project) {
        $this->_project = $project;
    }

    public function addVulnerability (VulnerabilityAbstract $vulnerability) {
        $this->_vulnerabilities[] = $vulnerability;
    }

    public function wutwut () {
        $this->_project->parseFiles();
        $files = $this->_project->getFiles();
        $vulnerabilityConfigs = $this->_project->getVulnerabilityConfigs();

        $newFiles = [];

        //print_r($files["input.php"]->toString());

        foreach ($files as $file) {
            /*if ($file->getPath() != "test/output.php") {
                continue;
            }*/

            $newFile = new File($file->getPath());

            foreach ($file as $part) {
                //echo get_class($part), "\n";
                if ($part instanceof Part\Vulnerability) {
                    $partConfig = $part->getConfig();
                    $identifier = $partConfig->identifier;

                    if (isset($this->_vulnerabilities->$identifier)) {
                        $injector = $vulnerabilityConfigs[$identifier]->getInjector($partConfig);

                        $injectedCode = $injector->inject($part->getCode(), $this->_vulnerabilities->$identifier);

                        //echo "injectedPart:\n";
                        //echo $injectedCode, "\n";

                        $injectedPart = new Part\Block($injectedCode);


                        /*$injector = new Injector\Xss;
                        $injector->setSanitationFunctionsFactory(new SanitationFunction\Factory);

                        print_r($config);

                        $injector->inject($part->getCode(), $this->_vulnerabilities->$identifier);
                        */
                        //print_r($vulnerabilityConfigs[$identifier]);

                        //echo $identifier, " isset\n";

                        $newFile->addPart($injectedPart);
                    }

                    //print_r($vulnerabilityConfigs[$config->identifier]);


                    //echo "is code\n";
                    //print_r($part->getConfig());
                } else {
                    $newFile->addPart($part);
                }

            }

            echo "File: ", $newFile->getPath(), "\n";
            echo $newFile->toString();
            echo "========\n";

            $newFiles[] = $newFile;
        }

        foreach ($newFiles as $file) {
            $outPath = $this->_outPath . $file->getPath();
            @mkdir(dirname($outPath));
            file_put_contents($outPath, $file->toString());
        }


        /*
        foreach ($this->_vulnerabilities as $vulnerability) {
            print_r($vulnerability);
        }
        */

        //$this->_project
    }

    private $_project;
    private $_vulnerabilities;
    private $_outPath;
}
