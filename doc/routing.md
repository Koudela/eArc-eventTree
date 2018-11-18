[event](event.md) | [listener](listener.md) | 
[observer](observer.md) | [tree](tree.md) | [index](../README.md)

# Routing events on the observer tree

Events always travel into the direction of the tree leafs.

Their traveling is affected by two parties:
- the event dispatcher
- the event listener

Their traveling can be splitted into four phases: 
- `start` (`EventRouter::PHASE_START`)
- `between` (`EventRouter::PHASE_BETWEEN`)
- `destination` (`EventRouter::PHASE_DESTINATION`)
- `beyond` (`EventRouter::PHASE_BEYOND`)

## the influence of the event dispatcher

The event dispatcher can define three criteria for the routing process:

- `start`: This is the observer leaf where the event first hits the tree. It 
can be configured through the `EventDispatcher` method `start()`. No argument,
`null` or `[]` as argument let the event start at the tree root which is best 
practice if possible. `['my', 'explicit', 'start']` would let the event start 
a leaf `treeRoot->my->explicit->start`.  
- `destination`: This is where the event heads for. It can be configured by the
the `EventDispatcher` method `destination()`. if no argument, `null` or `[]` 
was supplied the element directly switches from the `start` phase to the `beyond`
phase.
- `maxDepth`: This is the max leafs the event travels count from start in one
line. It can be set by the `EventDispatcher` method `maxDepth()`. If the
argument is omitted or `null` is supplied there is no limit to the depth. For
example if `0` is supplied as argument the event would die after visiting the 
starting observer leaf.      
  
These criteria cannot be altered once the event is dispatched. They define
the four event phases.

## the influence of the event listeners

Each event listener that is called by its corresponding observer leaf can 
inhibit the further traveling of the event by four methods of the `Event`:

- `silence()` The current observer discards its listener stack. The event 
travels to the next leaf (if any) directly. 
- `tie()` If the event travels or was traveling any neighboring leafs the 
propagation of the event on the neighboring leafs is stopped. This method 
is only relevant in the `beyond` phase. 
- `terminate()` The event does not visit the leafs that are direct or indirect
children of the observer leaf. But the current observer does not stop his work 
on the current listener stack.
- `kill()` The event does stop its traveling completely. The current observers 
listener stack remains intact. It the shortcut for calling `tie()` and
`terminate()` in a row. 

## the traveling phases

After the `start`, in the `between` and before the `destination` phase 
each leaf leads the event depper into the tree.

In the `destination` and the `beyond` phase the traveling scheme is altered to 
a wide search on the accessed tree parts. This means all children of the 
destination leaf are visited. Then all children of these children are visited. 
When this is completed all children of the children of the children of the 
destination leaf are visited and so on provided no listener inhibits the 
traveling.    

The listener can listen to a specific phase or to more phases. The
`EventRouter::PHASE_ACCESS` type is shortcut for being active in all phases.

## other influences

There are no other influencing factors. The router is called by the event
dispatcher and the observer only. If any other object has access to the router 
object directly or is even aware of its existence consider it as a bug.

[event](event.md) | [listener](listener.md) | 
[observer](observer.md) | [tree](tree.md) | [index](../README.md)
