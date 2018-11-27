<?php

namespace AppBundle\Helper;

/**
 * Class QuadrigacxHelper
 * @package AppBundle\Helper
 */
class QuadrigacxHelper
{
    /**
     * @var string $base_uri
     */
    private $base_uri;

    /**
     * @var array
     */
    private $credentials;

    /**
     * QuadrigacxHelper constructor.
     * @param $key
     * @param $secret
     * @param $client_id
     * @param $base_uri
     */
    public function __construct($key, $secret, $client_id, $base_uri)
    {
        $this->credentials = array(
            'QUADRIGA_API_KEY' => $key,
            'QUADRIGA_API_SECRET' => $secret,
            'QUADRIGA_CLIENT_ID' => $client_id,
        );

        $this->base_uri = $base_uri;
    }

    /**
     * @param null $filename
     * @return bool
     */
    public function load_credentials($filename = NULL)
    {
        if ($filename === NULL) {
            $filename = getenv('HOME') . DIRECTORY_SEPARATOR . '.quadriga.conf';
        }
        $credentials_file = fopen($filename, 'r');

        if ($credentials_file === FALSE) {
            print 'Unable to open credentials file!' . PHP_EOL;
            return FALSE;
        }

        while (($credentials_line = fgets($credentials_file)) !== FALSE) {
            // Ignore blank lines and lines beginning with '#'
            if (substr($credentials_line, 0, 1) == '#' || strlen($credentials_line) == 0) {
                continue;
            }

            // Split line on '='
            $line = explode('=', trim($credentials_line), 2);
            $this->credentials[$line[0]] = $line[1];
        }

        // If necessary credentials are not found, return FALSE.
        if (strlen($this->credentials['QUADRIGA_API_KEY']) > 1 ||
            strlen($this->credentials['QUADRIGA_API_SECRET']) > 1 ||
            intval($this->credentials['QUADRIGA_CLIENT_ID'] > 1)) {
            return TRUE;
        } else {
            print 'Unable to read credentials from ' . $filename;
            return FALSE;
        }
    }

    /**
     * @param $api_func
     * @return null|resource
     */
    private function _curler($api_func)
    {
        if (strlen($api_func) < 1) {
            return NULL;
        }

        $curler = curl_init($this->base_uri . $api_func);
        curl_setopt($curler, CURLOPT_RETURNTRANSFER, TRUE);

        return $curler;
    }

    /**
     * @param string $api_func
     * @param array $gets
     * @return bool|mixed
     */
    private function _public_api($api_func = '', $gets = array())
    {
        // Refuse to call a non-existent API Function.
        if (strlen($api_func) < 1) {
            return FALSE;
        }

        // Add any GET variables required.
        if (count($gets) > 0) {
            $api_func .= '?';
            foreach ($gets as $gk => $gv) {
                $api_func .= $gk . '=' . $gv . '&';
            }
            // Strip the trailing '&' added during the foreach()
            $api_func = trim($api_func, '&');
        }

        $curler = $this->_curler($api_func);
        if ($curler == NULL) {
            return FALSE;
        }
        return json_decode(curl_exec($curler));
    }

    /**
     * @param string $api_func
     * @param array $post
     * @return bool|mixed
     */
    private function _private_api($api_func = '', $post = array())
    {
        // Refuse to call a non-existent API Function.
        if (strlen($api_func) < 1) {
            return FALSE;
        }

        // Ensure we have loaded proper credentials first.
        if (strlen($this->credentials['QUADRIGA_API_KEY']) < 1 ||
            strlen($this->credentials['QUADRIGA_API_SECRET']) < 1 ||
            intval($this->credentials['QUADRIGA_CLIENT_ID'] < 1)) {
            $this->load_credentials();
        }

        $curler = $this->_curler($api_func);
        if ($curler == NULL) {
            return FALSE;
        }

        // nonce must increase for each API call so we use a smaller grain than 1s
        $nonce = intval(microtime(TRUE) * 100000);
        $post['key'] = $this->credentials['QUADRIGA_API_KEY'];
        $post['nonce'] = $nonce;
        $post['signature'] = hash_hmac('sha256', $nonce . $this->credentials['QUADRIGA_CLIENT_ID'] . $this->credentials['QUADRIGA_API_KEY'], $this->credentials['QUADRIGA_API_SECRET']);

        $post_json = json_encode($post);
        curl_setopt($curler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curler, CURLOPT_POSTFIELDS, $post_json);
        curl_setopt($curler, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($post_json))
        );

