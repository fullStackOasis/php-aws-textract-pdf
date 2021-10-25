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

// Output jobId should contain 64 hex digits, something like this:
// $jobId = 'ad6f...5346';

// Just hard-coded the jobId using the output from textract_pdf.php.
// You should have stored it somewhere: in a database, for example.
$jobId = 'ad6f...5346';

$getOptions = [
	'JobId' => $jobId
];
$getObject = $client->GetDocumentTextDetection($getOptions);
// For debugging:
// echo "getObject:\n" . print_r($getObject, true) . "\n";

$blocks = $getObject->get('Blocks');

$JobStatus = $getObject->get('JobStatus');

if ($JobStatus == 'SUCCEEDED') {
    processResult($blocks);
} else {
    echo "Job failed with status " . $JobStatus;
}


// If debugging:
// echo print_r($result, true);
function processResult($blocks) {
	// Loop through all the blocks:
	foreach ($blocks as $key => $value) {
		if (isset($value['BlockType']) && $value['BlockType']) {
            // BlockType is WORD, LINE, or PAGE
			$blockType = $value['BlockType'];
			if (isset($value['Text']) && $value['Text']) {
				$text = $value['Text'];
				if ($blockType == 'WORD') {
					echo "Word: ". print_r($text, true) . "\n";
				} else if ($blockType == 'LINE') {
					echo "Line: ". print_r($text, true) . "\n";
				}
			}
		}
	}
}

?>
