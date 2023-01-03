<?php

declare(strict_types = 1);

namespace Drupal\Tests\marvin_phpunit\Unit\Commands;

use Drush\Commands\marvin_phpunit\TestCommandsBase;
use Robo\Config\Config;
use Symfony\Component\Yaml\Yaml;

/**
 * @group marvin
 * @group drush-command
 *
 * @covers \Drush\Commands\marvin_phpunit\TestCommandsBase
 *
 * @property \Drush\Commands\marvin_phpunit\TestCommandsBase $commands
 */
class PhpunitCommandsBaseTest extends CommandsTestBase {

  protected string $commandsClass = TestCommandsBase::class;

  public function testGetClassKey(): void {
    $methodName = 'getClassKey';
    $class = new \ReflectionClass($this->commands);
    $method = $class->getMethod($methodName);
    $method->setAccessible(TRUE);

    static::assertSame('marvin.phpunit.a', $method->invokeArgs($this->commands, ['a']));
  }

  public function testGetConfigValue(): void {
    $configData = [
      'marvin' => [
        'phpunit' => [
          'my_key' => 'my_value',
        ],
      ],
    ];

    $configData = array_replace_recursive(
      $this->getDefaultConfigData(),
      $configData
    );
    $config = new Config($configData);

    $this->commands->setConfig($config);

    $methodName = 'getConfigValue';
    $class = new \ReflectionClass($this->commands);
    $method = $class->getMethod($methodName);
    $method->setAccessible(TRUE);

    static::assertSame('my_value', $method->invokeArgs($this->commands, ['my_key']));
  }

  public function testGetCustomEventNamePrefix(): void {
    $methodName = 'getCustomEventNamePrefix';
    $class = new \ReflectionClass($this->commands);
    $method = $class->getMethod($methodName);
    $method->setAccessible(TRUE);

    static::assertSame('marvin:phpunit', $method->invokeArgs($this->commands, []));
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public static function casesGetTestSuiteNamesByEnvironmentVariant(): array {
    $default = [];
    $filePath = static::getMarvinRootDir() . '/Commands/drush.yml';
    if (file_exists($filePath)) {
      $default = Yaml::parseFile(static::getMarvinRootDir() . '/Commands/drush.yml');
    }

    return [
      'empty' => [
        [],
        [],
      ],
      'default' => [
        [
          'Unit',
        ],
        array_replace_recursive(
          $default,
          [
            'marvin' => [
              'gitHookName' => 'pre-commit',
            ],
          ]
        ),
      ],
      'basic' => [
        [
          'b',
        ],
        [
          'marvin' => [
            'environment' => 'local',
            'ci' => 'jenkins',
            'gitHookName' => 'pre-commit',
            'phpunit' => [
              'testSuite' => [
                'local' => [
                  'a' => TRUE,
                ],
                'localPreCommit' => [
                  'b' => TRUE,
                  'c' => FALSE,
                ],
                'ci' => [],
                'ciJenkins' => [],
              ],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * @phpstan-param array<string, mixed> $expected
   * @phpstan-param array<string, mixed> $configData
   *
   * @dataProvider casesGetTestSuiteNamesByEnvironmentVariant
   */
  public function testGetTestSuiteNamesByEnvironmentVariant(?array $expected, array $configData): void {
    $configData = array_replace_recursive(
      $this->getDefaultConfigData(),
      $configData,
    );

    $this->commands->setConfig(new Config($configData));

    $methodName = 'getTestSuiteNamesByEnvironmentVariant';
    $class = new \ReflectionClass($this->commands);
    $method = $class->getMethod($methodName);
    $method->setAccessible(TRUE);

    static::assertSame($expected, $method->invokeArgs($this->commands, []));
  }

}
