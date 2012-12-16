<?php
/**
 * WindowsAzure DistributionBundle
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace WindowsAzure\DistributionBundle\Deployment;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Wraps the ServiceDefinition.csdef file and allows convenient access.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class ServiceDefinition
{
    /**
     * @var string
     */
    private $serviceDefinitionFile;

    /**
     * @var DOMDocument
     */
    private $dom;

    /**
     * @var array
     */
    private $roleFiles = array();

    /**
     * @param string $serviceDefinitionFile
     */
    public function __construct($serviceDefinitionFile, array $roleFiles = array())
    {
        if (!file_exists($serviceDefinitionFile)) {
            throw new \InvalidArgumentException(sprintf(
                "No valid file-path given. The ServiceDefinition should be at %s but could not be found.",
                $serviceDefinitionFile
            ));
        }

        $this->serviceDefinitionFile = $serviceDefinitionFile;
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
        $this->dom->load($this->serviceDefinitionFile);

        $this->mergeRoleFilesConfig($roleFiles);
    }

    private function mergeRoleFilesConfig($roleFiles)
    {
        $this->roleFiles = array(
            'ignoreVCS' => (isset($roleFiles['ignoreVCS'])) ? $roleFiles['ignoreVCS'] : true,
            'exclude' => array('build', 'cache', 'logs', 'tests', 'Tests', 'docs', 'test-suite', 'role_template'),
            'notName' => array('#(.*)\.swp$#')
        );
        if (isset($roleFiles['exclude'])) {
            $this->roleFiles['exclude'] = array_merge($this->roleFiles['exclude'], $roleFiles['exclude']);
        }

        if (isset($roleFiles['include'])) {
            foreach ($roleFiles['include'] as $include) {
                $key = array_search($include, $this->roleFiles['exclude']);
                if ($key !== false) {
                    unset($this->roleFiles[$key]);
                }
            }
        }
        if (isset($roleFiles['ignorePatterns'])) {
            $this->roleFiles['notName'] = array_merge($this->roleFiles['notName'], $roleFiles['ignorePatterns']);
        }
    }

    public function getPath()
    {
        return $this->serviceDefinitionFile;
    }

    public function getNewBuildNumber()
    {
        $dir = dirname($this->getPath());
        $buildFile = $dir . DIRECTORY_SEPARATOR . "build.number";
        if (!file_exists($buildFile)) {
            file_put_contents($buildFile, "0");
        }
        $buildNumber = (int)file_get_contents($buildFile);
        $buildNumber++;
        file_put_contents($buildFile, (string)$buildNumber);

        // TODO: Refactor
        $parametersAzureFile = $dir . "/../config/parameters_azure.yml";
        $config = Yaml::parse($parametersAzureFile);
        $config['parameters']['assets_azure_version'] = $buildNumber;
        $yaml = Yaml::dump($config, 2);
        file_put_contents($parametersAzureFile, $yaml);

        return $buildNumber;
    }

    public function getWebRoleNames()
    {
        return $this->getValues('WebRole', 'name');
    }

    public function getWorkerRoleNames()
    {
        return $this->getValues('WorkerRole', 'name');
    }

    public function getRoleNames()
    {
        return array_merge($this->getWebRoleNames(), $this->getWorkerRoleNames());
    }

    public function addWebRole($name)
    {
        $existingRoles = $this->getRoleNames();
        if (in_array($name, $existingRoles)) {
            throw new \RuntimeException(sprintf("Role with name %s already exists.", $name));
        }

        $webrole = new \DOMDocument('1.0', 'UTF-8');
        $webrole->load(__DIR__ . '/../Resources/role_template/WebRole.xml');

        $roles = $webrole->getElementsByTagName('WebRole');
        $webRoleNode = $roles->item(0);
        $webRoleNode->setAttribute('name', $name);

        $sites = $webrole->getElementsByTagName('Site');
        $siteNode = $sites->item(0);
        $siteNode->setAttribute('physicalDirectory', $name . '\\' );

        $webRoleNode = $this->dom->importNode($webRoleNode, true);
        $this->dom->documentElement->appendChild($webRoleNode);

        $this->save();
    }

    private function save()
    {
        if ($this->dom->save($this->serviceDefinitionFile) === false) {
            throw new \RuntimeException(sprintf("Could not write ServiceDefinition to '%s'",
                $this->serviceDefinitionFile));
        }
    }

    public function addImport($moduleName)
    {
        $importNode = $this->dom->createElement('Import');
        $importNode->setAttribute('moduleName', $moduleName);

        $imports = $this->dom->getElementsByTagName('Imports')->item(0);
        $imports->appendChild($importNode);

        $this->save();
    }

    private function getValues($tagName, $attributeName)
    {
        $nodes = $this->dom->getElementsByTagName($tagName);
        $values = array();
        foreach ($nodes as $node) {
            $values[] = $node->getAttribute($attributeName);
        }
        return $values;
    }

    public function getPhysicalDirectories()
    {
        $nodes = $this->dom->getElementsByTagName('WebRole');
        $dirs = array();
        foreach ($nodes as $node) {
            $sites = $node->getElementsByTagName('Site');

            if (count($sites)) {
                $dirs[$node->getAttribute('name')] = realpath(
                    dirname($this->serviceDefinitionFile) . DIRECTORY_SEPARATOR .
                    rtrim($sites->item(0)->getAttribute('physicalDirectory'), "\\")
                );
            }
        }
        return $dirs;
    }

    public function getPhysicalDirectory($name)
    {
        $dirs = $this->getPhysicalDirectories();
        if (!isset($dirs[$name])) {
            throw new \RuntimeException(sprintf("There exists no role named '%s'.", $name));
        }
        return $dirs[$name];
    }

    /**
     * Create the role files for this service definition.
     *
     * A role file is a semicolon seperated list of target and destination
     * paths. Only these files are then copied during the cspack.exe process to
     * the target deployment directory or package file.
     *
     * @param string $inputDir
     * @param string $outputDir
     * @param string $roleFileDir
     * @return array
     */
    public function createRoleFiles($inputDir, $outputDir, $roleFileDir = null)
    {
        $roleFileDir = $roleFileDir ?: $inputDir;
        $outputDir = realpath($outputDir);
        $seenDirs = array();
        $longPaths = array();
        $roleFiles = array();

        foreach ($this->getWebRoleNames() as $roleName) {
            $dir = realpath($inputDir);
            $roleFilePath = sprintf('%s/%s.roleFiles.txt', $roleFileDir, $roleName);
            $roleFiles[$roleName] = $roleFilePath;

            if (isset($seenDirs[$dir])) {
                // we have seen this directory already, just copy the known
                // file with a new role file name.
                copy($seenDirs[$dir], $roleFilePath);
                continue;
            }
            $seenDirs[$dir] = $roleFilePath;
            $roleFile = $this->computeRoleFileContents($dir, $roleName, $outputDir);

            file_put_contents($roleFilePath, $roleFile);
        }

        if ($longPaths) {
            throw new \RuntimeException("Paths are too long. Not more than 248 chars per directory and 260 per file name allowed:\n" . implode("\n", $longPaths));
        }
        return $roleFiles;
    }

    /**
     * Compute the roleFiles.txt content that is necessary for a given role.
     *
     * @param string $dir
     * @param string $roleName
     * @param string $outputPath
     * @return string
     */
    private function computeRoleFileContents($dir, $roleName, $outputDir)
    {
        $roleFile = "";
        $iterator = $this->getIterator($dir);

        // optimization to inline vendor role files. Since vendor files
        // never change during development, their list can be computed
        // during vendor initialization (composer or bin/vendors scripts)
        // and does not need to be reperformed.
        if (file_exists($dir . '/vendor/azureRoleFiles.txt') &&
            ! in_array("vendor", $this->roleFiles['exclude'])) {

            $roleFile .= file_get_contents($dir . '/vendor/azureRoleFiles.txt');
        }

        $length = strlen($dir) + 1;
        foreach ($iterator as $file) {
            if (is_dir($file)) {
                continue;
            }
            $path = str_replace(DIRECTORY_SEPARATOR, "\\", substr($file, $length));
            $checkPath = sprintf('%s/roles/%s/approot/%s', $outputDir, $roleName, $path);
            if (strlen($checkPath) >= 248) {
                $longPaths[] = $checkPath . " (". strlen($checkPath) . ")";
            }
            $roleFile .= $path .";".$path."\r\n";
        }

        return $roleFile;
    }

    private function getIterator($dir)
    {
        $dirs = new Finder();
        $subdirs = array();
        foreach ($dirs->directories()->in($dir)->depth(0) as $subdir) {
            $subdir = (string)$subdir;
            if (!in_array(basename($subdir), $this->roleFiles['exclude'])) {
                $subdirs[basename($subdir)] = $subdir;
            }
        }

        if (file_exists($dir . '/vendor/azureRoleFiles.txt')) {
            unset($subdirs["vendor"]);
        }

        $finder = new Finder();
        $iterator = $finder->files()
                           ->in($subdirs)
                           ->ignoreDotFiles(false)
                           ->ignoreVCS($this->roleFiles['ignoreVCS']);
        foreach ($this->roleFiles['exclude'] as $exclude) {
            $iterator->exclude($exclude);
        }
        foreach ($this->roleFiles['notName'] as $notName) {
            $iterator->notName($notName);
        }

        return $iterator;
    }
}

