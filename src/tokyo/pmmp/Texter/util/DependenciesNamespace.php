<?php

declare(strict_types=1);

namespace tokyo\pmmp\Texter\util;

/**
 * Class DependenciesNamespace
 * @package tokyo\pmmp\Texter\util
 */
final class DependenciesNamespace {

  public const BASE_NAMESPACE = "\\tokyo\\pmmp\\Texter";
  public const PACKAGED_LIBRARY_NAMESPACE = self::BASE_NAMESPACE . "\\libs";

  public const ACCESSORS_TRAIT = "\\jp\\mcbe\\accessors\\AccessorsTrait";
  public const LIB_DESIGN = "\\jp\\mcbe\\libdesign\\pattern\\Singleton";
  public const LIB_FORM_API = "\\jojoe77777\\FormAPI\\FormAPI";

  public const VIRION_LIBRARY_TRAITS = [
    self::ACCESSORS_TRAIT,
    self::LIB_DESIGN,
  ];

  public const PACKAGED_LIBRARY_TRAITS = [
    self::PACKAGED_LIBRARY_NAMESPACE . self::ACCESSORS_TRAIT,
    self::PACKAGED_LIBRARY_NAMESPACE . self::LIB_DESIGN,
  ];

  private function __construct() {
    // THIS IS CONSTANT CLASS
  }

}