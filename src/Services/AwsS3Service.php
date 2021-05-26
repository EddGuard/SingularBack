<?php

namespace App\Services;

use Aws\S3\S3Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AwsS3Service
{
    private $key;
    private $region;
    private $secret;
    private $bucket;
    private $params;


    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->key = $this->params->get('AWS_S3_KEY');
        $this->secret = $this->params->get('AWS_S3_SECRET');
        $this->region = $this->params->get('AWS_S3_REGION');
        $this->bucket = $this->params->get('AWS_S3_BUCKET_PRIVATE');
    }

    public function uploadFile($file, $thumbnail = false)
    {
        $client = new S3Client(array(
            'credentials'   => array(
                'key'      => $this->key,
                'secret'   => $this->secret,
            ),
            'region'        => $this->region,
            'version'       => 'latest',
        ));

        $client->waitUntil('BucketExists', array('Bucket' => $this->bucket));

        if ($thumbnail) {
            $key = uniqid().$file->getFileName();
        } else {
            $key = uniqid().$file->getFileName().'.'.$file->guessExtension();
        }

        $result = $client->putObject(array(
            'Bucket'     => $this->bucket ,
            'Key'        => $key,
            'SourceFile' => $file->getPathName(),
        ));

        return $key;
    }

    public function downloadFile($name)
    {
        $client = S3Client::factory(array(
            'credentials'   => array(
                'key'      => $this->key,
                'secret'   => $this->secret,
            ),
            'region'        => $this->region,
            'version'       => 'latest',
        ));

        // Get an object.
        $result = $client->getObject(array(
            'Bucket' => $this->bucket,
            'Key'    => $name
        ));

        return $result;
    }
}
