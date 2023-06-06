<?php

namespace Drupal\embed;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for embed CKEditor plugins.
 */
abstract class EmbedCKEditorPluginBase extends CKEditorPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterfac
   */
  protected $entityTypeManager;

  /**
   * Constructs a Drupal\entity_embed\Plugin\CKEditorPlugin\DrupalEntity object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    if (!isset($plugin_definition['embed_type_id'])) {
      throw new InvalidPluginDefinitionException($plugin_id, sprintf('The %s plugin must define the embed_type_id property.', $plugin_id));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    $buttons = [];

    if ($embed_buttons = $this->entityTypeManager->getStorage('embed_button')->loadByProperties(['type_id' => $this->pluginDefinition['embed_type_id']])) {
      foreach ($embed_buttons as $embed_button) {
        $buttons[$embed_button->id()] = $this->getButton($embed_button);
      }
    }

    return $buttons;
  }

  /**
   * Build the information about the specific button.
   *
   * @param \Drupal\embed\EmbedButtonInterface $embed_button
   *   The embed button.
   *
   * @return array
   *   The array for use with getButtons().
   */
  protected function getButton(EmbedButtonInterface $embed_button) {
    $info = [
      'id' => $embed_button->id(),
      'name' => Html::escape($embed_button->label()),
      'label' => Html::escape($embed_button->label()),
      'image' => $embed_button->getIconUrl(),
    ];
    $definition = $this->getPluginDefinition();
    if (!empty($definition['required_filter_plugin_id'])) {
      $info['required_filter_plugin_id'] = $definition['required_filter_plugin_id'];
    }
    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return [
      'embed/embed',
    ];
  }

}
