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

namespace tokyo\pmmp\Texter\command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\command\utils\CommandException;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\Player;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;
use tokyo\pmmp\Texter\command\sub\TxtAdd;
use tokyo\pmmp\Texter\command\sub\TxtEdit;
use tokyo\pmmp\Texter\command\sub\TxtList;
use tokyo\pmmp\Texter\command\sub\TxtMove;
use tokyo\pmmp\Texter\command\sub\TxtRemove;
use tokyo\pmmp\Texter\Core;
use tokyo\pmmp\Texter\data\ConfigData;
use tokyo\pmmp\Texter\i18n\Lang;

/**
 * Class TxtCommand
 * @package tokyo\pmmp\Texter\command
 */
class TxtCommand extends PluginCommand {

  public const NAME = "txt";

  public function __construct(Core $plugin) {
    parent::__construct(self::NAME, $plugin);
    $cl = Lang::fromConsole();
    $permission = ConfigData::getInstance()->canUseOnlyOp() ? "texter.command.*" : "texter.command.txt";
    $description = $cl->translateString("command.txt.description");
    $usage = $cl->translateString("command.txt.usage");
    $this->setPermission($permission);
    $this->setDescription($description);
    $this->setUsage($usage);
  }

  public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
    $plugin = $this->getPlugin();
    if ($plugin->isDisabled() || !$this->testPermission($sender)) return false;
    if ($sender instanceof Player) {
      $pluginDescription = $plugin->getDescription();
      $cd = ConfigData::getInstance();
      $lang = Lang::fromLocale($sender->getLocale());
      if ($cd->checkWorldLimit($sender->getLevel()->getName())) {
        if (isset($args[0])) {
          switch ($args[0]) {
            case "add":
            case "a":
              new TxtAdd($plugin, $sender);
              break;

            case "edit":
            case "e":
              new TxtEdit($plugin, $sender);
              break;

            case "move":
            case "m":
              new TxtMove($plugin, $sender);
              break;

            case "remove":
            case "r":
              new TxtRemove($plugin, $sender);
              break;

            case "list":
            case "l":
              new TxtList($plugin, $sender);
              break;

            default:
              throw new InvalidCommandSyntaxException;
          }
        }else {
          throw new InvalidCommandSyntaxException;
        }
      }else {
        $message = $lang->translateString("error.config.limit.world", [
          $sender->getLevel()->getName()
        ]);
        $sender->sendMessage(TextFormat::RED . "[{$pluginDescription->getPrefix()}] $message");
      }
    }else {
      $info = Lang::fromConsole()->translateString("error.console");
      $plugin->getLogger()->info(TextFormat::RED.$info);
    }
    return true;
  }

}