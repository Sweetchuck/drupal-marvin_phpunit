<?php

declare(strict_types = 1);

namespace Drush\Commands\dummy_m1;

use Drupal\marvin\PhpVariantTrait;
use Drush\Commands\marvin_phpunit\TestCommandsBase;
use Robo\Collection\CollectionBuilder;
use Sweetchuck\Utils\Filesystem as FilesystemUtils;
use Symfony\Component\Filesystem\Path;

class PhpunitCommands extends TestCommandsBase {

  use PhpVariantTrait;

  /**
   * @command marvin:test:unit
   */
  public function marvinTestUnit(
    string $workingDirectory,
    array $args,
    array $options = []
  ): CollectionBuilder {
    $projectRoot = FilesystemUtils::findFileUpward(
      'composer.json',
      __DIR__,
    );
    $phpunitExecutable = Path::makeRelative("$projectRoot/vendor/bin/phpunit", $workingDirectory);

    $testSuiteNames = ['Unit'];
    $phpVariant = $this->createPhpVariantFromCurrent();

    return $this
      ->getTaskPhpUnitRun($this->geDefaultPhpunitTaskOptions($phpVariant))
      ->setWorkingDirectory($workingDirectory)
      ->setHideStdOutput(FALSE)
      ->setColors('never')
      ->setPhpunitExecutable($phpunitExecutable)
      ->setTestSuite($testSuiteNames)
      ->setArguments($args);
  }

}
