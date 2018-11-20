<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Status;
use AppBundle\Repository\BalanceRepository;
use AppBundle\Repository\OrderPairRepository;
use AppBundle\Repository\StatusRepository;
use AppBundle\Repository\TickerRepository;
use AppBundle\Service\BalanceService;
use AppBundle\Service\DifferenceService;
use AppBundle\Service\TickerService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        /** @var BalanceRepository $balanceRepository */
        $balanceRepository = $this->get('app.balance.repository');

        /** @var TickerRepository $tickerRepository */
        $tickerRepository = $this->get('app.ticker.repository');

        /** @var Status $status */
        $status = $statusRepository->findStatus();

        if($status->isRunning() === true){
            return new JsonResponse(['code' => 200, 'running' => false]);
        }

        $tickerRepository->deleteAll();
        $balanceRepository->deleteAll();

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
     */
    public function balanceAction(Request $request)
    {
        /** @var BalanceService $balanceService */
        $balanceService = $this->get('app.balance.service');

        return $this->render('@App/dashboard/balance.html.twig', [
            'balances' => $balanceService->getFormattedBalances()
        ]);
    }

    /**
     * @Route("/ticker", options={"expose"=true}, name="ticker")
     * @param Request $request
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
     */
    public function differenceAction(Request $request)
    {
        /** @var DifferenceService $differenceService */
        $differenceService = $this->get('app.difference.service');

        /** @var StatusRepository $statusRepository */
        $statusRepository = $this->get('app.status.repository');

        /** @var Status $status */
        $status = $statusRepository->findStatus();

        return $this->render('@App/dashboard/difference.html.twig', [
            'differences' => $differenceService->getFormattedDifferences(),
            'thresholdUsd' => $status->getThresholdUsd()
        ]);
    }

    /**
     * @Route("/order-pair", options={"expose"=true}, name="orderPair")
     * @param Request $request
     */
    public function orderPairAction(Request $request)
    {
        /** @var OrderPairRepository $orderPairRepository */
        $orderPairRepository = $this->get('app.order_pair.repository');

        /** @var StatusRepository $statusRepository */
        $statusRepository = $this->get('app.status.repository');

        /** @var Status $status */
        $status = $statusRepository->findStatus();

        return $this->render('@App/dashboard/order-pair.html.twig', [
            'orderPairs' => $orderPairRepository->findAll()
        ]);
    }

    /**
     * @Route("/change-trade-parameters", options={"expose"=true}, name="changeTradeParameters")
     * @param Request $request
     * @return JsonResponse
     */
    public function changeValuesAction(Request $request)
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
}
