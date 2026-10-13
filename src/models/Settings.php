<?php
namespace cloudgrayau\whoops\models;

use CraftCms\Cms\Plugin\PluginSettings;
use CraftCms\Cms\Validation\Rules\EnvValueRule;
use Illuminate\Validation\Rule;

class Settings extends PluginSettings {

  public string $theme = 'default';
  public string $customTheme = '';

  public function getRules(): array {
    return array_merge(parent::getRules(), [
      'theme' => ['string', Rule::in(['default', 'dark'])],
      'customTheme' => new EnvValueRule(['string']),
    ]);
  }

}