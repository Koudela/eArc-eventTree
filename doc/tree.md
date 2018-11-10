[event](event.md) | [listener](listener.md) | 
[observer](observer.md) | [index](../README.md) | [routing](routing.md)

# The observer tree

The observer tree is the heart of the eArc/eventTree package. It is a composite 
of observer classes that pass the event from the event routers to the event
listeners in a well defined manner.

There are two ways of building an observer tree:
- using the directory structure and the `ObserverTreeFactory` makes your
observer trees very explicit, which is the recommended way if possible. 
- building the tree out of the classes `ObserverTree` and `ObserverLeaf` 
directly, giving you the freedom to alter your trees at runtime. 

To give you a deeper understanding we handle the second case although not
recommended first.

## Building trees at runtime

Every Tree starts with a root. Basically it is an `ObserverLeaf` with an 
identifier.

```php
use eArc\eventTree\TreeObserverTree;

$tree = new ObserverTree('myIdentifier');
```

As to all `ObserverLeaf` you can register or unregister a listener to it or
add children. 

An observer tree can only grow as there is no remove child
method given - in fact such functionality that is hard to design coherently.

*Please note that a newly added neighbour may be visited in the `beyond` 
event phase. If so or not is not defined. This behaviour may change in some
versions even at runtime.* 

```php
$tree->registerListener('fully\qualified\name\of\listener\Foo', 'access', -80);

$child = $tree->addChild('leafIdentifier');

$tree->unregisterListener('fully\qualified\name\of\listener\Foo');
```

`registerListener` takes the fully qualified name of the listener or its 
dependency container name as first argument. Second is the event phase the
listener listens to. The third argument is an integer representing the patience
of the listener. It determines the position in the listener stack of the 
observer. The higher the patience the later it gets called by the observer leaf.  

*Theoretically you can execute the listener stack directly using an event 
router.* *__This is strongly discouraged.__*

You can always access the root, the parent or its children from a leaf.
 
```php
$child->getRoot();

$child->getParent();

$child->getChildren();

$child->getChild('leafIdentifierOfTheDirectChild');
```

This is all straight forward but not as easy as the first case.

## Defining trees using the directory structure

Since we use the native tree data structures of the modern operating systems to
organize our code it is a tiny step to put them to use to decouple code and
define data processing structures.

It is as easy as it can get.
 
1. Choose a directory where all your observer trees should live in.
2. For every observer tree root create an directory.
3. Expand the tree root with as many subdirectories as you need observer leafs.
4. Save your listener in the directory where it should get attached to the 
observer. If you wanna attach a listener to more than one observer leaf or
want your listener to listen to more than one event phase, use listeners that 
do nothing but forward the call. 
5. Determine the right namespace for autoloading.
6. Initialise your `ObserverTreeFactory`.
7. The method `get()` returns your tree according to the identifier supplied,
and if not build yet it builds the tree. You can also inject the 
`ObserverTreeFactory` into the `EventDispatcherFactory` and forget about its
existence for the rest of the coding.

## Extending (third party) observer trees

If you use observer trees for a library there are scenarios where a user of 
the library need to use, extend or overwrite the supplied observer trees. There
is no suitable way to write into the vendor directory. To overcome this the 
`ObserverTreeFactory` provides a way to inherit trees from other places and
to blacklist listeners.

Every directory defined tree has a directory he lives in and a namespace
for autoloading. If an array containing arrays with these two parameters are
supplied to the factories constructor as third argument the corresponding
event trees will be loaded if the main event tree directory has at least the
root of the tree in his directory. Trees with the same root identity are
composed to one tree.

Listeners in corresponding trees will have different namespaces due to the 
autoloading necessity and can therefore not be overwritten. To unregister them
add their fully qualified class name or their container name as key to the
ignore array which is the fourth argument of the `ObserverTreeFactory`. 

[event](event.md) | [listener](listener.md) | 
[observer](observer.md) | [index](../README.md) | [routing](routing.md)
