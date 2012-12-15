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

/**
 * Serve assets from the webrole.
 */
class WebRoleStrategy extends AssetStrategy
{
    public function deploy($documentRoot, $buildNumber)
    {
        // cleanup webdirectory, otherwise it gets huge.
        $assetPath = $documentRoot . DIRECTORY_SEPARATOR . "v%d";
        $filesystem = $this->container->get('filesystem');
        for ($i = $buildNumber; $i > 0; $i--) {
            $oldAssetPath = sprintf($assetPath, $i);
            if (file_exists($oldAssetPath)) {
                $filesystem->remove($oldAssetPath);
            }
        }

        $this->moveTo($documentRoot . DIRECTORY_SEPARATOR . "v". $buildNumber);
    }

    protected function getFilesystem()
    {
        return $this->container->get('filesystem');
    }
}

