# PHP Speech To Text #

### About ###

This is an example file to demonstrate how to interact with the Google Speech API.

### Getting Started ###

Visit: https://code.google.com/apis/console/
Enable the Speech API listed on the page.

#### Don't see Speech API? ####

That's a fairly common thing. It seems that only developers on the `chromium-dev` list actually can see it listed.

Don't worry. Go to: http://groups.google.com/a/chromium.org/group/chromium-dev/topics

Now, sign up for the list.

Go back to the API Console and refresh the page. You'll see it listed.

### Usage ###

See the `sample/example.php` file, or use this below:

```php
<?php
include 'GoogleSpeechToText.php';

// Your API Key goes here.
$apiKey = '';
$speech = new GoogleSpeechToText($apiKey);
$file = realpath(__DIR__ . '/quick.flac'); // Full path to the file.
$bitRate = 44100; // The bit rate of the file.
$result = $speech->process($file, $bitRate, 'en-US');
var_dump($result);
```

Running the example file outputs:

```text
array(1) {
  [0]=>
  array(2) {
    ["alternative"]=>
    array(5) {
      [0]=>
      array(2) {
        ["transcript"]=>
        string(44) "the quick brown fox jumped over the lazy dog"
        ["confidence"]=>
        float(0.87096781)
      }
      [1]=>
      array(1) {
        ["transcript"]=>
        string(43) "the quick brown fox jumps over the lazy dog"
      }
      [2]=>
      array(1) {
        ["transcript"]=>
        string(49) "the quick brown fox jumped over the lazy dog cafe"
      }
      [3]=>
      array(1) {
        ["transcript"]=>
        string(49) "the quick brown fox jumped over the lazy dog food"
      }
      [4]=>
      array(1) {
        ["transcript"]=>
        string(47) "the quick brown fox jumped over the lazy dog hi"
      }
    }
    ["final"]=>
    bool(true)
  }
}

```
