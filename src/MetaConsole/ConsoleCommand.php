<?php

namespace MetaConsole;

use MetaConsole\SymfonyConsoleHelper\ShellHelper;
use MetaModel\MetaModel;
use NumberTwo\Filter\DoctrineCollectionFilter;
use NumberTwo\Filter\DoctrineProxyFilter;
use NumberTwo\NumberTwo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run command.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ConsoleCommand extends Command
{
    /**
     * @var MetaModel
     */
    private $metaModel;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('console')
            ->setDescription('Interactive console');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getHelperSet()->set(new ShellHelper());
        /** @var ShellHelper $shell */
        $shell = $this->getHelperSet()->get('shell');
        /** @var FormatterHelper $formatter */
        $formatter = $this->getHelperSet()->get('formatter');

        $dumpFilters = [new DoctrineCollectionFilter(), new DoctrineProxyFilter()];

        $output->writeln("<info>Welcome in the MetaConsole, type ? for help</info>");

        $commandHistory = [];

        $searchHistory = function($command, $start = null, $backward = true) use (&$commandHistory) {
            if ($backward) {
                if ($start === null || $start < 0 || $start >= count($commandHistory)) {
                    $start = count($commandHistory) - 1;
                } else {
                    $start = $start - 1;
                }
                for ($i = $start; $i >= 0; $i--) {
                    $oldCommand = $commandHistory[$i];
                    if ($command == '' || strpos($oldCommand, $command) === 0) {
                        return [
                            'position' => $i,
                            'command'  => $oldCommand
                        ];
                    }
                }
                return null;
            } else {
                if ($start === null || $start < 0 || $start >= count($commandHistory)) {
                    return null;
                } else {
                    $start = $start + 1;
                }
                for ($i = $start; $i < count($commandHistory); $i++) {
                    $oldCommand = $commandHistory[$i];
                    if ($command == '' || strpos($oldCommand, $command) === 0) {
                        return [
                            'position' => $i,
                            'command'  => $oldCommand
                        ];
                    }
                }
                return null;
            }
        };

        while (true) {
            $output->writeln("");
            try {
                $expression = $shell->prompt(
                    $output,
                    '>>> ',
                    null,
                    $searchHistory
                );
                // Add to history
                $commandHistory[] = $expression;
            } catch (\RuntimeException $e) {
                // End of file
                return 0;
            }

            // Exit
            if ($expression === 0 || $expression == 'quit' || $expression == 'exit') {
                return 0;
            }

            if ($expression == '') {
                continue;
            }

            // Help
            if ($expression == '?' || $expression == 'help') {
                $this->showHelp($output, $formatter);
                continue;
            }

            try {
                $result = $this->metaModel->run($expression);

                echo NumberTwo::dump($result, 2, $dumpFilters) . PHP_EOL;
            } catch (\Exception $e) {
                $block = [
                    get_class($e),
                    $e->getMessage(),
                ];
                $output->writeln($formatter->formatBlock($block, 'error'));
            }
        }

        return 0;
    }

    /**
     * @param MetaModel $metaModel
     */
    public function setMetaModel($metaModel)
    {
        $this->metaModel = $metaModel;
    }

    private function showHelp(OutputInterface $output, FormatterHelper $formatter)
    {
        $help = <<<HELP
To exit, type 'exit'.

# Selectors
My\Entity(1)
My\Entity being the class name of the entity, 1 being the ID of the entity to load.

# Property access
Article(1).title
Article(1).author.name
HELP;
        $output->writeln($formatter->formatBlock($help, 'info'));
    }
}
