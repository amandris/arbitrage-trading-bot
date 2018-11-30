<?php

namespace AppBundle\Controller;

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
use AppBundle\Service\DifferenceService;
use AppBundle\Service\TradeService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    /**
     * @Route("/", name="dashboard")
     * @param Request $request
     * @return Response
     */
    public function dashboardAction(Request $request)
    {
        /** @var StatusRepository $statusRepository */
        $statusRepository = $this->get('app.status.repository');

        /** @var Status $status */
        $status = $statusRepository->findStatus();

        $interval = $this->getParameter('interval_seconds');

        return $this->render('@App/dashboard/index.html.twig', [
            'status' => $status,
            'interval' => $interval
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

        $thresholdUsd =         $request->get('thresholdUsd');
        $orderValueBtc =        $request->get('orderValueBtc');
        $addOrSubToOrderUsd =   $request->get('addOrSubToOrderUsd');
        $tradingTimeMinutes =   $request->get('tradingTimeMinutes') ?: null;
        $maxOpenOrders =        $request->get('maxOpenOrders') ?: null;

        $status->setTradingTimeMinutes  ($tradingTimeMinutes);
        $status->setOrderValueBtc       ($orderValueBtc);
        $status->setThresholdUsd        ($thresholdUsd);
        $status->setAddOrSubToOrderUsd  ($addOrSubToOrderUsd);
        $status->setMaxOpenOrders       ($maxOpenOrders);
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

        $statusRepository->save($status);

        return new JsonResponse(['code' => 200, 'running' => false]);
    }

    /**
     * @Route("/is-running", options={"expose"=true}, name="isRunning")
     * @param Request $request
     * @return Response
     */
    public function isRunningAction(Request $request)
    {
        /** @var StatusRepository $statusRepository */
        $statusRepository = $this->get('app.status.repository');

        /** @var Status $status */
        $status = $statusRepository->findStatus();

        if($status->isRunning() === true && $status->getTradingTimeMinutes() !== null) {

            $now = new \DateTime('now',  new \DateTimeZone('Europe/Madrid'));

            $startDate = $status->getStartDate()->add(new \DateInterval('PT'.($status->getTradingTimeMinutes() * 60).'S'));

            if(($now->getTimestamp() + $now->getOffset()) > $startDate->getTimestamp()){
                $status->setRunning(false);
                $statusRepository->save($status);
            }
        }

        return new Response($status->isRunning() ? 'ok' : 'ko');
    }

    /**
     * @Route("/balance", options={"expose"=true}, name="balance")
     * @param Request $request
     * @return Response
     */
    public function balanceAction(Request $request)
    {
        /** @var BalanceRepository $balanceRepository */
        $balanceRepository = $this->get('app.balance.repository');

        return $this->render('@App/dashboard/balance.html.twig', [
            'balances' =>  $balanceRepository->findAll()
        ]);
    }

    /**
     * @Route("/ticker", options={"expose"=true}, name="ticker")
     * @param Request $request
     * @return Response
     */
    public function tickerAction(Request $request)
    {
        $tickerRepository = $this->get('app.ticker.repository');

        return $this->render('@App/dashboard/ticker.html.twig', [
            'tickers' => $tickerRepository->findAll()
        ]);
    }

    /**
     * @Route("/difference", options={"expose"=true}, name="difference")
     * @param Request $request
     * @return Response
     */
    public function differenceAction(Request $request)
    {
        /** @var DifferenceService $differenceService */
        $differenceService = $this->get('app.difference.service');

        /** @var BalanceService $balanceService */
        $balanceService = $this->get('app.balance.service');

        /** @var StatusRepository $statusRepository */
        $statusRepository = $this->get('app.status.repository');

        /** @var Status $status */
        $status = $statusRepository->findStatus();

        /** @var array $usdBalances */
        $usdBalances = $balanceService->getUsdBalancesFormatted();

        /** @var array $btcBalances */
        $btcBalances = $balanceService->getBtcBalancesFormatted();

        return $this->render('@App/dashboard/difference.html.twig', [
            'differences' => $differenceService->getFormattedDifferences(),
            'thresholdUsd' => $status->getThresholdUsd(),
            'status' => $status,
            'usdBalances' => $usdBalances,
            'btcBalances' => $btcBalances
        ]);
    }

    /**
     * @Route("/order-pair", options={"expose"=true}, name="orderPair")
     * @param Request $request
     * @return Response
     */
    public function orderPairAction(Request $request)
    {
        /** @var OrderPairRepository $orderPairRepository */
        $orderPairRepository = $this->get('app.order_pair.repository');

        return $this->render('@App/dashboard/order-pair.html.twig', [
            'orderPairs' => $orderPairRepository->findAll()
        ]);
    }

    /**
     * @Route("/change-trade-parameters", options={"expose"=true}, name="changeTradeParameters")
     * @param Request $request
     * @return JsonResponse
     */
    public function changeTradeParametersAction(Request $request)
    {
        /** @var StatusRepository $statusRepository */
        $statusRepository = $this->get('app.status.repository');

        /** @var Status $status */
        $status = $statusRepository->findStatus();

        $thresholdUsd =         $request->get('thresholdUsd');
        $orderValueBtc =        $request->get('orderValueBtc');
        $addOrSubToOrderUsd =   $request->get('addOrSubToOrderUsd');

        if($thresholdUsd && is_int(intval($thresholdUsd))) {
            $status->setThresholdUsd(intval($thresholdUsd) <= 1 ? 1 : intval($thresholdUsd));
        }

        if($orderValueBtc && is_float(floatval($orderValueBtc))) {
            $status->setOrderValueBtc(floatval($orderValueBtc) <= 0.001 ? 0.001 : floatval($orderValueBtc));
        }

        if($addOrSubToOrderUsd && is_int(intval($addOrSubToOrderUsd))) {
            $status->setAddOrSubToOrderUsd(intval($addOrSubToOrderUsd) <= 0 ? 0 : intval($addOrSubToOrderUsd));
        }

        $statusRepository->save($status);

        return new JsonResponse(['thresholdUsd' => $status->getThresholdUsd(), 'orderValueBtc' => $status->getOrderValueBtc(), 'addOrSubToOrderUsd' => $status->getAddOrSubToOrderUsd()]);
    }

    /**
     * @Route("/place-order-pair", options={"expose"=true}, name="placeOrderPair")
     * @param Request $request
     * @return JsonResponse
     */
    public function placeOrderPairAction(Request $request)
    {
        /** @var StatusRepository $statusRepository */
        $statusRepository = $this->get('app.status.repository');

        /** @var DifferenceRepository $differenceRespository */
        $differenceRespository = $this->get('app.difference.repository');

        /** @var BalanceRepository $balanceRepository */
        $balanceRepository = $this->get('app.balance.repository');

        /** @var OrderPairRepository $orderPairRepository */
        $orderPairRepository  = $this->get('app.order_pair.repository');

        /** @var TradeService $tradeService */
        $tradeService = $this->get('app.trade.service');

        /** @var Status $status */
        $status = $statusRepository->findStatus();

        $differenceId = $request->get('differenceId');

        /** @var Difference $difference */
        $difference = $differenceRespository->findOneById($differenceId);

        if(!$difference){
            return new JsonResponse(['status' => 'error', 'message' => 'The difference you clicked on is out of date. Try again.']);
        }

        /** @var Balance $balanceUsd */
        $balanceBtc = $balanceRepository->findBalanceByExchange($difference->getExchangeSellName());

        /** @var Balance $balanceBtc */
        $balanceUsd = $balanceRepository->findBalanceByExchange($difference->getExchangeBuyName());

        if(!$balanceBtc || $balanceBtc->getBtc() < $status->getOrderValueBtc()){
            return new JsonResponse(['status' => 'warning', 'message' => 'Your BTC balance is not enough']);
        }

        if(!$balanceUsd || $balanceUsd->getUsd() < ($status->getOrderValueBtc() * ($difference->getAsk() + $status->getAddOrSubToOrderUsd()))){
            return new JsonResponse(['status' => 'warning', 'message' => 'Your USD balance is not enough']);
        }

        /** @var OrderPair[] $openOrderPairs */
        $openOrderPairs = $orderPairRepository->findOpenOrderPairs();

        foreach ($openOrderPairs as $openOrderPair) {
            if( $openOrderPair->getBuyOrderExchange() === $difference->getExchangeSellName() || $openOrderPair->getBuyOrderExchange() === $difference->getExchangeBuyName() ||
                $openOrderPair->getSellOrderExchange() === $difference->getExchangeSellName() || $openOrderPair->getSellOrderExchange() === $difference->getExchangeBuyName()){
                return new JsonResponse(['status' => 'warning', 'message' => 'There are orders in those exchanges. Close those orders before place a new one.']);
            }
        }

        $tradePairDTO = $tradeService->placeOrderPair($difference, $status);

        if(!$tradePairDTO){
            return new JsonResponse(['status' => 'error', 'message' => 'No orders placed.']);
        }

        if(!$tradePairDTO->getSellOrderId()){
            return new JsonResponse(['status' => 'warning', 'message' => 'No sell order placed.']);
        }

        return new JsonResponse(['status' => 'ok', 'message' => 'Buy and Sell orders placed successfully']);
    }

    /**
     * @Route("/balances-from-exchanges", options={"expose"=true}, name="balancesFromExchanges")
     * @param Request $request
     * @return Response
     */
    public function balancesFromRepositoryAction(Request $request)
    {
        /** @var BalanceService $balanceService */
        $balanceService = $this->get('app.balance.service');

        $balanceService->getBalancesFromExchanges();

        return new Response('ok');
    }

    /**
     * @Route("/balance-date", options={"expose"=true}, name="balanceDate")
     * @param Request $request
     * @return Response
     */
    public function balanceDateAction(Request $request)
    {
        /** @var BalanceRepository $balanceRepository */
        $balanceRepository = $this->get('app.balance.repository');

        /** @var Balance[] $balances */
        $balances = $balanceRepository->findAll();

        if($balances && count($balances) > 0){
            return new Response($balances[0]->getCreated()->format('d/m/y H:i:s'));
        }

        return new Response('');
    }

    /**
     * @Route("/ticker-date", options={"expose"=true}, name="tickerDate")
     * @param Request $request
     * @return Response
     */
    public function tickerDateAction(Request $request)
    {
        /** @var TickerRepository $tickerRepository */
        $tickerRepository = $this->get('app.ticker.repository');

        /** @var Ticker[] $tickers */
        $tickers = $tickerRepository->findAll();

        if($tickers && count($tickers) > 0){
            return new Response($tickers[0]->getCreated()->format('d/m/y H:i:s'));
        }

        return new Response('');
    }

    /**
     * @Route("/difference-date", options={"expose"=true}, name="differenceDate")
     * @param Request $request
     * @return Response
     */
    public function differenceDateAction(Request $request)
    {
        /** @var DifferenceRepository $differenceRepository */
        $differenceRepository = $this->get('app.difference.repository');

        /** @var Difference[] $differences */
        $differences = $differenceRepository->findAll();

        if($differences && count($differences) > 0){
            return new Response($differences[0]->getCreated()->format('d/m/y H:i:s'));
        }

        return new Response('');
    }

    /**
     * @Route("/order-pair-date", options={"expose"=true}, name="orderPairDate")
     * @param Request $request
     * @return Response
     */
    public function orderPairDateAction(Request $request)
    {
        /** @var StatusRepository $statusRepository */
        $statusRepository = $this->get('app.status.repository');

        /** @var Status $status */
        $status = $statusRepository->findStatus();

        if($status && $status->getOrderPairLastUpdateDate()){
            return new Response($status->getOrderPairLastUpdateDate()->format('d/m/y H:i:s'));
        }

        return new Response('');
    }
}
