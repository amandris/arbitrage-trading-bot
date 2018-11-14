<?php

namespace AppBundle\Command;


use AppBundle\Helper\BitstampHelper;
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

        $bitstamp_api_key = $this->getContainer()->getParameter('bitstamp_api_key');
        $bitstamp_api_secret = $this->getContainer()->getParameter('bitstamp_api_secret');
        $bitstamp_client_id = $this->getContainer()->getParameter('bitstamp_client_id');

        $bitstampHelper = new BitstampHelper( $bitstamp_api_key, $bitstamp_api_secret, $bitstamp_client_id, 'https://www.bitstamp.net/api/');

        $result = $bitstampHelper->buyBTC(0.5);

        dump($result);
        die();
    }

}