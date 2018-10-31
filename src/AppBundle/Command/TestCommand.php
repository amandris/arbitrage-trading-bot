<?php

namespace AppBundle\Command;


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

    protected function configure()
    {
        $this->commandName = 'test:ticker';

        $this
            ->setName($this->commandName)
            ->setDescription('Test')
            ->setHelp('');
    }

    private function configureServices()
    {
        /** @var ContainerInterface $container */
        $container = $this->getContainer();

        $this->tickerService = $container->get('app.ticker.service');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureServices();

        var_dump($this->tickerService->getTickers());
    }
}