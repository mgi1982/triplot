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

namespace WindowsAzure\DistributionBundle\Filesystem;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Azure Filesystem that handles mkdir() correctly.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class AzureFilesystem extends Filesystem
{
    public function mkdir($dirs, $mode = 0777)
    {
        $ret = true;
        foreach ($this->toIterator($dirs) as $dir) {
            if (is_dir($dir)) {
                continue;
            }

            if (strpos($dir, 'azure://') === 0) {
                $dir = substr($dir, 8);
            }

            if (count(explode("/", rtrim($dir, "/"))) > 1) {
                $ret = true;
            } else {
                $ret = @mkdir($dir, $mode, true) && $ret;
            }
        }

        return $ret;
    }

    private function toIterator($files)
    {
        if (!$files instanceof \Traversable) {
            $files = new \ArrayObject(is_array($files) ? $files : array($files));
        }

        return $files;
    }
}
