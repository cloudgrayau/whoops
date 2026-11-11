<?php
namespace cloudgrayau\whoops;

use cloudgrayau\whoops\models\Settings;
use cloudgrayau\utils\UtilityHelper;

use CraftCms\Cms\Cp\SelectOptions;
use CraftCms\Cms\Ui\{Ui, UiContext};
use CraftCms\Cms\Ui\Controls\{Choice, Text};
use CraftCms\Cms\Ui\Nodes\Field;
use CraftCms\Cms\Plugin\{Plugin, PluginSettings};
use CraftCms\Cms\Support\Env;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

use function CraftCms\Cms\t;

class Whoops extends Plugin {

  public bool $hasCpSettings = true;
  public bool $hasCpSection = false;

  // Public Methods
  // =========================================================================

  public function boot(): void {
    if (app()->runningInConsole()) {
      return;
    }
    if (!app()->hasDebugModeEnabled()) {
      return;
    }
    $settings = $this->getSettings();
    $whoops = new \Whoops\Run;
    $handler = new \Whoops\Handler\PrettyPageHandler;
    $custom = $settings->customTheme ? Env::parse($settings->customTheme) : null;
    if ($custom && file_exists($custom)) {
      $file = pathinfo($custom);
      $handler->setResourcesPath($file['dirname']);
      $handler->addCustomCss($file['basename']);
    } else {
      $handler->setResourcesPath(__DIR__ . '/themes');
      $handler->addCustomCss($settings->theme . '.css');
    }
    $whoops->pushHandler($handler);
    $whoops->allowQuit(false);
    $whoops->writeToOutput(false);
  
    $exceptions = $this->app->make(ExceptionHandler::class);
    if (!method_exists($exceptions, 'renderable')) {
      return;
    }
    $exceptions->renderable(function (\Throwable $e, Request $request) use ($whoops) {
      if (
        $request->expectsJson() ||
        $e instanceof HttpExceptionInterface ||
        $e instanceof HttpResponseException ||
        $e instanceof AuthenticationException ||
        $e instanceof ValidationException
      ) {
        return null; // let Laravel handle these as normal
      }
      return response($whoops->handleException($e), 500, ['Content-Type' => 'text/html']);
    });
  }

  // Protected Methods
  // =========================================================================

  #[\Override]
  protected static function createSettings(): PluginSettings {
    return new Settings();
  }

  public function settingsUi(UiContext $context = new UiContext): ?Ui {
    return Ui::make([
      Field::make(t('Theme', category: 'whoops'), Choice::make('theme')->options([
        ['value' => 'default', 'label' => t('Default', category: 'whoops')],
        ['value' => 'dark', 'label' => t('Dark', category: 'whoops')],
      ]))
        ->instructions(t('The default theme for whoops error handling.', category: 'whoops'))
        ->required()
        ->warning(UtilityHelper::configWarning($this->handle, 'theme')),
      Field::make(
        t('Custom Theme', category: 'whoops'),
        Text::make('customTheme')
          ->placeholder('@root/src/whoops.css')
          ->textExpanderTriggers(SelectOptions::getEnvTextExpanderTriggers(true))
      )
        ->instructions(t('The path to your custom theme CSS file.', category: 'whoops'))
        ->warning(UtilityHelper::configWarning($this->handle, 'customTheme'))
        ->tip(sprintf(
            '%s',
            t('Type `$` to choose an environment variable, or `@` to choose an alias.')
        )),
        UtilityHelper::footer()
    ]);
  }

}