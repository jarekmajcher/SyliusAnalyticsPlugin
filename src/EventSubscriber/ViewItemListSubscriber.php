<?php

declare(strict_types=1);

namespace Setono\SyliusAnalyticsPlugin\EventSubscriber;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Setono\GoogleAnalyticsBundle\Event\ClientSideEvent;
use Setono\GoogleAnalyticsMeasurementProtocol\Request\Body\Event\ViewItemListEvent;
use Setono\SyliusAnalyticsPlugin\Event\ItemListViewed;
use Setono\SyliusAnalyticsPlugin\Resolver\Item\ItemResolverInterface;
use Setono\SyliusAnalyticsPlugin\Util\FormatAmountTrait;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Bundle\ResourceBundle\Grid\View\ResourceGridView;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

final class ViewItemListSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use FormatAmountTrait;

    private LoggerInterface $logger;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ItemResolverInterface $itemResolver,
        private readonly RequestStack $requestStack,
        private readonly TaxonRepositoryInterface $taxonRepository,
        private readonly LocaleContextInterface $localeContext,
    ) {
        $this->logger = new NullLogger();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ItemListViewed::class => 'track',
            'sylius.product.index' => 'trackNative',
        ];
    }

    public function track(ItemListViewed $viewItemListEvent): void
    {
        try {
            $event = ViewItemListEvent::create()
                ->setListId($viewItemListEvent->listId)
                ->setListName($viewItemListEvent->listName)
            ;

            foreach ($viewItemListEvent->products as $product) {
                $event->addItem($this->itemResolver->resolveFromProduct($product));
            }

            $this->eventDispatcher->dispatch(new ClientSideEvent($event));
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function trackNative(ResourceControllerEvent $resourceControllerEvent): void
    {
        try {
            /** @var ResourceGridView|array<array-key, ProductInterface>|mixed $products */
            $products = $resourceControllerEvent->getSubject();
            if ($products instanceof ResourceGridView) {
                /** @var mixed $products */
                $products = $products->getData();
            }

            Assert::isIterable($products);
            Assert::allIsInstanceOf($products, ProductInterface::class);

            $event = ViewItemListEvent::create();

            $taxon = $this->resolveTaxon();
            if (null !== $taxon) {
                $event
                    ->setListId($taxon->getCode())
                    ->setListName($taxon->getName())
                ;
            }

            foreach ($products as $product) {
                $event->addItem($this->itemResolver->resolveFromProduct($product));
            }

            $this->eventDispatcher->dispatch(new ClientSideEvent($event));
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function resolveTaxon(): ?TaxonInterface
    {
        $slug = $this->requestStack->getMainRequest()?->attributes->get('slug');
        if (!is_string($slug)) {
            return  null;
        }

        return $this->taxonRepository->findOneBySlug($slug, $this->localeContext->getLocaleCode());
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
