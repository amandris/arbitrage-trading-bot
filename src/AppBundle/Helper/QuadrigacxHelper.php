<?php

namespace AppBundle\Helper;

class QuadrigacxHelper
{

    /**
     * @var string $base_uri
     */
    private $base_uri;

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
     * Load credentials from file. This is not necessary for Public
     * API calls such as the Ticker.
     *
     * This will attempt to read the given filename for credentials,
     * defaulting to $HOME/.quadriga.conf
     */
    public function load_credentials($filename = NULL) {
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
     * Common function to create a new cURL object for all API calls both
     * Public and Private.
     *
     * Parameters:
     * str $api_func - The API Function to call; Complete query string
     *                 including any '?' GET variables required.
     *                 Example: 'ticker?book=btc_usd'
     */
    private function _curler($api_func) {
        if (strlen($api_func) < 1) {
            return NULL;
        }

        $curler = curl_init($this->base_uri . $api_func);
        curl_setopt($curler, CURLOPT_RETURNTRANSFER, TRUE);

        return $curler;
    }

    /**
     * Common functionality to generate a cURL object and retrieve
     * data from the Public API Functions which do not require
     * authentication and use HTTP GET variables.
     *
     * Returns a json_decoded array or FALSE on failure.
     */
    private function _public_api($api_func = '', $gets = array()) {
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
     * Common functionality to generate a cURL object and retrieve
     * data from the Private API Functions which require authentication
     * (loaded credentials) and use HTTP POST variables.
     *
     * Returns a json_decoded array or FALSE on failure.
     */
    private function _private_api($api_func = '', $post = array()) {
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
     * Return an array of all valid book names.
     *
     * This will be useful as more books get added; Simply update this list.
     */
    public function get_books() {
        return array(
            'btc_cad',
            'btc_usd',
            'eth_btc',
            'eth_cad',
        );
    }

    /**
     * Check that a given book is valid.
     *
     * If the book is valid, return TRUE.
     * If the book is of the form "min_maj" (reversed, see API
     * documentation for details), return FALSE.
     * If either currency code is not recognized, or the two
     * currencies do not form a valid book (ex "cad_usd") return NULL.
     */
    public function validate_book($book) {
        $valid_books = $this->get_books();
        foreach ($valid_books as $b) {
            if ($book == $b) { return TRUE; }
            if ($this->reverse_book($book) == $b) { return FALSE; }
        }
        return NULL;
    }

    public function reverse_book($book) {
        $currencies = explode('_', $book);
        if (count($currencies) != 2) { return NULL; }
        return implode('_', array($currencies[1], $currencies[0]));
    }

    /**
     * Round off the given amount depending on the major currency.
     * Return the rounded value, or 0 on error (On the assumption it
     * is better to pass '0' to the API than NULL)
     */
    public function book_round($book, $amount) {
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

    /**** PUBLIC API CALLS ****/

    /**
     * The QuadrigaCX Ticker returns data regarding the
     * trading information for a given book (default btc_cad).
     *
     * Available books are:
     *   btc_cad
     *   btc_usd
     *   eth_btc
     *   eth_cad
     */
    public function ticker($book = NULL) {
        if ($book === NULL) {
            return $this->_public_api('ticker');
        } else {
            return $this->_public_api('ticker', array('book' => $book));
        }
    }

    /**
     * The public Order Book returns a listing of all open
     * orders for a given book (default btc_cad).
     *
     * Optional Parameters:
     *   str  $book  - The order book to retrieve.
     *   bool $group - Whether or not to group orders of the same price (Default true).
     */
    public function order_book($book = NULL, $group = TRUE) {
        $gets = array();
        if ($book !== NULL) {
            $gets['book'] = $book;
        }
        if ($group !== NULL) {
            $gets['group'] = ($group)?'1':'0';
        }
        return $this->_public_api('order_book', $gets);
    }

    /**
     * Transactions returns a list of recent trades
     *
     * Optional Parameters:
     *   str $book - The order book to retrieve (Default: btc_cad)
     *   str $time - Time frame for exported data ('minute' / 'hour', Default: hour)
     */
    public function transactions($book = NULL, $time = NULL) {
        $gets = array();
        if ($book !== NULL) {
            $gets['book'] = $book;
        }
        if ($time !== NULL && ($time == 'minute' || $time == 'hour')) {
            $gets['time'] = $time;
        }
        return $this->_public_api('transactions', $gets);
    }

    /**** PRIVATE API CALLS ****/

    /**
     * Obtain balance of a QuadrigaCX account.
     *
     * Returns array of account's balances, such as but not limited to:
     *
     *    cad_balance - CAD balance
     *    btc_balance - BTC balance
     *    cad_reserved - CAD reserved in open orders
     *    btc_reserved - BTC reserved in open orders
     *    cad_available - CAD available for trading
     *    btc_available - BTC available for trading
     *    fee - customer trading fee
     */
    public function balance() {
        return $this->_private_api('balance');
    }

    /**
     * Return array of account's current open orders.
     */
    public function open_orders() {
        return $this->_private_api('open_orders');
    }

    /**
     * Return array of a specific order.
     *
     * Parameters:
     *   str $id – a single or array of 64 characters long
     *             hex string(s) taken from the list of orders
     */
    public function lookup_order($id = '') {
        if (is_array($id)) {
            $id = json_encode($id);
        }
        return $this->_private_api('lookup_order', array('id' => $id));
    }

    /**
     * Cancel an order. Returns TRUE on success, FALSE otherwise.
     */
    public function cancel_order($id = '') {
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
     * Return list of account's transaction history.
     *
     * Parameters:
     *   str  $book  - The book to retrieve (Default: btc_cad)
     *   int  $offset - Skip a number of transactions returned from the API
     *                  (Default: 0)
     *   int  $limit - Limit the number of results returned from the API
     *                 (Default: 100)
     *   bool $sort - Sort the list by date+time TRUE = ASC, FALSE = DESC
     *                (Default: Do not sort)
     *   str  $prune - Prune the results: 'trades' shows only trades,
     *                'funds' shows only funding transactions. (Default: no pruning)
     */
    public function user_transactions($book = NULL, $offset = 0, $limit = 100, $sort = NULL, $prune = NULL) {
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

    /**
     * Returns a string of a Bitcoin address for deposits into a
     * QuadrigaCX account.
     */
    public function bitcoin_deposit_address() {
        return $this->_private_api('bitcoin_deposit_address');
    }
    /* Alias for bitcoin_deposit_address */
    public function btc_in() {
        return $this->bitcoin_deposit_address();
    }

    /**
     * Request the QuadrigaCX API generate a new transaction to send
     * BTC to a given address. Returns TRUE on success, FALSE on failure.
     *
     * Parameters:
     *   str $addr - The bitcoin address to send to.
     *   flt $amount - The amount to send.
     */
    public function bitcoin_wthdrawal($addr = '', $amount = 0) {
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
    /* Alias for function bitcoin_withdrawal */
    public function btc_out($address, $amount) {
        return $this->bitcoin_withdrawal($address, $amount);
    }

    /**
     * Returns a Ethereum address for deposits into a QuadrigaCX account.
     */
    public function ether_deposit_address() {
        return $this->_private_api('ether_deposit_address');
    }
    /* Alias for function ether_deposit_address */
    public function eth_in() {
        return $this->ether_deposit_address();
    }

    /**
     * Request the QuadrigaCX API generate a new transaction to send
     * Ethereum to a given address. Returns TRUE on success, FALSE on
     * failure.
     *
     * Parameters:
     *   str $addr - The Ethereum address to send to.
     *   flt $amount - The amount to send.
     */
    public function ether_withdrawal($addr = '', $amount = 0) {
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
    /* Alias for function ether_withdrawal */
    public function eth_out($address, $amount) {
        return $this->ether_withdrawal($address, $amount);
    }

    /**
     * Place a Buy order with the QuadrigaCX API. If $price is
     * specified, it will be passed along to the API which
     * creates a Limit Order, otherwise a Market Order is placed.
     * See the API documentation for more info.
     */
    public function buy($book = NULL, $amount = 0, $price = NULL) {
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
     * Place a Sell order with the QuadrigaCX API. If $price is
     * specified, it will be passed along to the API which
     * creates a Limit Order, otherwise a Market Order is placed.
     * See the API documentation for more info.
     */
    public function sell($book = NULL, $amount = 0, $price = NULL) {
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
     * The exchange function determines if the given book is either valid
     * or reversed, and issues a Sell Order or Buy Order as appropriate. If
     * a reversed book is givem (ex "cad_btc") then the ticker values for
     * the correct book are checked, the amount adjusted, and an Order
     * placed.
     *
     * Examples:
     *   exchange btc_cad 1 = Sell 1 on the btc_cad book
     *   exchange cad_btc 1 = Check ticker, adjust amount, reverse book and
     *                        place Buy Order on the btc_cad book
     */
    public function exchange($book = NULL, $amount = 0) {
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