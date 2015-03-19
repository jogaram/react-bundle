<?php

namespace Jogaram\ReactPHPBundle\Command;

use Jogaram\ReactPHPBundle\Reactor\ReactKernel;
use React\EventLoop\Factory;
use React\Http\Request;
use React\Http\Response;
use React\Socket\Server as SocketServer;
use React\Http\Server as HttpServer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServerStopCommand extends ContainerAwareCommand{

    protected function configure()
    {
        $this
            ->setName('react:server:stop')
            ->setDescription('Run server based on ReactPHP')
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Port of server that will be stopped.',
                1337
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output){
        $port = $input->getOption('port');
        $lock_file = sys_get_temp_dir() . '/react-' . $port . '.pid';

        if (!file_exists($lock_file)) {
            $output->writeln(sprintf('<error>Server with port %s is not running.</error>', $port));
            return 1;
        }

        $server_pid = file_get_contents($lock_file);
        posix_kill($server_pid, SIGTERM);
        unlink($lock_file);

        $output->writeln('<info>Server stopped.</info>');
    }
}