# eArc-eventTree
```php
use eArc\eventTree\Event\EventDispatcherFactory;
use eArc\eventTree\Transformation\ObserverTreeFactory;

include __DIR__ . '/vendor/autoload.php';

$OTF = new ObserverTreeFactory('path/to/my/eventTree/root', 'My\\eventTree\\Namespace', [], 
['My\\eventTree\\Namespace\\ObserverTree3\\preExport\\Listener2'
 => true]);
$EDF = new EventDispatcherFactory($OTF, null);
$EDF->build(null)->tree('ObserverTree3')->maxDepth(null)->dispatch();
echo $OTF->get('ObserverTree1')->toString();
echo $OTF->get('ObserverTree2')->toString();
echo $OTF->get('ObserverTree3')->toString();
```

```php
namespace My\eventTree\Namespace\ObserverTree3\preExport;

use eArc\eventTree\Event\Event;
use eArc\eventTree\Interfaces\EventListener;

class MyListenerFoo implements EventListener
{
    const EARC_LISTENER_PATIENCE = 20;
    const EARC_LISTENER_TYPE = 'access';

    public function processEvent(Event $event)
    {
        ...
    }
}
```
#TODO
- add documentation
- add Behat