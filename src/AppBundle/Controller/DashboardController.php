<?php

namespace AppBundle\Controller;

use AppBundle\Repository\StatusRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        $status = $statusRepository->findStatus();

        return $this->render('@App/dashboard/index.html.twig', [
            'status' => $status
        ]);
    }
}
