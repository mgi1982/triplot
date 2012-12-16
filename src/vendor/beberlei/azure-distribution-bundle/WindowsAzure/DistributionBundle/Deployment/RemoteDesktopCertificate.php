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
 * Remote Desktop Certificate helping with generation of the correct needed
 * certificate for remote desktoping certificates with Windows Azure.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class RemoteDesktopCertificate
{
    /**
     * @var resource
     */
    private $privKey;

    /**
     * @var resource
     */
    private $certificate;

    /**
     * Generate a pkcs12 file and private key to use with remote desktoping.
     *
     * @param string $password
     * @return RemoteDesktopCertificate
     */
    static public function generate()
    {
        if ( ! extension_loaded('openssl')) {
            throw new \RuntimeException("Can only generate a remote desktop certificate when OpenSSL PHP extension is installed.");
        }

        // Generate a new private (and public) key pair
        $config = array('config' => __DIR__ . '/../Resources/config/openssl.cnf');
        $privkey = openssl_pkey_new($config);

        // Generate a certificate signing request
        $dn     = array("commonName" => "AzureDistributionBundle for Symfony Tools");
        $csr    = openssl_csr_new($dn, $privkey, $config);
        $sscert = openssl_csr_sign($csr, null, $privkey, 365, $config);

        return new self($privkey, $sscert);
    }

    private function __construct($privateKey, $certificate)
    {
        $this->privKey     = $privateKey;
        $this->certificate = $certificate;
    }

    /**
     * Given this Remote Desktop instance, generate files with pkcs12 and
     * x509 certificate to a given directory using a password for the desktop
     * and the private key.
     *
     * Returns the path to the x509 file.
     *
     * @return string
     */
    public function export($directory, $filePrefix, $keyPassword, $overwrite = false)
    {
        if ( ! is_writeable($directory)) {
            throw new \RuntimeException("Key Export directory is not writable: " . $directory);
        }

        $pkcs12File = $directory . "/" . $filePrefix . ".pfx";
        $x509File   = $directory . "/" . $filePrefix . ".cer";

        if ( ! $overwrite && file_exists($pkcs12File)) {
            throw new \RuntimeException("PKCS12 File at " . $pkcs12File . " already exists and is not overwritten.");
        }

        if ( ! $overwrite && file_exists($x509File)) {
            throw new \RuntimeException("X509 Certificate File at " . $x509File . " already exists and is not overwritten.");
        }

        $args = array('friendly_name' => 'AzureDistributionBundle for Symfony Tools');
        openssl_pkcs12_export_to_file($this->certificate, $pkcs12File, $this->privKey, $keyPassword, $args);
        openssl_x509_export_to_file($this->certificate, $x509File, true);

        return $x509File;
    }

    /**
     * Encrypt Account Password
     *
     * @param string $desktopPassword
     * @return string
     */
    public function encryptAccountPassword($x509File, $desktopPassword)
    {
        $directory  = sys_get_temp_dir();
        $filePrefix = "azure";
        $pkcs7In    = $directory . "/" . $filePrefix . "_in.pkcs7";
        $pkcs7Out   = $directory . "/" . $filePrefix . "_out.pkcs7";
        $certificate = openssl_x509_read(file_get_contents($x509File));

        file_put_contents($pkcs7In, $desktopPassword);
        $ret = openssl_pkcs7_encrypt($pkcs7In, $pkcs7Out, $certificate, array());

        if ( ! $ret) {
            throw new \RuntimeException("Encrypting Password failed.");
        }

        $parts = explode("\n\n", file_get_contents($pkcs7Out));
        $body = str_replace("\n", "", $parts[1]);

        unlink($pkcs7In);
        unlink($pkcs7Out);

        return $body;
    }

    /**
     * Generate SHA1 THumbprint of the X509 Certificate
     */
    public function getThumbprint()
    {
        $resource   = openssl_x509_read($this->certificate);
        $thumbprint = null;
        $output     = null;

        $result = openssl_x509_export($resource, $output);
        if($result !== false) {
            $output     = str_replace('-----BEGIN CERTIFICATE-----', '', $output);
            $output     = str_replace('-----END CERTIFICATE-----', '', $output);
            $output     = base64_decode($output);
            $thumbprint = sha1($output);
        }

        return $thumbprint;
    }
}

