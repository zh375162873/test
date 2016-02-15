"use strict"; !
function() {
    function u(b, f, a) {
        return b.getAttribute(f) || a
    }
    function c() {
        var f = document.getElementsByTagName("script"),
        a = f.length,
        h = f[a - 1],
        b,
        j,
        g;
        b = u(h, "zIndex", -1);
        j = u(h, "opacity", 0.5);
        g = u(h, "color", "0,0,0");
        return {
            l: a,
            z: b,
            o: j,
            c: g
        }
    }
    function B() {
        i.width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
        i.height = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight
    }
    function C() {
        z.clearRect(0, 0, i.width, i.height);
        var a = [A].concat(e);
        e.forEach(function(o) {
            o.x += o.xa,
            o.y += o.ya,
            o.xa *= o.x > i.width || o.x < 0 ? -1 : 1,
            o.ya *= o.y > i.height || o.y < 0 ? -1 : 1,
            z.fillRect(o.x - 0.5, o.y - 0.5, 1, 1);
            for (var j = 0; j < a.length; j++) {
                var k = a[j];
                if (o !== k && null !== k.x && null !== k.y) {
                    var b, h = o.x - k.x,
                    f = o.y - k.y,
                    g = h * h + f * f;
                    g < k.max && (k === A && g >= k.max / 2 && (o.x -= 0.03 * h, o.y -= 0.03 * f), b = (k.max - g) / k.max, z.beginPath(), z.lineWidth = b / 2, z.strokeStyle = "rgba(" + D.c + "," + (b + 0.2) + ")", z.moveTo(o.x, o.y), z.lineTo(k.x, k.y), z.stroke())
                }
            }
            a.splice(a.indexOf(o), 1)
        }),
        m(C)
    }
    var i = document.createElement("canvas"),
    D = c(),
    n = "c_n" + D.l,
    z = i.getContext("2d");
    i.id = n;
    i.style.cssText = "position:fixed;top:0;left:0;z-index:" + D.z + ";opacity:" + D.o;
    document.getElementsByTagName("body")[0].appendChild(i);
    B(),
    window.onresize = B;
    var m = function() {
        return window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.oRequestAnimationFrame || window.msRequestAnimationFrame ||
        function(a) {
            window.setTimeout(a, 1000 / 60)
        }
    } (),
    A = {
        x: null,
        y: null,
        max: 20000
    };
    window.onmousemove = function(a) {
        a = a || window.event,
        A.x = a.clientX,
        A.y = a.clientY
    },
    window.onmouseout = function(a) {
        A.x = null,
        A.y = null
    };
    for (var e = [], t = 0; 150 > t; t++) {
        var l = Math.random() * i.width,
        x = Math.random() * i.height,
        r = 2 * Math.random() - 1,
        d = 2 * Math.random() - 1;
        e.push({
            x: l,
            y: x,
            xa: r,
            ya: d,
            max: 6000
        })
    }
    setTimeout(function() {
        C()
    },
    100)
} ();