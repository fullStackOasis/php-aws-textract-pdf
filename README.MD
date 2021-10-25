## How to use the Amazon AWS SDK for Textract with PHP 7.2 ##

The <a href="https://docs.aws.amazon.com/textract/latest/dg/what-is.html">Amazon AWS Textract</a> API lets you do OCR (optical character recognition) on digital files. It's actually pretty easy to use, although there's some prep work.

This post has instructions for using the Textract API using their **PHP SDK**. I'm using PHP version 7.2 on an Ubuntu 21 operating system. This demo works as of October 2021.

## Step 1: Create the project ##

Create a folder for your project, for example ``mkdir ~/TextractPDFDemo ; cd ~/TextractPDFDemo``. 

<a href="https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/getting-started_installation.html">Instructions for getting started with the SDK for PHP are here</a>. First, download the .zip file as described on that page. Then, extract the zip file to the root of your project. That adds a lot of files and folders to the project root, for example "Aws" folder is added.

This is what you should see when listing the contents of this directory:

```
~/TextractDemo$ ls -lairt
total 676
  396747 -rw-r--r--   1 fullstackdev fullstackdev  10129 Sep 12 14:11 README.md
  531373 drwxr-xr-x   3 fullstackdev fullstackdev   4096 Sep 12 14:11 Psr
  396739 -rw-r--r--   1 fullstackdev fullstackdev   2881 Sep 12 14:11 NOTICE.md
  399132 -rw-r--r--   1 fullstackdev fullstackdev   9202 Sep 12 14:11 LICENSE.md
  926072 drwxr-xr-x   2 fullstackdev fullstackdev   4096 Sep 12 14:11 JmesPath
  396755 drwxr-xr-x   7 fullstackdev fullstackdev   4096 Sep 12 14:11 GuzzleHttp
  399129 -rw-r--r--   1 fullstackdev fullstackdev 478403 Sep 12 14:11 CHANGELOG.md
  396748 -rw-r--r--   1 fullstackdev fullstackdev 132879 Sep 12 14:11 aws-autoloader.php
  531270 drwxr-xr-x 203 fullstackdev fullstackdev  12288 Sep 12 14:11 Aws
  396729 drwxr-xr-x   6 fullstackdev fullstackdev   4096 Sep 15 09:48 .
13500418 drwxr-xr-x  46 fullstackdev fullstackdev  20480 Sep 15 09:49 ..

```

## Create an IAM User ##

In order to use the Textract API, you need an <a href="https://aws.amazon.com/premiumsupport/knowledge-center/create-and-activate-aws-account/">Amazon AWS account</a>.

Assuming you've got an AWS account, next, you need to <a href="https://docs.aws.amazon.com/textract/latest/dg/setting-up.html">create an IAM (Identity and Access Management) user</a>. If you are signed in to your AWS console, just search for "Identity and Access Management", and it takes you to the right place to create an IAM user. There's an area called "Create individual IAM users". Go there, click the "Manage Users" button, click the "Add User" button, choose a name like TextractUser, and give it programmatic access only. Once you've created the name, go to the next step, where you can add the user to a specific group. Create a group which has the AmazonTextractFullAccess policy name. Name it something like TextractFullAccessGroup, and save that. Add the user you just created to this group. The next step lets you add tags to the user, but you can leave that blank.

In the Review (last) step, you are given the user's access key ID and secret key (which is hidden - you will have to unhide it to copy it). Save these in a secure place! As the documentation says, "This is the last time these credentials will be available to download. However, you can create new credentials at any time." (So if you lose them somehow, you can always generate a new set.)

The credentials that you just created may be saved in the file ~/.aws/credentials in Linux.

If this file already exists, you can add to it. <a href="https://docs.aws.amazon.com/cli/latest/userguide/cli-configure-profiles.html">Here's the documentation for adding lines to an AWS credentials file.</a> On that page, it gives you an example credentials file with this content:

```
[default]
aws_access_key_id=AKIAIOSFODNN7EXAMPLE
aws_secret_access_key=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY

[user1]
aws_access_key_id=AKIAI44QH8DHBEXAMPLE
aws_secret_access_key=je7MtGbClwBF/2Zp9Utk/h3yCo8nvbEXAMPLEKEY
```

Instead of user1, add the line ``[TextractUser]`` (or whatever user name you used in the "creating user" step above). Copy and paste your access key id and secret key as shown.

The credentials file is normally created when installing the AWS CLI. So if you do not already have a credentials file, <a href="https://docs.aws.amazon.com/cli/latest/userguide/cli-chap-install.html">install the CLI</a> first. Then you can add users to the file.

Now we're ready to use Textract.

We are going to try to detect text in a sample PDF file. An example PDF file is included with this project. You will need to upload your PDF file to your own AWS S3 bucket. Creating an S3 bucket is easy, but beyond the scope of this document. <a href="https://aws.amazon.com/s3/">Please follow Amazon's documentation for creating and uploading a file to an S3 bucket</a>. Once you do that, you will know the bucket name, and your file name to use in the code below.

Important! Textract cannot extract text from a PDF file if it is not in an S3 bucket.

## Call Textract on a PDF using the SDK ##

We need source code to do two separate things.

First, one little program creates a Textract client, and uses the client to call `StartDocumentTextDetection`. Since `StartDocumentTextDetection`, you need to save the output, which is a 'JobId', and use it in your other little program. You can save the output in many different ways. In this example, we're just going to print the 'JobId' value, and then copy and paste it into our second program. It's inefficient, but works well for the purposes of a demo.

After we have our 'JobId' pasted into our next program, we can run that program to get the extracted text.

Here's the first little program called `textract_demo_StartDocumentTextDetection.php`. To run it, do: `php textract_demo_StartDocumentTextDetection.php`. But before doing that, make sure you've:

1. Set up your S3 bucket
2. Uploaded your PDF file to the S3 bucket.
3. Edited the code below to use your own profile and region, and make sure you have AWS credentials.
4. Edited the code to refer to your own S3 bucket and file name and version.

```
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
```

After running this code, you should see output with the 'JobId'. Edit the next little program so that this 'JobId' is used to call `GetDocumentTextDetection`:

```
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
```

Run this code with `php textract_demo_StartDocumentTextDetection.php`. You should see output with words and lines extracted from the PDF file.

That's it! Feel free to email me with any question at fullstackdev@fullstackoasis.com.