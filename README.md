# Real time events #

## How to use in plugins ##

### Notify about an event in PHP: ###

```
\tool_realtime\api::notify($context, $component, $area, $itemid, $payload);
```

### Subscribe and listen to events: ###

Subscribe in PHP:
```
\tool_realtime\api::subscribe($context, $component, $area, $itemid);
```
Listen in Javascript:
```
require(['core/pubsub', 'tool_realtime/api'], function(PubSub, RealtimeApi) {
    PubSub.subscribe(RealtimeApi.channelName(context, component, area, itemid),
        function(eventData) {
            // access context, component, area, itemid, payload as keys in event data
            // example for context
            document.write(eventData['context']);
            // access payload by key
            document.write(eventData['payload']['testkey']);
        }
    );
});
```
OR
Dynamic Javascript Subscription
Initiliase in PHP:
```
tool_realtime\api::init();
```
then in Javascript subscribe using:
```
require(['core/pubsub', 'tool_realtime/events', 'tool_realtime/api'], function(PubSub, RealTimeEvents, api) {
    api.subscribe(context, component, area, itemid, function(eventData) {
        // access context, component, area, itemid, payload as keys in event data
        // example for context
        document.write(eventData['context']);
        // access payload by key
        document.write(eventData['payload']['testkey']);
    });
});
```

TODO: Connection lost JS event, change the favicon, etc.
