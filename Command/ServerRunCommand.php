<?php

namespace Jogaram\ReactPHPBundle\Command;

use Jogaram\ReactPHPBundle\Reactor\Server;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServerRunCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('react:server:run')
            ->setDescription('Run server based on ReactPHP')
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Port where server will be listening.',
                1337
            )
            ->addOption(
                'debug',
                null,
                InputOption::VALUE_NONE,
                'Enable debug mode'
            )
            ->addOption(
                'standalone',
                null,
                InputOption::VALUE_NONE,
                'Enable standalone mode. It means webserver isn\'t needed. Static file will be served by ReactPHP.'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $server = new Server($this->getContainer()->getParameter('kernel.root_dir'), $input->getOption('port'));

        $output->writeln(sprintf('<info>Server running on port %s.</info>', $input->getOption('port')));

        $server
            ->setDebug($input->getOption('debug'))
            ->setStandalone($input->getOption('standalone'))
            ->build()
            ->run()
        ;

        $output->writeln('<info>Server stopped.</info>');
    }
}