<?php
/**
 * Created by PhpStorm.
 * User: yvanwatchman
 * Date: 25-04-18
 * Time: 10:37
 */

namespace stiply;


class stiply
{

    public $Username, $Password;
    public $data;

    /**
     * stiply constructor.
     * @param array $user
     */
    public function __construct(array $user)
    {
        $this->Username = $user['username'];
        $this->Password = $user['password'];
    }

    /**
     *
     * Check ping to see if API con works
     * @param $data
     * @param $type
     * @return bool|mixed
     */
    public function checkPingRequest($data, $type)
    {
        if (isset($data, $type)) {
            $this->data = json_encode($data);
            $this->executeRequest("/actions/ping", $type);
            return true;
        }
        return false;
    }

    /**
     * Handles the requests to the API
     * @param $endpoint
     * @param $type
     * @param array $eHeaders
     * @return mixed
     */
    public function executeRequest($endpoint, $type, array $eHeaders = null)
    {
        $ch = curl_init();

        $headers = array(
            'Authorization: Basic ' . base64_encode($this->Username . ":" . $this->Password),
            'Cache-Control: no-cache'
        );

        if (isset($eHeaders)) {
            foreach ($eHeaders as $header) {
                array_push($headers, $header);
            }
        }

        // cURL Settings
        curl_setopt($ch, CURLOPT_URL, "https://api.stiply.nl/v1" . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (strtolower($type) == "post") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        return curl_exec($ch);

    }

    /**
     * Create a document sign request
     * @param $data
     * @param $type
     * @param $name
     * @param array $eHeaders
     * @return bool
     */
    public function createSignRequest($data, $type, $name, $signers, array $eHeaders = null)
    {
        if (isset($data, $type, $name)) {
            if ($eHeaders == null) {
                $eHeaders = array('Content-Type: multipart/form-data');
            }

            $this->data = $data;
            $request = json_decode($this->executeRequest("/sign_requests", $type, $eHeaders));
            foreach ($signers as $signer) {
                $this->addSigner($request->data->sign_request->key, $signer);
            }
            $this->sendSignrequest($request->data->sign_request->key);
            return true;
        }
        return false;
    }

    /**
     * Add a signer
     * @param $key
     * @param null $signers
     * @return bool|mixed
     */
    public function addSigner($key, array $signers = null)
    {
        if (isset($key, $signers)) {
            $this->data = json_encode($signers);
            $eHeaders = array('Content-Type:application/json');
            $result = $this->executeRequest("/sign_requests/" . $key . "/signers", "post", $eHeaders);
            return $result;
        }
        return false;
    }

    /**
     *
     * Sends the signrequest that has been made in the createSignRequest function
     *
     * @param $key
     * @return bool
     */
    public function sendSignrequest($key)
    {
        if (isset($key)) {
            $this->executeRequest("/sign_requests/" . $key . "/actions/send", "post");
            return true;
        }
        return false;
    }


}