Arbitrage Bitcoin Trading Bot
=============================

Using the tiny diferences in bitcoin value among several exchanges, this bot places buy and sell orders so some profit is made.

Supported exchanges
-------------------

 * Bitstamp
 * Bitfinex
 * OkCoin
 * Poloniex
 * Kraken
 * ItBit
 * GDAX (Coinbase Pro)
 * QuadrigaCX
 * Exmo
 * Cexio
 * Bittrex
 * Binance

Installation
------------

 * `git clone https://github.com/amandris/arbitrage-trading-bot.git`

 * `composer install`

 * Set the api keys of the exchanges you want to use in `app/config/config.yml`. At least two exchange api keys should be setted.

 * `bin/console test:ticker`
 
