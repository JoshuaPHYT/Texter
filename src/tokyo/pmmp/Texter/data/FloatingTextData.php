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
use pocketmine\level\Level;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Config;
use tokyo\pmmp\Texter\text\FloatingText;

/**
 * Class FloatingTextData
 * @package tokyo\pmmp\Texter\data
 */
class FloatingTextData extends Config {

  use Singleton {
    Singleton::__construct as singletonConstruct;
  }

  public const FILE_NAME = "ft.json";
  public const KEY_OWNER = "OWNER";

  public function __construct(Plugin $plugin) {
    $plugin->saveResource(self::FILE_NAME);
    parent::__construct($plugin->getDataFolder() . self::FILE_NAME, Config::JSON);
    $this->singletonConstruct();
    $this->enableJsonOption(Data::JSON_OPTIONS);
  }

  public function saveFtChange(FloatingText $ft): bool {
    $levelName = $ft->level->getFolderName();
    $levelFts = $this->get($levelName, []);
    $levelFts[$ft->name] = $ft->jsonSerialize();
    $this->set($levelName, $levelFts);
    $this->save();
    return true;
  }

  public function removeFtsByLevel(Level $level): bool {
    return $this->removeFtsByLevelName($level->getFolderName());
  }

  public function removeFtsByLevelName(string $levelName): bool {
    if ($bool = $this->exists($levelName)) {
      $this->remove($levelName);
      $this->save();
    }
    return $bool;
  }

  public function removeFtByLevel(Level $level, string $name) {
    $this->removeFtByLevelName($level->getFolderName(), $name);
  }

  public function removeFtByLevelName(string $levelName, string $name) {
    if ($this->exists($levelName)) {
      $levelFts = $this->get($levelName);
      unset($levelFts[$name]);
      $this->set($levelName, $levelFts);
      $this->save();
    }
  }

  public function getFlatten(): array {
    $fts = $this->getAll();
    $result = [];
    foreach ($fts as $levelName => $arrayFts) {
      foreach ($arrayFts as $ftName => $arrayFt) {
        $arrayFt += [
          Data::KEY_NAME => $ftName,
          Data::KEY_LEVEL => $levelName,
        ];
        $result[] = $arrayFt;
      }
    }
    return $result;
  }
}