<?php

namespace Assely\Installer\Console;

use Assely\Installer\Console\FetcherCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class FetchFrameworkCommand extends FetcherCommand
{
    /**
     * Package name.
     *
     * @var string
     */
    protected $package = 'framework';

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('fetch:framework')
            ->setDescription('Downloads lastest release of Assely framework.');
    }

    /**
     * Execute the command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Downloading...</comment>');

        $directory = getcwd() . '/assely-framework';

        $this->assertDoesNotExist($directory);

        $this->download($tempName = $this->makeFilename())
            ->extract($tempName, $directory)
            ->cleanUp($tempName);

        $output->writeln('<comment>Resolving dependences...</comment>');

        $composer = $this->findComposer();

        $commands = [
            $composer . ' install -o --no-dev',
        ];

        if ($input->getOption('no-ansi')) {
            $commands = array_map(function ($value) {
                return $value . ' --no-ansi';
            }, $commands);
        }

        $process = new Process(implode(' && ', $commands), $directory, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            $process->setTty(true);
        }

        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });

        $output->writeln('<info>Framework installed!</info>');
    }
}
