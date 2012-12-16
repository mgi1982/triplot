<?php
namespace WindowsAzure\DistributionBundle\Tests\Deployment;

use WindowsAzure\DistributionBundle\Deployment\RemoteDesktopCertificate;

class RemoteDesktopCertificateTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if ( ! extension_loaded('openssl')) {
            $this->markTestSkipped('OpenSSL is necessary for this test.');
        }
    }

    public function testGenerate()
    {
        $certificate       = RemoteDesktopCertificate::generate();
        $x509File          = $certificate->export(sys_get_temp_dir(), "azure_test", "test1234", true);
        $thumbprint        = $certificate->getThumbprint();
        $encryptedPassword = $certificate->encryptAccountPassword($x509File, "1234test");

        $this->assertEquals(40, strlen($thumbprint), "SHA1 Key");
        $this->assertTrue(strlen($encryptedPassword) > 0);
    }
}
