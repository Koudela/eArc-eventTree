[event](event.md) | [index](../README.md) | 
[observer](observer.md) | [tree](tree.md) | [routing](routing.md)

# The event listener

The event listener is the bridge between the event and the business logic of 
your application.

Like a front controller is attached to a route/request an event listener is 
attached to an observer/event. Best practice is to write small listener that
consists only of logic concerning the application flow. No business logic or 
persistence calls should happen in an event listener.

If you use the `ObserverTreeFactory` the event listener class has to be saved
in the directory of the observer leaf the listener should be attached to. If
you call `registerListener()` on the `ObserverLeaf` yourself, you can put the 
class file where you like.

Every event listener has to implement the `EventListener` Interface. 

```php
namespace your\eventTree\Namespace\eventTreeIdentifier\treeLeaf\anotherTreeLeaf;

use eArc\eventTree\Event\Event;
use eArc\eventTree\Interfaces\EventListener;

class MyFooListener implements EventListener
{
    public function processEvent(Event $event)
    {
        ...
    }
}
```

If you use the `TreeEventFactory` which is recommended for most usecases you
can use the following three constants:
- EARC_LISTENER_PATIENCE is a float determining the patience of the listener.
Lesser patience means getting called by the observer earlier. 
- EARC_LISTENER_TYPE is a int determining the event phase(s) the listener 
listens on. You can use the `EventRouter` constants `PHASE_START`, 
`PHASE_BEFORE`, `PHASE_DESTINATION` and `PHASE_BEYOND` in a bit field (concat 
them by `|`). If the constant `EARC_LISTENER_TYPE` is not used the 
`PHASE_ACCESS` is used, which is a shortcut for listening to all four event 
phases. 
- EARC_LISTENER_CONTAINER_ID is a string determining the container id of the
class. If you don't use a container or use the FQN as container id you must not 
set this constant.

```php
namespace your\eventTree\Namespace\eventTreeIdentifier\treeLeaf\anotherTreeLeaf;

use eArc\eventTree\Event\Event;
use eArc\eventTree\Interfaces\EventListener;
use eArc\eventTree\Tree\EventRouter;

class MyFooListener implements EventListener
{
    const EARC_LISTENER_PATIENCE = 20;
    const EARC_LISTENER_TYPE = EventRouter::PHASE_START | EventRouter::PHASE_DESTINATION;
    const EARC_LISTENER_CONTAINER_ID = 'my_project.my_foo_listener';

    public function processEvent(Event $event)
    {
        ...
    }
}
```

If you have supplied a container to your `EventDispatcherFactory` the listener 
is retrieved from it. Otherwise it is initialised on the first call.

[event](event.md) | [index](../README.md) | 
[observer](observer.md) | [tree](tree.md) | [routing](routing.md)
