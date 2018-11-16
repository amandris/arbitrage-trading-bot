<?php

namespace AppBundle\Command;


use AppBundle\Helper\BinanceHelper;
use AppBundle\Helper\BitstampHelper;
use AppBundle\Helper\BittrexHelper;
use AppBundle\Helper\CexioHelper;
use AppBundle\Helper\ItbitHelper;
use AppBundle\Helper\KrakenHelper;
use AppBundle\Helper\Okcoin\ApiKeyAuthentication;
use AppBundle\Helper\OkcoinHelper;
use AppBundle\Helper\QuadrigacxHelper;
use AppBundle\Service\BalanceService;
use AppBundle\Service\TickerService;
use AppBundle\Service\TradeService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TestCommand
 * @package AppBundle\Command
 */
class TestCommand extends ContainerAwareCommand
{
    /** @var string $commandName*/
    private $commandName;

    /** @var TickerService $tickerService */
    private $tickerService;

    /** @var BalanceService $balanceService */
    private $balanceService;

    /** @var TradeService $tradeService */
    private $tradeService;

    protected function configure()
    {
        $this->commandName = 'bot:test';

        $this
            ->setName($this->commandName)
            ->setDescription('Test Exchange Api calls')
            ->setHelp('');
    }

    private function configureServices()
    {
        /** @var ContainerInterface $container */
        $container = $this->getContainer();

        $this->tickerService = $container->get('app.ticker.service');
        $this->balanceService = $container->get('app.balance.service');
        $this->tradeService = $container->get('app.trade.service');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureServices();

        $bitstamp_api_key       = $this->getContainer()->getParameter('bitstamp_api_key');
        $bitstamp_api_secret    = $this->getContainer()->getParameter('bitstamp_api_secret');
        $bitstamp_client_id     = $this->getContainer()->getParameter('bitstamp_client_id');

        $bittrex_api_key        = $this->getContainer()->getParameter('bittrex_api_key');
        $bittrex_api_secret     = $this->getContainer()->getParameter('bittrex_api_secret');

        $cexio_api_key          = $this->getContainer()->getParameter('cexio_api_key');
        $cexio_api_secret       = $this->getContainer()->getParameter('cexio_api_secret');
        $cexio_user_id          = $this->getContainer()->getParameter('cexio_user_id');

        $binance_api_key        = $this->getContainer()->getParameter('binance_api_key');
        $binance_api_secret     = $this->getContainer()->getParameter('binance_api_secret');

        $itbit_api_key          = $this->getContainer()->getParameter('itbit_api_key');
        $itbit_api_secret       = $this->getContainer()->getParameter('itbit_api_secret');
        $itbit_user_id          = $this->getContainer()->getParameter('itbit_user_id');

        $kraken_api_key         = $this->getContainer()->getParameter('kraken_api_key');
        $kraken_api_secret      = $this->getContainer()->getParameter('kraken_api_secret');

        $okcoin_api_key         = $this->getContainer()->getParameter('okcoin_api_key');
        $okcoin_api_secret      = $this->getContainer()->getParameter('okcoin_api_secret');

        $quadrigacx_api_key     = $this->getContainer()->getParameter('quadrigacx_api_key');
        $quadrigacx_api_secret  = $this->getContainer()->getParameter('quadrigacx_api_secret');
        $quadrigacx_client_id   = $this->getContainer()->getParameter('quadrigacx_client_id');

        /*
        $bitstampHelper = new BitstampHelper($bitstamp_api_key, $bitstamp_api_secret, $bitstamp_client_id, 'https://www.bitstamp.net/api/');
        $result = $bitstampHelper->buyBTC(0.5);
        */

        /*
        $bittrexHelper = new BittrexHelper($bittrex_api_key, $bittrex_api_secret, 'https://bittrex.com/api/v1.1/');
        $result = $bittrexHelper->buyLimit('USDT-BTC', 0.01, 4500);
        */

        /*
        $cexioHelper = new CexioHelper($cexio_user_id, $cexio_api_key, $cexio_api_secret, 'https://cex.io/api/');
        $result = $cexioHelper->place_order('sell',0.1, 6000, 'BTC/USD');
        */

        /*
        $binanceHelper = new BinanceHelper($binance_api_key, $binance_api_secret, 'https://api.binance.com');
        $result = $binanceHelper->sell('BTC', 0.1, 6000);
        */

        /*
        $itbitHelper = new ItbitHelper($itbit_api_secret, $itbit_api_key, $itbit_user_id, 'https://api.itbit.com/v1/');
        $wallet = $itbitHelper->wallet()[0];
        $result = $itbitHelper->create_order($wallet->id, 'sell', 0.1, 3000);
        */

        /*
        $krakenHelper = new KrakenHelper($kraken_api_key, $kraken_api_secret, 'https://api.kraken.com');
        $query = ['pair' => 'xbtusd', 'type' => 'buy', 'ordertype' => 'limit', 'price' => 54300, 'volumen' => 0.2];
        $result = $krakenHelper->queryPrivate('AddOrder', $query);
        */

        /*
        $okcoinHelper = new OkcoinHelper( new ApiKeyAuthentication($okcoin_api_key, $okcoin_api_secret));
        $buyParams = ['api_key' => $okcoin_api_key, 'symbol' => 'btc_usd', 'type'=> 'sell', 'price' => 5400, 'amount' => 0.3];
        $orderParams = ['current_page' => 1, 'page_length' => 10, 'status' => 0];
        $result = $okcoinHelper->orderHistoryApi($orderParams);
        //$result = $okcoinHelper->tradeApi($buyParams);
        */

        $quadrigacxHelper = new QuadrigacxHelper($quadrigacx_api_key, $quadrigacx_api_secret, $quadrigacx_client_id, 'https://api.quadrigacx.com/v2/');
        $result = $quadrigacxHelper->buy('btc_usd', 4, 1000);

        dump($result);
        die();
    }

}