<?php
namespace cloudgrayau\whoops;

use cloudgrayau\whoops\models\Settings;
use cloudgrayau\utils\UtilityHelper;

use Craft;
use craft\base\Plugin;
use craft\helpers\App;
use craft\helpers\UrlHelper;

use yii\base\Event;

class Whoops extends Plugin {

   public static $plugin;
   public string $schemaVersion = '1.0.0';
   public bool $hasCpSettings = true;
   public bool $hasCpSection = false;

   // Public Methods
   // =========================================================================

   public function init(): void {
      parent::init();
      self::$plugin = $this;
      $this->_registerComponents();
      if (Craft::$app->getRequest()->getIsConsoleRequest()) {
        return;
      }
      if (App::devMode()){
        $whoops = new \Whoops\Run;
        $handler = new \Whoops\Handler\PrettyPageHandler;
        if (($this->settings->customTheme) && (file_exists(App::parseEnv($this->settings->customTheme)))){
          $file = pathinfo(App::parseEnv($this->settings->customTheme));
          $handler->setResourcesPath($file['dirname']);
          $handler->addCustomCss($file['basename']);
        } else {
          $handler->setResourcesPath(dirname(__FILE__).'/themes');
          $handler->addCustomCss($this->settings->theme.'.css');
        }
        $whoops->pushHandler($handler);
        $whoops->register();
      }
   }
   
   // Private Methods
   // =========================================================================
   
   private function _registerComponents(): void {
      UtilityHelper::registerModule();
   }

   // Protected Methods
   // =========================================================================

  protected function createSettingsModel(): ?\craft\base\Model {
    return new Settings();
  }
  
  protected function settingsHtml(): ?string {
    return Craft::$app->view->renderTemplate(
      'whoops/settings', [
        'settings' => $this->getSettings()
      ]
    );
  }
   
}
