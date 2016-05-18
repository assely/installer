<?php

namespace Assely\Installer\Console;

use Assely\Installer\Console\FetcherCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewCommand extends FetcherCommand
{
    /**
     * Package name.
     *
     * @var string
     */
    protected $package = 'assely';

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('new')
            ->setDescription('Create a new Assely application.')
            ->addArgument('name', InputArgument::REQUIRED);
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
        $output->writeln('<comment>Crafting application...</comment>');

        $directory = getcwd() . '/' . $input->getArgument('name');

        $this->assertDoesNotExist($directory);

        $this->download($tempName = $this->makeFilename())
            ->extract($tempName, $directory)
            ->cleanUp($tempName);

        $output->writeln('<info>Application ready!</info>');
    }
}
