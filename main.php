<?php

require_once 'vendor/autoload.php';

use Guzzle\Stream\Stream;


// Get creds.
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();



// Set up openstack client
$openstack = new OpenStack\OpenStack([
    'authUrl' => '{authUrl}',
    'region'  => '{region}',
    'user'    => [
        'id'       => '{userId}',
        'password' => '{password}'
    ],
    'scope'   => ['project' => ['id' => '{projectId}']]
]);


$options = [
    'name'   => 'object_name.txt',
    'stream' => new Stream(fopen('/path/to/large_object.mov', 'r')),
];

// optional: specify the size of each segment in bytes
$options['segmentSize'] = 1073741824;

// optional: specify the container where the segments live. This does not necessarily have to be the
// same as the container which holds the manifest file
$options['segmentContainer'] = 'test_segments';


/** @var \OpenStack\ObjectStore\v1\Models\StorageObject $object */
$object = $openstack->objectStoreV1()
                    ->getContainer('test')
                    ->createLargeObject($options);