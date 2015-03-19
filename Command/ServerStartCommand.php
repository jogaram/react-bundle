<?php

namespace Jogaram\ReactPHPBundle\Command;

use Jogaram\ReactPHPBundle\Reactor\Server;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServerStartCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('react:server:start')
            ->setDescription('Start background server based on ReactPHP')
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
        if (!extension_loaded('pcntl')) {
            $output->writeln('<error>PCNTL PHP extension is not installed or loaded, please enable it before launching server.</error>');
            return 1;
        }

        $output->writeln(sprintf('<info>Server running on port %s.</info>', $input->getOption('port')));

        $port = $input->getOption('port');
        $pid = pcntl_fork();
        if ($pid > 0) {
            $lock_file = sys_get_temp_dir() . '/react-' . $port . '.pid';
            file_put_contents($lock_file, $pid);
            return 0;

        } elseif ($pid < 0) {
            $output->writeln('<error>Child process could not be started. Server is not running.</error>');
            return 1;
        }

        $server = new Server($this->getContainer()->getParameter('kernel.root_dir'), $port);
        $server
            ->setDebug($input->getOption('debug'))
            ->setStandalone($input->getOption('standalone'))
            ->build()
            ->run()
        ;
    }
}