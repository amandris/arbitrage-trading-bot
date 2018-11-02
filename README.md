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
 - [ ] Cexio
 - [ ] Bittrex
 - [ ] Binance

Installation
------------

 * `git clone https://github.com/amandris/arbitrage-trading-bot.git`

 * `composer install`
 
 * Set the database parameters in `app/config/parameters.yml`

 * Set the api keys of the exchanges you want to use in `app/config/parameters.yml`. At least two exchange api keys should be setted.

 * `bin/console bot:ticker`
 
