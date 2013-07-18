<?php

namespace MetaConsole\SymfonyConsoleHelper;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interactive shell helper
 */
class ShellHelper extends Helper
{
    private $inputStream;
    private static $stty;

    /**
     * Asks a question to the user.
     *
     * @param OutputInterface $output       An Output instance
     * @param string          $prefix       The shell prefix
     * @param callable        $autocomplete Callback to autocomplete
     *
     * @throws \RuntimeException If there is no data to read in the input stream
     * @return string The user answer
     *
     */
    public function prompt(OutputInterface $output, $prefix, callable $autocomplete = null)
    {
        $output->write($prefix);

        $inputStream = $this->inputStream ?: STDIN;

        if (!$this->hasSttyAvailable()) {
            $command = fgets($inputStream, 4096);
            if (false === $command) {
                throw new \RuntimeException('Aborted');
            }
            $command = trim($command);
        } else {
            $command = '';

            $i = 0;

            $sttyMode = shell_exec('stty -g');

            // Disable icanon (so we can fread each keypress) and echo (we'll do echoing here instead)
            shell_exec('stty -icanon -echo');

            // Read a keypress
            while (!feof($inputStream)) {
                $c = fread($inputStream, 1);

                // Backspace Character
                if ("\177" === $c) {
                    if (0 !== $i) {
                        $i--;
                        // Move cursor backwards
                        $output->write("\033[1D");
                        // Pop the last character off the end of our string
                        $command = substr($command, 0, $i);
                    }
                } elseif ("\033" === $c) { // Did we read an escape sequence?
                    $c .= fread($inputStream, 2);

                    // A = Up Arrow. B = Down Arrow
                    if ('A' === $c[2] || 'B' === $c[2]) {
                        if ('A' === $c[2]) {
                            // TODO
                        }
                        continue;
                    }
                } elseif (ord($c) < 32) {
                    // Return
                    if ("\n" === $c) {
                        $output->write($c);
                        break;
                    }
                    // Tab
                    if ("\t" === $c && $autocomplete) {
                        // Autocomplete
                        $autocompletedCommand = $autocomplete($command);
                        $command .= substr($autocompletedCommand, $i);
                        // Echo out remaining chars for current match
                        $output->write(substr($command, $i));
                        $i = strlen($command);
                    }
                    continue;
                } else {
                    $output->write($c);
                    $command .= $c;
                    $i++;
                }

                // Erase characters from cursor to end of line
                $output->write("\033[K");
            }

            // Reset stty so it behaves normally again
            shell_exec(sprintf('stty %s', $sttyMode));
        }

        return strlen($command) > 0 ? $command : null;
    }

    /**
     * Sets the input stream to read from when interacting with the user.
     *
     * This is mainly useful for testing purpose.
     *
     * @param resource $stream The input stream
     */
    public function setInputStream($stream)
    {
        $this->inputStream = $stream;
    }

    /**
     * Returns the helper's input stream
     *
     * @return string
     */
    public function getInputStream()
    {
        return $this->inputStream;
    }

    private function hasSttyAvailable()
    {
        if (null !== self::$stty) {
            return self::$stty;
        }

        exec('stty 2>&1', $output, $exitcode);

        return self::$stty = $exitcode === 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'shell';
    }
}
