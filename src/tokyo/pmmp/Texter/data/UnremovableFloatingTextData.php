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

namespace tokyo\pmmp\Texter\data;

use jp\mcbe\libdesign\pattern\Singleton;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;

/**
 * Class UnremovableFloatingTextData
 * @package tokyo\pmmp\Texter\data
 */
class UnremovableFloatingTextData extends Config {

  use Singleton {
    Singleton::__construct as singletonConstruct;
  }

  public const FILE_NAME = "uft.json";

  public function __construct(Plugin $plugin) {
    $plugin->saveResource(self::FILE_NAME);
    parent::__construct($plugin->getDataFolder() . self::FILE_NAME, Config::JSON);
    $this->singletonConstruct();
    $this->enableJsonOption(Data::JSON_OPTIONS);
  }

  public function getFlatten(): array {
    $ufts = $this->getAll();
    $result = [];
    foreach ($ufts as $levelName => $arrayUfts) {
      foreach ($arrayUfts as $uftName => $arrayUft) {
        $arrayUft += [
          Data::KEY_NAME => $uftName,
          Data::KEY_LEVEL => $levelName,
        ];
        $result[] = $arrayUft;
      }
    }
    return $result;
  }
}