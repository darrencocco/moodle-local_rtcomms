/**
 * Real time events
 *
 * @module     local_rtcomms/events
 * @copyright  2020 Marina Glancy
 */
define(['core/pubsub', 'local_rtcomms/events'], function(PubSub, RealTimeEvents) {

    document.listofchannels = [];

    return {
        setImplementation: function(plugin) {
            var totalchannels;
            document.delegatedplugin = plugin;
            // in here check list to subscribe once plugin has been set
            if(!document.listofchannels) {
                return;
            }
            totalchannels = document.listofchannels.length;

            if (totalchannels > 0) {
                for (var i = 0; i < totalchannels; i++) {
                    var channeltosub = document.listofchannels.shift();
                    document.delegatedplugin.subscribe( channeltosub.context,
                                                        channeltosub.component,
                                                        channeltosub.area,
                                                        channeltosub.itemid,
                                                        channeltosub.fromid,
                                                        channeltosub.fromtimestamp);
                }
            }
        },
        subscribe: function(context, component, area, itemid, callback = null, fromId= -1, fromTimestamp = -1) {
            if(fromId == -1 && fromTimestamp == -1) {
                fromTimestamp = (new Date).getTime();
            }

            // Check that plugin implementation has been set.
            if (this.getPlugin()) {
                //  conditional for plugin being set
                this.getPlugin().subscribe(context, component, area, itemid, fromId, fromTimestamp);
            } else {
                // Channel object to store in list
                var channel = {
                    context: context,
                    component: component,
                    area: area,
                    itemid: itemid,
                    fromid: fromId,
                    fromtimestamp: fromTimestamp
                };
                // push channel to list
                document.listofchannels.push(channel);
            }
            if (callback instanceof Function) {
                PubSub.subscribe(this.channelName(context, component, area, itemid), callback);
            }
        },

        sendToServer: function(context, component, area, itemId, payload) {
            return this.getPlugin().sendToServer(context, component, area, itemId, payload);
        },

        getPlugin: function() {
            return document.delegatedplugin;
        },

        publish: function(message) {
            PubSub.publish(this.channelName(message.context.id, message.component, message.area, message.itemid), message);
        },

        connectionFailure: function() {
            PubSub.publish(RealTimeEvents.CONNECTION_LOST, {});
        },

        channelName: function (context, component, area, itemid) {
            return RealTimeEvents.EVENT + '/' + context + '/' + component + '/' + area + '/' + itemid;
        }
    };
});