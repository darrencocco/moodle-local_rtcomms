/**
 * Real time events
 *
 * @module     realtimeplugin_phppollmuc/realtime
 * @package    realtimeplugin_phppollmuc
 * @copyright  2022 Darren Cocco
 */
define(['core/pubsub', 'tool_realtime/events', 'tool_realtime/api'], function(PubSub, RealTimeEvents, api) {

    let params;
    let channels = [];
    let requestsCounter = [];
    let pollURL;
    let ajax = new XMLHttpRequest();
    let json;
    let timeout;

    var checkRequestCounter = function() {
        var curDate = new Date(),
            curTime = curDate.getTime();
        requestsCounter.push(curTime);
        requestsCounter = requestsCounter.slice(-10);
        // If there were 10 requests in less than 5 seconds, it must be an error. Stop polling.
        if (requestsCounter.length >= 10 && curTime - requestsCounter[0] < 5000) {
            PubSub.publish(RealTimeEvents.CONNECTION_LOST, {});
            return false;
        }
        return true;
    };

    ajax.onreadystatechange = function() {
        if (this.readyState === XMLHttpRequest.DONE) {
            if (this.status === 200) {
                try {
                    json = JSON.parse(this.responseText);
                } catch {
                    setTimeout(poll, params.timeout);
                    return;
                }

                // Process results - trigger all necessary Javascript/jQuery events.
                var events = json.events;
                for (var i in events) {
                    PubSub.publish(RealTimeEvents.EVENT, events[i]);
                    // Remember the last id.
                    params.fromid = events[i].id;
                }
            }
            resetTimeout();
            queueNextPoll();
        }
    };

    let poll = function() {
        if (!checkRequestCounter() || channels.length < 1) {
            return;
        }

        let url = pollURL + '?userid=' + encodeURIComponent(params.userid) + '&token=' +
            encodeURIComponent(params.token) + '&fromid=' + encodeURIComponent(params.fromid);

        let channelParams = channels.reduce((accumulator, current) => {
            return accumulator + current.context + ":" + current.component + ":"
                + current.area + ":" + current.itemId + ":" + current.fromTimestamp + ";";
        }, "");

        ajax.open('GET', url + "&channel=" + channelParams, true);
        ajax.send();
    };

    let queueNextPoll = () => {
        if (timeout === null) {
            timeout = setTimeout(poll, params.timeout);
        }
    };

    let resetTimeout = () => {
        timeout = null;
    };

    let plugin = {
        init: function(userId, token, pollURLParam, timeout) {
            if (params && params.userid) {
                // Log console dev error.
            } else {
                params = {
                    userid: userId,
                    token: token,
                    timeout: timeout,
                };
            }
            pollURL = pollURLParam;
            api.setImplementation(plugin);
        },
        subscribe: function(context, component, area, itemId, fromId, fromTimeStamp) {
            params.fromid = fromId;
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
