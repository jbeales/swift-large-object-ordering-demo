<?php

require_once 'vendor/autoload.php';

use GuzzleHttp\Psr7;


// Get creds.
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


function createTestObjectSource( $number_of_segments ) {
    // The "large" object will be a text file that's $number_of_segments lines long.
    // Each line will be 1024 bytes long. That's what we'll use as our segment size.
    
    $source = '';
    for( $i=0; $i < $number_of_segments; $i++ ) {
        $source .= str_pad($i + 1 . '  ' , 1021, '*' ) . "\n";
    }

    file_put_contents( 'test-files/object-source.txt', $source );
}

createTestObjectSource( 12 );


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

$filename = 'object-source.txt';

echo "Uploading $filename\n";
flush();

$destination_name = 'debug-files/' . $filename;

$options = [
    'name'   => $destination_name,
    'stream' => Psr7\stream_for(fopen(__DIR__ . '/test-files/' . $filename, 'r')),
    'segmentSize' => 1024,
    'segmentContainer' => $_ENV['OVH_SEGMENT_CONTAINER']
];


$container = $openstack->objectStoreV1()->getContainer($_ENV['OVH_CONTAINER']);

/** @var \OpenStack\ObjectStore\v1\Models\StorageObject $object */
$object = $container->createLargeObject($options);

echo "Uploaded.\n";
echo "Downloading for verification.\n";

$sourceStream = $container->getObject($destination_name)->download();

$resultFileName = __DIR__ . '/test-files/object-result.txt';
$destinationStream = Psr7\Utils::streamFor(fopen( $resultFileName, 'w'));
Psr7\Utils::copyToStream($sourceStream, $destinationStream);

// Close resources. Downloading done.
$sourceStream->close();
$destinationStream->close();
echo "Downloaded.\n";

echo "Compare two files.\n";

$source = file_get_contents( 'test-files/object-source.txt' );
$source_length = strlen($source);
echo "Source, (length: $source_length): \n\n";
echo $source;

$result = file_get_contents( $resultFileName );
$result_length = strlen($result);
echo "Result, (length: $result_length): \n\n";

echo $result;

echo "\n\n\n";
if($source === $result ) {
    echo "The data is the same!\n";
} else {
    echo "The data has changed!\n";
}




