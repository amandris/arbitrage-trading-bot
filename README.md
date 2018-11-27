Arbitrage Bitcoin Trading Bot
=============================

Using the tiny diferences in bitcoin value among several exchanges, this bot places buy and sell orders so some profit is made.

Supported exchanges
-------------------

 - [x] Bitstamp
 - [ ] Bitfinex 
 - [x] OkCoin
 - [ ] Poloniex
 - [x] Kraken
 - [x] ItBit
 - [ ] GDAX (Coinbase Pro)
 - [x] QuadrigaCX
 - [ ] Exmo
 - [x] Cexio
 - [x] Bittrex
 - [x] Binance

Prerequisites
-------------

 * PHP 7.1
 * Composer
 * Some RDBS (Mysql, Postgres, Sqlite, ...)
 * Node.js
 * Gulp

Installation
------------

 * `git clone https://github.com/amandris/arbitrage-trading-bot.git`

 * `cd arbitrage-trading-bot`
 
 * `composer install`
 
 * Set the database parameters in `app/config/parameters.yml`

 * Create database and run `bin/console doctrine:schema:update --force`
 
 * Set the api keys of the exchanges you want to use in `app/config/parameters.yml`. At least two exchange api keys should be setted.

 * `npm install`
 
 * `gulp`

 * `bin/console server:run localhost:8000`

 * `bin/console bot:trade`
 
 *  Open <http://localhost:8000> on your browser
 

Donations
---------

**Bitcoin**: 35JBxSyefxmVj34obKC2od3r98MuaJ34am 

**Bitcoin Cash**: 3NrTmv4f1752D9vtYTMh8EaqtHQE1ZXdbR

**Litecoin**: MM1p5NRCPqa5EUaU5PNjDqy1FXHQmxsiUF