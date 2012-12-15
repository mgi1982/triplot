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

/**
 * Wraps the ServiceConfiguration.csdef file and allows convenient access.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class ServiceConfiguration
{
    /**
     * @var string
     */
    private $serviceConfigurationFile;

    /**
     * @var DOMDocument
     */
    private $dom;

    /**
     * @var array
     */
    private $storage;

    /**
     * @param string $serviceConfigurationFile
     * @param array $storage
     */
    public function __construct($serviceConfigurationFile, array $storage = array())
    {
        if (!file_exists($serviceConfigurationFile)) {
            throw new \InvalidArgumentException(sprintf(
                "No valid file-path given. The ServiceConfiguration should be at %s but could not be found.",
                $serviceConfigurationFile
            ));
        }

        $this->serviceConfigurationFile = $serviceConfigurationFile;
        $this->storage                  = $storage;
        $this->dom                      = new \DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput        = true;
        $this->dom->load($this->serviceConfigurationFile);
    }

    public function getPath()
    {
        return $this->serviceConfigurationFile;
    }

    /**
     * Add a role to service configuration
     *
     * @param string $name
     */
    public function addRole($name)
    {
        $namespaceUri = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);

        $roleNode = $this->dom->createElementNS($namespaceUri, 'Role');
        $roleNode->setAttribute('name', $name);

        $instancesNode = $this->dom->createElementNS($namespaceUri, 'Instances');
        $instancesNode->setAttribute('count', '2');

        $configurationSettings = $this->dom->createElementNS($namespaceUri, 'ConfigurationSettings');

        $roleNode->appendChild($instancesNode);
        $roleNode->appendChild($configurationSettings);

        $this->dom->documentElement->appendChild($roleNode);

        $this->save();
    }

    private function save()
    {
        if ($this->dom->save($this->serviceConfigurationFile) === false) {
            throw new \RuntimeException(sprintf("Could not write ServiceConfiguration to '%s'",
                        $this->serviceConfigurationFile));
        }
    }

    /**
     * Copy ServiceConfiguration over to build directory given with target path
     * and modify some of the settings to point to development settings.
     *
     * @param string $targetPath
     * @return void
     */
    public function copyForDeployment($targetPath, $development = true)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadXML($this->dom->saveXML());

        $xpath = new \DOMXpath($dom);
        $xpath->registerNamespace('sc', $dom->lookupNamespaceUri($dom->namespaceURI));
        $settings = $xpath->evaluate('//sc:ConfigurationSettings/sc:Setting[@name="Microsoft.WindowsAzure.Plugins.Diagnostics.ConnectionString"]');
        foreach ($settings as $setting) {
            if ($development) {
                $setting->setAttribute('value', 'UseDevelopmentStorage=true');
            } else if (strlen($setting->getAttribute('value')) === 0) {
                if ($this->storage) {
                    $setting->setAttribute('value', sprintf('DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s',
                        $this->storage['accountName'], $this->storage['accountKey']
                    ));
                } else {
                    throw new \RuntimeException(<<<EXC
ServiceConfiguration.csdef: Missing value for
'Microsoft.WindowsAzure.Plugins.Diagnostics.ConnectionString'.

You have to modify the app/azure/ServiceConfiguration.csdef to contain
a value for the diagnostics connection string or better configure
'windows_azure_distribution.diagnostics.accountName' and
'windows_azure_distribution.diagnostics.accountKey' in your
app/config/config.yml

If you don't want to enable diagnostics you should delete the
connection string elements from ServiceConfiguration.csdef file.
EXC
                    );
                }
            }
        }

        $dom->save($targetPath . '/ServiceConfiguration.cscfg');
    }

    /**
     * Add a configuration setting to the ServiceConfiguration.cscfg
     *
     * @param string $name
     * @param string $value
     */
    public function setConfigurationSetting($roleName, $name, $value)
    {
        $namespaceUri = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
        $xpath = new \DOMXpath($this->dom);
        $xpath->registerNamespace('sc', $namespaceUri);

        $xpathExpression = '//sc:Role[@name="' . $roleName . '"]//sc:ConfigurationSettings//sc:Setting[@name="' . $name . '"]';
        $settingList     = $xpath->evaluate($xpathExpression);

        if ($settingList->length == 1) {
            $settingNode = $settingList->item(0);
        } else {
            $settingNode = $this->dom->createElementNS($namespaceUri, 'Setting');
            $settingNode->setAttribute('name', $name);

            $configSettingList = $xpath->evaluate('//sc:Role[@name="' . $roleName . '"]/sc:ConfigurationSettings');

            if ($configSettingList->length == 0) {
                throw new \RuntimeException("Cannot find <ConfigurationSettings /> in Role '" . $roleName . "'.");
            }

            $configSettings = $configSettingList->item(0);
            $configSettings->appendChild($settingNode);
        }

        $settingNode->setAttribute('value', $value);

        $this->save();
    }

    /**
     * Add Certificate to the Role
     *
     * @param string $roleName
     * @param RemoteDesktopCertificate $certificate
     */
    public function addCertificate($roleName, RemoteDesktopCertificate $certificate)
    {
        $namespaceUri = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
        $xpath = new \DOMXpath($this->dom);
        $xpath->registerNamespace('sc', $namespaceUri);

        $xpathExpression = '//sc:Role[@name="' . $roleName . '"]//sc:Certificates';
        $certificateList = $xpath->evaluate($xpathExpression);

        if ($certificateList->length == 1) {
            $certificatesNode = $certificateList->item(0);

            foreach ($certificatesNode->childNodes as $certificateNode) {
                $certificatesNode->removeElement($certificateNode);
            }
        } else {
            $certificatesNode = $this->dom->createElementNS($namespaceUri, 'Certificates');

            $roleNodeList = $xpath->evaluate('//sc:Role[@name="' . $roleName . '"]');

            if ($roleNodeList->length == 0) {
                throw new \RuntimeException("No Role found with name '" . $roleName . "'.");
            }

            $roleNode = $roleNodeList->item(0);
            $roleNode->appendChild($certificatesNode);
        }

        $certificateNode = $this->dom->createElementNS($namespaceUri, 'Certificate');
        $certificateNode->setAttribute('name', 'Microsoft.WindowsAzure.Plugins.RemoteAccess.PasswordEncryption');
        $certificateNode->setAttribute('thumbprint', $certificate->getThumbprint());
        $certificateNode->setAttribute('thumbprintAlgorithm', 'sha1');

        $certificatesNode->appendChild($certificateNode);

        $this->save();
    }

    public function getXml()
    {
        return $this->dom->saveXml();
    }
}

