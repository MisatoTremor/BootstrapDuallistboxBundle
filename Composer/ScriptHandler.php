<?php
/*
 * This file is part of the MisatoBootstrapDuallistBundle.
 *
 * (c) MisatoTremor <tlot@blackblizzard.org>
 */
namespace Misato\BootstrapDuallistboxBundle\Composer;

use Composer\Script\Event;
use Mopa\Bridge\Composer\Util\ComposerPathFinder;
use Misato\BootstrapDuallistboxBundle\Command\DuallistboxSymlinkCommand;

/**
 * Script for Composer, create symlink to bootstrap-duallistbox lib into the BootstrapDuallistboxBundle.
 */
class ScriptHandler
{
    public static function postInstallSymlinkBootstrapDuallistbox(Event $event)
    {
        $IO = $event->getIO();
        $composer = $event->getComposer();
        $cmanager = new ComposerPathFinder($composer);
        $options = array(
            'targetSuffix' => self::getTargetSuffix(),
            'sourcePrefix' => self::getSourcePrefix()
        );
        list($symlinkTarget, $symlinkName) = $cmanager->getSymlinkFromComposer(
            DuallistboxSymlinkCommand::$misatoBootstrapDuallistboxBundleName,
            DuallistboxSymlinkCommand::$duallistboxName,
            $options
        );
        $symlinkTarget .= !empty(DuallistboxSymlinkCommand::$sourceSuffix) ? DIRECTORY_SEPARATOR . DuallistboxSymlinkCommand::$sourceSuffix : '';

        $IO->write("Checking Symlink", FALSE);
        if (false === DuallistboxSymlinkCommand::checkSymlink($symlinkTarget, $symlinkName, true)) {
            $IO->write("Creating Symlink: " . $symlinkName, FALSE);
            DuallistboxSymlinkCommand::createSymlink($symlinkTarget, $symlinkName);
        }
        $IO->write(" ... <info>OK</info>");
    }

    public static function postInstallMirrorBootstrapDuallistbox(Event $event)
    {
        $IO = $event->getIO();
        $composer = $event->getComposer();
        $cmanager = new ComposerPathFinder($composer);
        $options = array(
            'targetSuffix' =>  self::getTargetSuffix(),
            'sourcePrefix' => self::getSourcePrefix()
        );
        list($symlinkTarget, $symlinkName) = $cmanager->getSymlinkFromComposer(
            DuallistboxSymlinkCommand::$misatoBootstrapDuallistboxBundleName,
            DuallistboxSymlinkCommand::$duallistboxName,
            $options
        );
        $symlinkTarget .= !empty(DuallistboxSymlinkCommand::$sourceSuffix) ? DIRECTORY_SEPARATOR . DuallistboxSymlinkCommand::$sourceSuffix : '';

        $IO->write("Checking Mirror", FALSE);
        if (false === DuallistboxSymlinkCommand::checkSymlink($symlinkTarget, $symlinkName)) {
            $IO->write("Creating Mirror: " . $symlinkName, FALSE);
            DuallistboxSymlinkCommand::createMirror($symlinkTarget, $symlinkName);
        }
        $IO->write(" ... <info>OK</info>");
    }

    protected static function getTargetSuffix($end = "")
    {
        return DIRECTORY_SEPARATOR . "Resources" . DIRECTORY_SEPARATOR . "public" . $end;
    }

    protected static function getSourcePrefix($end = "")
    {
        return '..' . DIRECTORY_SEPARATOR . $end;
    }
}
