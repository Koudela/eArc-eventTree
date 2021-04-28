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
 - [Basic usage](#basic-usage)
   - [The observer tree](#the-observer-tree)
   - [The listener](#the-listener)
   - [The event](#the-event)
   - [The propagation type](#the-propagation-type)
   - [Dispatching events](#dispatching-events)
 - [Advanced usage](#advanced-usage)
   - [Patience](#patience)
   - [Listening to specific traveling phases](#listening-to-specific-traveling-phases)
   - [Manipulating the traveling of dispatched events](#manipulating-the-traveling-of-dispatched-events)
   - [Custom events](#custom-events)
   - [Subsystem handling](#subsystem-handling)
   - [Extending (third party) observer trees](#extending-third-party-observer-trees)
   - [The redirect directive](#the-redirect-directive)
   - [The lookup directive](#the-redirect-directive)
   - [Performance optimization](#performance-optimization)
   - [The view tree tool](#the-view-tree-tool)
 - [Conclusion](#conclusion)
 - [Releases](#releases)
   - [Release 2.1](#release-21)
   - [Release 2.0](#release-20)
   - [Release 1.1](#release-11)
   - [Release 1.0](#release-10)
   - [Release 0.0](#release-00)

## Install

```bash
composer install earc/event-tree
```

## Bootstrap

earc/event-tree uses [earc/di](https://github.com/Koudela/eArc-di) for dependency
injection and [earc/core](https://github.com/Koudela/eArc-core) for the configuration
file. 

```php
use eArc\Core\Configuration;
use eArc\DI\DI;

DI::init();
Configuration::build();
```

Place the above code in the section where your script/framework is bootstrapped
or your `index.php`.

## Configure

Put a file named `.earc-config.php` beneath the vendor dir. Its the configuration 
file for all the earc components.

```php
<?php #.earc-config.php

return ['earc' => [
    'is_production_environment' => false,
    'event_tree' => [
        'directories' => [
            '../path/to/your/eventTree/root/folder' => '\\your\\eventTree\\root\\namespace',
        ]
    ]
]];

```

The event trees live in a folder in your project directory. It is possible to 
import and even extend trees from other projects.

Best practice is to have only one directory which is the root for all your event
trees. This constrain ensures that every developer who is or will be engaged in 
your project can easily keep track of all event trees.

The path of the root folder has to be relative to your projects vendor directory
or an absolute path.

## Basic Usage

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
listener have attached. By this you can wire your application through your event 
tree.

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

A listener is autoloaded and initialised on the first visit of an event.

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
`../export/init/collect/process/finish` folder. If the `destination` parameter is 
an empty array the `start` folder is also the `destination` folder.

After the `destination` the event behaves as if it performs a
[wide search (BFS)](https://en.wikipedia.org/wiki/Breadth-first_search) on the 
remaining tree. (The directories are sorted by name ascending.)

The last parameter `maxDepth` restricts the overall travel to folder/vertices 
with a maximal distance from the `destination` folder/vertice of `maxDepth`. 
If `maxDepth` is configured to `null` there is no restriction. For example if 
`0` is supplied as argument the event would die after visiting the destination 
observer leaf.

The `PropagationType` is immutable. Thus, these criteria cannot be altered once 
the event build. They define the four 
[traveling phases](#listening-to-specific-traveling-phases).

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

Hint: If two listeners have the same patience you cannot rely on the order they 
are called.    

### Listening to specific traveling phases

The `PropagationType` gives birth to four event phases:
- `start` - the event has not traveled yet.
- `before`- the event is between its `start` and its `destination` vertice.
- `destination` - the event is on its `destination` vertice. 
- `beyond` - the event has traveled beyond its `destination` vertice.

If `destination` is empty the `start` phase is also a `before` phase 
and a `destination` phase.

Listeners implementing the `PhaseSpecificListenerInterface` can listen to one, 
two or three instead of all four event phases. Use the `ObserverTreeInterface` 
constants `PHASE_START`, `PHASE_BEFORE`, `PHASE_DESTINATION` and `PHASE_BEYOND`.
If you listen to more than one use a bit field (concat them by `|`). 

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

Listeners cannot change the immutable `PropagationType`, but they can restrict
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
        $event->getHandler()->kill();
        // ...
    }
}
```

Even the remaining listeners of the same directory won't get called.  

`forward()` forces the event to leave the current observer and discards its 
listener stack.  

```php
        // ...
        $event->getHandler()->forward();
        // ...
```

The event travels to the next leaf (if any) directly. No listener in the same 
directory can listen to that specific event anymore.  

`terminate()` stops the event from visiting the leafs that are direct or indirect
children of the observer leaf.

```php
        // ...
        $event->getHandler()->terminate();
        // ...
```

But the current observer does not stop his work on the current listener stack.

Keep in mind in the `beyond` phase there are active observers who are not children
oder parents of the current observer.

You can dismiss them by calling `tie()`.

```php
        // ...
        $event->getHandler()->tie();
        // ...
```

The event is tied to the current observer and its children. The events travel on
any neighboring leafs is stopped.

### Custom events

### Customized events

To keep your components decoupled the event should be the only place where 
runtime information is kept (when a listener has finished his work).
As the runtime information is app specific it is part of your architectural
responsibility to design your own events.

Best practice is to use interfaces to describe the runtime information. Follow
the interface segregation principle 
([ISP](https://en.wikipedia.org/wiki/Interface_segregation_principle)). Design
objects that implement the interface(s) and extend the `eArc\EventTree\TreeEvent` 
to provide these objects.

As example what it could look like for an import process.

```php
use eArc\EventTree\TreeEvent;
use eArc\EventTree\Propagation\PropagationType;

interface ImportInformationInterface
{
    public function getRunnerId(): int;
    public function addWarning(Exception $exception);
    public function getWarnings(): array;
    public function getImported(): array;
    public function getChanged(): array;
}

class ImportInformation implements ImportInformationInterface
{
    protected $runnerId;
    protected $warnings = [];
    protected $imported = [];
    protected $changed = [];

    //...
}

class AppRouterEvent extends TreeEvent
{
    protected $runtimeInformation;

    public function __construct(PropagationType $propagationType)
    {
        parent::__construct($propagationType);

        $this->runtimeInformation = di_get(ImportInformation::class);              
    }

    public function getRI(): ImportInformationInterface {/*...*/}
}
```

Now all import information that has to be exchanged between your listeners
is exposed, easy to find and easy to understand.

### Subsystem handling

If you need an event that triggers only a subset of listeners, you can modify the 
`getApplicableListener()` method provided by the `EventInterface`. It returns an 
array of all listener interfaces that are called by the event.

For example if a core app supports several versions you can use separate listeners 
for different versions this way. If a controller supports more than one 
version it simply implements more than one listener interface. 

Other use cases where this functionality comes handy: 
- Some part of the app is only available in some country or to some language.
- Some part of the app is only active in debug mode.
- The app behaviour changes significantly for power users paying more money.
- Different parts of the app can be toggled.
- Different phases of processing events on the same part of the tree.

### Extending (third party) observer trees

If you use observer trees for a library there are scenarios where a user of 
the library need to use, extend or overwrite the supplied observer trees. There
is no suitable way to write into the vendor directory. To overcome this the 
earc event tree provides a way to inherit trees from other places and blacklist
listeners.

Every directory defined tree has a root directory he lives in and a root namespace
for autoloading. You can specify as many `earc.event_tree.directories` as you want.
 
```php
di_import_param(['earc' => ['event_tree' => ['directories' => [
    '../src/MyProject/Events/TreeRoot' => 'MyProject\\Events\\TreeRoot',
    'Framework/src/EventTreeRoot' => 'Framework\\EventTreeRoot',
    'ShopCreator/Engine/events/tree/root' => 'ShopCE\\events\\tree\\root',
]]]]);
```

The configured roots are seen as one big root. If there are identical paths relative
to the root the listeners are bundled in one observer leaf.

Listeners in corresponding trees will have different namespaces due to the 
autoloading necessity and can therefore not be overwritten. To unregister them
add their fully qualified class name to the `earc.event_tree.blacklist`.

```php
use Framework\EventTreeRoot\Some\Path\SomeListener;
use Framework\EventTreeRoot\Some\Other\Path\SomeOtherListener;
use ShopCE\events\tree\root\a\third\path\to\a\ThirdUnwantedListener;

di_import_param(['earc' => ['event_tree' => ['blacklist' => [
    SomeListener::class => true,
    SomeOtherListener::class => true,
    ThirdUnwantedListener::class => true,
]]]]);
```

Hint: Listener must be blacklisted before the `ObserverTree` is build. Therefore
as soon an event has be dispatched changes to the blacklist are not recognised
anymore. (You can force the dependency injection system to drop references to 
**ALL** old build objects using `di_clear_cache`.)

### The redirect directive

The tree extending/inheritance mechanism has one significant drawback: If the inherited 
tree is not part of your repository you can not change it. That hinders refactoring 
the tree. Or worse if the inherited tree change you must change your own tree to 
retain the functionality. The tool to handle this situation smoothly is the `.redirect` 
directive.

Its a file named `.redirect` you can place in the directory to manipulate the observer 
leafs. Every line is a redirection. At the beginning of the line you put the sub folder 
name you want to redirect (it does not need to exist) and at second place separated 
by a blank you put the target path (it has to exist in at least one event tree directory).
The target path has to be relative to the event trees root directory, but you can
use `~/` as a shortcut to reference the current directory.

To exclude an existing or inherited directory just leave the target empty. `.redirect`
directives are part of the tree inheritance. If several `.redirect` directives
of the same path exists naming the same sub folder the ordering of the 
´earc.event_tree.directories´ is important. The directives are overwritten in
the order their directory tree are registered. You can use the target shortcut `~` 
to cancel an redirect. 

```
lama creatures/animals/fox #redirects events targeting the lama subfolder to creatures/animals/fox
eagle ~/extinct/2351       #redirects events targeting the eagle subfolder to the extinct/2351 subfolder
maple                      #hides the maple subdirectory from the events
tiger ~                    #cancels all redirects for tiger made by the event trees inherited so far
```

For example the rewrite of the path `routing/imported/products` to `routing/products/imported` 
would take two steps (for each rewritten part one):

1 ) Place into the `routing` directory the `.redirect` directive
```
products routing/imported
imported
```

2 ) Place into the `routing/imported` directory the `.redirect` directive
```
imported ~/products
products
``` 

Obviously using this you can not rely on the directory arguments of the route
only on the parameters.

To rewrite the base leafs put the `.redirect` directive into the event tree root.

Hint: The namespace constrains on characters and reserved words limits the usable 
leaf names. The `.redirect` directive allows you to define a leaf name with no 
restrictions at all.
 
### The lookup directive

Every `.redirect` directive you use destroys a bit of the clarity the explicit
design of the event tree gives to you. Therefore making massive use of the `.redirect` 
directive is an anti pattern. If you need to redirect quite a bit of the tree
it is better to rewrite it and use the `.lookup` directive to include the listeners
of the old tree.

Like the `.redirect` directive the `.lookup` directive is a plain text file. 
If you put a path in there it will be included. That means every listener in the 
linked leaf of the event tree will be handled as if it would reside in the current
leaf. Every line is an separate include. The path has to be relative to the event
tree root.

If we use the example of the rewrite of the path `routing/imported/products` to 
`routing/products/imported` again:

1 ) Make a directory path `routing/products/imported`

2 ) Place into the `routing/imported` directory the `.lookup` directive
```
routing/imported/products
``` 

3 ) To cancel the old path place into the `routing` directory the `.redirect` directive
```
imported
```

### Performance optimization

The concept of the event tree is deeply rooted in the file system. File access is
not cheap in terms of time and can be a bottle neck. If you use a file cache like
[ACPu](https://www.php.net/manual/en/book.apcu.php) it is worth considering loading
the event tree structure (even if it may be huge) into memory. This is done via 
a static file. Please note that earc/event-tree writes this file only if it is 
not found. If you make changes to the tree you have to delete the file or regenerate 
it via the commandline script `build-cache` manually.

To use the cache add the following lines to the `event_tree` section in your `.earc-config.php`.

```php
#.earc-config.php
//...
    'event_tree' => [
        'use_cache' => true,
        'cache_file' => '/absolute/path/to/your/cache_file.php', 
        'report_invalid_observer_node' => false,
    ],
//...
```

If you omit the `cache_file` parameter it defaults to `/tmp/earc_event_tree_cache.php`.

The `report_invalid_observer_node` defaults to true. Setting it to false suppresses
the `InvalidObserverNodeException` in the cases the tree has some false configured
subtree. Thus the tree cache file can be written even if some part of the tree fails.

Hint: If you use a customized config file location you have to pass the file location 
as parameter to the script `vendor/earc/event-tree/tools/build-cache`. 

### The view tree tool

To get a picture of a observer tree and the listener living in it use the command
line tool `view-tree`.

```shell script
vendor/earc/event-tree/tools/view-tree
```

Hint: If you use a customized config file location you have to pass the file location 
as parameter. 

## Conclusion

With this library at hand you can tie the main part of your process-logic to the 
event trees (plus exposing lifecycle hooks to it) while keeping your other
objects decoupled doing what objects can do best: handling state. 

Of course you can stay to your architectural style as well, use your preferred 
framework furthermore and add event trees as an explicit way of event handling.

## Releases

### Release 2.1

- PHP ^7.3 || ^8.0

### Release 2.1

- every event has a `before` and a `destination` phase 

### Release 2.0

- bootstrap via [earc/core](https://github.com/Koudela/eArc-core)
- caching of the observer tree

### Release 1.1

- redirect directive
- lookup directive

### Release 1.0

- simplified syntax
- use of earc/di as dependency injection framework
- new `view-tree` command line tool
- dropped support for building trees at runtime

### Release 0.0

- initial release
