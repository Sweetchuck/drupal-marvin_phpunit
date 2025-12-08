<?php

declare(strict_types=1);

namespace Drupal\marvin_phpunit;

use Robo\Contract\TaskInterface;
use Sweetchuck\Robo\PHPUnit\PHPUnitTaskLoader;
use Sweetchuck\Utils\Filter\EnabledFilter;

trait PhpunitCommandTrait {

  use PHPUnitTaskLoader;

  /**
   * @phpstan-param array<string, mixed> $options
   *
   * @return \Sweetchuck\Robo\PHPUnit\Task\RunTask|\Robo\Collection\CollectionBuilder
   */
  protected function getTaskPhpUnitRun(array $options): TaskInterface {
    $options += [
      'phpunitExecutable' => $this->getPhpUnitExecutable(),
    ];

    $task = $this->taskPHPUnitRun($options);
    $task->setHideStdOutput(FALSE);

    $gitHookName = $this->getConfig()->get('marvin.gitHookName');
    if ($gitHookName === 'pre-commit') {
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

    return array_keys(array_filter($testSuites, new EnabledFilter()));
  }

  protected function getColors(): string {
    $state = $this->getTriStateOptionValue('ansi');

    if ($state === NULL) {
      $state = 'auto';
    }

    return $state ? 'always' : 'never';
  }
}
