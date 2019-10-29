Arbitrage Bitcoin Trading Bot
=============================

Using the tiny diferences in bitcoin value among several exchanges, this bot places buy and sell orders so some profit is made.

**Example:**

Once you are running the web interface, you spot a 352.48$ difference between sell price in QuadrigaCX and buy price in Bitstamp.

| Exchange Sell | Exchange Buy | Ask     | Bid     | Difference |
|---------------|--------------|---------|---------|------------|
|  QuadrigaCX   | Bitstamp     | 3648.52 | 4001.00 | 352.48     |


 1. Deposit 3648.52$ in Bitstamp.
 2. Deposit 1 BTC in QuadrigaCX.
 3. In the **Order value (BTC)** field set the desired order value (between 0.001BTC and 1 BTC)
 4. Press :arrow_forward: button on the **QuadrigaCX-Bitstamp** difference (where the 352.48$ difference in value is shown) several times until you spent all your BTC balance on QuadrigaCX. You can also start the automate trading.
 5. You now have 4001$ in QuadrigaCX and 1 BTC in Bitstamp.
 6. Withdraw balances on both exchanges. The profit before fees is 352.48$.
 7. Repeat.
 
 ![Web interface](https://github.com/amandris/arbitrage-trading-bot/blob/master/src/AppBundle/Resources/public/dist/img/screenshot.png)

Supported exchanges
-------------------

 - [x] Bitstamp
 - [x] OkCoin
 - [x] Kraken
 - [x] ItBit
 - [x] QuadrigaCX
 - [x] Cexio
 - [x] Bittrex
 - [x] Binance

Prerequisites
-------------

 * PHP 7.1
 * Composer
 * Some RDBS (Mysql, Postgres, Sqlite, ...)
 * Node.js (Known issues with versions higher than 10. Try with 10 or lower)
 * Gulp

Installation
------------

 * `git clone https://github.com/amandris/arbitrage-trading-bot.git`

 * `cd arbitrage-trading-bot`
 
 * `composer install`
 
 * Set the database parameters in `app/config/parameters.yml` file

 * Create database with `php bin/console doctrine:database:create`
 
 * Create schema with `bin/console doctrine:schema:update --force`
 
 * `npm install`
 
 * `gulp`
 
 * Set the api keys of the exchanges you want to use in `app/config/parameters.yml`. At least two exchange api keys should be setted.
 
 * Enable or disable exchanges on `app/config/config.yml` file

 * `bin/console server:run localhost:8000`

 * `bin/console bot:trade` on a new console
 
 *  Open <http://localhost:8000> on your browser
 

Donations
---------

**Bitcoin**: 35JBxSyefxmVj34obKC2od3r98MuaJ34am 

**Bitcoin Cash**: 3NrTmv4f1752D9vtYTMh8EaqtHQE1ZXdbR

**Litecoin**: MM1p5NRCPqa5EUaU5PNjDqy1FXHQmxsiUF


Disclaimer
----------
This software project has only educational purposes. Arbitrage trading is a complex and dangerous game. Use this tool at your own risk.
