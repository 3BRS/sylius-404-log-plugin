<?php

declare(strict_types=1);

namespace ThreeBRS\Sylius404LogPlugin\EventListener;

use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class SetonoFormScriptListener implements EventSubscriberInterface
{
    /** @var ChannelRepositoryInterface<ChannelInterface> */
    private ChannelRepositoryInterface $channelRepository;

    private Environment $twig;

    /**
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     */
    public function __construct(ChannelRepositoryInterface $channelRepository, Environment $twig)
    {
        $this->channelRepository = $channelRepository;
        $this->twig = $twig;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        // Check if this is the Setono redirect create page
        if ($request->get('_route') === 'setono_sylius_redirect_admin_redirect_create' &&
            $response->getStatusCode() === Response::HTTP_OK) {
            $content = $response->getContent();

            // Get channel mapping for JavaScript
            $channelMapping = $this->buildChannelMapping();

            // Render JavaScript from Twig template
            $script = $this->twig->render('@ThreeBRSSylius404LogPlugin/setono_redirect_autofill.html.twig', [
                'channel_mapping' => $channelMapping,
            ]);

            $scriptWithBody = $script . '</body>';
            $content = str_replace('</body>', $scriptWithBody, $content);
            $response->setContent($content);
        }
    }

    /**
     * @return array<string, string>
     */
    private function buildChannelMapping(): array
    {
        /** @var ChannelInterface[] $channels */
        $channels = $this->channelRepository->findAll();
        $mapping = [];

        foreach ($channels as $channel) {
            $hostname = $channel->getHostname();
            $code = $channel->getCode();

            if ($hostname) {
                // Extract domain keywords for matching
                $domainParts = explode('.', $hostname);
                $mainDomain = $domainParts[0] ?? '';

                $mapping[$code] = $mainDomain;
            }
        }

        return $mapping;
    }
}
