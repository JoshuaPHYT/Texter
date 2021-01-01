<?php

/**
 * // English
 *
 * Texter, the display FloatingTextPerticle plugin for PocketMine-MP
 * Copyright (c) 2019-2020 yuko fuyutsuki < https://github.com/fuyutsuki >
 *
 * This software is distributed under "NCSA license".
 * You should have received a copy of the NCSA license
 * along with this program.  If not, see
 * < https://opensource.org/licenses/NCSA >.
 *
 * ---------------------------------------------------------------------
 * // 日本語
 *
 * TexterはPocketMine-MP向けのFloatingTextPerticleを表示するプラグインです
 * Copyright (c) 2019-2020 yuko fuyutsuki < https://github.com/fuyutsuki >
 *
 * このソフトウェアは"NCSAライセンス"下で配布されています。
 * あなたはこのプログラムと共にNCSAライセンスのコピーを受け取ったはずです。
 * 受け取っていない場合、下記のURLからご覧ください。
 * < https://opensource.org/licenses/NCSA >
 */

declare(strict_types = 1);

namespace tokyo\pmmp\Texter;

use Exception;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\VersionString;
use tokyo\pmmp\Texter\command\TxtCommand;
use tokyo\pmmp\Texter\data\ConfigData;
use tokyo\pmmp\Texter\data\FloatingTextData;
use tokyo\pmmp\Texter\data\UnremovableFloatingTextData;
use tokyo\pmmp\Texter\i18n\Lang;
use tokyo\pmmp\Texter\task\CheckUpdateTask;
use tokyo\pmmp\Texter\task\PrepareTextsTask;
use tokyo\pmmp\Texter\util\DependenciesNamespace;
use function class_exists;
use function trait_exists;

/**
 * Class Core
 * @package tokyo\pmmp\Texter
 */
class Core extends PluginBase {

  /** @var bool */
  private static $isUpdater = false;

  public static function isUpdater(): bool {
    return self::$isUpdater;
  }

  public static function setIsUpdater(bool $bool = true) {
    self::$isUpdater = $bool;
  }

  public function onLoad() {
    $this->loadResources();
    $this->loadLanguage();
    $this->registerCommands();
    $this->prepareTexts();
    $this->checkUpdate();
    $this->mineflowLinkage();
  }

  public function onEnable() {
    if ($this->checkPackaged()) {
      $listener = new EventListener($this);
      $this->getServer()->getPluginManager()->registerEvents($listener, $this);
    }else {
      $this->getServer()->getPluginManager()->disablePlugin($this);
    }
  }

  private function loadResources() {
    new ConfigData($this);
    new UnremovableFloatingTextData($this);
    new FloatingTextData($this);
  }

  private function loadLanguage() {
    new Lang($this);
    $cl = Lang::fromConsole();
    $message1 = $cl->translateString("language.selected", [
      $cl->getName(),
      $cl->getLang()
    ]);
    $this->getLogger()->info(TextFormat::GREEN . $message1);
    if (self::isUpdater()) {
      $message2 = $cl->translateString("on.load.is.updater");
      $this->getLogger()->notice($message2);
    }
  }

  private function registerCommands() {
    if ($canUse = ConfigData::getInstance()->canUseCommands()) {
      $map = $this->getServer()->getCommandMap();
      $map->register($this->getName(), new TxtCommand($this), TxtCommand::NAME);
      $message = Lang::fromConsole()->translateString("on.load.commands.on");
    }else {
      $message = Lang::fromConsole()->translateString("on.load.commands.off");
    }
    $this->getLogger()->info(($canUse ? TextFormat::GREEN : TextFormat::RED) . $message);
  }

  private function prepareTexts() {
    $prepare = new PrepareTextsTask;
    $this->getScheduler()->scheduleDelayedRepeatingTask($prepare, 20, 1);
  }

  private function checkUpdate() {
    if (ConfigData::getInstance()->checkUpdate()) {
      try {
        $this->getServer()->getAsyncPool()->submitTask(new CheckUpdateTask);
      } catch (Exception $ex) {
        $this->getLogger()->warning($ex->getMessage());
      }
    }
  }

  public function compareVersion(bool $success, ?VersionString $new = null, string $url = "") {
    $cl = Lang::fromConsole();
    $logger = $this->getLogger();
    if ($success) {
      $current = new VersionString($this->getDescription()->getVersion());
      switch ($current->compare($new)) {
        case -1:// new: older
          $message = $cl->translateString("on.load.version.dev");
          $logger->warning($message);
          break;

        case 0:// same
          $message = $cl->translateString("on.load.update.nothing", [
            $current->getFullVersion()
          ]);
          $logger->notice($message);
          break;

        case 1:// new: newer
          $messages[] = $cl->translateString("on.load.update.available.1", [
            $new->getFullVersion(),
            $current->getFullVersion()
          ]);
          $messages[] = $cl->translateString("on.load.update.available.2");
          $messages[] = $cl->translateString("on.load.update.available.3", [
            $url
          ]);
          foreach ($messages as $message) $logger->notice($message);
      }
    }else {
      $message = $cl->translateString("on.load.update.offline");
      $logger->notice($message);
    }
  }

  private function mineflowLinkage() {
    $plugins = $this->getServer()->getPluginManager()->getPlugins();
    if (isset($plugins["MineFlowLinkage"])) {

    }
  }

  private function checkPackaged(): bool {
    $cl = Lang::fromConsole();
    $logger = $this->getLogger();
    $result = true;
    if ($this->isPhar()) {
      if (!class_exists(DependenciesNamespace::PACKAGED_LIBRARY_NAMESPACE . DependenciesNamespace::LIB_FORM_API)) {
        $message = $cl->translateString("error.on.enable.not.packaged");
        $logger->critical($message);
        $result = false;
      }else {
        foreach (DependenciesNamespace::PACKAGED_LIBRARY_TRAITS as $libraryClassString) {
          if (!trait_exists($libraryClassString)) {
            $message = $cl->translateString("error.on.enable.not.packaged");
            $logger->critical($message);
            $result = false;
            break;
          }
        }
      }
    }else {
      $plugins = $this->getServer()->getPluginManager()->getPlugins();
      if (isset($plugins["DEVirion"])) {
        if (!class_exists(DependenciesNamespace::LIB_FORM_API)) {
          $message = $cl->translateString("error.on.enable.virion.not.found");
          $logger->critical($message);
          $result = false;
        }else {
          foreach (DependenciesNamespace::VIRION_LIBRARY_TRAITS as $virionClassString) {
            if (!trait_exists($virionClassString)) {
              $message = $cl->translateString("error.on.enable.virion.not.found");
              $logger->critical($message);
              $result = false;
              break;
            }
          }
        }
      }else {
        $message = $cl->translateString("error.on.enable.not.packaged");
        $logger->critical($message);
        $result = false;
      }
    }
    return $result;
  }

}