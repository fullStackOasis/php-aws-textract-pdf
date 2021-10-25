<?php
/*
Copyright 2021 Marya Doery

MIT License https://opensource.org/licenses/MIT

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/*
 * To run this project, make sure that the AWS PHP SDK has been unzipped in the current directory.
 * 
 * Caution: this is not production quality code. There are no tests, and there is no error handling.
 */


// See https://docs.aws.amazon.com/textract/latest/dg/API_StartDocumentTextDetection.html
// https://docs.aws.amazon.com/textract/latest/dg/API_GetDocumentTextDetection.html
require './aws-autoloader.php';

use Aws\Credentials\CredentialProvider;
use Aws\Textract\TextractClient;

// If you use CredentialProvider, it will use credentials in your .aws/credentials file.
$provider = CredentialProvider::env();
$client = new TextractClient([
	'profile' => 'TextractUser',
    'region' => 'us-west-2',
	'version' => 'latest',
	'credentials' => $provider
]);

$bucket = 'my-textract-s3-bucket-us-west-2';
$keyname = 'my-special-file.pdf';
$version = 'qaEXAMPLEOH1REm3Dy.Ca9W4Gpqdj6Ro';

$startOptions = [
	'DocumentLocation' => [
		'S3Object' => [
			'Bucket' => $bucket,
			'Name' => $keyname,
			'Version' => $version,
		],
	],
    'FeatureTypes' => ['FORMS']
];

$object = $client->StartDocumentTextDetection($startOptions);

echo "output:\n" . print_r($object, true) . "\n";

$jobId = $object->get('JobId');

echo "JobId:\n" . print_r($jobId, true) . "\n";

?>
