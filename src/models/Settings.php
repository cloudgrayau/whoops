<?php
namespace cloudgrayau\whoops\models;

use craft\base\Model;

class Settings extends Model {
  
  // Static Variables
  // =========================================================================
  
  public array $themeOptions = [
    ['label' => 'Default', 'value' => 'default'],
    ['label' => 'Dark', 'value' => 'dark']
  ];
  
  // Editable Variables
  // =========================================================================
  
  public string $theme = 'default';
  public string $customTheme = '';
  
  // Public Methods
  // =========================================================================

  public function rules(): array {
    return [
      [['theme','customTheme'], 'string'],
    ];
  }
  
}