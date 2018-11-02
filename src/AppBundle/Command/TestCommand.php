<?php

namespace AppBundle\Command;


use AppBundle\DataTransferObject\TickerDTO;
use AppBundle\Entity\Ticker;
use AppBundle\Repository\TickerRepository;
use AppBundle\Service\TickerService;
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

    /** @var TickerRepository $tickerRepository */
    private $tickerRepository;

    /** @var int $interValSeconds */
    private $interValSeconds;

    protected function configure()
    {
        $this->commandName = 'bot:ticker';

        $this
            ->setName($this->commandName)
            ->setDescription('Gets prices from every enabled exchange and persists them on the DB')
            ->setHelp('');
    }

    private function configureServices()
    {
        /** @var ContainerInterface $container */
        $container = $this->getContainer();

        $this->tickerService    = $container->get('app.ticker.service');
        $this->tickerRepository = $container->get('app.ticker.repository');
        $this->interValSeconds  = $container->getParameter('interval_seconds');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureServices();

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