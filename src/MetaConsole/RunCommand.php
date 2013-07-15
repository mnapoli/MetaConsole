<?php
/**
 * @author matthieu.napoli
 */

namespace MetaConsole;

use MetaModel\MetaModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Deploy command
 */
class RunCommand extends Command
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
            ->setName('run')
            ->setDescription('Run a MetaModel expression')
            ->addArgument(
                'expression',
                InputArgument::REQUIRED,
                "The MetaModel expression to run."
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->metaModel->run($input->getArgument('expression'));

        var_dump($result);

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
