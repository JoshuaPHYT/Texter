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

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use tokyo\pmmp\Texter\command\TxtCommand;
use tokyo\pmmp\Texter\i18n\Lang;
use tokyo\pmmp\Texter\task\SendTextsTask;
use tokyo\pmmp\Texter\text\Text;

class EventListener implements Listener {

  /** @var Plugin */
  private $plugin;

  public function __construct(Plugin $plugin) {
    $this->plugin = $plugin;
  }

  public function onJoin(PlayerJoinEvent $ev) {
    $p = $ev->getPlayer();
    $l = $p->getLevel();
    $add = new SendTextsTask($this->plugin, $p, $l);
    $this->plugin->getScheduler()->scheduleDelayedRepeatingTask($add, 20, 1);
  }

  public function onLevelChange(EntityLevelChangeEvent $ev) {
    $ent = $ev->getEntity();
    if ($ent instanceof Player) {
      $from = $ev->getOrigin();
      $to = $ev->getTarget();
      $remove = new SendTextsTask($this->plugin, $ent, $from, Text::SEND_TYPE_REMOVE);
      $this->plugin->getScheduler()->scheduleDelayedRepeatingTask($remove, SendTextsTask::DELAY_TICKS, SendTextsTask::TICKING_PERIOD);
      $add = new SendTextsTask($this->plugin, $ent, $to);
      $this->plugin->getScheduler()->scheduleDelayedRepeatingTask($add, SendTextsTask::DELAY_TICKS, SendTextsTask::TICKING_PERIOD);
    }
  }

  public function onSendPacket(DataPacketSendEvent $ev) {
    $pk = $ev->getPacket();
    if ($pk->pid() === ProtocolInfo::AVAILABLE_COMMANDS_PACKET) {
      /** @var AvailableCommandsPacket $pk */
      if (isset($pk->commandData[TxtCommand::NAME])) {
        $p = $ev->getPlayer();
        $txt = $pk->commandData[TxtCommand::NAME];
        $txt->commandDescription = Lang::fromLocale($p->getLocale())->translateString("command.txt.description");
      }
    }
  }
}