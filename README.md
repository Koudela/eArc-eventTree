[event](doc/event.md) | [listener](doc/listener.md) | 
[observer](doc/observer.md) | [tree](doc/tree.md) | [routing](doc/routing.md)

# eArc/eventTree

Developer of PHP-applications might know the debug hell that lives right beside
the massive use of event-listeners. The fancy thing is most JS-developer do not 
know this pain, although JS-Code is often heavy loaded with event-listeners.
Some people suspect the major difference is the DOM. JS-developer always have a 
picture where the event-listeners hook in.

To enrich the PHP-community by this advantage I have developed the observer tree
pattern/architecture which is in fact a combination of the composite and
observer pattern and acts as architectural base for the eArc eventTree package.

It can be used as an easy way to expose lifecycle hooks (an incredible powerful
tool in a collaborative workflow), to implement complex iterators, the broker, 
the chain of responsibility or the strategy pattern, MVC/ADR and many more.

As of all eArc packages one of its driving ideas is to make your code as
explicit and easy to understand as possible without imposing to much 
restrictions on it.    

## Installation
```bash
$ composer install eArc/eventTree
```

## The event tree

The event trees live in your project directory. It is possible to import and
even extend trees from other projects.  

Best practice is to have only one directory which is the root for all your event
trees. This constrain ensures that every developer who is or will be engaged in 
your project can easily keep track of all event trees.

Each event tree maps to a directory tree. Each directory maps to an event
observer. A class which implements the interface 
`eArc\eventTree\Interfaces\EventListener` and which corresponding class file
lives in such an observer directory gets attached to the observer.  

Events travel from the root of the event tree to its leafs.

Thus an event tree is a observer tree whose leafs are populated by listeners and
that gets traveled by events.

If you name your observer leafs and listeners in an explicit way, all you need 
to get a basic understanding of the event tree is hitting the command `tree` in
the trees root directory.

## The event

Every event is tied to an event tree. 

Events can be configured to restrict their traveling by three parameter `start`, 
`destination` and `maxDepth`. Each event travels from the `start` observer 
vertice to the `destination` observer vertice in a linear manner. Thereafter it 
behaves as if it performs a wide search on the remaining tree. `maxDepth` 
restricts the overall travel to vertices with a maximal distance from the 
`start` vertice of `maxDepth`. If `maxDepth` is configured to `null` there is 
no restriction. If `destination` is `null` the `start` vertice is also the 
`destination` vertice. And if `start` is `null` the event starts at the root of 
the observer tree.

This gives birth to four event phases:
- `start` - the event has not traveled yet.
- `before`- the event is between its `start` and its `destination` vertice.
- `destination` - the event is on its `destination` vertice. (If `destination`
    is null there is no `destination` phase.)
- `beyond` - the event has traveled beyond its `destination` vertice.

Listeners can listen to one, two, three or all four event phases.

If a dependency container is injected into the `eventDispatcherFactory` each
event has a getter for this container, such that the listeners can be used as 
front controllers. ([eArc/router](http://github.com/Koudela/eArc-router) will 
map http requests using event trees as of v1.0)

## The listener

The listener can attach a payload to an event and read the payload other 
listener have attached. By this you can wire your application through one or 
more event trees.

You can determine by the constant `EARC_LISTENER_PATIENCE` the order in which 
the listener get called by their observer.

The event phase(s) the listener should listen to can be expressed by the 
constant `EARC_LISTENER_TYPE`. You can use the `EventRouter` constants
`PHASE_START`, `PHASE_BEFORE`, `PHASE_DESTINATION` and `PHASE_BEYOND` in a 
bit field (concat them by `|`). If the constant `EARC_LISTENER_TYPE` is not used 
the `PHASE_ACCESS` is used, which is a shortcut for listening to all four event 
phases. 

Listeners can manipulate the traveling of events. They can silence them by
`$event.silence()`, such that no listener in the same directory can
listen to that specific event anymore.  They can tie them to their directory
by `$event.tie()`, such that the tied event can only travel the 
tree where the current observer vertice is the root. They can stop its travel on 
this tree part by `$event.terminate()`. And they can even kill the event by
`$event.kill()`. 

## Building trees at runtime

If you use event trees as complex iterators it may not be possible to restrict
yourself to predefined tree structures. In this case (and maybe others) where it 
is not beneficial to stick to close to the explicit programming paradigm you
are encouraged to use the `ObserverTree` and `ObserverLeaf` classes directly.  

## Conclusion

With this library at hand you can tie the main part of your process-logic to the 
event trees (plus exposing lifecycle hooks to it) while keeping your other
objects decoupled doing what objects can do best: handling state. 

Of course you can stay to your architectural style as well, use your preferred 
framework furthermore and add event trees as an explicit way of event handling.

## Example

As always you can use the composer autoloader.
```php
include 'path/to/your/project/dir/' . 'vendor/autoload.php';
``` 

First of all you need an `ObserverTreeFactory`.

```php
use eArc\eventTree\Transformation\ObserverTreeFactory;

$OTF = new ObserverTreeFactory(
    '/absolute/path/to/your/eventTree/root', 
    'your\\eventTree\\root\\namespace'
);
```

Now your code knows where your event trees live. You can use `toString()` to 
debug any tree. 

```php
echo $OTF->get('myFirstObserverTree')->toString();
```

Inject the `ObserverTreeFactory` into an `EventDispatcherFactory`. As second 
argument you may wish to inject a dependency injection container.  
 
```php
use eArc\eventTree\Event\EventDispatcherFactory;

$EDF = new EventDispatcherFactory($OTF, null);
```

`build()` gives you a new `EventDispatcher`. You can configure the event which
is going to be dispatched with `tree()`, `start()`, `destination()` and 
`maxDepth()` in any order you like. And then dispatch it with `dispatch()`; 


```php
$EDF->build()->tree('myFirstObserverTree')->maxDepth(null)->dispatch();
```

A listener may look like this.

```php
# /absolute/path/to/your/eventTree/root/myFirstObserverTree/preExport/myFooListener.php

namespace your\eventTree\Namespace\myFirstObserverTree\preExport;

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

Every listener can trigger new events. `$event->new()` is a shortcut for  
`$event->getEventDispatcherFactory->build()` and `$event->clone()` for
`$event->getEventDispatcherFactory->build($event)`. Both are giving an 
`EventDispatcher` back. You can use the `EventDispatcher` to configure and
dispatch the event as before. 

It might be worth to mention that the trees get initialized when they first get
called. eArc/eventTree will not construct any of your observer or listener
classes before.

[event](doc/event.md) | [listener](doc/listener.md) | 
[observer](doc/observer.md) | [tree](doc/tree.md) | [routing](doc/routing.md)

#TODO
- add detailed object documentation
- add Behat