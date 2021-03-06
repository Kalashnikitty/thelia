<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace Thelia\Action;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Cache\CacheEvent;
use Thelia\Core\Event\Hook\HookCreateAllEvent;
use Thelia\Core\Event\Hook\HookCreateEvent;
use Thelia\Core\Event\Hook\HookDeactivationEvent;
use Thelia\Core\Event\Hook\HookDeleteEvent;
use Thelia\Core\Event\Hook\HookToggleActivationEvent;
use Thelia\Core\Event\Hook\HookToggleNativeEvent;
use Thelia\Core\Event\Hook\HookUpdateEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\Hook as HookModel;
use Thelia\Model\HookQuery;

/**
 * Class HookAction
 * @package Thelia\Action
 * @author  Julien Chanséaume <jchanseaume@openstudio.fr>
 */
class Hook extends BaseAction implements EventSubscriberInterface
{
    /**
     * @var string
     */
    protected $cacheDir;

    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    public function create(HookCreateEvent $event)
    {
        $hook = new HookModel();

        $hook
            ->setLocale($event->getLocale())
            ->setCode($event->getCode())
            ->setType($event->getType())
            ->setNative($event->getNative())
            ->setActivate($event->getActive())
            ->setTitle($event->getTitle())
            ->save();

        $event->setHook($hook);

        $this->cacheClear($event->getDispatcher());
    }

    public function update(HookUpdateEvent $event)
    {
        if (null !== $hook = HookQuery::create()->findPk($event->getHookId())) {
            $hook
                ->setLocale($event->getLocale())
                ->setCode($event->getCode())
                ->setType($event->getType())
                ->setNative($event->getNative())
                ->setActivate($event->getActive())
                ->setBlock($event->getBlock())
                ->setByModule($event->getByModule())
                ->setTitle($event->getTitle())
                ->setChapo($event->getChapo())
                ->setDescription($event->getDescription())
                ->save();

            $event->setHook($hook);
            $this->cacheClear($event->getDispatcher());
        }
    }

    public function delete(HookDeleteEvent $event)
    {
        if (null !== $hook = HookQuery::create()->findPk($event->getHookId())) {
            $hook->delete();
            $event->setHook($hook);

            $this->cacheClear($event->getDispatcher());
        }
    }

    public function createAll(HookCreateAllEvent $event)
    {
        $hook = new HookModel();

        $hook
            ->setLocale($event->getLocale())
            ->setCode($event->getCode())
            ->setType($event->getType())
            ->setNative($event->getNative())
            ->setActivate($event->getActive())
            ->setBlock($event->getBlock())
            ->setByModule($event->getByModule())
            ->setTitle($event->getTitle())
            ->setChapo($event->getChapo())
            ->setDescription($event->getDescription())
            ->save();

        $event->setHook($hook);
    }

    public function deactivation(HookDeactivationEvent $event)
    {
        if (null !== $hook = HookQuery::create()->findPk($event->getHookId())) {
            $hook
                ->setActivate(false)
                ->save();
            $event->setHook($hook);
        }
    }

    public function toggleNative(HookToggleNativeEvent $event)
    {
        if (null !== $hook = HookQuery::create()->findPk($event->getHookId())) {
            $hook
                ->setNative(!$hook->getNative())
                ->save();
            $event->setHook($hook);
        }
    }

    public function toggleActivation(HookToggleActivationEvent $event)
    {
        if (null !== $hook = HookQuery::create()->findPk($event->getHookId())) {
            $hook
                ->setActivate(!$hook->getActivate())
                ->save();
            $event->setHook($hook);

            $this->cacheClear($event->getDispatcher());
        }
    }

    protected function cacheClear(EventDispatcherInterface $dispatcher)
    {
        $cacheEvent = new CacheEvent($this->cacheDir);

        $dispatcher->dispatch(TheliaEvents::CACHE_CLEAR, $cacheEvent);
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            TheliaEvents::HOOK_CREATE            => array('create', 128),
            TheliaEvents::HOOK_UPDATE            => array('update', 128),
            TheliaEvents::HOOK_DELETE            => array('delete', 128),
            TheliaEvents::HOOK_TOGGLE_ACTIVATION => array('toggleActivation', 128),
            TheliaEvents::HOOK_TOGGLE_NATIVE     => array('toggleNative', 128),
            TheliaEvents::HOOK_CREATE_ALL        => array('createAll', 128),
            TheliaEvents::HOOK_DEACTIVATION      => array('deactivation', 128),

        );
    }
}
