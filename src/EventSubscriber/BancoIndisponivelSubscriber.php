<?php

namespace App\EventSubscriber;

use Doctrine\DBAL\Exception\ConnectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class BancoIndisponivelSubscriber implements EventSubscriberInterface
{
    public function __construct(private Environment $twig)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onException'];
    }

    public function onException(ExceptionEvent $event): void
    {
        if (!$event->getThrowable() instanceof ConnectionException) {
            return;
        }

        $event->setResponse(new Response(
            $this->twig->render('erro/banco.html.twig'),
            Response::HTTP_SERVICE_UNAVAILABLE,
        ));
    }
}
