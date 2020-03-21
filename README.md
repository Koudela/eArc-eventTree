# eArc-eventTree

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

## Table of contents
 
 - [Install](#install)
 - [Bootstrap](#bootstrap)
 - [Configure](#configure)
 - [Use](#use)
   - [The observer tree](#the-observer-tree)
   - [The listener](#the-listener)
   - [The event](#the-event)
   - [The propagation Type](#the-propagation-type)
   - [Dispatching Events](#dispatching-events)
 - [Advanced Usage](#advanced-usage)
   - [Patience](#patience)
   - [Listening to specific traveling phases](#listening-to-specific-traveling-phases)
   - [Manipulating the traveling of dispatched events](#manipulating-the-traveling-of-dispatched-events)
   - [Extending (third party) observer trees](#extending-third-party-observer-trees)
 - [Conclusion](#conclusion)
 - [Releases](#releases)
   - [Release 1.0](#release-10)
   - [Release 0.0](#release-00)

## Install

```bash
$ composer install earc/event-tree
```

## Bootstrap

earc/observer uses [earc/di](https://github.com/Koudela/eArc-di) for dependency
injection. 

```php
use eArc\DI\DI;

DI::init();
```

Place the above code in the section where your script/framework is 
bootstrapped.

## Configure

The event trees live in a folder in your project directory. It is possible to 
import and even extend trees from other projects. Hence the 
`earc.vendor_directory` parameter has to be set.

```php
di_import_param(['earc' => ['vendor_directory' => __DIR__.'/../../vendor/']]);
```

Best practice is to have only one directory which is the root for all your event
trees. This constrain ensures that every developer who is or will be engaged in 
your project can easily keep track of all event trees.

```php
$directories = di_param('earc.event_tree.directories', []);
$directories['../path/to/your/eventTree/root/folder'] = '\\your\\eventTree\\root\\namespace'; 
di_import_param(['earc' => ['event_tree' => ['directories' => $directories]]]);
```

The path of the root folder has to be relative to your projects vendor directory.

## Use

Since we use the native tree data structures of the modern operating systems to
organize our code it is a tiny step to put them to use to decouple code and
define data processing structures.

It is as easy as it can get.
 
1. Choose a directory where all your observer trees should live in. 
([read `configure` for more details](#configure))
2. Expand this tree root with as many subdirectories as you need observer leafs.
3. Save your listener in the directory where it should get attached to the 
observer. ([read `the listener` for more details](#the-listener))
4. Dispatch your events. ([read `the event` for more details](#the-event))

### The observer tree

Every event tree is in fact an observer tree. Every single directory maps to an 
event observer. A class which implements the interface 
`eArc\Observer\Interfaces\ListenerInterface` and which corresponding class file 
lives in such an observer directory gets attached to the observer. 
([read `the listener` for more details](#the-listener))

Events travel from the root of the tree to its leafs.

Thus an event tree is an observer tree whose leafs are populated by listeners 
and is traveled by events in a well defined manner.
([read `the event` for more details](#the-event))

If you name your observer leafs and listeners in an explicit way, all you need 
to get a basic understanding of the event tree is hitting the command `tree` in
the trees root directory or use the [view-tree](#view-observer-tree) command
line tool.

### The listener

The event listener is the bridge between the event and the business logic of 
your application. It can attach a payload to an event and read the payload other 
listener have attached. By this you can wire your application through one or 
more event trees.

Like a front controller is attached to a route/request an event listener is 
attached to an observer/event. Best practice is to write small listener that
consists only of logic concerning the application flow. No business logic or 
persistence calls should happen in an event listener.

Each Listener has to implement the `ListenerInterface`.

```php
use eArc\Observer\Interfaces\ListenerInterface;
use eArc\Observer\Interfaces\EventInterface;

class MyListener implements ListenerInterface
{
    public function process(EventInterface $event): void
    {
        // The listener logic goes here...
    }
}
```

It gets autoloaded and initialised on the first visit of an event.

### The event

Every event is tied to the observer tree described above. Events have to inherit 
from the `TreeEvent`.

```php
use eArc\EventTree\TreeEvent;

class MyEvent extends TreeEvent
{
    // Code to handle information specific for your event...
}
```

Every event is initialized with a `PropagationType`.

```php
use eArc\EventTree\Propagation\PropagationType;

$event = new MyEvent(new PropagationType());
``` 

### The propagation Type

The propagation type restricts the traveling of the event.

The first parameter `start` determines the starting point in the directory tree 
relative to the events tree root. For example given a value of 
`['product','export']` the event would start in the folder 
`event-tree-root/product/export`. If the `start` is an empty array the event
starts at the root of the event tree.

The second parameter `destination` determines how far the event should travel in 
a linear manner. For example given a value of 
`['init','collect','process','finish']` the event would travel from the `../export`
folder to the `../export/init`, to the `../export/init/collect`, to the
`../export/init/collect/process` and thereafter to the 
`../export/init/collect/process/finish` folder. If the `destination` is an empty
array the `start` folder is also the `destination` folder.

After the `destination` the event behaves as if it performs a a wide search on 
the remaining tree. 

The last parameter `maxDepth` restricts the overall travel to folder/vertices 
with a maximal distance from the `destination` folder/vertice of `maxDepth`. 
If `maxDepth` is configured to `null` there is no restriction. For example if 
`0` is supplied as argument the event would die after visiting the destination 
observer leaf.

The `PropagationType` is immutable. Thus these criteria cannot be altered once 
the event build. They define the four [traveling phases](#listening-to-specific-traveling-phases).

### Dispatching Events

Every `TreeEvent` comes with a `dispatch()`method. You do not need a dispatcher.
It is called upon internally by the dependency injection magic of
[earc/di](https://github.com/Koudela/eArc-di).

Note: You can dispatch an events instance only once.

## Advanced Usage

### Patience

If a listener implements the `SortableListenerInterface` it can define its
patience as a float. (Otherwise it has a patience of 0.)

```php
use eArc\Observer\Interfaces\ListenerInterface;
use eArc\Observer\Interfaces\EventInterface;
use eArc\EventTree\Interfaces\SortableListenerInterface;

class MyListener implements ListenerInterface, SortableListenerInterface
{
    public function process(EventInterface $event): void
    {
        // The listener logic goes here...
    }

    public static function getPatience() : float
    {
        return -12.7;
    }

}
```

As smaller the patience the sooner the listener is called.

Hint: If two listener have the same patience you cannot rely on the order they 
are called.    

### Listening to specific traveling phases

The `PropagationType` gives birth to four event phases:
- `start` - the event has not traveled yet.
- `before`- the event is between its `start` and its `destination` vertice.
- `destination` - the event is on its `destination` vertice. 
- `beyond` - the event has traveled beyond its `destination` vertice.

If `destination` is empty there is no `destination` phase nor a `beyond` phase.
Same applies if the `depth` parameter smaller than the `destinations` length.

Listeners implementing the `PhaseSpecificListenerInterface` can listen to one, 
two or three instead of all four event phases. Use the `ObserverTreeInterface` 
constants `PHASE_START`, `PHASE_BEFORE`, `PHASE_DESTINATION` and `PHASE_BEYOND`.
If you listen to more then one use a bit field (concat them by `|`). 

```php
use eArc\Observer\Interfaces\ListenerInterface;
use eArc\Observer\Interfaces\EventInterface;
use eArc\EventTree\Interfaces\PhaseSpecificListenerInterface;
use eArc\EventTree\Interfaces\Transformation\ObserverTreeInterface;

class MyListener implements ListenerInterface, PhaseSpecificListenerInterface
{
    public function process(EventInterface $event): void
    {
        // The listener logic goes here...
    }

    public static function getPhase() : int
    {
        // Listening to the phases destination and beyond only.
        // Keep in mind it is only one pipe. It is a bit field not a boolean.
        return ObserverTreeInterface::PHASE_DESTINATION | ObserverTreeInterface::PHASE_BEYOND;
    }

}
```

If the `PhaseSpecificListenerInterface` is not used the `PHASE_ACCESS` is assumed, 
which is a shortcut for listening to all four event phases. 

### Manipulating the traveling of dispatched events

Listeners can not change the immutable `PropagationType`, but they can restrict
the traveling of events. This comes handy if you want to implement the chain of 
responsibility pattern or similar using an event tree. 

Each listener that is called by its corresponding observer leaf can inhibit the 
further traveling of the event by four methods of the event.

They can stop the event from traveling any further by killing it.

```php
use eArc\Observer\Interfaces\ListenerInterface;
use eArc\Observer\Interfaces\EventInterface;

class MyListener implements ListenerInterface
{
    public function process(EventInterface $event): void
    {
        // ...
        $event->kill();
        // ...
    }
}
```

Even the remaining listeners of the same directory won't get called.  

`silence()` forces the event to leave the current observer and discards its 
listener stack.  

```php
        // ...
        $event->silence();
        // ...
```

The event travels to the next leaf (if any) directly. No listener in the same 
directory can listen to that specific event anymore.  

`terminate()` stops the event from visiting the leafs that are direct or indirect
children of the observer leaf.

```php
        // ...
        $event->terminate();
        // ...
```

But the current observer does not stop his work on the current listener stack.

Keep in mind in the `beyond` phase there are active observer who are not children
oder parents of the current observer.

You can dismiss them by calling `tie()`.

```php
        // ...
        $event->tie();
        // ...
```

The event is tied to the current observer and its children. The events travel on
any neighboring leafs is stopped.

### Extending (third party) observer trees

If you use observer trees for a library there are scenarios where a user of 
the library need to use, extend or overwrite the supplied observer trees. There
is no suitable way to write into the vendor directory. To overcome this the 
earc event tree provides a way to inherit trees from other places and blacklist
listeners.

Every directory defined tree has a root directory he lives in and a root namespace
for autoloading. You can specify as many `earc.event_tree.directories` as you want.
 
```php
$directories = di_param('earc.event_tree.directories', []);
$directories['../src/MyProject/Events/TreeRoot'] = 'MyProject\\Events\\TreeRoot';
$directories['Framework/src/EventTreeRoot'] = 'Framework\\EventTreeRoot';
$directories['ShopCreator/Engine/events/tree/root'] = 'ShopCE\\events\\tree\\root';
di_import_param(['earc' => ['event_tree' => ['directories' => $directories]]]);
```

The configured roots are seen as one big root. If there are identical paths relative
to the root the listeners are bundled in one observer leaf.

Listeners in corresponding trees will have different namespaces due to the 
autoloading necessity and can therefore not be overwritten. To unregister them
add their fully qualified class name to the `earc.event_tree.blacklist`.

```php
$blacklist = di_param('earc.event_tree.blacklist', []);
$directories['Framework\\EventTreeRoot\\Some\\Path\\SomeListener'] = true;
$directories['Framework\\EventTreeRoot\\Some\\Other\\Path\\SomeOtherListener'] = true;
$directories['ShopCE\\events\\tree\\root\\a\\third\\path\\to\\a\\ThirdUnwantedListener'] = true;
di_import_param(['earc' => ['event_tree' => ['blacklist' => $blacklist]]]);
```

Hint: Listener must be blacklisted before the `ObserverTree` is build. Therefore
as soon an event has be dispatched changes to the blacklist are not recognised
anymore. (You can force the dependency injection system to drop references to 
**ALL** old build objects using `di_clear_cache`.)

### View Observer Tree

To get a picture of a observer tree and the listener living in it use the command
line tool `view-tree`.

```shell script
vendor/earc/event-tree/tools/view-tree 'path/tree/root/1' 'path/tree/root/2' 'path/tree/root/3'
```

## Conclusion

With this library at hand you can tie the main part of your process-logic to the 
event trees (plus exposing lifecycle hooks to it) while keeping your other
objects decoupled doing what objects can do best: handling state. 

Of course you can stay to your architectural style as well, use your preferred 
framework furthermore and add event trees as an explicit way of event handling.

## Releases

### Release 1.0

- simplified syntax
- use of earc/di as dependency injection framework
- new `view-tree` command line tool
- dropped support for building trees at runtime

### Release 0.0

- initial release


TODO 
- Implement: print trees tool.
- Implement: TESTS.

- fix make wideSearch not deepSearch