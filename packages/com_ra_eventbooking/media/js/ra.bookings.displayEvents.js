/* 
 * copyright: Chris Vaughan
 * email: ruby.tuesday@ramblers-webs.org.uk
 * EW     an RA event or walk in ramblers library format
 * ESC    a collection of booking records , EVB
 * EVB    a booking record for an event,  an object
 * NBI    a new booking information for one user
 * BLC    a collection of bookings, collection of BLI
 * BLI    the user information booking for a user
 * WLC    a collection of waiting records, collection of WLI
 * WLI    the user information about someone on waiting list
 */
var ra;
if (typeof (ra) === "undefined") {
    ra = {};
}

if (typeof (ra.bookings) === "undefined") {
    ra.bookings = {};
}

// display table of all events that have a booking requirement
ra.bookings.displayEvents = function () {
    this.elements;
    this.bookings = null;
    this.defaults = null;
    this.user = null;

    this.display = function (tag) {
        var tags = [
            {parent: 'root', tag: 'h3', innerHTML: ''},
            {name: 'details', parent: 'root', tag: 'details', attrs: {class: "ra-detailsButton"}},
            {name: 'summary', parent: 'details', tag: 'summary', innerHTML: 'Booking information'},
            {name: 'title', parent: 'details', tag: 'h2', innerHTML: 'Booking information'},
            {name: 'message', parent: 'details', tag: 'p'},
            {name: 'defaults', parent: 'details', tag: 'div'},
            {name: 'div', parent: 'details', tag: 'div'}
        ];
        this.elements = ra.html.generateTags(tag, tags);
        var sa = new ra.bookings.queryServer(this, 'getEventsSummary');
        sa.action(null, (self, results) => {
            self._dispTable(results);
        }
        );

    };

    this._dispTable = function (results) {
        document.addEventListener('disableEvent', e => {
            var ewid = e.raData.ewid;
            this.esc.removeItem(ewid);
            this.elements.div.innerHTML = '';
            this.esc.displaySummary(this.elements.div, this.user.canEdit);
        });
        this.defaults = new ra.bookings.defaults(results.data.defaults);
        this.user = results.data.user;
        this.esc = new ra.bookings.esc();
        this.esc.process(results.data.esc);
        this.esc.setOverrides(this.defaults);
        this.defaults.display(this.elements.defaults);
        this.esc.displaySummary(this.elements.div, this.user.canEdit);
    };
};