<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use ApiPlatform\Core\EventListener\EventPriorities;
use ApiPlatform\Core\Util\RequestAttributesExtractor;
use App\Entity\BlogPost;
use App\Entity\MediaObject;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Vich\UploaderBundle\Storage\StorageInterface;

final class ResolveBlogPostWithMediaObjectContentUrlSubscriber implements EventSubscriberInterface
{
    private $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onPreSerialize', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function onPreSerialize(ViewEvent $event): void
    {
        $controllerResult = $event->getControllerResult();
        $request = $event->getRequest();

        if ($controllerResult instanceof Response || !$request->attributes->getBoolean('_api_respond', true)) {
            return;
        }

        if (!($attributes = RequestAttributesExtractor::extractAttributes($request)) || !\is_a($attributes['resource_class'], BlogPost::class, true)) {
            return;
        }

        if($controllerResult instanceof Paginator) {
            /** @var BlogPost $blogPost */
            foreach ($controllerResult as $blogPost) {
                // Заберем только первую картинку к посту и прикрепим к mediaObjects.
                $mediaObjects = $blogPost->getMediaObjects();
                if (!empty($mediaObjects)) {
                    /** @var MediaObject $mediaObjectFirst */
                    $mediaObjectFirst = $mediaObjects[0];
                    $blogPost->removeMediaObjectsAll();
                    $mediaObjectFirst->contentUrl = $this->storage->resolveUri($mediaObjectFirst, 'file');
                    $blogPost->addMediaObject($mediaObjectFirst);
                }
            }
        } else {
            /** @var BlogPost $blogPost */
            $blogPost = $controllerResult;
            foreach ($blogPost->getMediaObjects() as $mediaObject) {
                $mediaObject->contentUrl = $this->storage->resolveUri($mediaObject, 'file');
            }
        }
    }
}
