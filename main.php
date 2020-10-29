<?php

require_once 'vendor/autoload.php';

use GuzzleHttp\Psr7;


// Get creds.
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();



// Set up openstack client
$openstack = new OpenStack\OpenStack([
    'authUrl' => $_ENV['OVH_AUTH_URL'],
    'region'  => $_ENV['OVH_REGION'],
    'user'    => [
        'name'       => $_ENV['OVH_USER_NAME'],
        'password' => $_ENV['OVH_PASSWORD'], 
        'domain'   => [
            'id' => 'default'
        ]
    ],
    'scope'   => ['project' => ['id' => $_ENV['OVH_PROJECT_ID']]]
]);



$options = getopt("f:");

if(isset($options['f']) && $options['f'] === 'prod' ) {
	$filename = '2020-10-29-02-45-01.zip';
} else {
	$filename = 'dev-2020-10-29-09-33-44.zip';
}

echo "Uploading $filename\n";
flush();


$options = [
    'name'   => 'debug-files/' . $filename,
    'stream' => Psr7\stream_for(fopen(__DIR__ . '/test-files/' . $filename, 'r')),
];

// optional: specify the size of each segment in bytes
$options['segmentSize'] = 1073741824;

// optional: specify the container where the segments live. This does not necessarily have to be the
// same as the container which holds the manifest file
$options['segmentContainer'] = 'LaravelBackup-segments';


$container = $openstack->objectStoreV1()
                    ->getContainer('test-container');

/** @var \OpenStack\ObjectStore\v1\Models\StorageObject $object */
$object = $container->createLargeObject($options);

echo "Done.\n";



