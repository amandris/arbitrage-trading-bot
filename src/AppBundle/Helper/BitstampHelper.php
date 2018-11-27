<?php

namespace AppBundle\Helper;

use Exception;

/**
 * Class BitstampHelper
 * @package AppBundle\Helper
 */
class BitstampHelper
{
    /**
     * @var string $key
     */
    private $key;

    /**
     * @var string $secret
     */
    private $secret;

    /**
     * @var string $client_id
     */
    private $client_id;

    /**
     * @var string $base_uri
     */
    private $base_uri;

    /**
     * @var string $redeemd
     */
    public $redeemd;

    /**
     * @var $withdrew
     */
    public $withdrew;

    /**
     * @var string $info
     */
    public $info;

    /**
     * @var string $ticker
     */
    public $ticker;

    /**
     * @var string $eurusd
     */
    public $eurusd;

    /**
     * BitstampHelper constructor.
     * @param $key
     * @param $secret
     * @param $client_id
     * @param $base_uri
     */
    public function __construct($key, $secret, $client_id, $base_uri)
    {
        if (isset($secret) && isset($key) && isset($client_id))
        {
            $this->key = $key;
            $this->secret = $secret;
            $this->client_id = $client_id;
            $this->base_uri = $base_uri;
        } else
            die("No key/secret/client_id");
    }

    /**
     * @param $path
     * @param array $req
     * @param string $verb
     * @return mixed
     * @throws \Exception
     */
    public function bitstamp_query($path, array $req = array(), $verb = 'post')
    {
        $key = $this->key;

        $mt = explode(' ', microtime());
        $req['nonce'] = $mt[1] . substr($mt[0], 2, 6);
        $req['key'] = $key;
        $req['signature'] = $this->get_signature($req['nonce']);


        $post_data = http_build_query($req, '', '&');

        $headers = array();

        static $ch = null;
        if (is_null($ch)){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT,
                'Mozilla/4.0 (compatible; MtGox PHP Client; ' . php_uname('s') . '; PHP/' .
                phpversion() . ')');
        }

        curl_setopt($ch, CURLOPT_URL, $this->base_uri . $path .'/');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if ($verb == 'post'){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


        $res = curl_exec($ch);
        if ($res === false)
            throw new \Exception('Could not get reply: ' . curl_error($ch));
        $dec = json_decode($res, true);

        if (is_null($dec))
            throw new \Exception('Invalid data received, please make sure connection is working and requested API exists');
        return $dec;
    }

    /**
     * Returns current ticker from Bitstamp
     * @return mixed
     */
    function ticker()
    {
        $ticker = $this->bitstamp_query('ticker', array(), 'get');
        $this->ticker = $ticker; // Another variable to contain it.
        return $ticker;
    }

    /**
     * Returns current EUR/USD rate from Bitstamp
     * @return mixed
     */
    function eurusd()
    {
        $eurusd = $this->bitstamp_query('eur_usd', array(), 'get');
        $this->eurusd = $eurusd; // Another variable to contain it.
        return $eurusd;
    }

    /**
     * @param float $amount
     * @param float $price
     * @return mixed
     */
    function buyBTC($amount, $price=NULL)
    {
        if(is_null($price)){
            if (!isset($this->ticker))
                $this->ticker();

            $price = $this->ticker['ask'];
        }

        return $this->bitstamp_query('buy', array('amount' => $amount, 'price' => $price));
    }

    /**
     * @param float $amount
     * @param float $price
     * @return mixed
     */
    function sellBTC($amount, $price=NULL)
    {
        if(is_null($price)){
            if (!isset($this->ticker))
                $this->ticker();

            $price = $this->ticker['bid'];
        }

        return $this->bitstamp_query('sell', array('amount' => $amount, 'price' => $price));
    }

    /**
     * @param string $time
     * @return mixed
     */
    function transactions($time='hour')
    {
        return $this->bitstamp_query('transactions', array('time' => $time), 'get');
    }

    /**
     * @param int $group
     * @return mixed
     */
    function orderBook($group=1)
    {
        return $this->bitstamp_query('order_book', array('group' => $group), 'get');
    }

    /**
     * Bitstamp::openOrders()
     * List of open orders
     */
    function openOrders(){
        return $this->bitstamp_query('open_orders');
    }

    /**
     * @param int $id
     * @return mixed
     * @throws Exception
     */
    function cancelOrder($id=NULL)
    {
        if(is_null($id))
            throw new Exception('Order id is undefined');
        return $this->bitstamp_query('cancel_order', array('id' => $id));
    }

    /**
     * Bitstamp::balance()
     */
    function balance()
    {
        $balance = $this->bitstamp_query('balance');
        return $balance;
    }


    /**
     * Bitstamp::unconfirmedbtc()
     */
    function unconfirmedbtc()
    {
        $unconfirmedbtc = $this->bitstamp_query('unconfirmed_btc');
        return $unconfirmedbtc;
    }

    /**
     * Bitstamp::bitcoindepositaddress()
     */
    function bitcoindepositaddress()
    {
        $bitcoindepositaddress = $this->bitstamp_query('bitcoin_deposit_address');
        return $bitcoindepositaddress;
    }

    /**
     * Compute bitstamp signature
     * @param float $nonce
     * @return mixed
     */
    private function get_signature($nonce)
    {
        $message = $nonce.$this->client_id.$this->key;

        return strtoupper(hash_hmac('sha256', $message, $this->secret));
    }
}