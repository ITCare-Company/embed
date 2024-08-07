<?php

namespace Drupal\embed\EmbedType;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a base implementation that most embed type plugins will extend.
 *
 * @ingroup embed_api
 */
abstract class EmbedTypeBase extends PluginBase implements EmbedTypeInterface, ContainerFactoryPluginInterface {

  /**
   * The module list service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleList;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleExtensionList $module_extension_list,) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleList = $module_extension_list;
    $this->configuration = NestedArray::mergeDeep($this->defaultConfiguration(), $this->configuration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('extension.list.module'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurationValue($name, $default = NULL) {
    $configuration = $this->getConfiguration();
    if (array_key_exists($name, $configuration)) {
      return $configuration[$name];
    }
    else {
      return $default;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfigurationValue($name, $value) {
    $this->configuration[$name] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->hasAnyErrors()) {
      $this->setConfiguration(
        array_intersect_key(
          $form_state->getValues(),
          $this->defaultConfiguration()
        )
      );
    }
  }

  /**
   * Gets the module list service.
   *
   * @return \Drupal\Core\Extension\ModuleExtensionList
   *   The module extension list service.
   */
  protected function getModuleList(): ModuleExtensionList {
    return $this->moduleList;
  }

  /**
   * Gets the Drupal-root relative installation directory of a module.
   *
   * @param string $module_name
   *   The machine name of the module.
   *
   * @return string
   *   The module installation directory.
   *
   * @throws \InvalidArgumentException
   *   If there is no extension with the supplied machine name.
   *
   * @see \Drupal\Core\Extension\ExtensionList::getPath()
   */
  protected function getModulePath(string $module_name): string {
    return $this->getModuleList()->getPath($module_name);
  }

}