        return json_decode(curl_exec($curler));
    }

    /**
     * @return array
     */
    public function get_books()
    {
        return array(
            'btc_cad',
            'btc_usd',
            'eth_btc',
            'eth_cad',
        );
    }

    /**
     * @param $book
     * @return bool|null
     */
    public function validate_book($book)
    {
        $valid_books = $this->get_books();
        foreach ($valid_books as $b) {
            if ($book == $b) { return TRUE; }
            if ($this->reverse_book($book) == $b) { return FALSE; }
        }
        return NULL;
    }

    /**
     * @param $book
     * @return null|string
     */
    public function reverse_book($book)
    {
        $currencies = explode('_', $book);
        if (count($currencies) != 2) { return NULL; }
        return implode('_', array($currencies[1], $currencies[0]));
    }

    /**
     * @param $book
     * @param $amount
     * @return float|int
     */
    public function book_round($book, $amount)
    {
        $currencies = explode('_', $book);
        if (count($currencies) != 2) { return 0; }
        $maj = $currencies[0];
        if ($maj == 'btc' || $maj == 'eth') {
            $precision = 8;
        } else if ($maj == 'cad' || $maj == 'usd') {
            $precision = 2;
        } else {
            return 0;
        }
        return round($amount, $precision);
    }

    /**
     * @param null $book
     * @return bool|mixed
     */
    public function ticker($book = NULL)
    {
        if ($book === NULL) {
            return $this->_public_api('ticker');
        } else {
            return $this->_public_api('ticker', array('book' => $book));
        }
    }

    /**
     * @param null $book
     * @param bool $group
     * @return bool|mixed
     */
    public function order_book($book = NULL, $group = TRUE)
    {
        $gets = array();
        if ($book !== NULL) {
            $gets['book'] = $book;
        }
        if ($group !== NULL) {
            $gets['group'] = ($group)?'1':'0';
        }
        return $this->_public_api('order_book', $gets);
    }

    //**

    public function transactions($book = NULL, $time = NULL)
    {
        $gets = array();
        if ($book !== NULL) {
            $gets['book'] = $book;
        }
        if ($time !== NULL && ($time == 'minute' || $time == 'hour')) {
            $gets['time'] = $time;
        }
        return $this->_public_api('transactions', $gets);
    }

    public function balance()
    {
        return $this->_private_api('balance');
    }

    public function open_orders($book = NULL)
    {
        $post_array = array();
        if ($book !== NULL) {
            $post_array['book'] = $book;
        }
        return $this->_private_api('open_orders', $post_array);
    }

    /**
     * @param string $id
     * @return bool|mixed
     */
    public function lookup_order($id = '')
    {
        if (is_array($id)) {
            $id = json_encode($id);
        }
        return $this->_private_api('lookup_order', array('id' => $id));
    }

    /**
     * @param string $id
     * @return bool
     */
    public function cancel_order($id = '')
    {
        // Strip '0x' from start of string
        if (substr($id, 0, 2) == '0x') {
            $id = substr($id, 2, 64);
        }
        if (strlen($id) != 64) {
            return FALSE;
        }

        // Get the API result and return a PHP boolean
        $res = $this->_private_api('cancel_order', array('id' => $id));
        if ($res == 'true') {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param null $book
     * @param int $offset
     * @param int $limit
     * @param null $sort
     * @param null $prune
     * @return array|bool|mixed
     */
    public function user_transactions($book = NULL, $offset = 0, $limit = 100, $sort = NULL, $prune = NULL)
    {
        $post_array = array();
        if ($book !== NULL) {
            $post_array['book'] = $book;
        }
        if ($sort !== NULL) {
            $post_array['sort'] = ($sort)?'asc':'desc';
        }
        $post_array['offset'] = $offset;
        $post_array['limit'] = $limit;
        $xactions = $this->_private_api('user_transactions', $post_array);
        // The API does not include a key 'id' for funding transactions, so
        // the existence of 'id' key indicates a trade.
        switch($prune) {
            case 'trades':
                $ret = array();
                foreach ($xactions as $xaction) {
                    if (!isset($xaction->id)) {
                        continue;
                    }
                    $ret[$xaction->id] = $xaction;
                }
                return $ret;
            case 'funds':
                $ret = array();
                foreach ($xactions as $xaction) {
                    if (isset($xaction->id)) {
                        continue;
                    }
                    $ret[$xaction->id] = $xaction;
                }
                return $ret;
            default:
                return $xactions;
        }
    }


    public function bitcoin_deposit_address()
    {
        return $this->_private_api('bitcoin_deposit_address');
    }

    public function btc_in()
    {
        return $this->bitcoin_deposit_address();
    }

    /**
     * @param string $addr
     * @param int $amount
     * @return bool
     */
    public function bitcoin_wthdrawal($addr = '', $amount = 0)
    {
        // Do not attempt a withdrawal through the API unless the address
        // appears valid and the amount > 0
        if (strlen($addr) != 34 || floatval($amount) == 0) {
            return FALSE;
        }
        $post_array = array('address' => $addr,
            'amount' => $amount);
        if ($this->_private_api('bitcoin_withdrawal', $post_array) == 'OK') {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function ether_deposit_address()
    {
        return $this->_private_api('ether_deposit_address');
    }

    public function eth_in()
    {
        return $this->ether_deposit_address();
    }

    /**
     * @param string $addr
     * @param int $amount
     * @return bool
     */
    public function ether_withdrawal($addr = '', $amount = 0)
    {
        // Refuse to ask for an empty transaction
        if (floatval($amount) == 0) {
            return FALSE;
        }
        $post_array = array('address' => $addr,
            'amount' => $amount);
        if ($this->_private_api('ether_withdrawal', $post_array) == 'OK') {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * @param $address
     * @param $amount
     * @return bool
     */
    public function eth_out($address, $amount)
    {
        return $this->ether_withdrawal($address, $amount);
    }

    /**
     * @param null $book
     * @param int $amount
     * @param null $price
     * @return bool|mixed
     */
    public function buy($book = NULL, $amount = 0, $price = NULL)
    {
        $post = array();

        if (floatval($amount) == 0) {
            return FALSE;
        } else {
            $post['amount'] = $this->book_round($book, $amount);
        }
        if ($book !== NULL) {
            $post['book'] = $book;
        }
        if ($price !== NULL) {
            $post['price'] = $price;
        }

        return $this->_private_api('buy', $post);
    }

    /**
     * @param null $book
     * @param int $amount
     * @param null $price
     * @return bool|mixed
     */
    public function sell($book = NULL, $amount = 0, $price = NULL)
    {
        $post = array();

        if (floatval($amount) == 0) {
            return FALSE;
        } else {
            $post['amount'] = $this->book_round($book, $amount);
        }
        if ($book !== NULL) {
            $post['book'] = $book;
        }
        if ($price !== NULL) {
            $post['price'] = $price;
        }

        return $this->_private_api('sell', $post);
    }

    /**
     * @param null $book
     * @param int $amount
     * @return bool|mixed
     */
    public function exchange($book = NULL, $amount = 0)
    {
        $vbook = $this->validate_book($book);
        if ($vbook === TRUE) {
            // Book is valid so simply place a Sell Order for the specified amount
            return $this->sell($book, $amount);
        } else if ($vbook === FALSE) { // Reversed book
            $book = $this->reverse_book($book);
            // Check the 'ask' rate, as this is what we will get now when
            // exchanging in this (reversed) direction
            $ticker = $this->ticker($book);
            $amount = $amount / $ticker->ask;
            return $this->buy($book, $amount);
        } else {
            return FALSE;
        }
    }
}