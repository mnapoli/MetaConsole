<?php

namespace MetaConsole;

use MetaModel\MetaModel;
use NumberTwo\Filter\DoctrineCollectionFilter;
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
        /** @var DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');
        /** @var FormatterHelper $formatter */
        $formatter = $this->getHelperSet()->get('formatter');

        $dumpFilters = [new DoctrineCollectionFilter()];

        while (true) {
            try {
                $expression = $dialog->ask(
                    $output,
                    '>>> '
                );
            } catch (\RuntimeException $e) {
                // End of file
                return 0;
            }

            if ($expression == '') {
                continue;
            }

            // Exit
            if ($expression == 'quit' || $expression == 'exit') {
                return 0;
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
}
