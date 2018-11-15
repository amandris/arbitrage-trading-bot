<?php

namespace AppBundle\Helper;

/**
 * Class BinanceHelper
 * @package AppBundle\Helper
 */
class BinanceHelper
{
    /**
     * @var float $btc_value
     */
    public $btc_value = 0.00;

    /**
     * @var  string $base_uri
     */
    protected $base_uri;

    /**
     * @var string $api_key
     */
    protected $api_key;

    /**
     * @var string $api_secret
     */
    protected $api_secret;


    /**
     * BinanceHelper constructor.
     * @param $api_key
     * @param $api_secret
     * @param $base_uri
     */
    public function __construct($api_key, $api_secret, $base_uri)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->base_uri = $base_uri;
    }


    public function ping()
    {
        return $this->request("/api/v1/ping");
    }

    public function time()
    {
        return $this->request("/api/v1/time");
    }

    public function exchangeInfo()
    {
        return $this->request("/api/v1/exchangeInfo");
    }

    public function buy_test($symbol, $quantity, $price, $type = "LIMIT", $flags = [])
    {
        return $this->order_test("BUY", $symbol, $quantity, $price, $type, $flags);
    }

    public function sell_test($symbol, $quantity, $price, $type = "LIMIT", $flags = [])
    {
        return $this->order_test("SELL", $symbol, $quantity, $price, $type, $flags);
    }

    public function buy($symbol, $quantity, $price, $type = "LIMIT", $flags = [])
    {
        return $this->order("BUY", $symbol, $quantity, $price, $type, $flags);
    }

    public function sell($symbol, $quantity, $price, $type = "LIMIT", $flags = [])
    {
        return $this->order("SELL", $symbol, $quantity, $price, $type, $flags);
    }

    public function cancel($symbol, $orderid)
    {
        return $this->signedRequest("/api/v3/order",["symbol"=>$symbol, "orderId"=>$orderid], "DELETE");
    }

    public function orderStatus($symbol, $orderid)
    {
        return $this->signedRequest("/api/v3/order",["symbol"=>$symbol, "orderId"=>$orderid]);
    }

    public function openOrders($symbol)
    {
        return $this->signedRequest("/api/v3/openOrders",["symbol"=>$symbol]);
    }

    public function orders($symbol, $limit = 500)
    {
        return $this->signedRequest("/api/v3/allOrders",["symbol"=>$symbol, "limit"=>$limit]);
    }

    public function trades($symbol)
    {
        return $this->signedRequest("/api/v3/myTrades",["symbol"=>$symbol]);
    }

    public function prices()
    {
        return $this->priceData($this->request("/api/v1/ticker/allPrices"));
    }

    public function bookPrices()
    {
        return $this->bookPriceData($this->request("/api/v1/ticker/allBookTickers"));
    }

    public function account()
    {
        return $this->signedRequest("/api/v3/account");
    }

    public function depth($symbol)
    {
        return $this->request("/api/v1/depth",["symbol"=>$symbol]);
    }

    public function balances($priceData = false)
    {
        return $this->balanceData($this->signedRequest("/api/v3/account"),$priceData);
    }

    public function prevDay($symbol)
    {
        return $this->request("/api/v1/ticker/24hr", ["symbol"=>$symbol]);
    }

    /**
     * @param $url
     * @param array $params
     * @param string $method
     * @return mixed
     */
    private function request($url, $params = [], $method = "GET")
    {
        $opt = [
            "http" => [
                "method" => $method,
                "header" => "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)\r\n"
            ]
        ];
        $context = stream_context_create($opt);
        $query = http_build_query($params, '', '&');
        return json_decode(file_get_contents($this->base_uri.$url.'?'.$query, false, $context), true);
    }

    /**
     * @param $url
     * @param array $params
     * @param string $method
     * @return mixed
     */
    private function signedRequest($url, $params = [], $method = "GET")
    {
        $params['timestamp'] = number_format(microtime(true)*1000,0,'.','');
        $query = http_build_query($params, '', '&');

        $signature = hash_hmac('sha256', $query, $this->api_secret);
        $opt = [
            "http" => [
                "method" => $method,
                "ignore_errors" => false,
                "header" => "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)\r\nX-MBX-APIKEY: {$this->api_key}\r\nContent-type: application/x-www-form-urlencoded\r\n"
            ]
        ];
        if ( $method == 'GET' ) {
            // parameters encoded as query string in URL
            $endpoint = "{$this->base_uri}{$url}?{$query}&signature={$signature}";
        } else {
            // parameters encoded as POST data (in $context)
            $endpoint = "{$this->base_uri}{$url}";
            $postdata = "{$query}&signature={$signature}";
            $opt['http']['content'] = $postdata;
        }
        $context = stream_context_create($opt);

        return json_decode(file_get_contents($endpoint, false, $context), true);
    }

    /**
     * @param $side
     * @param $symbol
     * @param $quantity
     * @param $price
     * @param string $type
     * @param array $flags
     * @return mixed
     */
    private function order_test($side, $symbol, $quantity, $price, $type = "LIMIT", $flags = [])
    {
        $opt = [
            "symbol" => $symbol,
            "side" => $side,
            "type" => $type,
            "quantity" => $quantity,
            "recvWindow" => 200000
        ];
        if ( $type == "LIMIT" ) {
            $opt["price"] = $price;
            $opt["timeInForce"] = "GTC";
        }
        // allow additional options passed through $flags
        if ( isset($flags['recvWindow']) ) $opt['recvWindow'] = $flags['recvWindow'];
        if ( isset($flags['timeInForce']) ) $opt['timeInForce'] = $flags['timeInForce'];
        if ( isset($flags['stopPrice']) ) $opt['stopPrice'] = $flags['stopPrice'];
        if ( isset($flags['icebergQty']) ) $opt['icebergQty'] = $flags['icebergQty'];
        return $this->signedRequest("/api/v3/order/test", $opt, "POST");
    }

    /**
     * @param $side
     * @param $symbol
     * @param $quantity
     * @param $price
     * @param string $type
     * @param array $flags
     * @return mixed
     */
    private function order($side, $symbol, $quantity, $price, $type = "LIMIT", $flags = [])
    {
        $opt = [
            "symbol" => $symbol,
            "side" => $side,
            "type" => $type,
            "quantity" => $quantity,
            "recvWindow" => 200000
        ];
        if ( $type == "LIMIT" ) {
            $opt["price"] = $price;
            $opt["timeInForce"] = "GTC";
        }
        // allow additional options passed through $flags
        if ( isset($flags['recvWindow']) ) $opt["recvWindow"] = $flags['recvWindow'];
        if ( isset($flags['timeInForce']) ) $opt["timeInForce"] = $flags['timeInForce'];
        if ( isset($flags['stopPrice']) ) $opt['stopPrice'] = $flags['stopPrice'];
        if ( isset($flags['icebergQty']) ) $opt['icebergQty'] = $flags['icebergQty'];
        return $this->signedRequest("/api/v3/order", $opt, "POST");
    }

    //1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
    public function candlesticks($symbol, $interval = "5m")
    {
        return $this->request("/api/v1/klines",["symbol"=>$symbol, "interval"=>$interval]);
    }

    /**
     * @param $array
     * @param bool $priceData
     * @return array
     */
    private function balanceData($array, $priceData = false)
    {
        if ( $priceData ) $btc_value = 0.00;
        $balances = [];
        foreach ( $array['balances'] as $obj ) {
            $asset = $obj['asset'];
            $balances[$asset] = ["available"=>$obj['free'], "onOrder"=>$obj['locked'], "btcValue"=>0.00000000];
            if ( $priceData ) {
                if ( $obj['free'] < 0.00000001 ) continue;
                if ( $asset == 'BTC' ) {
                    $balances[$asset]['btcValue'] = $obj['free'];
                    $btc_value+= $obj['free'];
                    continue;
                }
                $btcValue = number_format($obj['free'] * $priceData[$asset.'BTC'],8,'.','');
                $balances[$asset]['btcValue'] = $btcValue;
                $btc_value+= $btcValue;
            }
        }
        if ( $priceData ) {
            uasort($balances, function($a, $b) { return $a['btcValue'] < $b['btcValue']; });
            $this->btc_value = $btc_value;
        }
        return $balances;
    }

    /**
     * @param $array
     * @return array
     */
    private function bookPriceData($array)
    {
        $bookprices = [];
        foreach ( $array as $obj ) {
            $bookprices[$obj['symbol']] = [
                "bid"=>$obj['bidPrice'],
                "bids"=>$obj['bidQty'],
                "ask"=>$obj['askPrice'],
                "asks"=>$obj['askQty']
            ];
        }
        return $bookprices;
    }

    /**
     * @param $array
     * @return array
     */
    private function priceData($array)
    {
        $prices = [];
        foreach ( $array as $obj ) {
            $prices[$obj['symbol']] = $obj['price'];
        }
        return $prices;
    }
}