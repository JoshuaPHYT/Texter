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

namespace tokyo\pmmp\Texter\command\sub;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use tokyo\pmmp\Texter\data\ConfigData;
use tokyo\pmmp\Texter\data\FloatingTextData;
use tokyo\pmmp\Texter\text\Text;
use tokyo\pmmp\Texter\TexterApi;

/**
 * Class TxtEdit
 * @package tokyo\pmmp\Texter\command\sub
 */
class TxtEdit extends TexterSubCommand {

  /** @var int response key */
  public const FT_NAME = 1;
  public const TYPE = 2;
  public const TITLE = 0;
  public const TEXT = 1;
  public const CONTENT = 4;

  public function execute(string $default = ""): void {
    $pluginDescription = $this->plugin->getDescription();
    $description = $this->lang->translateString("form.edit.description");
    $ftName = $this->lang->translateString("form.ftname");
    $type = $this->lang->translateString("form.edit.type");
    $title = $this->lang->translateString("form.title");
    $text = $this->lang->translateString("form.text");
    $tips = $this->lang->translateString("command.txt.usage.indent");
    $content = $this->lang->translateString("form.edit.content");

    $custom = new CustomForm(function (Player $player, ?array $response) use ($pluginDescription, $title, $text) {
      if ($response !== null) {
        $level = $player->getLevel();
        if (!empty($response[self::FT_NAME])) {
          $ft = TexterApi::getFtByLevel($level, $response[self::FT_NAME]);
          if ($ft !== null) {
            if ($ft->isOwner($player)) {
              $cd = ConfigData::getInstance();
              switch ($response[self::TYPE]) {
                case self::TITLE:
                  $test = TextFormat::clean($response[self::CONTENT].$ft->getText());
                  if ($cd->checkCharLimit(str_replace("\n", "", $test))) {
                    if ($cd->checkFeedLimit($test)) {
                      $ft->title = $response[self::CONTENT];
                      $ft->sendToLevel($level, Text::SEND_TYPE_EDIT);
                      FloatingTextData::getInstance()->saveFtChange($ft);
                      $message = $this->lang->translateString("command.txt.edit.success", [
                        $ft->name,
                        $title
                      ]);
                      $player->sendMessage(TextFormat::GREEN . "[{$pluginDescription->getPrefix()}] $message");
                    }
                  }
                  break;

                case self::TEXT:
                  $test = TextFormat::clean($ft->getTitle().$response[self::CONTENT]);
                  if ($cd->checkCharLimit(str_replace("\n", "", $test))) {
                    if ($cd->checkFeedLimit($test)) {
                      $ft->text = $response[self::CONTENT];
                      $ft->sendToLevel($level, Text::SEND_TYPE_EDIT);
                      FloatingTextData::getInstance()->saveFtChange($ft);
                      $message = $this->lang->translateString("command.txt.edit.success", [
                        $ft->name,
                        $text
                      ]);
                      $player->sendMessage(TextFormat::GREEN . "[{$pluginDescription->getPrefix()}] $message");
                    }
                  }
                  break;
              }
            }else {
              $message = $this->lang->translateString("error.permission");
              $player->sendMessage(TextFormat::RED . "[{$pluginDescription->getPrefix()}] $message");
            }
          }else {
            $message = $this->lang->translateString("error.ftname.not.exists", [
              $response[self::FT_NAME]
            ]);
            $player->sendMessage(TextFormat::RED . "[{$pluginDescription->getPrefix()}] $message");
          }
        }else {
          $message = $this->lang->translateString("error.ftname.not.specified");
          $player->sendMessage(TextFormat::RED . "[{$pluginDescription->getPrefix()}] $message");
        }
      }
    });

    $custom->setTitle("[{$pluginDescription->getPrefix()}] /txt edit");
    $custom->addLabel($description);
    $custom->addInput($ftName, $ftName, $default);
    $custom->addDropdown($type, [$title, $text]);
    $custom->addLabel($tips);
    $custom->addInput($content, $content);
    $this->player->sendForm($custom);
  }
}