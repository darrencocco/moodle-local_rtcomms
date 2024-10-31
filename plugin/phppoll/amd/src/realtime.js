/**
 * Real time events
 *
 * @module     rtcomms_phppoll/realtime
 * @copyright  2024 Darren Cocco
 */
define(['local_rtcomms/api', 'core/ajax'], function(api, ajax) {
    const phpPollPrototype = {

        pollType: {
            short: 1,
            long: 2,
        },


        ajaxOnReadyStateChange(self) {
            return function() {
                if (this.readyState === XMLHttpRequest.DONE) {
                    if (this.status === 200) {
                        try {
                            let json = JSON.parse(this.responseText);
                            if (!json.error) {
                                // Process results - trigger all necessary Javascript/jQuery events.
                                // FIXME: not handling Moodle errors correctly
                                let events = json.events;
                                for (let i in events) {
                                    api.publish(events[i]);
                                    // Remember the last id.
                                    self.params.lastIdSeen = Number(events[i].id);
                                }
                                self.errorCounter = 0;
                            } else {
                                self.errorCounter++;
                            }
                        } catch {
                            self.errorCounter++;
                        }
                    } else {
                        self.errorCounter++;
                    }
                    self.resetTimeout();
                    self.queueNextPoll();
                }
            };
        },

        poll() {
            if (this.channels < 1) {
                return;
            }

            if (this.errorCounter > this.params.maxFailures) {
                // Notify subscribers that something has gone wrong.
                api.connectionFailure();
            }

            let url = this.pollURL + '?userid=' + encodeURIComponent(this.params.userid) + '&token=' +
            encodeURIComponent(this.params.token) +
                (this.params.lastIdSeen === -1 ?
                '&since=' + encodeURIComponent(this.params.earliestMessageCreationTime) :
                '&lastidseen=' + encodeURIComponent(this.params.lastIdSeen));

            this.ajax.open('GET', url, true);
            this.ajax.send();
        },

        queueNextPoll() {
            if (!this.timeout) {
                this.timeout = setTimeout(this.poll.bind(this),
                    Math.max(2 ** this.errorCounter * 1000, this.params.maxDelay));
            }
        },

        resetTimeout() {
            this.timeout = null;
        },
        init(userId, token, pollURLParam, maxDelay, maxFailures, earliestMessageCreationTime, pollType) {
            if (this.params && this.params.userid) {
                // Log console dev error.
            } else {
                this.params = {
                    userid: userId,
                    token: token,
                    maxDelay: maxDelay,
                    maxFailures: maxFailures,
                    earliestMessageCreationTime: earliestMessageCreationTime,
                    lastIdSeen: -1,
                    pollType: pollType === 'short' ? pollType.short : pollType.long,
                };
            }
            this.pollURL = pollURLParam;
            this.ajax.onreadystatechange = this.ajaxOnReadyStateChange(this);
            api.setImplementation(pub);
        },
        subscribe() {
            this.channels++;
            this.queueNextPoll();
        },
        sendToServer(context, component, area, itemId, payload) {
            ajax.call([{
                methodname: 'rtcomms_phppoll_send',
                args: {
                    contextid: context,
                    component: component,
                    area: area,
                    itemid: itemId,
                    payload: JSON.stringify(payload),
                },
            }]);
        }
    };

    /**
     * Handles interacting with PHP Poll DB plugin.
     * @constructor
     */
    function PhpPoll() {
        this.params = null;
        this.channels = 0;
        this.pollURL = null;
        this.ajax =  new XMLHttpRequest();
        this.json = null;
        this.timeout = null;
        this.errorCounter = 0;
    }
    Object.assign(PhpPoll.prototype, phpPollPrototype);
    let instance = new PhpPoll();
    let pub = {
        init: (configuration) => {
            instance.init(configuration.userId, configuration.token, configuration.pollURLParam,
                configuration.maxDelay, configuration.maxFailures, configuration.earliestMessageCreationTime,
                configuration.pollType);
        },
        subscribe: () => {
            instance.subscribe();
        },
        sendToServer: (context, component, area, itemId, payload) => {
            instance.sendToServer(context, component, area, itemId, payload);
        },
    };
    return pub;
});