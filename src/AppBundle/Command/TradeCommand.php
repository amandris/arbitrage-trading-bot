<?php

namespace AppBundle\Command;


use AppBundle\DataTransferObject\BalanceDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Balance;
use AppBundle\Entity\Difference;
use AppBundle\Entity\Status;
use AppBundle\Entity\Ticker;
use AppBundle\Repository\BalanceRepository;
use AppBundle\Repository\DifferenceRepository;
use AppBundle\Repository\StatusRepository;
use AppBundle\Repository\TickerRepository;
use AppBundle\Service\BalanceService;
use AppBundle\Service\TickerService;
use AppBundle\Service\TradeService;
use DateTime;
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

    /** @var TradeService $tradeService */
    private $tradeService;

    /** @var TickerRepository $tickerRepository */
    private $tickerRepository;

    /** @var DifferenceRepository $differenceRepository */
    private $differenceRepository;

    /** @var StatusRepository $statusRepository */
    private $statusRepository;

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
        $this->tradeService         = $container->get('app.trade.service');
        $this->tickerRepository     = $container->get('app.ticker.repository');
        $this->differenceRepository = $container->get('app.difference.repository');
        $this->balanceRepository    = $container->get('app.balance.repository');
        $this->statusRepository     = $container->get('app.status.repository');
        $this->interValSeconds      = $container->getParameter('interval_seconds');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureServices();

        /** @var Status $status */
        $status = $this->statusRepository->findStatus();

        if( $status->isRunning() === true){
            $this->getBalance($output);
        }

        while(true) {
            /** @var boolean $previousRunning */
            $previousRunning = $status->isRunning();

            $status = $this->statusRepository->findStatus();

            $this->getTickerAndDifferences($output);

            /** @var Difference[] $differences */
            $differences = $this->differenceRepository->findLastDifferencesGreaterThan($status->getStartDate(), $status->getThresholdUsd());

            foreach ($differences as $difference){
                $this->tradeService->placeOrderPair($difference, $status);
            }

            if($status->isRunning() === true && $status->isRunning() !== $previousRunning){
                $this->getBalance($output);
            }

            sleep($this->interValSeconds);
        }
    }

    /**
     * @param OutputInterface $output
     */
    function getBalance(OutputInterface $output)
    {
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
    }

    /**
     * @param OutputInterface $output
     */
    function getTickerAndDifferences(OutputInterface $output)
    {
        /** @var DateTime $now */
        $now = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));

        /** @var TickerDTO[] $tickerDTOs */
        $tickerDTOs = $this->tickerService->getTickers();

        $output->writeln(date_format($now, 'd/m/Y H:i:s'));
        foreach ($tickerDTOs as $tickerDTO) {
            $ticker = new Ticker();
            $ticker->setName($tickerDTO->getName());
            $ticker->setAsk($tickerDTO->getAsk());
            $ticker->setBid($tickerDTO->getBid());
            $ticker->setCreated($now);

            $output->writeln('    '.$tickerDTO->toString());
            $this->tickerRepository->save($ticker);
        }

        $this->differenceRepository->deleteAll();

        $observedExchanges = array();
        foreach ($tickerDTOs as $askTickerDTO) {
            array_push($observedExchanges, $askTickerDTO->getName());
            foreach ($tickerDTOs as $bidTickerDTO) {
                if(in_array($bidTickerDTO->getName(), $observedExchanges)) {
                    continue;
                }
                $difference = new Difference();
                $difference->setCreated($now);
                $difference->setBid($askTickerDTO->getBid());
                $difference->setAsk($bidTickerDTO->getAsk());
                $difference->setExchangeAskName($askTickerDTO->getName());
                $difference->setExchangeBidName($bidTickerDTO->getName());
                $difference->setExchangeNames($askTickerDTO->getName().'-'.$bidTickerDTO->getName());
                $difference->setDifference($askTickerDTO->getBid() - $bidTickerDTO->getAsk());

                $this->differenceRepository->save($difference);

                $difference = new Difference();
                $difference->setCreated($now);
                $difference->setBid($bidTickerDTO->getBid());
                $difference->setAsk($askTickerDTO->getAsk());
                $difference->setExchangeAskName($bidTickerDTO->getName());
                $difference->setExchangeBidName($askTickerDTO->getName());
                $difference->setExchangeNames($bidTickerDTO->getName().'-'.$askTickerDTO->getName());
                $difference->setDifference($bidTickerDTO->getBid() - $askTickerDTO->getAsk());

                $this->differenceRepository->save($difference);
            }
        }
    }
}