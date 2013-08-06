<?php
include '../GoogleSpeechToText.php';

// Your API Key goes here.
$apiKey = '';
$speech = new GoogleSpeechToText($apiKey);
$file = realpath(__DIR__ . '/quick.flac'); // Full path to the file.
$bitRate = 44100; // The bit rate of the file.
$result = $speech->process($file, $bitRate, 'en-US');
var_dump($result);