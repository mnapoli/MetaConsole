<?php
/**
 * @author matthieu.napoli
 */

namespace MetaConsole;

use MetaModel\MetaModel;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Extending the Symfony Console Application to allow having one command only
 *
 * @see http://symfony.com/doc/current/components/console/single_command_tool.html
 */
class Application extends \Symfony\Component\Console\Application
{
    /**
     * @var MetaModel
     */
    private $metaModel;

    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN', MetaModel $metaModel = null)
    {
        $this->metaModel = $metaModel ?: new MetaModel();

        parent::__construct($name, $version);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'console';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();

        $consoleCommand = new ConsoleCommand();
        $consoleCommand->setMetaModel($this->metaModel);

        $defaultCommands[] = $consoleCommand;

        return $defaultCommands;
    }

    /**
     * Overridden so that the application doesn't expect the command
     * name to be the first argument
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
