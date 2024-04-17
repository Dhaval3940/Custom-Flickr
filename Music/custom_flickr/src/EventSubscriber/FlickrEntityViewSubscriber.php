<?php

namespace Drupal\custom_flickr\EventSubscriber;

use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * EventSubscriber for photo album node view redirect.
 */
class FlickrEntityViewSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['redirectPhotoAlbumToFlickr'],
    ];
  }

  /**
   * Redirects photo album nodes to the custom Flickr display page.
   */
  public function redirectPhotoAlbumToFlickr(RequestEvent $event) {
    $request = $event->getRequest();
    $route_name = $request->attributes->get('_route');

    // Check if the route corresponds to the node view page.
    if ($route_name === 'entity.node.canonical') {
      $entity = $request->attributes->get('node');

      // Check if the entity is a node.
      if ($entity instanceof NodeInterface) {
        // Check if the node is of the photo_album type.
        if ($entity->bundle() === 'photo_album') {
          $flickr_id = $entity->get('field_album_flickr_id')->value;

          if (!empty($flickr_id)) {
            // Redirect to the custom Flickr display page.
            $url = Url::fromRoute('custom_flickr.display_photoset', [
              'photoset_id' => $flickr_id,
              'node' => $entity->id(),
            ])->toString();
            $response = new RedirectResponse($url);
            $event->setResponse($response);
          }
        }
      }
    }
  }

}
