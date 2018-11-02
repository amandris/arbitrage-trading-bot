<?php

namespace AppBundle\Helper;

/**
 * Class ItbitHelper
 * @package AppBundle\Helper
 */
class ItbitHelper
{
    /**
     * @var string $baseUri
     */
    private $baseUri;

    /**
     * @var string $secret
     */
    private $secret;

    /**
     * @var string $client
     */
    private $client;

    /**
     * @var string $userId
     */
    private $userId;


    /**
     * ItbitHelper constructor.
     * @param $secret
     * @param $client
     * @param $userId
     * @param $baseUri
     */
    function __construct($secret, $client, $userId, $baseUri)
    {
        $this->secret = $secret;
        $this->client = $client;
        $this->userId = $userId;
        $this->baseUri = $baseUri;
    }

    /**
     * @param $url
     * @param string $body
     * @param string $type
     * @return mixed|string
     */
    private function curl($url, $body = '', $type='')
    {
        $url = $this->baseUri.$url;

        // Generate a nonce
        $mt = explode(' ', microtime());
        $nonce = $mt[1].substr($mt[0], 2, 6);

        // Use current timestamp
        $timestamp = time() * 1000;
        if($body != ''){
            $body = json_encode($body);
        }

        $signature = $this->sign_message(($type != '' ? $type : ($body == '' ? 'GET' : 'POST')),$url, $body, $nonce, $timestamp);


        $headers = array('Authorization: '.$this->client.':'.$signature,
            'X-Auth-Timestamp: '.$timestamp,
            'X-Auth-Nonce: '. $this->nformat($nonce),
            'User-Agent: php-requester',
            'Connection: keep-alive',
            'Accept-Encoding: deflate',
            'Content-Type: application/json');
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, ($type != '' ? $type : ($body == '' ? 'GET' : 'POST')));
        if($body != ''){
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $rawData = curl_exec($curl);
        $info = curl_getinfo ($curl);

        curl_close($curl);

        if($json = json_decode(trim($rawData))){
            return $json;
        }
        return trim($rawData);
    }

    /**
     * @param $verb
     * @param $url
     * @param $body
     * @param $nonce
     * @param $timestamp
     * @return string
     */
    private function sign_message($verb, $url, $body, $nonce, $timestamp)
    {
        $message = stripslashes(json_encode(array($verb, $url, ($body == '' ? '' : addslashes($body)), (string)$nonce, (string)$timestamp)));
        $nonced_message = $this->nformat($nonce) . $message;
        $hash_digest = hash('sha256',$nonced_message, true);
        $hmac_digest = hash_hmac('sha512', utf8_encode($url) . $hash_digest, utf8_encode($this->secret),true);
        $sig = base64_encode($hmac_digest);
        return $sig;
    }

    /**
     * @param $nonce
     * @return string
     */
    private function nformat($nonce){
        return number_format($nonce,0,'','');
    }

    /**
     * @param string $wallet_id
     * @param string $currency
     * @return mixed|string
     */
    public function wallet($wallet_id='', $currency = '')
    {
        return $this->curl('wallets'.($wallet_id != '' ? '/'.$wallet_id . ($currency != '' ? '/balances/'.$currency : '') : '?userId='.$this->userId));
    }

    /**
     * @param $wallet_id
     * @param $currency
     * @return mixed|string
     */
    public function balance($wallet_id, $currency)
    {
        return $this->wallet($wallet_id, $currency);
    }

    /**
     * @param $wallet_id
     * @param string $order_id
     * @return mixed|string
     */
    public function orders($wallet_id, $order_id='')
    {
        return $this->curl('wallets/'.$wallet_id.'/orders'.($order_id != '' ? '/'.$order_id : ''));
    }

    /**
     * @param $wallet_id
     * @return mixed|string
     */
    public function trades($wallet_id)
    {
        return $this->curl('wallets/'.$wallet_id.'/trades');
    }

    /**
     * @param $wallet_id
     * @param $order_id
     * @return mixed|string
     */
    public function cancel($wallet_id, $order_id)
    {
        return $this->curl('wallets/'.$wallet_id.'/orders/'.$order_id,'','DELETE');
    }

    /**
     * @param $wallet_id
     * @param $order_type
     * @param $amount
     * @param $price
     * @return mixed|string
     */
    public function create_order($wallet_id, $order_type, $amount, $price)
    {
        $order_data = array('side' => ($order_type == 'sell' ? 'sell' : 'buy'),
            'type' => 'limit',
            'currency' => 'XBT',
            'amount' => (string)number_format($amount,4,'.',''),
            'price' => (string)$price,
            'instrument' => 'XBTUSD');

        return $this->curl('wallets/'.$wallet_id.'/orders',$order_data,'POST');
    }

    /**
     * @param $wallet_id
     * @param $amount
     * @param $address
     * @return mixed|string
     */
    public function withdraw($wallet_id, $amount, $address)
    {
        $withdraw_data = array('currency' => 'XBT',
            'amount' => (string)$amount,
            'address' => $address);

        return $this->curl('wallets/'.$wallet_id.'/cryptocurrency_withdrawals',$withdraw_data,'POST');
    }

    /**
     * @param $wallet_id
     * @return mixed|string
     */
    public function deposit($wallet_id)
    {
        return $this->curl('wallets/'.$wallet_id.'/cryptocurrency_deposits',array('currency' => 'XBT'),'POST');
    }
}