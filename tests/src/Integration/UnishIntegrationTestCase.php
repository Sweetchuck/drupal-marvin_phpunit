<?php

declare(strict_types = 1);

namespace Drupal\Tests\marvin_phpunit\Integration;

use Drush\TestTraits\DrushTestTrait;
use Webmozart\PathUtil\Path;
use weitzman\DrupalTestTraits\ExistingSiteBase;

class UnishIntegrationTestCase extends ExistingSiteBase {

  use DrushTestTrait;

  protected string $projectName = 'project_01';

  protected function getCommonCommandLineOptions(): array {
    return [
      'config' => [
        Path::join($this->getDrupalRoot(), '..', 'drush'),
      ],
    ];
  }

  protected function getCommonCommandLineEnvVars(): array {
    return [
      'HOME' => '/dev/null',
    ];
  }

  protected function getProjectRootDir(): string {
    return dirname($this->getDrupalRoot());
  }

  public function getMarvinRootDir(): string {
    return dirname(__DIR__, 3);
  }

  public function getDrupalRoot(): string {
    return Path::join($this->getMarvinRootDir(), "tests/fixtures/{$this->projectName}/docroot");
  }

}
