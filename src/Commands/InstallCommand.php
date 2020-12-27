<?php

declare(strict_types=1);

namespace Windy\Hydra\Commands;

use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Windy\Hydra\Core\Bench;
use Windy\Hydra\Core\Config;
use Windy\Hydra\Core\Installer;
use Windy\Hydra\Utils\FileUtils;
use function array_map;
use function copy;
use function dirname;
use function implode;
use function strpos;
use function usleep;

class InstallCommand extends Command
{
    protected static $defaultName = 'install';

    protected function configure(): void
    {
        $this->setDescription('Install Hydra benches');
        $this->addOption('only', null, InputOption::VALUE_OPTIONAL, 'Install only a single bench');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new LogicException(
                'This command accepts only an instance of "ConsoleOutputInterface".'
            );
        }

        // Retrieve benches
        $benches = [];

        if ($input->getOption('only')) {
            $benches[] = Bench::fromName($input->getOption('only'));
        } else {
            foreach (Config::get('benches') as $name => $bench) {
                if ($name === 'default') {
                    continue;
                }

                $benches[$name] = new Bench($name, $bench['framework'], $bench['constraint']);
            }
        }

        // Install all the benches
        $installers = array_map(static function ($bench) use ($input) {
            return new Installer($bench, $input->getOption('verbose'));
        }, $benches);

        $sections = $output->section();

        // Tick the installers
        do {
            $continue = false;
            $output   = [];

            foreach ($installers as $i => $installer) {
                $out      = $installer->tick();
                $output[] = $out;

                if (strpos($out, 'ready') === false) {
                    $continue = true;
                }
            }

            $sections->overwrite(implode("\n", $output));
            usleep(250 * 1000);
        } while ($continue);

        // Copy the bootstrap
        copy(
            FileUtils::join(dirname(__DIR__), 'bootstrap.php'),
            FileUtils::join(Config::get('sandbox'), 'bootstrap.php')
        );

        return 0;
    }
}
