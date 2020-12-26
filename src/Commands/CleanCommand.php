<?php

declare(strict_types=1);

namespace Windy\Hydra\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Windy\Hydra\Core\Config;
use Windy\Hydra\Utils\FileUtils;

class CleanCommand extends Command
{
    /** @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint */
    protected static $defaultName = 'clean';

    protected function configure(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_NONE,
            'Force the deletion of the sandbox without asking for any confirmation.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $directory = Config::get('sandbox');

        if (!$input->getOption('force')) {
            $helper   = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                <<<QUESTION
You are going to delete all the Hydra benches located in $directory.
Are you sure to continue? [y/N] 
QUESTION
                , false);

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('Aborting');
                return 0;
            }
        }

        $output->writeln("Deleting $directory");
        FileUtils::rrmdir($directory);

        return 0;
    }
}
