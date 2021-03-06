<?php
/*
 * This file is part of the MisatoBootstrapDuallistBundle.
 *
 * (c) MisatoTremor <tlot@blackblizzard.org>
 */
namespace Misato\BootstrapDuallistboxBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Mopa\Bridge\Composer\Adapter\ComposerAdapter;
use Mopa\Bridge\Composer\Util\ComposerPathFinder;

/**
 * Command to check and create bootstrap-duallistbox symlink into MisatoBootstrapDuallistboxBundle
 *
 * @author phiamo <phiamo@googlemail.com>
 * @author MisatoTremor <tlot@blackblizzard.org>
 */
class DuallistboxSymlinkCommand extends ContainerAwareCommand
{
    public static $misatoBootstrapDuallistboxBundleName = "misato/bootstrap-duallistbox-bundle";
    public static $duallistboxName = "istvan-ujjmeszaros/bootstrap-duallistbox";
    public static $sourceSuffix = 'dist';
    public static $targetSuffix = 'Resources/public';
    public static $pathName = 'BootstrapDuallistbox';

    /**
     * Checks symlink's existence.
     *
     * @param string  $symlinkTarget The Target
     * @param string  $symlinkName   The Name
     * @param boolean $forceSymlink  Force to be a link or throw exception
     *
     * @return boolean
     *
     * @throws \Exception
     */
    public static function checkSymlink($symlinkTarget, $symlinkName, $forceSymlink = false)
    {
        if ($forceSymlink && file_exists($symlinkName) && !is_link($symlinkName)) {
            if ("link" != filetype($symlinkName)) {
                throw new \Exception($symlinkName . " exists and is no link!");
            }
        } elseif (is_link($symlinkName)) {
            $linkTarget = readlink($symlinkName);
            if ($linkTarget != $symlinkTarget) {
                if (!$forceSymlink) {
                    throw new \Exception(sprintf('Symlink "%s" points to "%s" instead of "%s"', $symlinkName, $linkTarget, $symlinkTarget));
                }
                unlink($symlinkName);

                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Create the symlink.
     *
     * @param string $symlinkTarget The Target
     * @param string $symlinkName   The Name
     *
     * @throws \Exception
     */
    public static function createSymlink($symlinkTarget, $symlinkName)
    {
        if (false === @symlink($symlinkTarget, $symlinkName)) {
            throw new \Exception("An error occurred while creating symlink" . $symlinkName);
        }
        if (false === $target = readlink($symlinkName)) {
            throw new \Exception("Symlink $symlinkName points to target $target");
        }
    }

    /**
     * Create the directory mirror.
     *
     * @param string $symlinkTarget The Target
     * @param string $symlinkName   The Name
     *
     * @throws \Exception
     */
    public static function createMirror($symlinkTarget, $symlinkName)
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir($symlinkName);
        $filesystem->mirror(
            realpath($symlinkTarget . DIRECTORY_SEPARATOR ),
            $symlinkName,
            null,
            array('copy_on_windows' => true, 'delete' => true, 'override' => true)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription("Check and if possible install symlink to " . static::$targetSuffix)
            ->addArgument('pathTo' . static::$pathName, InputArgument::OPTIONAL, 'Where is istvan-ujjmeszaros/bootstrap-duallistbox located?')
            ->addArgument('pathToMisatoBootstrapDuallistboxBundle', InputArgument::OPTIONAL, 'Where is MisatoBootstrapDuallistboxBundle located?')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force rewrite of existing symlink if possible!')
            ->addOption('manual', 'm', InputOption::VALUE_NONE, 'If set please specify pathTo' . static::$pathName . ', and pathToMisatoBootstrapDuallistboxBundle')
            ->addOption('no-symlink', null, InputOption::VALUE_NONE, 'Use hard copy/mirroring instead of symlink. This is required for Windows without administrator privileges.')
            ->setName('misato:bootstrap-duallistbox:symlink')
            ->setHelp(<<<EOT
The <info>misato:bootstrap-duallistbox:symlink</info> command helps you checking and symlinking/mirroring the istvan-ujjmeszaros/bootstrap-duallistbox library.

By default, the command uses composer to retrieve the paths of MisatoBootstrapDuallistboxBundle and istvan-ujjmeszaros/bootstrap-duallistbox in your vendors.

If you want to control the paths yourself specify the paths manually:

php app/console misato:bootstrap-duallistbox:symlink <comment>--manual</comment> <pathToBootstrapDuallistbox> <pathToMisatoBootstrapDuallistboxBundle>

Defaults if installed by composer would be :

pathToBootstrapDuallistbox:    ../../../../../../../../vendor/istvan-ujjmeszaros/bootstrap-duallistbox/dist
pathToMisatoBootstrapDuallistboxBundle: vendor/misato/bootstrap-duallistbox-bundle/Misato/BootstrapDuallistboxBundle/Resources/public/bootstrap-duallistbox

EOT
            );
    }

    /**
     * Get Package involved
     *
     * @return string Name of bootstrap-duallistbox package
     */
    protected function getDuallistboxName()
    {
        return self::$duallistboxName;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        if ($input->getOption('manual')) {
			list($symlinkTarget, $symlinkName) = $this->getBootstrapduallistboxPathsFromUser();
            if ($symlinkTarget === null) {
                return;
            }
        } elseif (false !== $composer = ComposerAdapter::getComposer($input, $output)) {
            $cmanager = new ComposerPathFinder($composer);
            $options = array(
                    'targetSuffix' => DIRECTORY_SEPARATOR . static::$targetSuffix,
                    'sourcePrefix' => '..' . DIRECTORY_SEPARATOR,
            );
            list($symlinkTarget, $symlinkName) = $cmanager->getSymlinkFromComposer(
                self::$misatoBootstrapDuallistboxBundleName,
                $this->getDuallistboxName(),
                $options
            );
            $symlinkTarget .= !empty(static::$sourceSuffix) ? DIRECTORY_SEPARATOR . static::$sourceSuffix : '';
        } else {
            $this->output->writeln("<error>Could not find composer and manual option not specified!</error>");

            return;
        }

        // Automatically detect if on Win XP where symlink will allways fail
        if ($input->getOption('no-symlink') || PHP_OS == "WINNT") {
            $this->output->write("Checking destination");

            if (true === self::checkSymlink($symlinkTarget, $symlinkName)) {
                $this->output->writeln(" ... <comment>symlink already exists</comment>");
            } else {
                $this->output->writeln(" ... <comment>not existing</comment>");
                $this->output->writeln("Mirroring from: " . $symlinkName);
                $this->output->write("for target: " . $symlinkTarget);
                self::createMirror($symlinkTarget, $symlinkName);
            }
        } else {
            $this->output->write("Checking symlink");
            if (false === self::checkSymlink($symlinkTarget, $symlinkName, true)) {
                $this->output->writeln(" ... <comment>not existing</comment>");
                $this->output->writeln("Creating symlink: " . $symlinkName);
                $this->output->write("for target: " . $symlinkTarget);
                self::createSymlink($symlinkTarget, $symlinkName);
            }
        }

        $this->output->writeln(" ... <info>OK</info>");
    }

    protected function getBootstrapduallistboxPathsFromUser()
    {
        $symlinkTarget = $this->input->getArgument('pathTo' . static::$pathName);
        $symlinkName = $this->input->getArgument('pathToMisatoBootstrapDuallistboxBundle');

        if (empty($symlinkName)) {
            throw new \Exception("pathToMisatoBootstrapDuallistboxBundle not specified");
        }

        if (!is_dir(dirname($symlinkName))) {
            throw new \Exception("pathToMisatoBootstrapDuallistboxBundle: " . dirname($symlinkName) . " does not exist");
        }

        if (empty($symlinkTarget)) {
            throw new \Exception(static::$pathName . " not specified");
        }

        if (substr($symlinkTarget, 0, 1) == "/") {
            $this->output->writeln("<comment>Try avoiding absolute paths, for portability!</comment>");
            if (!is_dir($symlinkTarget)) {
                throw new \Exception("Target path " . $symlinkTarget . "is not a directory!");
            }
        } else {
            $resolve = $symlinkName . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . $symlinkTarget;
            $symlinkTarget = self::getAbsolutePath($resolve);
        }

        if (!is_dir($symlinkTarget)) {
            throw new \Exception(static::$pathName . " would resolve to: " . $symlinkTarget . "\n and this is not reachable from \npathToMisatoBootstrapDuallistboxBundle: " . dirname($symlinkName));
        }

        $dialog = $this->getHelperSet()->get('dialog');
        $text = <<<EOF
Creating the symlink: $symlinkName
Pointing to: $symlinkTarget
EOF
;
        $this->output->writeln(array(
            '',
            $this->getHelperSet()->get('formatter')->formatBlock($text, 'bg=blue;fg=white', true),
            '',
        ));

        if ($this->input->isInteractive() && !$dialog->askConfirmation($this->output, '<question>Should this link be created? (y/n)</question>', false)) {
            return;
        }

        return array($symlinkTarget, $symlinkName);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected static function getAbsolutePath($path)
    {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();

        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            }

            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }
}
