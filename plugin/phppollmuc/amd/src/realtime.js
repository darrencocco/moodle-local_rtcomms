/**
 * Real time events
 *
 * @module     realtimeplugin_phppoll/realtime
 * @copyright  2024 Darren Cocco
 */
define(['core/pubsub', 'tool_realtime/events', 'tool_realtime/api'], function(PubSub, RealTimeEvents, api) {

    let params;
    let channels = [];
    let pollURL;
    let ajax = new XMLHttpRequest();
    let json;
    let timeout;
    let errorCounter = 0;

    ajax.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE) {
            if (this.status === 200) {
                try {
                    json = JSON.parse(this.responseText);
                    // Process results - trigger all necessary Javascript/jQuery events.
                    let events = json.events;
                    for (let i in events) {
                        PubSub.publish(RealTimeEvents.EVENT, events[i]);
                        // Remember the last id.
                        params.lastIdSeen = Number(events[i].id);
                    }
                    errorCounter = 0;
                } catch {
                    errorCounter++;
                }
            } else {
                errorCounter++;
            }
            resetTimeout();
            queueNextPoll();
        }
    };

    let poll = function() {
        if (channels.length < 1) {
            return;
        }

        if (errorCounter > params.maxFailures) {
            // Notify subscribers that something has gone wrong.
            PubSub.publish(RealTimeEvents.CONNECTION_LOST, {});
        }

        let url = pollURL + '?userid=' + encodeURIComponent(params.userid) + '&token=' +
        encodeURIComponent(params.token) +
        params.lastIdSeen === -1 ?
            '&since=' + encodeURIComponent(params.earliestMessageCreationTime) :
            '&lastidseen=' + encodeURIComponent(params.lastIdSeen);

        ajax.open('GET', url, true);
        ajax.send();
    };

    let queueNextPoll = () => {
        if (timeout === null) {
            timeout = setTimeout(poll, Math.min(2 ^ errorCounter * 1000, params.maxDelay));
        }
    };

    let resetTimeout = () => {
        timeout = null;
    };

    let plugin = {
        init: function(userId, token, pollURLParam, maxDelay, maxFailures, earliestMessageCreationTime) {
            if (params && params.userid) {
                // Log console dev error.
            } else {
                params = {
                    userid: userId,
                    token: token,
                    maxDelay: maxDelay * 1000,
                    maxFailures: maxFailures,
                    earliestMessageCreationTime: earliestMessageCreationTime,
                    lastIdSeen: -1
                };
            }
            pollURL = pollURLParam;
            api.setImplementation(plugin);
        },
        subscribe: function(context, component, area, itemId, fromId, fromTimeStamp) {
            params.lastIdSeen = fromId;
            let channelToSubTo = {
                context: context,
                component: component,
                area: area,
                itemId: itemId,
                fromTimestamp: fromTimeStamp,
            };
            if (channelToSubTo) {
                channels.push(channelToSubTo);
            }
            queueNextPoll();
        }
    };
    return plugin;
});