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
 - [ ] QuadrigaCX
 - [ ] Exmo
 - [x] Cexio
 - [ ] Bittrex
 - [x] Binance

Installation
------------

 * `git clone https://github.com/amandris/arbitrage-trading-bot.git`

 * `composer install`
 
 * Set the database parameters in `app/config/parameters.yml`

 * Create database and run `bin/console doctrine:schema:update --force`
 
 * Set the api keys of the exchanges you want to use in `app/config/parameters.yml`. At least two exchange api keys should be setted.

 * `bin/console server:run localhost:8000`

 * `bin/console bot:trade`
 
 *  Open <http://localhost:8000> on your browser
 
