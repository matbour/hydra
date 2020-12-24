<?php

namespace Windy\Hydra\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Windy\Hydra\Core\Bench;
use Windy\Hydra\Core\Config;
use Windy\Hydra\Core\Installer;

class InstallCommand extends Command
{
    protected static $defaultName = 'install';

    protected function configure()
    {
        $this->setDescription('Install Hydra benches');
        $this->setHelp('ss');
        $this->addOption('only', null, InputOption::VALUE_OPTIONAL, 'Install only a single bench');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config  = Config::getInstance();
        $benches = [];

        if (!$input->getOption('only')) {
            foreach ($config->get('benches') as $name => $bench) {
                if ($name === 'default') continue;

                $benches[$name] = new Bench($name, $bench['framework'], $bench['constraint']);
            }
        } else {
            $benches[] = Bench::fromName($input->getOption('only'));
        }

        foreach ($benches as $name => $bench) {
            (new Installer($bench))->install();
        }

        $bootstrapFile = "{$config->get('hydra$path')}/{$config->get('directory')}/bootstrap.php";

        copy(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php', $bootstrapFile);

        return Command::SUCCESS;
    }
}
