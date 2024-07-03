<?php

namespace WPMailSMTP\Vendor\Aws\Signature;

use WPMailSMTP\Vendor\Aws\Credentials\CredentialsInterface;
use WPMailSMTP\Vendor\AWS\CRT\Auth\SignatureType;
use WPMailSMTP\Vendor\AWS\CRT\Auth\SigningAlgorithm;
use WPMailSMTP\Vendor\AWS\CRT\Auth\SigningConfigAWS;
use WPMailSMTP\Vendor\Psr\Http\Message\RequestInterface;
/**
 * Amazon S3 signature version 4 support.
 */
class S3SignatureV4 extends \WPMailSMTP\Vendor\Aws\Signature\SignatureV4
{
    /**
     * S3-specific signing logic
     *
     * {@inheritdoc}
     */
    use SignatureTrait;
    public function signRequest(\WPMailSMTP\Vendor\Psr\Http\Message\RequestInterface $request, \WPMailSMTP\Vendor\Aws\Credentials\CredentialsInterface $credentials, $signingService = null)
    {
        // Always add a x-amz-content-sha-256 for data integrity
        if (!$request->hasHeader('x-amz-content-sha256')) {
            $request = $request->withHeader('x-amz-content-sha256', $this->getPayload($request));
        }
        $useCrt = \strpos($request->getUri()->getHost(), "accesspoint.s3-global") !== \false;
        if (!$useCrt) {
            if (\strpos($request->getUri()->getHost(), "s3-object-lambda")) {
                return parent::signRequest($request, $credentials, "s3-object-lambda");
            }
            return parent::signRequest($request, $credentials);
        }
        $signingService = $signingService ?: 's3';
        return $this->signWithV4a($credentials, $request, $signingService);
    }
    /**
     * @param CredentialsInterface $credentials
     * @param RequestInterface $request
     * @param $signingService
     * @param SigningConfigAWS|null $signingConfig
     * @return RequestInterface
     *
     * Instantiates a separate sigv4a signing config.  All services except S3
     * use double encoding.  All services except S3 require path normalization.
     */
    protected function signWithV4a(\WPMailSMTP\Vendor\Aws\Credentials\CredentialsInterface $credentials, \WPMailSMTP\Vendor\Psr\Http\Message\RequestInterface $request, $signingService, \WPMailSMTP\Vendor\AWS\CRT\Auth\SigningConfigAWS $signingConfig = null)
    {
        $this->verifyCRTLoaded();
        $credentials_provider = $this->createCRTStaticCredentialsProvider($credentials);
        $signingConfig = new \WPMailSMTP\Vendor\AWS\CRT\Auth\SigningConfigAWS(['algorithm' => \WPMailSMTP\Vendor\AWS\CRT\Auth\SigningAlgorithm::SIGv4_ASYMMETRIC, 'signature_type' => \WPMailSMTP\Vendor\AWS\CRT\Auth\SignatureType::HTTP_REQUEST_HEADERS, 'credentials_provider' => $credentials_provider, 'signed_body_value' => $this->getPayload($request), 'region' => "*", 'should_normalize_uri_path' => \false, 'use_double_uri_encode' => \false, 'service' => $signingService, 'date' => \time()]);
        return parent::signWithV4a($credentials, $request, $signingService, $signingConfig);
    }
    /**
     * Always add a x-amz-content-sha-256 for data integrity.
     *
     * {@inheritdoc}
     */
    public function presign(\WPMailSMTP\Vendor\Psr\Http\Message\RequestInterface $request, \WPMailSMTP\Vendor\Aws\Credentials\CredentialsInterface $credentials, $expires, array $options = [])
    {
        if (!$request->hasHeader('x-amz-content-sha256')) {
            $request = $request->withHeader('X-Amz-Content-Sha256', $this->getPresignedPayload($request));
        }
        if (\strpos($request->getUri()->getHost(), "accesspoint.s3-global")) {
            $request = $request->withHeader("x-amz-region-set", "*");
        }
        return parent::presign($request, $credentials, $expires, $options);
    }
    /**
     * Override used to allow pre-signed URLs to be created for an
     * in-determinate request payload.
     */
    protected function getPresignedPayload(\WPMailSMTP\Vendor\Psr\Http\Message\RequestInterface $request)
    {
        return \WPMailSMTP\Vendor\Aws\Signature\SignatureV4::UNSIGNED_PAYLOAD;
    }
    /**
     * Amazon S3 does not double-encode the path component in the canonical request
     */
    protected function createCanonicalizedPath($path)
    {
        // Only remove one slash in case of keys that have a preceding slash
        if (\substr($path, 0, 1) === '/') {
            $path = \substr($path, 1);
        }
        return '/' . $path;
    }
}
