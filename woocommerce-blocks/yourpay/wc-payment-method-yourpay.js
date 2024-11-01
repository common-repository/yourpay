!(function (e) {
    var t = {};
    function n(r) {
        if (t[r]) return t[r].exports;
        var c = (t[r] = { i: r, l: !1, exports: {} });
        return e[r].call(c.exports, c, c.exports, n), (c.l = !0), c.exports;
    }
    (n.m = e),
        (n.c = t),
        (n.d = function (e, t, r) {
            n.o(e, t) || Object.defineProperty(e, t, { enumerable: !0, get: r });
        }),
        (n.r = function (e) {
            "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, { value: "Module" }), Object.defineProperty(e, "__esModule", { value: !0 });
        }),
        (n.t = function (e, t) {
            if ((1 & t && (e = n(e)), 8 & t)) return e;
            if (4 & t && "object" == typeof e && e && e.__esModule) return e;
            var r = Object.create(null);
            if ((n.r(r), Object.defineProperty(r, "default", { enumerable: !0, value: e }), 2 & t && "string" != typeof e))
                for (var c in e)
                    n.d(
                        r,
                        c,
                        function (t) {
                            return e[t];
                        }.bind(null, c)
                    );
            return r;
        }),
        (n.n = function (e) {
            var t =
                e && e.__esModule
                    ? function () {
                        return e.default;
                    }
                    : function () {
                        return e;
                    };
            return n.d(t, "a", t), t;
        }),
        (n.o = function (e, t) {
            return Object.prototype.hasOwnProperty.call(e, t);
        }),
        (n.p = ""),
        n((n.s = 23));
})({
    1: function (e, t) {
        !(function () {
            e.exports = this.wp.i18n;
        })();
    },
    23: function (e, t, n) {
        "use strict";
        n.r(t);
        var r = n(6),
            c = n(1),
            o = n(5),
            i = n(7),
            //u = Object(o.getSetting)("woocommerce_yourpay_settings", {}),
            u = { title: "Yourpay", description: "Make payment", icon: "../wp-content/plugins/yourpay/assets/images/cards/kortlogoer.png", yp_icon : "https://cdn.yourpay.io/wp-content/uploads/2019/05/cropped-Logo-blue.png"},
            l = Object(c.__)( 'yourpay', 'yourpay' ),
            a = Object(i.decodeEntities)(u.title) || l,
            f = function () {
                return React.createElement("div", null, Object(i.decodeEntities)(React.createElement( 'img', { src: u.icon, style: { display: 'inline' } } )||""));
            },
            s = function (e) {
                var icon = React.createElement( 'img', { src: u.yp_icon, style: { display: 'inline' } } );
                var span = React.createElement( 'span', { className: 'wc-block-components-payment-method-label wc-block-components-payment-method-label--with-icon' }, a );
                return icon;

            },
            d = {
                name: "yourpay",
                label: React.createElement(s, null),
                //label: <span>{ a }</span>,
                content: React.createElement(f, null),
                edit: React.createElement(f, null),
                icons: null,
                canMakePayment: function ( canPayArgument ) {
                    return true;
                },
                ariaLabel: a,
            };

        //Our settings on the console
        console.log(Object(o.getSetting)("woocommerce_yourpay_settings", {}));
        //console.log((n(5).getSetting)("siteurl", {}));
        //console.log(n(5).getSettings());


        Object(r.registerPaymentMethod)(function (e) {
            return new e(d);
        });
    },
    5: function (e, t) {
        !(function () {
            e.exports = this.wc.wcSettings;
        })();
    },
    6: function (e, t) {
        !(function () {
            e.exports = this.wc.wcBlocksRegistry;
        })();
    },
    7: function (e, t) {
        !(function () {
            e.exports = this.wp.htmlEntities;
        })();
    },
});
