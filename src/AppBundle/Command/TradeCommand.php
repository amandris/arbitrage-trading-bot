<?php

namespace AppBundle\Command;

use AppBundle\DataTransferObject\OrderDTO;
use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Balance;
use AppBundle\Entity\Difference;
use AppBundle\Entity\OrderPair;
use AppBundle\Entity\Status;
use AppBundle\Entity\Ticker;
use AppBundle\Repository\BalanceRepository;
use AppBundle\Repository\DifferenceRepository;
use AppBundle\Repository\OrderPairRepository;
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

    /** @var OrderPairRepository $orderPairRepository */
    private $orderPairRepository;

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
        $this->orderPairRepository  = $container->get('app.order_pair.repository');
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
            $this->balanceService->getBalancesFromExchanges();
        }

        while(true) {
            /** @var boolean $previousRunning */
            $previousRunning = $status->isRunning();

            /** @var DateTime $now */
            $now = new \DateTime('now', new \DateTimeZone('Europe/Madrid'));

            $status = $this->statusRepository->findStatus();

            $this->getTickerAndDifferences($output);

            /** @var boolean $balancesNeedToBeReloaded */
            $balancesNeedToBeReloaded = false;

            if($status->isRunning()) {
                /** @var Difference[] $differences */
                $differences = $this->differenceRepository->findLastDifferencesGreaterThan($status->getStartDate(), $status->getThresholdUsd());

                /** @var OrderPair[] $openOrderPairs */
                $openOrderPairs = $this->orderPairRepository->findOpenOrderPairs();

                foreach ($differences as $difference) {
                    if ($status->getMaxOpenOrders() && $status->getMaxOpenOrders() > count($openOrderPairs)) {

                        /** @var Balance $balanceUsd */
                        $balanceBtc = $this->balanceRepository->findBalanceByExchange($difference->getExchangeSellName());

                        /** @var Balance $balanceBtc */
                        $balanceUsd = $this->balanceRepository->findBalanceByExchange($difference->getExchangeBuyName());

                        if(!$balanceUsd || $balanceUsd->getUsd() < ($status->getOrderValueBtc() * ($difference->getAsk() + $status->getAddOrSubToOrderUsd()))){
                            continue;
                        }

                        if(!$balanceBtc || $balanceBtc->getBtc() < ($status->getOrderValueBtc() - ($status->getAddOrSubToOrderUsd()) / $difference->getBid())){
                            continue;
                        }

                        /** @var boolean $exchangeHasOpenOrder */
                        $exchangeHasOpenOrder = false;
                        //check if there are open order on those exchanges
                        foreach ($openOrderPairs as $openOrderPair) {
                            if( $openOrderPair->getBuyOrderExchange() === $difference->getExchangeSellName() || $openOrderPair->getBuyOrderExchange() === $difference->getExchangeBuyName() ||
                                $openOrderPair->getSellOrderExchange() === $difference->getExchangeSellName() || $openOrderPair->getSellOrderExchange() === $difference->getExchangeBuyName()){
                                $exchangeHasOpenOrder = true;
                                break;
                            }
                        }

                        if(!$exchangeHasOpenOrder) {
                            $this->tradeService->placeOrderPair($difference, $status);
                        }
                    }
                }
            }

            $openOrderPairs = $this->orderPairRepository->findOpenOrderPairs();

            /** @var OrderDTO[] $orders */
            $orders = $this->tradeService->getOrders();

            $status->setOrderPairLastUpdateDate($now);
            $this->statusRepository->save($status);
            $status = $this->statusRepository->findStatus();

            $orderIds = array_column($orders, 'orderId');

            foreach ($openOrderPairs as $openOrderPair) {
                $orderHasChange = false;
                if (!in_array($openOrderPair->getBuyOrderId(), $orderIds)) {
                    $openOrderPair->setBuyOrderOpen(false);
                    $orderHasChange = true;
                }
                if (!in_array($openOrderPair->getSellOrderId(), $orderIds)) {
                    $openOrderPair->setSellOrderOpen(false);
                    $orderHasChange = true;
                }

                if ($orderHasChange) {
                    $this->orderPairRepository->save($openOrderPair);
                    $balancesNeedToBeReloaded = true;
                }
            }

            if($balancesNeedToBeReloaded || ($status->isRunning() === true && $status->isRunning() !== $previousRunning)){
                $this->balanceService->getBalancesFromExchanges();
            }

            $status = $this->statusRepository->findStatus();

            sleep($this->interValSeconds);
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

        $this->tickerRepository->deleteAll();

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
                if($askTickerDTO->getBid() - $bidTickerDTO->getAsk() >= 0) {
                    $difference = new Difference();
                    $difference->setCreated($now);
                    $difference->setBid($askTickerDTO->getBid());
                    $difference->setAsk($bidTickerDTO->getAsk());
                    $difference->setExchangeSellName($askTickerDTO->getName());
                    $difference->setExchangeBuyName($bidTickerDTO->getName());
                    $difference->setExchangeNames($askTickerDTO->getName() . '-' . $bidTickerDTO->getName());
                    $difference->setDifference($askTickerDTO->getBid() - $bidTickerDTO->getAsk());

                    $this->differenceRepository->save($difference);
                }

                if($bidTickerDTO->getBid() - $askTickerDTO->getAsk() >= 0) {
                    $difference = new Difference();
                    $difference->setCreated($now);
                    $difference->setBid($bidTickerDTO->getBid());
                    $difference->setAsk($askTickerDTO->getAsk());
                    $difference->setExchangeSellName($bidTickerDTO->getName());
                    $difference->setExchangeBuyName($askTickerDTO->getName());
                    $difference->setExchangeNames($bidTickerDTO->getName() . '-' . $askTickerDTO->getName());
                    $difference->setDifference($bidTickerDTO->getBid() - $askTickerDTO->getAsk());

                    $this->differenceRepository->save($difference);
                }
            }
        }
    }
}