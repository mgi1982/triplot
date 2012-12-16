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

namespace WindowsAzure\DistributionBundle\Deployment\Assets;

use Symfony\Component\Finder\Finder;
use Assetic\Util\PathUtils;
use Assetic\AssetWriter;
use Assetic\Asset\AssetInterface;
use Assetic\Factory\LazyAssetManager;

/**
 * Strategy to deploy assets for this application when on Azure
 */
abstract class AssetStrategy
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Deploy the assets of a deployment before packaging is started.
     *
     * The selected strategy depends on the configuration of the site and could
     * be either local to the web role or blob storage for example.
     */
    abstract public function deploy($documentRoot, $buildNumber);

    /**
     * @return Filesystem
     */
    abstract protected function getFilesystem();

    protected function moveTo($targetArg)
    {
        $filesystem = $this->getFilesystem();

        // Create the bundles directory otherwise symlink will fail.
        $filesystem->mkdir($targetArg.'/bundles/', 0777);
        $bundles = $this->container->get('kernel')->getBundles();

        foreach ($bundles as $bundle) {
            if (is_dir($originDir = $bundle->getPath().'/Resources/public')) {
                $targetDir = $targetArg.'/bundles/'.preg_replace('/bundle$/', '', strtolower($bundle->getName()));

                $filesystem->remove($targetDir);
                $filesystem->mkdir($targetDir, 0777);
                // We use a custom iterator to ignore VCS files
                $filesystem->mirror($originDir, $targetDir, Finder::create()->in($originDir));
            }
        }

        if (!$this->container->has('assetic.asset_manager')) {
            return;
        }

        $am = $this->container->get('assetic.asset_manager');

        foreach ($am->getNames() as $name) {
            $this->doDump($am->get($name), $targetArg);
        }
    }

    private function doDump(AssetInterface $asset, $documentRoot)
    {
        $writer = new AssetWriter(sys_get_temp_dir(), $this->container->getParameter('assetic.variables'));
        $ref = new \ReflectionMethod($writer, 'getCombinations');
        $ref->setAccessible(true);
        $combinations = $ref->invoke($writer, $asset->getVars());

        foreach ($combinations as $combination) {
            $asset->setValues($combination);

            $target = rtrim($documentRoot, '/').'/'.str_replace('_controller/', '',
                PathUtils::resolvePath($asset->getTargetPath(), $asset->getVars(),
                    $asset->getValues()));

            if (!is_dir($dir = dirname($target))) {
                if (false === @mkdir($dir, 0777, true)) {
                    throw new \RuntimeException('Unable to create directory '.$dir);
                }
            }

            if (false === @file_put_contents($target, $asset->dump())) {
                throw new \RuntimeException('Unable to write file '.$target);
            }
        }
    }
}

