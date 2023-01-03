<?php

declare(strict_types = 1);

namespace Drush\Commands\marvin_phpunit;

use Drush\Commands\marvin\CommandsBase;
use Robo\Contract\TaskInterface;
use Sweetchuck\Robo\PHPUnit\PHPUnitTaskLoader;
use Sweetchuck\Utils\Filter\ArrayFilterEnabled;

class TestCommandsBase extends CommandsBase {

  use PHPUnitTaskLoader;

  protected static string $classKeyPrefix = 'marvin.phpunit';

  protected string $customEventNamePrefix = 'marvin:phpunit';

  /**
   * @phpstan-param array<string, mixed> $options
   *
   * @return \Sweetchuck\Robo\PHPUnit\Task\RunTask|\Robo\Collection\CollectionBuilder
   */
  protected function getTaskPhpUnitRun(array $options): TaskInterface {
    $this->initComposerInfo();
    $options += [
      'phpunitExecutable' => $this->getPhpUnitExecutable(),
    ];

    $task = $this->taskPHPUnitRun($options);
    $task->setHideStdOutput(FALSE);

    $gitHook = $this->getConfig()->get('marvin.gitHook');
    if ($gitHook === 'pre-commit') {
      $task->setNoCoverage(TRUE);
      // @todo $task->setNoLogging(true);
    }

    return $task;
  }

  /**
   * @phpstan-param null|array<string, mixed> $phpVariant
   *
   * @phpstan-return array<string, mixed>
   */
  protected function geDefaultPhpunitTaskOptions(?array $phpVariant = NULL): array {
    $options = [
      'colors' => $this->getColors(),
      'processTimeout' => NULL,
      'phpExecutable' => $phpVariant['phpExecutable'],
    ];

    if (!empty($phpVariant['phpExecutable'])) {
      $options['phpunitExecutable'] = $this->getPhpUnitExecutable();
    }

    return $options;
  }

  protected function getPhpUnitExecutable(): string {
    $composerInfo = $this->getComposerInfo();

    $binDir = 'vendor/bin';
    if (isset($composerInfo['config']['bin-dir'])) {
      $binDir = $composerInfo['config']['bin-dir'];
    }
    elseif (isset($composerInfo['config']['vendor-dir'])) {
      $binDir = $composerInfo['config']['vendor-dir'] . '/bin';
    }

    return "$binDir/phpunit";
  }

  /**
   * @return null|string[]
   */
  protected function getTestSuiteNamesByEnvironmentVariant(): ?array {
    $environmentVariants = $this->getEnvironmentVariants();

    $testSuites = NULL;
    foreach ($environmentVariants as $environmentVariant) {
      $testSuites = $this->getConfigValue("testSuite.$environmentVariant");
      if ($testSuites !== NULL) {
        break;
      }
    }

    if ($testSuites === FALSE) {
      // Do not run any phpunit tests.
      return NULL;
    }

    if ($testSuites === TRUE || $testSuites === NULL) {
      // Run all phpunit tests.
      return [];
    }

    return array_keys(array_filter($testSuites, new ArrayFilterEnabled()));
  }

  protected function getColors(): string {
    $state = $this->getTriStateOptionValue('ansi');

    if ($state === NULL) {
      $state = 'auto';
    }

    return $state ? 'always' : 'never';
  }

}
