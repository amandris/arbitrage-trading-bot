<?php

namespace AppBundle\Command;


use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Balance;
use AppBundle\Entity\Ticker;
use AppBundle\Repository\BalanceRepository;
use AppBundle\Repository\TickerRepository;
use AppBundle\Service\BalanceService;
use AppBundle\Service\TickerService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TestCommand
 * @package AppBundle\Command
 */
class TradeCommand extends ContainerAwareCommand
{
    /** @var string $commandName*/
    private $commandName;

    /** @var TickerService $tickerService */
    private $tickerService;

    /** @var BalanceService $balanceService */
    private $balanceService;

    /** @var TickerRepository $tickerRepository */
    private $tickerRepository;

    /** @var BalanceRepository $balanceRepository */
    private $balanceRepository;

    /** @var int $interValSeconds */
    private $interValSeconds;

    protected function configure()
    {
        $this->commandName = 'bot:trade';

        $this
            ->setName($this->commandName)
            ->setDescription('Arbitrage trade in several exchanges with USD/BTC pairs')
            ->setHelp('');
    }

    private function configureServices()
    {
        /** @var ContainerInterface $container */
        $container = $this->getContainer();

        $this->tickerService        = $container->get('app.ticker.service');
        $this->balanceService       = $container->get('app.balance.service');
        $this->tickerRepository     = $container->get('app.ticker.repository');
        $this->balanceRepository    = $container->get('app.balance.repository');
        $this->interValSeconds      = $container->getParameter('interval_seconds');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureServices();

        /** @var BalanceDTO[] $balanceDTOs */
        $balanceDTOs = $this->balanceService->getBalances();

        if( count($balanceDTOs) < 2){
            die('Error: At least two exchanges must be setted.');
        }

        $now = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));
        $output->writeln('Balances at '. date_format($now, 'd/m/Y H:i:s'));

        foreach ($balanceDTOs as $balanceDTO) {
            $balance = new Balance();
            $balance->setName($balanceDTO->getName());
            $balance->setUsd($balanceDTO->getUsd());
            $balance->setBtc($balanceDTO->getBtc());
            $balance->setCreated($now);

            $output->writeln('    '.$balanceDTO->toString());
            $this->balanceRepository->save($balance);
        }

        sleep($this->interValSeconds);


        /** @var TickerDTO[] $tickerDTOs */
        $tickerDTOs = $this->tickerService->getTickers();

        while(true) {
            $output->writeln(date_format(new \DateTime('now', new \DateTimeZone('Europe/Madrid')), 'd/m/Y H:i:s'));
            foreach ($tickerDTOs as $tickerDTO) {
                $ticker = new Ticker();
                $ticker->setName($tickerDTO->getName());
                $ticker->setAsk($tickerDTO->getAsk());
                $ticker->setBid($tickerDTO->getBid());
                $ticker->setCreated($tickerDTO->getTimestamp());

                $output->writeln('    '.$tickerDTO->toString());
                $this->tickerRepository->save($ticker);
            }

            sleep($this->interValSeconds);
        }
    }
}