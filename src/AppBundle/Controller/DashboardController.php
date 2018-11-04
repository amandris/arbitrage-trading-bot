<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Status;
use AppBundle\Repository\BalanceRepository;
use AppBundle\Repository\StatusRepository;
use AppBundle\Repository\TickerRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends Controller
{
    /**
     * @Route("/", name="dashboard")
     */
    public function indexAction(Request $request)
    {
        /** @var StatusRepository $statusRepository */
        $statusRepository = $this->get('app.status.repository');

        /** @var Status $status */
        $status = $statusRepository->findStatus();

        return $this->render('@App/dashboard/index.html.twig', [
            'status' => $status
        ]);
    }

    /**
     * @Route("/start-trading", options={"expose"=true}, name="startTrading")
     * @param Request $request
     * @return JsonResponse
     */
    public function startTradingAction(Request $request)
    {
        /** @var StatusRepository $statusRepository */
        $statusRepository = $this->get('app.status.repository');

        /** @var Status $status */
        $status = $statusRepository->findStatus();
        if($status->isRunning() === true){
            return new JsonResponse(['code' => 200, 'running' => false]);
        }

        $thresholdUsd = $request->get       ('thresholdUsd');
        $orderValueUsd = $request->get      ('orderValueUsd');
        $tradingTimeMinutes = $request->get ('tradingTimeMinutes') ?: null;

        $status->setTradingTimeMinutes  ($tradingTimeMinutes);
        $status->setOrderValueUsd       ($orderValueUsd);
        $status->setThresholdUsd        ($thresholdUsd);
        $status->setRunning             (true);
        $status->setStartDate           (new \DateTime('now', new \DateTimeZone('Europe/Madrid')));

        $statusRepository->save($status);

        return new JsonResponse(['code' => 200, 'running' => true, 'startDate' => $status->getStartDate()->format('d/m/Y h:i:s')]);
    }

    /**
     * @Route("/stop-trading", options={"expose"=true}, name="stopTrading")
     * @param Request $request
     * @return JsonResponse
     */
    public function stopTradingAction(Request $request)
    {
        /** @var StatusRepository $statusRepository */
        $statusRepository = $this->get('app.status.repository');

        /** @var Status $status */
        $status = $statusRepository->findStatus();
        if($status->isRunning() === false){
            return new JsonResponse(['code' => 200, 'running' => true]);
        }

        $status->setRunning(false);
        $status->setStartDate(null);

        $statusRepository->save($status);

        return new JsonResponse(['code' => 200, 'running' => false]);
    }

    /**
     * @Route("/balance", options={"expose"=true}, name="balance")
     * @param Request $request
     */
    public function balanceAction(Request $request)
    {
        /** @var StatusRepository $statusRepository */
        $statusRepository = $this->get('app.status.repository');

        /** @var BalanceRepository $balanceRepository */
        $balanceRepository = $this->get('app.balance.repository');

        /** @var Status $status */
        $status = $statusRepository->findStatus();

        $resultFirst    = [];
        $resultLast     = [];
        $exchangeNames  = [];
        $result         = [];

        if($status->isRunning() === true && $status->getStartDate()){
            $firstBalances = $balanceRepository->findFirstBalances($status->getStartDate(), 12);
            $lastBalances = $balanceRepository->findLastBalances($status->getStartDate(),12);

            foreach($firstBalances as $firstBalance){
                if(!array_key_exists($firstBalance->getName(), $resultFirst)){
                    $resultFirst[$firstBalance->getName()] = ['usd'=>$firstBalance->getUsd(), 'btc'=>number_format($firstBalance->getBtc(), 8)];
                    array_push($exchangeNames, $firstBalance->getName());
                }
            }

            foreach($lastBalances as $lastBalance){
                if(!array_key_exists($lastBalance->getName(), $resultLast)) {
                    $resultLast[$lastBalance->getName()] = ['usd' => $lastBalance->getUsd(), 'btc' => number_format($lastBalance->getBtc() ,8)];
                    array_push($exchangeNames, $lastBalance->getName());
                }
            }
        }

        $exchangeNames = array_unique($exchangeNames);

        foreach ($exchangeNames as $exchangeName){
            $firstBalance = array_key_exists($exchangeName, $resultFirst) ? $resultFirst[$exchangeName] : ['usd' => '', 'btc' => ''];
            $lastBalance = array_key_exists($exchangeName, $resultLast) ? $resultLast[$exchangeName] : ['usd' => '', 'btc' => ''];
            array_push($result, ['name' => $exchangeName , 'first' => $firstBalance , 'last' => $lastBalance]);
        }

        return $this->render('@App/dashboard/balance.html.twig', [
            'balances' => $result
        ]);
    }

    /**
     * @Route("/ticker", options={"expose"=true}, name="ticker")
     * @param Request $request
     */
    public function tickerAction(Request $request)
    {
        /** @var StatusRepository $statusRepository */
        $statusRepository = $this->get('app.status.repository');

        /** @var TickerRepository $tickerRepository */
        $tickerRepository = $this->get('app.ticker.repository');

        /** @var Status $status */
        $status = $statusRepository->findStatus();

        $resultFirst    = [];
        $resultLast     = [];
        $exchangeNames  = [];
        $result         = [];

        if($status->isRunning() === true && $status->getStartDate()){
            $firstTickers = $tickerRepository->findFirstTickers($status->getStartDate(), 12);
            $lastTickers = $tickerRepository->findLastTickers($status->getStartDate(),12);

            foreach($firstTickers as $firstTicker){
                if(!array_key_exists($firstTicker->getName(), $resultFirst)){
                    $resultFirst[$firstTicker->getName()] = ['ask'=>$firstTicker->getAsk(), 'bid'=>$firstTicker->getBid()];
                    array_push($exchangeNames, $firstTicker->getName());
                }
            }

            foreach($lastTickers as $lastTicker){
                if(!array_key_exists($lastTicker->getName(), $resultLast)) {
                    $resultLast[$lastTicker->getName()] = ['ask' => $lastTicker->getAsk(), 'bid' => $lastTicker->getBid()];
                    array_push($exchangeNames, $lastTicker->getName());
                }
            }
        }

        $exchangeNames = array_unique($exchangeNames);

        foreach ($exchangeNames as $exchangeName){
            $firstTicker = array_key_exists($exchangeName, $resultFirst) ? $resultFirst[$exchangeName] : ['ask' => '', 'bid' => ''];
            $lastTicker = array_key_exists($exchangeName, $resultLast) ? $resultLast[$exchangeName] : ['ask' => '', 'bid' => ''];
            array_push($result, ['name' => $exchangeName , 'first' => $firstTicker , 'last' => $lastTicker]);
        }

        return $this->render('@App/dashboard/ticker.html.twig', [
            'tickers' => $result
        ]);
    }
}
