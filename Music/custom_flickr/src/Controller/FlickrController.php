<?php

namespace Drupal\custom_flickr\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Render\RendererInterface;
use Drupal\key\KeyRepositoryInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Flickr controller for the custom_flickr module.
 */
class FlickrController extends ControllerBase {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The HTTP Client.
   *
   * @var \Drupal\http_client_manager\HttpClientInterface
   */
  protected $httpClient;

  /**
   * The key repository.
   *
   * @var \Drupal\key\KeyRepositoryInterface
   */
  protected $keyRepository;

  /**
   * Constructs a new FlickrController object.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Http\ClientFactory $http_client_factory
   *   The HTTP Client Factory service.
   * @param \Drupal\key\KeyRepositoryInterface $key_repository
   *   The HTTP Client Manager Factory service.
   */
  public function __construct(RendererInterface $renderer, ClientFactory $http_client_factory, KeyRepositoryInterface $key_repository) {
    $this->renderer = $renderer;
    $this->httpClient = $http_client_factory;
    $this->keyRepository = $key_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('http_client_factory'),
      $container->get('key.repository')
    );
  }

  /**
   * Retrieves content based on the submitted photoset_id.
   */
  public function content(NodeInterface $node, $photoset_id) {
    // Get the HTTP client.
    $client = $this->httpClient->fromOptions();

    $flickr_api_endpoint = $this->keyRepository->getKey('flickr_api_endpoint')->getKeyValue();
    $flickr_api_key = $this->keyRepository->getKey('flickr_api_key')->getKeyValue();

    // Retrieve 50 latest photos from specific Flickr photoset via request.
    $response = $client->get($flickr_api_endpoint, [
      'query' => [
        'method' => 'flickr.photosets.getPhotos',
        'api_key' => $flickr_api_key,
        'photoset_id' => $photoset_id,
        'format' => 'json',
        'nojsoncallback' => 1,
        'per_page' => 50,
        'extras' => 'date_upload',
        'sort' => 'date-posted-desc',
      ],
    ]);

    if ($response->getStatusCode() == 200) {
      $data = json_decode($response->getBody());

      if (!empty($data->photoset->photo)) {
        $photos = [];

        foreach ($data->photoset->photo as $photo) {
          // Generate the URL of the photo.
          if (isset($photo->url_o)) {
            $photo_url = $photo->url_o;
          }
          else {
            // Fallback to higher quality if original size URL unavailable.
            $photo_url = sprintf('https://farm%s.staticflickr.com/%s/%s_%s_b.jpg', $photo->farm, $photo->server, $photo->id, $photo->secret);
          }
          // Create a render array for the image.
          $photos[] = [
            '#theme' => 'image',
            '#uri' => $photo_url,
            '#alt' => $photo->title,
          ];
        }

        // Render the Twig template.
        $build = [
          '#theme' => 'flickr_slider',
          '#photos' => $photos,
        ];

        // Attach library for Slick Slider.
        $build['#attached']['library'][] = 'custom_flickr/slick-slider';
        return $build;
      }
      else {
        return [
          '#markup' => $this->t('No photos found in the photoset.'),
        ];
      }
    }
    else {
      return [
        '#markup' => $this->t('Error fetching photos from the photoset.'),
      ];
    }
  }

  /**
   * Returns page title.
   */
  public function pageTitle(NodeInterface $node, $photoset_id) {
    return $this->t('%title', [
      '%title' => $node->label(),
    ]);
  }

}
