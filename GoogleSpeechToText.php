<?php
/**
 * Convert FLAC files to Text using the Google Speech API
 *
 * Credit due to Mike (mike@mikepultz.com) for his first version of this.
 *
 * @version 0.1
 * @author Roger Thomas
 * @see
 *
 */
class GoogleSpeechToText
{
    /**
     * URL of the Speech API
     * @var string
     */
    const SPEECH_BASE_URL = 'https://www.google.com/speech-api/full-duplex/v1/';

    /**
     * A 'unique' string to use for the requests
     *
     * @var string
     */
    private $requestPair;

    /**
     * The Google Auth API Key
     *
     * @var string
     */
    private $apiKey;

    /**
     * CURL Upload Handle
     *
     * @var resource
     */
    private $uploadHandle;

    /**
     * CURL Download Handle
     *
     * @var resource
     */
    private $downloadHandle;

    /**
     * Construct giving the Google Auth API Key.
     *
     * @param string $apiKey
     * @throws Exception
     */
    public function __construct($apiKey)
    {
        if (empty($apiKey)) {
            throw new Exception('$apiKey should not be empty.');
        }
        $this->apiKey = $apiKey;
        $this->requestPair = $this->getPair();
        $this->setupCurl();
    }

    /**
     * Setup CURL requests, both up and down.
     */
    private function setupCurl()
    {
        $this->uploadHandle = curl_init();
        $this->downloadHandle = curl_init();
        curl_setopt(
            $this->downloadHandle,
            CURLOPT_URL,
            self::SPEECH_BASE_URL . 'down?pair=' . $this->requestPair
        );

        curl_setopt(
            $this->downloadHandle,
            CURLOPT_RETURNTRANSFER,
            true
        );

        curl_setopt(
            $this->uploadHandle,
            CURLOPT_RETURNTRANSFER,
            true
        );

        curl_setopt(
            $this->uploadHandle,
            CURLOPT_POST,
            true
        );
    }

    /**
     * Generate a Pair for the request. This identifies the requests later.
     *
     * @return string
     */
    private function getPair()
    {
        $c = '0123456789';
        $s = '';
        for ($i=0; $i<16; $i++) {
            $s .= $c[rand(0, strlen($c) - 1)];
        }

        return $s;
    }

    /**
     * Make the request, returning either an array, or boolean false on
     * failure.
     *
     * @param string $file the file name to process
     * @param integer $rate the bitrate of the flac content (example: 44100)
     * @param string $language the ISO language code
     *      (en-US has been confirmed as working)
     * @throws Exception
     * @return array|boolean false for failure.
     */
    public function process($file, $rate, $language = 'en-US')
    {
        if (!$file || !file_exists($file) || !is_readable($file)) {
            throw new Exception(
                '$file must be specified and be a valid location.'
            );
        }

        $data = file_get_contents($file);
        if (!$data) {
            throw new Exception('Unable to read ' . $file);
        }

        if (empty($rate) || !is_integer($rate)) {
            throw new Exception('$rate must be specified and be an integer');
        }

        curl_setopt(
            $this->uploadHandle,
            CURLOPT_URL,
            self::SPEECH_BASE_URL . 'up?lang=' .
                $language . '&lm=dictation&client=chromium&pair=' .
                $this->requestPair . '&key=' . $this->apiKey
        );
        curl_setopt(
            $this->uploadHandle,
            CURLOPT_HTTPHEADER,
            array(
                'Transfer-Encoding: chunked',
                'Content-Type: audio/x-flac; rate=' . $rate
            )
        );
        curl_setopt(
            $this->uploadHandle,
            CURLOPT_POSTFIELDS,
            array(
                'file' => $data
            )
        );

        $curlMulti = curl_multi_init();

        curl_multi_add_handle($curlMulti, $this->downloadHandle);
        curl_multi_add_handle($curlMulti, $this->uploadHandle);

        $active = null;
        do {
            curl_multi_exec($curlMulti, $active);
        } while ($active > 0);

        $res = curl_multi_getcontent($this->downloadHandle);

        $output = array();
        $results = explode("\n", $res);
        foreach ($results as $result) {
            $object = json_decode($result, true);
            if (
                (isset($object['result']) == true) &&
                (count($object['result']) > 0)
            ) {
                foreach ($object['result'] as $obj) {
                    $output[] = $obj;
                }
            }
        }

        curl_multi_remove_handle($curlMulti, $this->downloadHandle);
        curl_multi_remove_handle($curlMulti, $this->uploadHandle);
        curl_multi_close($curlMulti);

        if (empty($output)) {
            return false;
        }

        return $output;
    }

    /**
     * Close any outstanding connections in the destruct
     */
    public function __destruct()
    {
        curl_close($this->uploadHandle);
        curl_close($this->downloadHandle);
    }
}
