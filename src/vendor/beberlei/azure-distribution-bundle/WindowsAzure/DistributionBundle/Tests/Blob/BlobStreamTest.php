<?php

namespace WindowsAzure\DistributionBundle\Tests\Blob;

use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Blob\Models\ListContainersOptions;
use WindowsAzure\DistributionBundle\Blob\Stream;

class BlobTest extends \PHPUnit_Framework_TestCase
{
    const CONTAINER_PREFIX = 'aztest';

    protected static $path;
    protected static $uniqId;
    protected static $uniqStart;

    protected function setUp()
    {
        self::$path = dirname(__FILE__).'/_files/';
        date_default_timezone_set('UTC');

        if (in_array('azure', stream_get_wrappers())) {
            stream_wrapper_unregister('azure');
        }
    }

    protected function tearDown()
    {
        $blobClient = $this->createBlobClient();
        for ($i = self::$uniqStart; $i <= self::$uniqId; $i++) {
            try {
                $blobClient->deleteContainer( self::CONTAINER_PREFIX . $i);
            } catch (\Exception $e) {
            }
        }
    }

    protected function createBlobClient()
    {
        if ( ! isset($GLOBALS['AZURE_BLOB_CONNECTION'])) {
            $this->markTestSkipped("Configure <php><var name=\"AZURE_BLOB_CONNECTION\" value=\"\"></php> to run the blob  tests.");
        }

        $proxy = ServicesBuilder::getInstance()->createBlobService($GLOBALS['AZURE_BLOB_CONNECTION']);

        if (in_array('azure', stream_get_wrappers())) {
            stream_wrapper_unregister('azure');
        }
        Stream::register($proxy, 'azure');

        return $proxy;
    }

    protected function generateName()
    {
        if (self::$uniqId === null) {
            self::$uniqId = self::$uniqStart = time();
        }
        self::$uniqId++;
        return self::CONTAINER_PREFIX . self::$uniqId;
    }
    /**
     * Test read file
     */
    public function testReadFile()
    {
        $containerName = $this->generateName();
        $fileName = 'azure://' . $containerName . '/test.txt';

        $blobClient = $this->createBlobClient();

        $fh = fopen($fileName, 'w');
        fwrite($fh, "Hello world!");
        fclose($fh);

        $result = file_get_contents($fileName);

        $this->assertEquals('Hello world!', $result);
    }

    /**
     * Test write file
     */
    public function testWriteFile()
    {
        $containerName = $this->generateName();
        $fileName = 'azure://' . $containerName . '/test.txt';

        $blobClient = $this->createBlobClient();

        $fh = fopen($fileName, 'w');
        fwrite($fh, "Hello world!");
        fclose($fh);

        $instance = $blobClient->getBlobProperties($containerName, 'test.txt');
        $this->assertEquals(12, $instance->getProperties()->getContentLength());
    }

    /**
     * Test unlink file
     */
    public function testUnlinkFile()
    {
        $containerName = $this->generateName();
        $fileName = 'azure://' . $containerName . '/test.txt';

        $blobClient = $this->createBlobClient();

        $fh = fopen($fileName, 'w');
        fwrite($fh, "Hello world!");
        fclose($fh);

        unlink($fileName);

        $result = $blobClient->listBlobs($containerName);
        $this->assertEquals(0, count($result->getBlobs()));
    }

    /**
     * Test copy file
     */
    public function testCopyFile()
    {
        $containerName = $this->generateName();
        $sourceFileName = 'azure://' . $containerName . '/test.txt';
        $destinationFileName = 'azure://' . $containerName . '/test2.txt';

        $blobClient = $this->createBlobClient();

        $fh = fopen($sourceFileName, 'w');
        fwrite($fh, "Hello world!");
        fclose($fh);

        copy($sourceFileName, $destinationFileName);

        $instance = $blobClient->getBlobProperties($containerName, 'test2.txt');
        $this->assertEquals(12, $instance->getProperties()->getContentLength());
    }

    /**
     * Test rename file
     */
    public function testRenameFile()
    {
        $containerName = $this->generateName();
        $sourceFileName = 'azure://' . $containerName . '/test.txt';
        $destinationFileName = 'azure://' . $containerName . '/test2.txt';

        $blobClient = $this->createBlobClient();

        $fh = fopen($sourceFileName, 'w');
        fwrite($fh, "Hello world!");
        fclose($fh);

        rename($sourceFileName, $destinationFileName);

        $instance = $blobClient->getBlobInstance($containerName, 'test2.txt');
        $this->assertEquals('test2.txt', $instance->Name);
    }

    /**
     * Test mkdir
     */
    public function testMkdir()
    {
        $containerName = $this->generateName();

        $blobClient = $this->createBlobClient();

        $current = count($blobClient->listContainers()->getContainers());

        mkdir('azure://' . $containerName);

        $after = count($blobClient->listContainers()->getContainers());

        $this->assertEquals($current + 1, $after, "One new container should exist");

        $options = new ListContainersOptions();
        $options->setPrefix($containerName);
        $this->assertEquals(1, count($blobClient->listContainers($options)->getContainers()));
    }

    /**
     * Test rmdir
     */
    public function testRmdir()
    {
        $containerName = $this->generateName();

        $blobClient = $this->createBlobClient();

        mkdir('azure://' . $containerName);
        rmdir('azure://' . $containerName);

        $options = new ListContainersOptions();
        $options->setPrefix($containerName);
        $result = $blobClient->listContainers($options);

        $this->assertEquals(0, count($result->getContainers()));
    }

    /**
     * Test opendir
     */
    public function testOpendir()
    {
        $containerName = $this->generateName();
        $blobClient = $this->createBlobClient();
        $blobClient->createContainer($containerName);

        $blobClient->createBlockBlob($containerName, 'images/WindowsAzure1.gif', file_get_contents(self::$path . 'WindowsAzure.gif'));
        $blobClient->createBlockBlob($containerName, 'images/WindowsAzure2.gif', file_get_contents(self::$path . 'WindowsAzure.gif'));
        $blobClient->createBlockBlob($containerName, 'images/WindowsAzure3.gif', file_get_contents(self::$path . 'WindowsAzure.gif'));
        $blobClient->createBlockBlob($containerName, 'images/WindowsAzure4.gif', file_get_contents(self::$path . 'WindowsAzure.gif'));
        $blobClient->createBlockBlob($containerName, 'images/WindowsAzure5.gif', file_get_contents(self::$path . 'WindowsAzure.gif'));

        $result1 = $blobClient->listBlobs($containerName)->getBlobs();

        $result2 = array();
        if ($handle = opendir('azure://' . $containerName)) {
            while (false !== ($file = readdir($handle))) {
                $result2[] = $file;
            }
            closedir($handle);
        }

        $this->assertEquals(count($result1), count($result2));
    }

    static public function dataNestedDirectory()
    {
        return array(
            array('/nested/test.txt'),
            array('/nested1/nested2/test.txt'),
        );
    }

    /**
     * @dataProvider dataNestedDirectory
     */
    public function testNestedDirectory($file)
    {
        $containerName = $this->generateName();
        $fileName = 'azure://' . $containerName . $file;

        $blobClient = $this->createBlobClient();

        $fh = fopen($fileName, 'w');
        fwrite($fh, "Hello world!");
        fclose($fh);

        $result = file_get_contents($fileName);

        $this->assertEquals('Hello world!', $result);
    }
}

