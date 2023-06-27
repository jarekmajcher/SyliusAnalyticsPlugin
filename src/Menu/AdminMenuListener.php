<?php

declare(strict_types=1);

namespace Setono\SyliusAnalyticsPlugin\Menu;

use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    private bool $gtagEnabled;

    public function __construct(bool $gtagEnabled)
    {
        $this->gtagEnabled = $gtagEnabled;
    }

    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $configuration = $menu->getChild('marketing');

        if (null !== $configuration) {
            $this->addChild($configuration);
        } else {
            $this->addChild($menu->getFirstChild());
        }
    }

    private function addChild(ItemInterface $item): void
    {
        $item
            ->addChild('analytics', [
                'route' => $this->gtagEnabled ? 'setono_sylius_analytics_admin_property_index' : 'setono_sylius_analytics_admin_container_index',
            ])
            ->setLabel('setono_sylius_analytics.ui.google_analytics')
            ->setLabelAttribute('icon', 'google')
        ;
    }
}
