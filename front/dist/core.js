(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
	typeof define === 'function' && define.amd ? define(factory) :
	(global.Core = factory());
}(this, (function () { 'use strict';

function _AsyncGenerator(e) {
  var r, t;
  function resume(r, t) {
    try {
      var n = e[r](t),
        o = n.value,
        u = o instanceof _OverloadYield;
      Promise.resolve(u ? o.v : o).then(function (t) {
        if (u) {
          var i = "return" === r ? "return" : "next";
          if (!o.k || t.done) return resume(i, t);
          t = e[i](t).value;
        }
        settle(n.done ? "return" : "normal", t);
      }, function (e) {
        resume("throw", e);
      });
    } catch (e) {
      settle("throw", e);
    }
  }
  function settle(e, n) {
    switch (e) {
      case "return":
        r.resolve({
          value: n,
          done: !0
        });
        break;
      case "throw":
        r.reject(n);
        break;
      default:
        r.resolve({
          value: n,
          done: !1
        });
    }
    (r = r.next) ? resume(r.key, r.arg) : t = null;
  }
  this._invoke = function (e, n) {
    return new Promise(function (o, u) {
      var i = {
        key: e,
        arg: n,
        resolve: o,
        reject: u,
        next: null
      };
      t ? t = t.next = i : (r = t = i, resume(e, n));
    });
  }, "function" != typeof e.return && (this.return = void 0);
}
_AsyncGenerator.prototype["function" == typeof Symbol && Symbol.asyncIterator || "@@asyncIterator"] = function () {
  return this;
}, _AsyncGenerator.prototype.next = function (e) {
  return this._invoke("next", e);
}, _AsyncGenerator.prototype.throw = function (e) {
  return this._invoke("throw", e);
}, _AsyncGenerator.prototype.return = function (e) {
  return this._invoke("return", e);
};
function _OverloadYield(t, e) {
  this.v = t, this.k = e;
}
function _regeneratorRuntime() {
  "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */
  _regeneratorRuntime = function () {
    return e;
  };
  var t,
    e = {},
    r = Object.prototype,
    n = r.hasOwnProperty,
    o = Object.defineProperty || function (t, e, r) {
      t[e] = r.value;
    },
    i = "function" == typeof Symbol ? Symbol : {},
    a = i.iterator || "@@iterator",
    c = i.asyncIterator || "@@asyncIterator",
    u = i.toStringTag || "@@toStringTag";
  function define(t, e, r) {
    return Object.defineProperty(t, e, {
      value: r,
      enumerable: !0,
      configurable: !0,
      writable: !0
    }), t[e];
  }
  try {
    define({}, "");
  } catch (t) {
    define = function (t, e, r) {
      return t[e] = r;
    };
  }
  function wrap(t, e, r, n) {
    var i = e && e.prototype instanceof Generator ? e : Generator,
      a = Object.create(i.prototype),
      c = new Context(n || []);
    return o(a, "_invoke", {
      value: makeInvokeMethod(t, r, c)
    }), a;
  }
  function tryCatch(t, e, r) {
    try {
      return {
        type: "normal",
        arg: t.call(e, r)
      };
    } catch (t) {
      return {
        type: "throw",
        arg: t
      };
    }
  }
  e.wrap = wrap;
  var h = "suspendedStart",
    l = "suspendedYield",
    f = "executing",
    s = "completed",
    y = {};
  function Generator() {}
  function GeneratorFunction() {}
  function GeneratorFunctionPrototype() {}
  var p = {};
  define(p, a, function () {
    return this;
  });
  var d = Object.getPrototypeOf,
    v = d && d(d(values([])));
  v && v !== r && n.call(v, a) && (p = v);
  var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p);
  function defineIteratorMethods(t) {
    ["next", "throw", "return"].forEach(function (e) {
      define(t, e, function (t) {
        return this._invoke(e, t);
      });
    });
  }
  function AsyncIterator(t, e) {
    function invoke(r, o, i, a) {
      var c = tryCatch(t[r], t, o);
      if ("throw" !== c.type) {
        var u = c.arg,
          h = u.value;
        return h && "object" == typeof h && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) {
          invoke("next", t, i, a);
        }, function (t) {
          invoke("throw", t, i, a);
        }) : e.resolve(h).then(function (t) {
          u.value = t, i(u);
        }, function (t) {
          return invoke("throw", t, i, a);
        });
      }
      a(c.arg);
    }
    var r;
    o(this, "_invoke", {
      value: function (t, n) {
        function callInvokeWithMethodAndArg() {
          return new e(function (e, r) {
            invoke(t, n, e, r);
          });
        }
        return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg();
      }
    });
  }
  function makeInvokeMethod(e, r, n) {
    var o = h;
    return function (i, a) {
      if (o === f) throw new Error("Generator is already running");
      if (o === s) {
        if ("throw" === i) throw a;
        return {
          value: t,
          done: !0
        };
      }
      for (n.method = i, n.arg = a;;) {
        var c = n.delegate;
        if (c) {
          var u = maybeInvokeDelegate(c, n);
          if (u) {
            if (u === y) continue;
            return u;
          }
        }
        if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) {
          if (o === h) throw o = s, n.arg;
          n.dispatchException(n.arg);
        } else "return" === n.method && n.abrupt("return", n.arg);
        o = f;
        var p = tryCatch(e, r, n);
        if ("normal" === p.type) {
          if (o = n.done ? s : l, p.arg === y) continue;
          return {
            value: p.arg,
            done: n.done
          };
        }
        "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg);
      }
    };
  }
  function maybeInvokeDelegate(e, r) {
    var n = r.method,
      o = e.iterator[n];
    if (o === t) return r.delegate = null, "throw" === n && e.iterator.return && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y;
    var i = tryCatch(o, e.iterator, r.arg);
    if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y;
    var a = i.arg;
    return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y);
  }
  function pushTryEntry(t) {
    var e = {
      tryLoc: t[0]
    };
    1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e);
  }
  function resetTryEntry(t) {
    var e = t.completion || {};
    e.type = "normal", delete e.arg, t.completion = e;
  }
  function Context(t) {
    this.tryEntries = [{
      tryLoc: "root"
    }], t.forEach(pushTryEntry, this), this.reset(!0);
  }
  function values(e) {
    if (e || "" === e) {
      var r = e[a];
      if (r) return r.call(e);
      if ("function" == typeof e.next) return e;
      if (!isNaN(e.length)) {
        var o = -1,
          i = function next() {
            for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next;
            return next.value = t, next.done = !0, next;
          };
        return i.next = i;
      }
    }
    throw new TypeError(typeof e + " is not iterable");
  }
  return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", {
    value: GeneratorFunctionPrototype,
    configurable: !0
  }), o(GeneratorFunctionPrototype, "constructor", {
    value: GeneratorFunction,
    configurable: !0
  }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) {
    var e = "function" == typeof t && t.constructor;
    return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name));
  }, e.mark = function (t) {
    return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t;
  }, e.awrap = function (t) {
    return {
      __await: t
    };
  }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () {
    return this;
  }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) {
    void 0 === i && (i = Promise);
    var a = new AsyncIterator(wrap(t, r, n, o), i);
    return e.isGeneratorFunction(r) ? a : a.next().then(function (t) {
      return t.done ? t.value : a.next();
    });
  }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () {
    return this;
  }), define(g, "toString", function () {
    return "[object Generator]";
  }), e.keys = function (t) {
    var e = Object(t),
      r = [];
    for (var n in e) r.push(n);
    return r.reverse(), function next() {
      for (; r.length;) {
        var t = r.pop();
        if (t in e) return next.value = t, next.done = !1, next;
      }
      return next.done = !0, next;
    };
  }, e.values = values, Context.prototype = {
    constructor: Context,
    reset: function (e) {
      if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t);
    },
    stop: function () {
      this.done = !0;
      var t = this.tryEntries[0].completion;
      if ("throw" === t.type) throw t.arg;
      return this.rval;
    },
    dispatchException: function (e) {
      if (this.done) throw e;
      var r = this;
      function handle(n, o) {
        return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o;
      }
      for (var o = this.tryEntries.length - 1; o >= 0; --o) {
        var i = this.tryEntries[o],
          a = i.completion;
        if ("root" === i.tryLoc) return handle("end");
        if (i.tryLoc <= this.prev) {
          var c = n.call(i, "catchLoc"),
            u = n.call(i, "finallyLoc");
          if (c && u) {
            if (this.prev < i.catchLoc) return handle(i.catchLoc, !0);
            if (this.prev < i.finallyLoc) return handle(i.finallyLoc);
          } else if (c) {
            if (this.prev < i.catchLoc) return handle(i.catchLoc, !0);
          } else {
            if (!u) throw new Error("try statement without catch or finally");
            if (this.prev < i.finallyLoc) return handle(i.finallyLoc);
          }
        }
      }
    },
    abrupt: function (t, e) {
      for (var r = this.tryEntries.length - 1; r >= 0; --r) {
        var o = this.tryEntries[r];
        if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) {
          var i = o;
          break;
        }
      }
      i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null);
      var a = i ? i.completion : {};
      return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a);
    },
    complete: function (t, e) {
      if ("throw" === t.type) throw t.arg;
      return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y;
    },
    finish: function (t) {
      for (var e = this.tryEntries.length - 1; e >= 0; --e) {
        var r = this.tryEntries[e];
        if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y;
      }
    },
    catch: function (t) {
      for (var e = this.tryEntries.length - 1; e >= 0; --e) {
        var r = this.tryEntries[e];
        if (r.tryLoc === t) {
          var n = r.completion;
          if ("throw" === n.type) {
            var o = n.arg;
            resetTryEntry(r);
          }
          return o;
        }
      }
      throw new Error("illegal catch attempt");
    },
    delegateYield: function (e, r, n) {
      return this.delegate = {
        iterator: values(e),
        resultName: r,
        nextLoc: n
      }, "next" === this.method && (this.arg = t), y;
    }
  }, e;
}
function _typeof(o) {
  "@babel/helpers - typeof";

  return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) {
    return typeof o;
  } : function (o) {
    return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o;
  }, _typeof(o);
}
function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) {
  try {
    var info = gen[key](arg);
    var value = info.value;
  } catch (error) {
    reject(error);
    return;
  }
  if (info.done) {
    resolve(value);
  } else {
    Promise.resolve(value).then(_next, _throw);
  }
}
function _asyncToGenerator(fn) {
  return function () {
    var self = this,
      args = arguments;
    return new Promise(function (resolve, reject) {
      var gen = fn.apply(self, args);
      function _next(value) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value);
      }
      function _throw(err) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err);
      }
      _next(undefined);
    });
  };
}
function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}
function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor);
  }
}
function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  Object.defineProperty(Constructor, "prototype", {
    writable: false
  });
  return Constructor;
}
function _unsupportedIterableToArray(o, minLen) {
  if (!o) return;
  if (typeof o === "string") return _arrayLikeToArray(o, minLen);
  var n = Object.prototype.toString.call(o).slice(8, -1);
  if (n === "Object" && o.constructor) n = o.constructor.name;
  if (n === "Map" || n === "Set") return Array.from(o);
  if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen);
}
function _arrayLikeToArray(arr, len) {
  if (len == null || len > arr.length) len = arr.length;
  for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i];
  return arr2;
}
function _createForOfIteratorHelper(o, allowArrayLike) {
  var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"];
  if (!it) {
    if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") {
      if (it) o = it;
      var i = 0;
      var F = function () {};
      return {
        s: F,
        n: function () {
          if (i >= o.length) return {
            done: true
          };
          return {
            done: false,
            value: o[i++]
          };
        },
        e: function (e) {
          throw e;
        },
        f: F
      };
    }
    throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  }
  var normalCompletion = true,
    didErr = false,
    err;
  return {
    s: function () {
      it = it.call(o);
    },
    n: function () {
      var step = it.next();
      normalCompletion = step.done;
      return step;
    },
    e: function (e) {
      didErr = true;
      err = e;
    },
    f: function () {
      try {
        if (!normalCompletion && it.return != null) it.return();
      } finally {
        if (didErr) throw err;
      }
    }
  };
}
function _toPrimitive(input, hint) {
  if (typeof input !== "object" || input === null) return input;
  var prim = input[Symbol.toPrimitive];
  if (prim !== undefined) {
    var res = prim.call(input, hint || "default");
    if (typeof res !== "object") return res;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return (hint === "string" ? String : Number)(input);
}
function _toPropertyKey(arg) {
  var key = _toPrimitive(arg, "string");
  return typeof key === "symbol" ? key : String(key);
}

function e(e) {
  this.message = e;
}
e.prototype = new Error(), e.prototype.name = "InvalidCharacterError";
var r = "undefined" != typeof window && window.atob && window.atob.bind(window) || function (r) {
  var t = String(r).replace(/=+$/, "");
  if (t.length % 4 == 1) throw new e("'atob' failed: The string to be decoded is not correctly encoded.");
  for (var n, o, a = 0, i = 0, c = ""; o = t.charAt(i++); ~o && (n = a % 4 ? 64 * n + o : o, a++ % 4) ? c += String.fromCharCode(255 & n >> (-2 * a & 6)) : 0) o = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=".indexOf(o);
  return c;
};
function t(e) {
  var t = e.replace(/-/g, "+").replace(/_/g, "/");
  switch (t.length % 4) {
    case 0:
      break;
    case 2:
      t += "==";
      break;
    case 3:
      t += "=";
      break;
    default:
      throw "Illegal base64url string!";
  }
  try {
    return function (e) {
      return decodeURIComponent(r(e).replace(/(.)/g, function (e, r) {
        var t = r.charCodeAt(0).toString(16).toUpperCase();
        return t.length < 2 && (t = "0" + t), "%" + t;
      }));
    }(t);
  } catch (e) {
    return r(t);
  }
}
function n(e) {
  this.message = e;
}
function o(e, r) {
  if ("string" != typeof e) throw new n("Invalid token specified");
  var o = !0 === (r = r || {}).header ? 0 : 1;
  try {
    return JSON.parse(t(e.split(".")[o]));
  } catch (e) {
    throw new n("Invalid token specified: " + e.message);
  }
}
n.prototype = new Error(), n.prototype.name = "InvalidTokenError";

/**
 * FingerprintJS v3.3.3 - Copyright (c) FingerprintJS, Inc, 2022 (https://fingerprintjs.com)
 * Licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) license.
 *
 * This software contains code from open-source projects:
 * MurmurHash3 by Karan Lyons (https://github.com/karanlyons/murmurHash3.js)
 */

!function (e, t) {
  "object" == typeof exports && "undefined" != typeof module ? t(exports) : "function" == typeof define && define.amd ? define(["exports"], t) : t((e = "undefined" != typeof globalThis ? globalThis : e || self).FingerprintJS = {});
}(window, function (e) {
  "use strict";

  var t = function () {
    return (t = Object.assign || function (e) {
      for (var t, n = 1, r = arguments.length; n < r; n++) for (var a in t = arguments[n]) Object.prototype.hasOwnProperty.call(t, a) && (e[a] = t[a]);
      return e;
    }).apply(this, arguments);
  };
  function n(e, t, n, r) {
    return new (n || (n = Promise))(function (a, o) {
      function i(e) {
        try {
          u(r.next(e));
        } catch (t) {
          o(t);
        }
      }
      function c(e) {
        try {
          u(r.throw(e));
        } catch (t) {
          o(t);
        }
      }
      function u(e) {
        var t;
        e.done ? a(e.value) : (t = e.value, t instanceof n ? t : new n(function (e) {
          e(t);
        })).then(i, c);
      }
      u((r = r.apply(e, t || [])).next());
    });
  }
  function r(e, t) {
    var n,
      r,
      a,
      o,
      i = {
        label: 0,
        sent: function () {
          if (1 & a[0]) throw a[1];
          return a[1];
        },
        trys: [],
        ops: []
      };
    return o = {
      next: c(0),
      throw: c(1),
      return: c(2)
    }, "function" == typeof Symbol && (o[Symbol.iterator] = function () {
      return this;
    }), o;
    function c(o) {
      return function (c) {
        return function (o) {
          if (n) throw new TypeError("Generator is already executing.");
          for (; i;) try {
            if (n = 1, r && (a = 2 & o[0] ? r.return : o[0] ? r.throw || ((a = r.return) && a.call(r), 0) : r.next) && !(a = a.call(r, o[1])).done) return a;
            switch (r = 0, a && (o = [2 & o[0], a.value]), o[0]) {
              case 0:
              case 1:
                a = o;
                break;
              case 4:
                return i.label++, {
                  value: o[1],
                  done: !1
                };
              case 5:
                i.label++, r = o[1], o = [0];
                continue;
              case 7:
                o = i.ops.pop(), i.trys.pop();
                continue;
              default:
                if (!(a = i.trys, (a = a.length > 0 && a[a.length - 1]) || 6 !== o[0] && 2 !== o[0])) {
                  i = 0;
                  continue;
                }
                if (3 === o[0] && (!a || o[1] > a[0] && o[1] < a[3])) {
                  i.label = o[1];
                  break;
                }
                if (6 === o[0] && i.label < a[1]) {
                  i.label = a[1], a = o;
                  break;
                }
                if (a && i.label < a[2]) {
                  i.label = a[2], i.ops.push(o);
                  break;
                }
                a[2] && i.ops.pop(), i.trys.pop();
                continue;
            }
            o = t.call(e, i);
          } catch (c) {
            o = [6, c], r = 0;
          } finally {
            n = a = 0;
          }
          if (5 & o[0]) throw o[1];
          return {
            value: o[0] ? o[1] : void 0,
            done: !0
          };
        }([o, c]);
      };
    }
  }
  function a() {
    for (var e = 0, t = 0, n = arguments.length; t < n; t++) e += arguments[t].length;
    var r = Array(e),
      a = 0;
    for (t = 0; t < n; t++) for (var o = arguments[t], i = 0, c = o.length; i < c; i++, a++) r[a] = o[i];
    return r;
  }
  function o(e, t) {
    return new Promise(function (n) {
      return setTimeout(n, e, t);
    });
  }
  function i(e, t) {
    try {
      var n = e();
      (r = n) && "function" == typeof r.then ? n.then(function (e) {
        return t(!0, e);
      }, function (e) {
        return t(!1, e);
      }) : t(!0, n);
    } catch (a) {
      t(!1, a);
    }
    var r;
  }
  function c(e, t, a) {
    return void 0 === a && (a = 16), n(this, void 0, void 0, function () {
      var n, i, c;
      return r(this, function (r) {
        switch (r.label) {
          case 0:
            n = Date.now(), i = 0, r.label = 1;
          case 1:
            return i < e.length ? (t(e[i], i), (c = Date.now()) >= n + a ? (n = c, [4, o(0)]) : [3, 3]) : [3, 4];
          case 2:
            r.sent(), r.label = 3;
          case 3:
            return ++i, [3, 1];
          case 4:
            return [2];
        }
      });
    });
  }
  function u(e, t) {
    e = [e[0] >>> 16, 65535 & e[0], e[1] >>> 16, 65535 & e[1]], t = [t[0] >>> 16, 65535 & t[0], t[1] >>> 16, 65535 & t[1]];
    var n = [0, 0, 0, 0];
    return n[3] += e[3] + t[3], n[2] += n[3] >>> 16, n[3] &= 65535, n[2] += e[2] + t[2], n[1] += n[2] >>> 16, n[2] &= 65535, n[1] += e[1] + t[1], n[0] += n[1] >>> 16, n[1] &= 65535, n[0] += e[0] + t[0], n[0] &= 65535, [n[0] << 16 | n[1], n[2] << 16 | n[3]];
  }
  function s(e, t) {
    e = [e[0] >>> 16, 65535 & e[0], e[1] >>> 16, 65535 & e[1]], t = [t[0] >>> 16, 65535 & t[0], t[1] >>> 16, 65535 & t[1]];
    var n = [0, 0, 0, 0];
    return n[3] += e[3] * t[3], n[2] += n[3] >>> 16, n[3] &= 65535, n[2] += e[2] * t[3], n[1] += n[2] >>> 16, n[2] &= 65535, n[2] += e[3] * t[2], n[1] += n[2] >>> 16, n[2] &= 65535, n[1] += e[1] * t[3], n[0] += n[1] >>> 16, n[1] &= 65535, n[1] += e[2] * t[2], n[0] += n[1] >>> 16, n[1] &= 65535, n[1] += e[3] * t[1], n[0] += n[1] >>> 16, n[1] &= 65535, n[0] += e[0] * t[3] + e[1] * t[2] + e[2] * t[1] + e[3] * t[0], n[0] &= 65535, [n[0] << 16 | n[1], n[2] << 16 | n[3]];
  }
  function l(e, t) {
    return 32 === (t %= 64) ? [e[1], e[0]] : t < 32 ? [e[0] << t | e[1] >>> 32 - t, e[1] << t | e[0] >>> 32 - t] : (t -= 32, [e[1] << t | e[0] >>> 32 - t, e[0] << t | e[1] >>> 32 - t]);
  }
  function d(e, t) {
    return 0 === (t %= 64) ? e : t < 32 ? [e[0] << t | e[1] >>> 32 - t, e[1] << t] : [e[1] << t - 32, 0];
  }
  function f(e, t) {
    return [e[0] ^ t[0], e[1] ^ t[1]];
  }
  function h(e) {
    return e = f(e, [0, e[0] >>> 1]), e = f(e = s(e, [4283543511, 3981806797]), [0, e[0] >>> 1]), e = f(e = s(e, [3301882366, 444984403]), [0, e[0] >>> 1]);
  }
  function v(e, t) {
    t = t || 0;
    var n,
      r = (e = e || "").length % 16,
      a = e.length - r,
      o = [0, t],
      i = [0, t],
      c = [0, 0],
      v = [0, 0],
      p = [2277735313, 289559509],
      m = [1291169091, 658871167];
    for (n = 0; n < a; n += 16) c = [255 & e.charCodeAt(n + 4) | (255 & e.charCodeAt(n + 5)) << 8 | (255 & e.charCodeAt(n + 6)) << 16 | (255 & e.charCodeAt(n + 7)) << 24, 255 & e.charCodeAt(n) | (255 & e.charCodeAt(n + 1)) << 8 | (255 & e.charCodeAt(n + 2)) << 16 | (255 & e.charCodeAt(n + 3)) << 24], v = [255 & e.charCodeAt(n + 12) | (255 & e.charCodeAt(n + 13)) << 8 | (255 & e.charCodeAt(n + 14)) << 16 | (255 & e.charCodeAt(n + 15)) << 24, 255 & e.charCodeAt(n + 8) | (255 & e.charCodeAt(n + 9)) << 8 | (255 & e.charCodeAt(n + 10)) << 16 | (255 & e.charCodeAt(n + 11)) << 24], c = l(c = s(c, p), 31), o = u(o = l(o = f(o, c = s(c, m)), 27), i), o = u(s(o, [0, 5]), [0, 1390208809]), v = l(v = s(v, m), 33), i = u(i = l(i = f(i, v = s(v, p)), 31), o), i = u(s(i, [0, 5]), [0, 944331445]);
    switch (c = [0, 0], v = [0, 0], r) {
      case 15:
        v = f(v, d([0, e.charCodeAt(n + 14)], 48));
      case 14:
        v = f(v, d([0, e.charCodeAt(n + 13)], 40));
      case 13:
        v = f(v, d([0, e.charCodeAt(n + 12)], 32));
      case 12:
        v = f(v, d([0, e.charCodeAt(n + 11)], 24));
      case 11:
        v = f(v, d([0, e.charCodeAt(n + 10)], 16));
      case 10:
        v = f(v, d([0, e.charCodeAt(n + 9)], 8));
      case 9:
        v = s(v = f(v, [0, e.charCodeAt(n + 8)]), m), i = f(i, v = s(v = l(v, 33), p));
      case 8:
        c = f(c, d([0, e.charCodeAt(n + 7)], 56));
      case 7:
        c = f(c, d([0, e.charCodeAt(n + 6)], 48));
      case 6:
        c = f(c, d([0, e.charCodeAt(n + 5)], 40));
      case 5:
        c = f(c, d([0, e.charCodeAt(n + 4)], 32));
      case 4:
        c = f(c, d([0, e.charCodeAt(n + 3)], 24));
      case 3:
        c = f(c, d([0, e.charCodeAt(n + 2)], 16));
      case 2:
        c = f(c, d([0, e.charCodeAt(n + 1)], 8));
      case 1:
        c = s(c = f(c, [0, e.charCodeAt(n)]), p), o = f(o, c = s(c = l(c, 31), m));
    }
    return o = u(o = f(o, [0, e.length]), i = f(i, [0, e.length])), i = u(i, o), o = u(o = h(o), i = h(i)), i = u(i, o), ("00000000" + (o[0] >>> 0).toString(16)).slice(-8) + ("00000000" + (o[1] >>> 0).toString(16)).slice(-8) + ("00000000" + (i[0] >>> 0).toString(16)).slice(-8) + ("00000000" + (i[1] >>> 0).toString(16)).slice(-8);
  }
  function p(e) {
    return parseInt(e);
  }
  function m(e) {
    return parseFloat(e);
  }
  function g(e, t) {
    return "number" == typeof e && isNaN(e) ? t : e;
  }
  function b(e) {
    return e.reduce(function (e, t) {
      return e + (t ? 1 : 0);
    }, 0);
  }
  function w(e, t) {
    if (void 0 === t && (t = 1), Math.abs(t) >= 1) return Math.round(e / t) * t;
    var n = 1 / t;
    return Math.round(e * n) / n;
  }
  function y(e) {
    return e && "object" == typeof e && "message" in e ? e : {
      message: e
    };
  }
  function k(e, t, a) {
    var u = Object.keys(e).filter(function (e) {
        return !function (e, t) {
          for (var n = 0, r = e.length; n < r; ++n) if (e[n] === t) return !0;
          return !1;
        }(a, e);
      }),
      s = Array(u.length);
    return c(u, function (n, r) {
      s[r] = function (e, t) {
        var n = function (e) {
            return "function" != typeof e;
          },
          r = new Promise(function (r) {
            var a = Date.now();
            i(e.bind(null, t), function () {
              for (var e = [], t = 0; t < arguments.length; t++) e[t] = arguments[t];
              var o = Date.now() - a;
              if (!e[0]) return r(function () {
                return {
                  error: y(e[1]),
                  duration: o
                };
              });
              var c = e[1];
              if (n(c)) return r(function () {
                return {
                  value: c,
                  duration: o
                };
              });
              r(function () {
                return new Promise(function (e) {
                  var t = Date.now();
                  i(c, function () {
                    for (var n = [], r = 0; r < arguments.length; r++) n[r] = arguments[r];
                    var a = o + Date.now() - t;
                    if (!n[0]) return e({
                      error: y(n[1]),
                      duration: a
                    });
                    e({
                      value: n[1],
                      duration: a
                    });
                  });
                });
              });
            });
          });
        return function () {
          return r.then(function (e) {
            return e();
          });
        };
      }(e[n], t);
    }), function () {
      return n(this, void 0, void 0, function () {
        var e, t, n, a, i, l;
        return r(this, function (d) {
          switch (d.label) {
            case 0:
              for (e = {}, t = 0, n = u; t < n.length; t++) a = n[t], e[a] = void 0;
              i = Array(u.length), l = function () {
                var t;
                return r(this, function (n) {
                  switch (n.label) {
                    case 0:
                      return t = !0, [4, c(u, function (n, r) {
                        i[r] || (s[r] ? i[r] = s[r]().then(function (t) {
                          return e[n] = t;
                        }) : t = !1);
                      })];
                    case 1:
                      return n.sent(), t ? [2, "break"] : [4, o(1)];
                    case 2:
                      return n.sent(), [2];
                  }
                });
              }, d.label = 1;
            case 1:
              return [5, l()];
            case 2:
              if ("break" === d.sent()) return [3, 4];
              d.label = 3;
            case 3:
              return [3, 1];
            case 4:
              return [4, Promise.all(i)];
            case 5:
              return d.sent(), [2, e];
          }
        });
      });
    };
  }
  function C() {
    var e = window,
      t = navigator;
    return b(["MSCSSMatrix" in e, "msSetImmediate" in e, "msIndexedDB" in e, "msMaxTouchPoints" in t, "msPointerEnabled" in t]) >= 4;
  }
  function A() {
    var e = window,
      t = navigator;
    return b(["msWriteProfilerMark" in e, "MSStream" in e, "msLaunchUri" in t, "msSaveBlob" in t]) >= 3 && !C();
  }
  function S() {
    var e = window,
      t = navigator;
    return b(["webkitPersistentStorage" in t, "webkitTemporaryStorage" in t, 0 === t.vendor.indexOf("Google"), "webkitResolveLocalFileSystemURL" in e, "BatteryManager" in e, "webkitMediaStream" in e, "webkitSpeechGrammar" in e]) >= 5;
  }
  function x() {
    var e = window,
      t = navigator;
    return b(["ApplePayError" in e, "CSSPrimitiveValue" in e, "Counter" in e, 0 === t.vendor.indexOf("Apple"), "getStorageUpdates" in t, "WebKitMediaKeys" in e]) >= 4;
  }
  function M() {
    var e = window;
    return b(["safari" in e, !("DeviceMotionEvent" in e), !("ongestureend" in e), !("standalone" in navigator)]) >= 3;
  }
  function P() {
    var e,
      t,
      n = window;
    return b(["buildID" in navigator, "MozAppearance" in (null !== (t = null === (e = document.documentElement) || void 0 === e ? void 0 : e.style) && void 0 !== t ? t : {}), "onmozfullscreenchange" in n, "mozInnerScreenX" in n, "CSSMozDocumentRule" in n, "CanvasCaptureMediaStream" in n]) >= 4;
  }
  function _() {
    var e = document;
    return e.fullscreenElement || e.msFullscreenElement || e.mozFullScreenElement || e.webkitFullscreenElement || null;
  }
  function T() {
    var e = S(),
      t = P();
    if (!e && !t) return !1;
    var n = window;
    return b(["onorientationchange" in n, "orientation" in n, e && !("SharedWorker" in n), t && /android/i.test(navigator.appVersion)]) >= 2;
  }
  function E(e) {
    var t = new Error(e);
    return t.name = e, t;
  }
  function D(e, t, a) {
    var i, c, u;
    return void 0 === a && (a = 50), n(this, void 0, void 0, function () {
      var n, s;
      return r(this, function (r) {
        switch (r.label) {
          case 0:
            n = document, r.label = 1;
          case 1:
            return n.body ? [3, 3] : [4, o(a)];
          case 2:
            return r.sent(), [3, 1];
          case 3:
            s = n.createElement("iframe"), r.label = 4;
          case 4:
            return r.trys.push([4,, 10, 11]), [4, new Promise(function (e, r) {
              var a = !1,
                o = function () {
                  a = !0, e();
                };
              s.onload = o, s.onerror = function (e) {
                a = !0, r(e);
              };
              var i = s.style;
              i.setProperty("display", "block", "important"), i.position = "absolute", i.top = "0", i.left = "0", i.visibility = "hidden", t && "srcdoc" in s ? s.srcdoc = t : s.src = "about:blank", n.body.appendChild(s);
              var c = function () {
                var e, t;
                a || ("complete" === (null === (t = null === (e = s.contentWindow) || void 0 === e ? void 0 : e.document) || void 0 === t ? void 0 : t.readyState) ? o() : setTimeout(c, 10));
              };
              c();
            })];
          case 5:
            r.sent(), r.label = 6;
          case 6:
            return (null === (c = null === (i = s.contentWindow) || void 0 === i ? void 0 : i.document) || void 0 === c ? void 0 : c.body) ? [3, 8] : [4, o(a)];
          case 7:
            return r.sent(), [3, 6];
          case 8:
            return [4, e(s, s.contentWindow)];
          case 9:
            return [2, r.sent()];
          case 10:
            return null === (u = s.parentNode) || void 0 === u || u.removeChild(s), [7];
          case 11:
            return [2];
        }
      });
    });
  }
  function L(e) {
    for (var t = function (e) {
        for (var t, n, r = "Unexpected syntax '" + e + "'", a = /^\s*([a-z-]*)(.*)$/i.exec(e), o = a[1] || void 0, i = {}, c = /([.:#][\w-]+|\[.+?\])/gi, u = function (e, t) {
            i[e] = i[e] || [], i[e].push(t);
          };;) {
          var s = c.exec(a[2]);
          if (!s) break;
          var l = s[0];
          switch (l[0]) {
            case ".":
              u("class", l.slice(1));
              break;
            case "#":
              u("id", l.slice(1));
              break;
            case "[":
              var d = /^\[([\w-]+)([~|^$*]?=("(.*?)"|([\w-]+)))?(\s+[is])?\]$/.exec(l);
              if (!d) throw new Error(r);
              u(d[1], null !== (n = null !== (t = d[4]) && void 0 !== t ? t : d[5]) && void 0 !== n ? n : "");
              break;
            default:
              throw new Error(r);
          }
        }
        return [o, i];
      }(e), n = t[0], r = t[1], a = document.createElement(null != n ? n : "div"), o = 0, i = Object.keys(r); o < i.length; o++) {
      var c = i[o],
        u = r[c].join(" ");
      "style" === c ? z(a.style, u) : a.setAttribute(c, u);
    }
    return a;
  }
  function z(e, t) {
    for (var n = 0, r = t.split(";"); n < r.length; n++) {
      var a = r[n],
        o = /^\s*([\w-]+)\s*:\s*(.+?)(\s*!([\w-]+))?\s*$/.exec(a);
      if (o) {
        var i = o[1],
          c = o[2],
          u = o[4];
        e.setProperty(i, c, u || "");
      }
    }
  }
  var I = ["monospace", "sans-serif", "serif"],
    B = ["sans-serif-thin", "ARNO PRO", "Agency FB", "Arabic Typesetting", "Arial Unicode MS", "AvantGarde Bk BT", "BankGothic Md BT", "Batang", "Bitstream Vera Sans Mono", "Calibri", "Century", "Century Gothic", "Clarendon", "EUROSTILE", "Franklin Gothic", "Futura Bk BT", "Futura Md BT", "GOTHAM", "Gill Sans", "HELV", "Haettenschweiler", "Helvetica Neue", "Humanst521 BT", "Leelawadee", "Letter Gothic", "Levenim MT", "Lucida Bright", "Lucida Sans", "Menlo", "MS Mincho", "MS Outlook", "MS Reference Specialty", "MS UI Gothic", "MT Extra", "MYRIAD PRO", "Marlett", "Meiryo UI", "Microsoft Uighur", "Minion Pro", "Monotype Corsiva", "PMingLiU", "Pristina", "SCRIPTINA", "Segoe UI Light", "Serifa", "SimHei", "Small Fonts", "Staccato222 BT", "TRAJAN PRO", "Univers CE 55 Medium", "Vrinda", "ZWAdobeF"];
  function F(e) {
    return e.rect(0, 0, 10, 10), e.rect(2, 2, 6, 6), !e.isPointInPath(5, 5, "evenodd");
  }
  function O(e, t) {
    e.width = 240, e.height = 60, t.textBaseline = "alphabetic", t.fillStyle = "#f60", t.fillRect(100, 1, 62, 20), t.fillStyle = "#069", t.font = '11pt "Times New Roman"';
    var n = "Cwm fjordbank gly " + String.fromCharCode(55357, 56835);
    return t.fillText(n, 2, 15), t.fillStyle = "rgba(102, 204, 0, 0.2)", t.font = "18pt Arial", t.fillText(n, 4, 45), G(e);
  }
  function R(e, t) {
    e.width = 122, e.height = 110, t.globalCompositeOperation = "multiply";
    for (var n = 0, r = [["#f2f", 40, 40], ["#2ff", 80, 40], ["#ff2", 60, 80]]; n < r.length; n++) {
      var a = r[n],
        o = a[0],
        i = a[1],
        c = a[2];
      t.fillStyle = o, t.beginPath(), t.arc(i, c, 40, 0, 2 * Math.PI, !0), t.closePath(), t.fill();
    }
    return t.fillStyle = "#f9c", t.arc(60, 60, 60, 0, 2 * Math.PI, !0), t.arc(60, 60, 20, 0, 2 * Math.PI, !0), t.fill("evenodd"), G(e);
  }
  function G(e) {
    return e.toDataURL();
  }
  var j, U;
  function W() {
    var e = this;
    return function () {
      if (void 0 === U) {
        var e = function () {
          var t = N();
          q(t) ? U = setTimeout(e, 2500) : (j = t, U = void 0);
        };
        e();
      }
    }(), function () {
      return n(e, void 0, void 0, function () {
        var e;
        return r(this, function (t) {
          switch (t.label) {
            case 0:
              return q(e = N()) ? j ? [2, a(j)] : _() ? [4, (n = document, (n.exitFullscreen || n.msExitFullscreen || n.mozCancelFullScreen || n.webkitExitFullscreen).call(n))] : [3, 2] : [3, 2];
            case 1:
              t.sent(), e = N(), t.label = 2;
            case 2:
              return q(e) || (j = e), [2, e];
          }
          var n;
        });
      });
    };
  }
  function N() {
    var e = screen;
    return [g(m(e.availTop), null), g(m(e.width) - m(e.availWidth) - g(m(e.availLeft), 0), null), g(m(e.height) - m(e.availHeight) - g(m(e.availTop), 0), null), g(m(e.availLeft), null)];
  }
  function q(e) {
    for (var t = 0; t < 4; ++t) if (e[t]) return !1;
    return !0;
  }
  var H = {
    abpIndo: ["#Iklan-Melayang", "#Kolom-Iklan-728", "#SidebarIklan-wrapper", 'a[title="7naga poker" i]', '[title="ALIENBOLA" i]'],
    abpvn: ["#quangcaomb", ".iosAdsiosAds-layout", ".quangcao", '[href^="https://r88.vn/"]', '[href^="https://zbet.vn/"]'],
    adBlockFinland: [".mainostila", ".sponsorit", ".ylamainos", 'a[href*="/clickthrgh.asp?"]', 'a[href^="https://app.readpeak.com/ads"]'],
    adBlockPersian: ["#navbar_notice_50", 'a[href^="http://g1.v.fwmrm.net/ad/"]', ".kadr", 'TABLE[width="140px"]', "#divAgahi"],
    adBlockWarningRemoval: ["#adblock-honeypot", ".adblocker-root", ".wp_adblock_detect"],
    adGuardAnnoyances: ['amp-embed[type="zen"]', ".hs-sosyal", "#cookieconsentdiv", 'div[class^="app_gdpr"]', ".as-oil"],
    adGuardBase: ["#ad-after", "#ad-p3", ".BetterJsPopOverlay", "#ad_300X250", "#bannerfloat22"],
    adGuardChinese: ['a[href*=".ttz5.cn"]', 'a[href*=".yabovip2027.com/"]', ".tm3all2h4b", ".cc5278_banner_ad"],
    adGuardFrench: [".zonepub", '[class*="_adLeaderboard"]', '[id^="block-xiti_oas-"]', 'a[href^="http://ptapjmp.com/"]', 'a[href^="https://go.alvexo.com/"]'],
    adGuardGerman: [".banneritemwerbung_head_1", ".boxstartwerbung", ".werbung3", 'a[href^="http://www.eis.de/index.phtml?refid="]', 'a[href^="https://www.tipico.com/?affiliateId="]'],
    adGuardJapanese: ["#kauli_yad_1", "#ad-giftext", "#adsSPRBlock", 'a[href^="http://ad2.trafficgate.net/"]', 'a[href^="http://www.rssad.jp/"]'],
    adGuardMobile: ["amp-auto-ads", "#mgid_iframe", ".amp_ad", 'amp-embed[type="24smi"]', "#mgid_iframe1"],
    adGuardRussian: ['a[href^="https://ya-distrib.ru/r/"]', 'a[href^="https://ad.letmeads.com/"]', ".reclama", 'div[id^="smi2adblock"]', 'div[id^="AdFox_banner_"]'],
    adGuardSocial: ['a[href^="//www.stumbleupon.com/submit?url="]', 'a[href^="//telegram.me/share/url?"]', ".etsy-tweet", "#inlineShare", ".popup-social"],
    adGuardSpanishPortuguese: ["#barraPublicidade", "#Publicidade", "#publiEspecial", "#queTooltip", '[href^="http://ads.glispa.com/"]'],
    adGuardTrackingProtection: ['amp-embed[type="taboola"]', "#qoo-counter", 'a[href^="http://click.hotlog.ru/"]', 'a[href^="http://hitcounter.ru/top/stat.php"]', 'a[href^="http://top.mail.ru/jump"]'],
    adGuardTurkish: ["#backkapat", "#reklami", 'a[href^="http://adserv.ontek.com.tr/"]', 'a[href^="http://izlenzi.com/campaign/"]', 'a[href^="http://www.installads.net/"]'],
    bulgarian: ["td#freenet_table_ads", "#adbody", "#ea_intext_div", ".lapni-pop-over", "#xenium_hot_offers"],
    easyList: ["#AD_banner_bottom", "#Ads_google_02", "#N-ad-article-rightRail-1", "#ad-fullbanner2", "#ad-zone-2"],
    easyListChina: ['a[href*=".wensixuetang.com/"]', 'A[href*="/hth107.com/"]', '.appguide-wrap[onclick*="bcebos.com"]', ".frontpageAdvM", "#taotaole"],
    easyListCookie: ["#adtoniq-msg-bar", "#CoockiesPage", "#CookieModal_cookiemodal", "#DO_CC_PANEL", "#ShowCookie"],
    easyListCzechSlovak: ["#onlajny-stickers", "#reklamni-box", ".reklama-megaboard", ".sklik", '[id^="sklikReklama"]'],
    easyListDutch: ["#advertentie", "#vipAdmarktBannerBlock", ".adstekst", 'a[href^="https://xltube.nl/click/"]', "#semilo-lrectangle"],
    easyListGermany: ['a[href^="http://www.hw-area.com/?dp="]', 'a[href^="https://ads.sunmaker.com/tracking.php?"]', ".werbung-skyscraper2", ".bannergroup_werbung", ".ads_rechts"],
    easyListItaly: [".box_adv_annunci", ".sb-box-pubbliredazionale", 'a[href^="http://affiliazioniads.snai.it/"]', 'a[href^="https://adserver.html.it/"]', 'a[href^="https://affiliazioniads.snai.it/"]'],
    easyListLithuania: [".reklamos_tarpas", ".reklamos_nuorodos", 'img[alt="Reklaminis skydelis"]', 'img[alt="Dedikuoti.lt serveriai"]', 'img[alt="Hostingas Serveriai.lt"]'],
    estonian: ['A[href*="http://pay4results24.eu"]'],
    fanboyAnnoyances: ["#feedback-tab", "#taboola-below-article", ".feedburnerFeedBlock", ".widget-feedburner-counter", '[title="Subscribe to our blog"]'],
    fanboyAntiFacebook: [".util-bar-module-firefly-visible"],
    fanboyEnhancedTrackers: [".open.pushModal", "#issuem-leaky-paywall-articles-zero-remaining-nag", "#sovrn_container", 'div[class$="-hide"][zoompage-fontsize][style="display: block;"]', ".BlockNag__Card"],
    fanboySocial: [".td-tags-and-social-wrapper-box", ".twitterContainer", ".youtube-social", 'a[title^="Like us on Facebook"]', 'img[alt^="Share on Digg"]'],
    frellwitSwedish: ['a[href*="casinopro.se"][target="_blank"]', 'a[href*="doktor-se.onelink.me"]', "article.category-samarbete", "div.holidAds", "ul.adsmodern"],
    greekAdBlock: ['A[href*="adman.otenet.gr/click?"]', 'A[href*="http://axiabanners.exodus.gr/"]', 'A[href*="http://interactive.forthnet.gr/click?"]', "DIV.agores300", "TABLE.advright"],
    hungarian: ['A[href*="ad.eval.hu"]', 'A[href*="ad.netmedia.hu"]', 'A[href*="daserver.ultraweb.hu"]', "#cemp_doboz", ".optimonk-iframe-container"],
    iDontCareAboutCookies: ['.alert-info[data-block-track*="CookieNotice"]', ".ModuleTemplateCookieIndicator", ".o--cookies--container", ".cookie-msg-info-container", "#cookies-policy-sticky"],
    icelandicAbp: ['A[href^="/framework/resources/forms/ads.aspx"]'],
    latvian: ['a[href="http://www.salidzini.lv/"][style="display: block; width: 120px; height: 40px; overflow: hidden; position: relative;"]', 'a[href="http://www.salidzini.lv/"][style="display: block; width: 88px; height: 31px; overflow: hidden; position: relative;"]'],
    listKr: ['a[href*="//kingtoon.slnk.kr"]', 'a[href*="//playdsb.com/kr"]', "div.logly-lift-adz", 'div[data-widget_id="ml6EJ074"]', "ins.daum_ddn_area"],
    listeAr: [".geminiLB1Ad", ".right-and-left-sponsers", 'a[href*=".aflam.info"]', 'a[href*="booraq.org"]', 'a[href*="dubizzle.com/ar/?utm_source="]'],
    listeFr: ['a[href^="http://promo.vador.com/"]', "#adcontainer_recherche", 'a[href*="weborama.fr/fcgi-bin/"]', ".site-pub-interstitiel", 'div[id^="crt-"][data-criteo-id]'],
    officialPolish: ["#ceneo-placeholder-ceneo-12", '[href^="https://aff.sendhub.pl/"]', 'a[href^="http://advmanager.techfun.pl/redirect/"]', 'a[href^="http://www.trizer.pl/?utm_source"]', "div#skapiec_ad"],
    ro: ['a[href^="//afftrk.altex.ro/Counter/Click"]', 'a[href^="/magazin/"]', 'a[href^="https://blackfridaysales.ro/trk/shop/"]', 'a[href^="https://event.2performant.com/events/click"]', 'a[href^="https://l.profitshare.ro/"]'],
    ruAd: ['a[href*="//febrare.ru/"]', 'a[href*="//utimg.ru/"]', 'a[href*="://chikidiki.ru"]', "#pgeldiz", ".yandex-rtb-block"],
    thaiAds: ["a[href*=macau-uta-popup]", "#ads-google-middle_rectangle-group", ".ads300s", ".bumq", ".img-kosana"],
    webAnnoyancesUltralist: ["#mod-social-share-2", "#social-tools", ".ctpl-fullbanner", ".zergnet-recommend", ".yt.btn-link.btn-md.btn"]
  };
  function J(e) {
    var t;
    return n(this, void 0, void 0, function () {
      var n, a, i, c, u, s, l;
      return r(this, function (r) {
        switch (r.label) {
          case 0:
            for (n = document, a = n.createElement("div"), i = new Array(e.length), c = {}, $(a), l = 0; l < e.length; ++l) u = L(e[l]), $(s = n.createElement("div")), s.appendChild(u), a.appendChild(s), i[l] = u;
            r.label = 1;
          case 1:
            return n.body ? [3, 3] : [4, o(50)];
          case 2:
            return r.sent(), [3, 1];
          case 3:
            n.body.appendChild(a);
            try {
              for (l = 0; l < e.length; ++l) i[l].offsetParent || (c[e[l]] = !0);
            } finally {
              null === (t = a.parentNode) || void 0 === t || t.removeChild(a);
            }
            return [2, c];
        }
      });
    });
  }
  function $(e) {
    e.style.setProperty("display", "block", "important");
  }
  function V(e) {
    return matchMedia("(inverted-colors: " + e + ")").matches;
  }
  function K(e) {
    return matchMedia("(forced-colors: " + e + ")").matches;
  }
  function X(e) {
    return matchMedia("(prefers-contrast: " + e + ")").matches;
  }
  function Y(e) {
    return matchMedia("(prefers-reduced-motion: " + e + ")").matches;
  }
  function Z(e) {
    return matchMedia("(dynamic-range: " + e + ")").matches;
  }
  var Q = Math,
    ee = function () {
      return 0;
    };
  var te = {
    default: [],
    apple: [{
      font: "-apple-system-body"
    }],
    serif: [{
      fontFamily: "serif"
    }],
    sans: [{
      fontFamily: "sans-serif"
    }],
    mono: [{
      fontFamily: "monospace"
    }],
    min: [{
      fontSize: "1px"
    }],
    system: [{
      fontFamily: "system-ui"
    }]
  };
  var ne = {
    fonts: function () {
      return D(function (e, t) {
        var n = t.document,
          r = n.body;
        r.style.fontSize = "48px";
        var a = n.createElement("div"),
          o = {},
          i = {},
          c = function (e) {
            var t = n.createElement("span"),
              r = t.style;
            return r.position = "absolute", r.top = "0", r.left = "0", r.fontFamily = e, t.textContent = "mmMwWLliI0O&1", a.appendChild(t), t;
          },
          u = I.map(c),
          s = function () {
            for (var e = {}, t = function (t) {
                e[t] = I.map(function (e) {
                  return function (e, t) {
                    return c("'" + e + "'," + t);
                  }(t, e);
                });
              }, n = 0, r = B; n < r.length; n++) {
              t(r[n]);
            }
            return e;
          }();
        r.appendChild(a);
        for (var l = 0; l < I.length; l++) o[I[l]] = u[l].offsetWidth, i[I[l]] = u[l].offsetHeight;
        return B.filter(function (e) {
          return t = s[e], I.some(function (e, n) {
            return t[n].offsetWidth !== o[e] || t[n].offsetHeight !== i[e];
          });
          var t;
        });
      });
    },
    domBlockers: function (e) {
      var t = (void 0 === e ? {} : e).debug;
      return n(this, void 0, void 0, function () {
        var e, n, a, o;
        return r(this, function (r) {
          switch (r.label) {
            case 0:
              return x() || T() ? (e = Object.keys(H), [4, J((o = []).concat.apply(o, e.map(function (e) {
                return H[e];
              })))]) : [2, void 0];
            case 1:
              return n = r.sent(), t && function (e) {
                for (var t = "DOM blockers debug:\n```", n = 0, r = Object.keys(H); n < r.length; n++) {
                  var a = r[n];
                  t += "\n" + a + ":";
                  for (var o = 0, i = H[a]; o < i.length; o++) {
                    var c = i[o];
                    t += "\n  " + c + " " + (e[c] ? "🚫" : "➡️");
                  }
                }
                console.log(t + "\n```");
              }(n), (a = e.filter(function (e) {
                var t = H[e];
                return b(t.map(function (e) {
                  return n[e];
                })) > .6 * t.length;
              })).sort(), [2, a];
          }
        });
      });
    },
    fontPreferences: function () {
      return function (e, t) {
        void 0 === t && (t = 4e3);
        return D(function (n, r) {
          var o = r.document,
            i = o.body,
            c = i.style;
          c.width = t + "px", c.webkitTextSizeAdjust = c.textSizeAdjust = "none", S() ? i.style.zoom = "" + 1 / r.devicePixelRatio : x() && (i.style.zoom = "reset");
          var u = o.createElement("div");
          return u.textContent = a(Array(t / 20 << 0)).map(function () {
            return "word";
          }).join(" "), i.appendChild(u), e(o, i);
        }, '<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1">');
      }(function (e, t) {
        for (var n = {}, r = {}, a = 0, o = Object.keys(te); a < o.length; a++) {
          var i = o[a],
            c = te[i],
            u = c[0],
            s = void 0 === u ? {} : u,
            l = c[1],
            d = void 0 === l ? "mmMwWLliI0fiflO&1" : l,
            f = e.createElement("span");
          f.textContent = d, f.style.whiteSpace = "nowrap";
          for (var h = 0, v = Object.keys(s); h < v.length; h++) {
            var p = v[h],
              m = s[p];
            void 0 !== m && (f.style[p] = m);
          }
          n[i] = f, t.appendChild(e.createElement("br")), t.appendChild(f);
        }
        for (var g = 0, b = Object.keys(te); g < b.length; g++) {
          r[i = b[g]] = n[i].getBoundingClientRect().width;
        }
        return r;
      });
    },
    audio: function () {
      var e = window,
        t = e.OfflineAudioContext || e.webkitOfflineAudioContext;
      if (!t) return -2;
      if (x() && !M() && !function () {
        var e = window;
        return b(["DOMRectList" in e, "RTCPeerConnectionIceEvent" in e, "SVGGeometryElement" in e, "ontransitioncancel" in e]) >= 3;
      }()) return -1;
      var n = new t(1, 5e3, 44100),
        r = n.createOscillator();
      r.type = "triangle", r.frequency.value = 1e4;
      var a = n.createDynamicsCompressor();
      a.threshold.value = -50, a.knee.value = 40, a.ratio.value = 12, a.attack.value = 0, a.release.value = .25, r.connect(a), a.connect(n.destination), r.start(0);
      var o = function (e) {
          var t = 3,
            n = 500,
            r = 500,
            a = 5e3,
            o = function () {};
          return [new Promise(function (i, c) {
            var u = !1,
              s = 0,
              l = 0;
            e.oncomplete = function (e) {
              return i(e.renderedBuffer);
            };
            var d = function () {
                setTimeout(function () {
                  return c(E("timeout"));
                }, Math.min(r, l + a - Date.now()));
              },
              f = function () {
                try {
                  switch (e.startRendering(), e.state) {
                    case "running":
                      l = Date.now(), u && d();
                      break;
                    case "suspended":
                      document.hidden || s++, u && s >= t ? c(E("suspended")) : setTimeout(f, n);
                  }
                } catch (r) {
                  c(r);
                }
              };
            f(), o = function () {
              u || (u = !0, l > 0 && d());
            };
          }), o];
        }(n),
        i = o[0],
        c = o[1],
        u = i.then(function (e) {
          return function (e) {
            for (var t = 0, n = 0; n < e.length; ++n) t += Math.abs(e[n]);
            return t;
          }(e.getChannelData(0).subarray(4500));
        }, function (e) {
          if ("timeout" === e.name || "suspended" === e.name) return -3;
          throw e;
        });
      return u.catch(function () {}), function () {
        return c(), u;
      };
    },
    screenFrame: function () {
      var e = this,
        t = W();
      return function () {
        return n(e, void 0, void 0, function () {
          var e, n;
          return r(this, function (r) {
            switch (r.label) {
              case 0:
                return [4, t()];
              case 1:
                return e = r.sent(), [2, [(n = function (e) {
                  return null === e ? null : w(e, 10);
                })(e[0]), n(e[1]), n(e[2]), n(e[3])]];
            }
          });
        });
      };
    },
    osCpu: function () {
      return navigator.oscpu;
    },
    languages: function () {
      var e,
        t = navigator,
        n = [],
        r = t.language || t.userLanguage || t.browserLanguage || t.systemLanguage;
      if (void 0 !== r && n.push([r]), Array.isArray(t.languages)) S() && b([!("MediaSettingsRange" in (e = window)), "RTCEncodedAudioFrame" in e, "" + e.Intl == "[object Intl]", "" + e.Reflect == "[object Reflect]"]) >= 3 || n.push(t.languages);else if ("string" == typeof t.languages) {
        var a = t.languages;
        a && n.push(a.split(","));
      }
      return n;
    },
    colorDepth: function () {
      return window.screen.colorDepth;
    },
    deviceMemory: function () {
      return g(m(navigator.deviceMemory), void 0);
    },
    screenResolution: function () {
      var e = screen,
        t = function (e) {
          return g(p(e), null);
        },
        n = [t(e.width), t(e.height)];
      return n.sort().reverse(), n;
    },
    hardwareConcurrency: function () {
      return g(p(navigator.hardwareConcurrency), void 0);
    },
    timezone: function () {
      var e,
        t = null === (e = window.Intl) || void 0 === e ? void 0 : e.DateTimeFormat;
      if (t) {
        var n = new t().resolvedOptions().timeZone;
        if (n) return n;
      }
      var r,
        a = (r = new Date().getFullYear(), -Math.max(m(new Date(r, 0, 1).getTimezoneOffset()), m(new Date(r, 6, 1).getTimezoneOffset())));
      return "UTC" + (a >= 0 ? "+" : "") + Math.abs(a);
    },
    sessionStorage: function () {
      try {
        return !!window.sessionStorage;
      } catch (e) {
        return !0;
      }
    },
    localStorage: function () {
      try {
        return !!window.localStorage;
      } catch (e) {
        return !0;
      }
    },
    indexedDB: function () {
      if (!C() && !A()) try {
        return !!window.indexedDB;
      } catch (e) {
        return !0;
      }
    },
    openDatabase: function () {
      return !!window.openDatabase;
    },
    cpuClass: function () {
      return navigator.cpuClass;
    },
    platform: function () {
      var e = navigator.platform;
      return "MacIntel" === e && x() && !M() ? function () {
        if ("iPad" === navigator.platform) return !0;
        var e = screen,
          t = e.width / e.height;
        return b(["MediaSource" in window, !!Element.prototype.webkitRequestFullscreen, t > .65 && t < 1.53]) >= 2;
      }() ? "iPad" : "iPhone" : e;
    },
    plugins: function () {
      var e = navigator.plugins;
      if (e) {
        for (var t = [], n = 0; n < e.length; ++n) {
          var r = e[n];
          if (r) {
            for (var a = [], o = 0; o < r.length; ++o) {
              var i = r[o];
              a.push({
                type: i.type,
                suffixes: i.suffixes
              });
            }
            t.push({
              name: r.name,
              description: r.description,
              mimeTypes: a
            });
          }
        }
        return t;
      }
    },
    canvas: function () {
      var e = function () {
          var e = document.createElement("canvas");
          return e.width = 1, e.height = 1, [e, e.getContext("2d")];
        }(),
        t = e[0],
        n = e[1];
      return function (e, t) {
        return !(!t || !e.toDataURL);
      }(t, n) ? {
        winding: F(n),
        geometry: R(t, n),
        text: O(t, n)
      } : {
        winding: !1,
        geometry: "",
        text: ""
      };
    },
    touchSupport: function () {
      var e,
        t = navigator,
        n = 0;
      void 0 !== t.maxTouchPoints ? n = p(t.maxTouchPoints) : void 0 !== t.msMaxTouchPoints && (n = t.msMaxTouchPoints);
      try {
        document.createEvent("TouchEvent"), e = !0;
      } catch (r) {
        e = !1;
      }
      return {
        maxTouchPoints: n,
        touchEvent: e,
        touchStart: "ontouchstart" in window
      };
    },
    vendor: function () {
      return navigator.vendor || "";
    },
    vendorFlavors: function () {
      for (var e = [], t = 0, n = ["chrome", "safari", "__crWeb", "__gCrWeb", "yandex", "__yb", "__ybro", "__firefox__", "__edgeTrackingPreventionStatistics", "webkit", "oprt", "samsungAr", "ucweb", "UCShellJava", "puffinDevice"]; t < n.length; t++) {
        var r = n[t],
          a = window[r];
        a && "object" == typeof a && e.push(r);
      }
      return e.sort();
    },
    cookiesEnabled: function () {
      var e = document;
      try {
        e.cookie = "cookietest=1; SameSite=Strict;";
        var t = -1 !== e.cookie.indexOf("cookietest=");
        return e.cookie = "cookietest=1; SameSite=Strict; expires=Thu, 01-Jan-1970 00:00:01 GMT", t;
      } catch (n) {
        return !1;
      }
    },
    colorGamut: function () {
      for (var e = 0, t = ["rec2020", "p3", "srgb"]; e < t.length; e++) {
        var n = t[e];
        if (matchMedia("(color-gamut: " + n + ")").matches) return n;
      }
    },
    invertedColors: function () {
      return !!V("inverted") || !V("none") && void 0;
    },
    forcedColors: function () {
      return !!K("active") || !K("none") && void 0;
    },
    monochrome: function () {
      if (matchMedia("(min-monochrome: 0)").matches) {
        for (var e = 0; e <= 100; ++e) if (matchMedia("(max-monochrome: " + e + ")").matches) return e;
        throw new Error("Too high value");
      }
    },
    contrast: function () {
      return X("no-preference") ? 0 : X("high") || X("more") ? 1 : X("low") || X("less") ? -1 : X("forced") ? 10 : void 0;
    },
    reducedMotion: function () {
      return !!Y("reduce") || !Y("no-preference") && void 0;
    },
    hdr: function () {
      return !!Z("high") || !Z("standard") && void 0;
    },
    math: function () {
      var e,
        t = Q.acos || ee,
        n = Q.acosh || ee,
        r = Q.asin || ee,
        a = Q.asinh || ee,
        o = Q.atanh || ee,
        i = Q.atan || ee,
        c = Q.sin || ee,
        u = Q.sinh || ee,
        s = Q.cos || ee,
        l = Q.cosh || ee,
        d = Q.tan || ee,
        f = Q.tanh || ee,
        h = Q.exp || ee,
        v = Q.expm1 || ee,
        p = Q.log1p || ee;
      return {
        acos: t(.12312423423423424),
        acosh: n(1e308),
        acoshPf: (e = 1e154, Q.log(e + Q.sqrt(e * e - 1))),
        asin: r(.12312423423423424),
        asinh: a(1),
        asinhPf: function (e) {
          return Q.log(e + Q.sqrt(e * e + 1));
        }(1),
        atanh: o(.5),
        atanhPf: function (e) {
          return Q.log((1 + e) / (1 - e)) / 2;
        }(.5),
        atan: i(.5),
        sin: c(-1e300),
        sinh: u(1),
        sinhPf: function (e) {
          return Q.exp(e) - 1 / Q.exp(e) / 2;
        }(1),
        cos: s(10.000000000123),
        cosh: l(1),
        coshPf: function (e) {
          return (Q.exp(e) + 1 / Q.exp(e)) / 2;
        }(1),
        tan: d(-1e300),
        tanh: f(1),
        tanhPf: function (e) {
          return (Q.exp(2 * e) - 1) / (Q.exp(2 * e) + 1);
        }(1),
        exp: h(1),
        expm1: v(1),
        expm1Pf: function (e) {
          return Q.exp(e) - 1;
        }(1),
        log1p: p(10),
        log1pPf: function (e) {
          return Q.log(1 + e);
        }(10),
        powPI: function (e) {
          return Q.pow(Q.PI, e);
        }(-100)
      };
    }
  };
  function re(e) {
    var t = function (e) {
        if (T()) return .4;
        if (x()) return M() ? .5 : .3;
        var t = e.platform.value || "";
        if (/^Win/.test(t)) return .6;
        if (/^Mac/.test(t)) return .5;
        return .7;
      }(e),
      n = function (e) {
        return w(.99 + .01 * e, 1e-4);
      }(t);
    return {
      score: t,
      comment: "$ if upgrade to Pro: https://fpjs.dev/pro".replace(/\$/g, "" + n)
    };
  }
  function ae(e) {
    return JSON.stringify(e, function (e, n) {
      return n instanceof Error ? t({
        name: (r = n).name,
        message: r.message,
        stack: null === (a = r.stack) || void 0 === a ? void 0 : a.split("\n")
      }, r) : n;
      var r, a;
    }, 2);
  }
  function oe(e) {
    return v(function (e) {
      for (var t = "", n = 0, r = Object.keys(e).sort(); n < r.length; n++) {
        var a = r[n],
          o = e[a],
          i = o.error ? "error" : JSON.stringify(o.value);
        t += (t ? "|" : "") + a.replace(/([:|\\])/g, "\\$1") + ":" + i;
      }
      return t;
    }(e));
  }
  function ie(e) {
    return void 0 === e && (e = 50), function (e, t) {
      void 0 === t && (t = 1 / 0);
      var n = window.requestIdleCallback;
      return n ? new Promise(function (e) {
        return n.call(window, function () {
          return e();
        }, {
          timeout: t
        });
      }) : o(Math.min(e, t));
    }(e, 2 * e);
  }
  function ce(e, t) {
    var a = Date.now();
    return {
      get: function (o) {
        return n(this, void 0, void 0, function () {
          var n, i, c;
          return r(this, function (r) {
            switch (r.label) {
              case 0:
                return n = Date.now(), [4, e()];
              case 1:
                return i = r.sent(), c = function (e) {
                  var t;
                  return {
                    get visitorId() {
                      return void 0 === t && (t = oe(this.components)), t;
                    },
                    set visitorId(e) {
                      t = e;
                    },
                    confidence: re(e),
                    components: e,
                    version: "3.3.3"
                  };
                }(i), (t || (null == o ? void 0 : o.debug)) && console.log("Copy the text below to get the debug data:\n\n```\nversion: " + c.version + "\nuserAgent: " + navigator.userAgent + "\ntimeBetweenLoadAndGet: " + (n - a) + "\nvisitorId: " + c.visitorId + "\ncomponents: " + ae(i) + "\n```"), [2, c];
            }
          });
        });
      }
    };
  }
  function ue(e) {
    var t = void 0 === e ? {} : e,
      a = t.delayFallback,
      o = t.debug;
    t.monitoring;
    return n(this, void 0, void 0, function () {
      return r(this, function (e) {
        switch (e.label) {
          case 0:
            return [4, ie(a)];
          case 1:
            return e.sent(), [2, ce(k(ne, {
              debug: o
            }, []), o)];
        }
      });
    });
  }
  var se = {
      load: ue,
      hashComponents: oe,
      componentsToDebugString: ae
    },
    le = v;
  e.componentsToDebugString = ae, e.default = se, e.getFullscreenElement = _, e.getScreenFrame = W, e.hashComponents = oe, e.isAndroid = T, e.isChromium = S, e.isDesktopSafari = M, e.isEdgeHTML = A, e.isGecko = P, e.isTrident = C, e.isWebKit = x, e.load = ue, e.loadSources = k, e.murmurX64Hash128 = le, e.prepareForSources = ie, e.sources = ne, Object.defineProperty(e, "__esModule", {
    value: !0
  });
});

var coreTools = {
  /**
   * Получение паравметров из хэша
   * @param url
   * @returns {{module: string, action: string, params: string}}
   */
  getParams: function getParams(url) {
    if (typeof url === 'undefined') {
      url = '/mod' + location.hash.substring(1);
    }
    var params = url.match(/^\/mod\/([a-z0-9_]*)(?:\/|)([a-z0-9_]*)(?:(\?[^?]*)|)/);
    var result = {
      module: params !== null && typeof params[1] === 'string' ? params[1] : '',
      section: params !== null && typeof params[2] === 'string' ? params[2] : '',
      query: params !== null && typeof params[3] === 'string' ? params[3] : ''
    };
    result.query = coreTools.parseQuery(result.query);
    return result;
  },
  /**
   * @param {String} query
   * @returns {{}}
   */
  parseQuery: function parseQuery(query) {
    query = typeof query === 'string' ? query.replace(/^\?/, '') : '';
    var vars = query.split("&");
    var query_string = {};
    for (var i = 0; i < vars.length; i++) {
      var pair = vars[i].split("=");
      var key = decodeURIComponent(pair[0]);
      var value = decodeURIComponent(pair[1]);
      if (typeof query_string[key] === "undefined") {
        query_string[key] = decodeURIComponent(value);
      } else if (typeof query_string[key] === "string") {
        query_string[key] = [query_string[key], decodeURIComponent(value)];
      } else {
        query_string[key].push(decodeURIComponent(value));
      }
    }
    return query_string;
  },
  /**
   *
   */
  toggleFullscreen: function toggleFullscreen() {
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen();
    } else {
      if (document.exitFullscreen) {
        document.exitFullscreen();
      }
    }
  },
  /**
   * Форматирование числа
   * @param   {number|string} numb
   * @returns {string}
   * @private
   */
  formatNumber: function formatNumber(numb) {
    numb = numb.toString();
    return numb.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
  },
  /**
   * Форматирование числа
   * @param   {number|string} numb
   * @param   {string}       divider
   * @returns {string}
   * @private
   */
  formatMoney: function formatMoney(numb, divider) {
    if (isNaN(numb)) {
      return this.formatNumber(numb);
    } else {
      divider = divider || ' ';
      numb = Number(numb).toFixed(2).toString();
      return numb.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1' + divider);
    }
  },
  /**
   * Копирование
   * @param text
   * @returns {Promise<unknown>|Promise<void>}
   */
  clipboardText: function clipboardText(text) {
    /**
     * Старый вариант копирования
     * @param text
     */
    function fallbackCopyTextToClipboard(text) {
      return new Promise(function (resolve, reject) {
        var textArea = document.createElement("textarea");
        textArea.value = text;

        // Avoid scrolling to bottom
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
          var successful = document.execCommand('copy');
          document.body.removeChild(textArea);
          if (successful) {
            resolve();
          } else {
            reject();
          }
        } catch (err) {
          document.body.removeChild(textArea);
          reject();
        }
      });
    }

    /**
     * @param text
     * @returns {Promise<void>|Promise<unknown>}
     */
    function copyTextToClipboard(text) {
      if (!navigator.clipboard) {
        return fallbackCopyTextToClipboard(text);
      }
      return navigator.clipboard.writeText(text);
    }
    return copyTextToClipboard(text);
  },
  /**
   * @returns {number}
   * @private
   */
  hashCode: function hashCode() {
    var string = 'A' + new Date().getTime();
    for (var h = 0, i = 0; i < string.length; h &= h) {
      h = 31 * h + string.charCodeAt(i++);
    }
    return Math.abs(h);
  },
  /**
   * @returns Promise
   */
  getFingerprint: function getFingerprint() {
    return FingerprintJS.load().then(function (fp) {
      return fp.get();
    }).then(function (result) {
      return result.visitorId;
    });
  },
  /**
   * @param token
   * @returns {*}
   */
  jwtDecode: function jwtDecode(token) {
    return o(token);
  }
};

var coreTokens = {
  _refreshInterval: 0,
  /**
   *
   */
  initRefresh: function initRefresh() {
    this.deinitRefresh();
    this._refreshInterval = setInterval(this.refreshToken, 300000); // 5 минут
  },
  /**
   * s
   */
  deinitRefresh: function deinitRefresh() {
    if (this._refreshInterval) {
      clearInterval(this._refreshInterval);
    }
  },
  /**
   * @param success
   * @param fail
   * @returns {Promise<void>}
   */
  refreshToken: function () {
    var _refreshToken = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee(success, fail) {
      var refreshToken, tokenData;
      return _regeneratorRuntime().wrap(function _callee$(_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            refreshToken = coreTokens.getRefreshToken();
            tokenData = coreTools.jwtDecode(refreshToken);
            if (!(new Date(tokenData.exp * 1000) <= new Date())) {
              _context.next = 6;
              break;
            }
            coreTokens.clearRefreshToken();
            if (typeof fail === 'function') {
              fail();
            }
            return _context.abrupt("return");
          case 6:
            _context.t0 = $;
            _context.t1 = coreMain.options.basePath + "/auth/refresh";
            _context.t2 = JSON;
            _context.t3 = refreshToken;
            _context.next = 12;
            return coreTools.getFingerprint();
          case 12:
            _context.t4 = _context.sent;
            _context.t5 = {
              refresh_token: _context.t3,
              fp: _context.t4
            };
            _context.t6 = _context.t2.stringify.call(_context.t2, _context.t5);
            _context.t7 = {
              url: _context.t1,
              method: "POST",
              dataType: "json",
              contentType: "application/json; charset=utf-8",
              data: _context.t6
            };
            _context.t0.ajax.call(_context.t0, _context.t7).done(function (response) {
              if (typeof response.access_token !== 'string' || typeof response.refresh_token !== 'string' || !response.access_token || !response.refresh_token) {
                var errorMessage = response.error_message || "Ошибка. Попробуйте позже, либо обратитесь к администратору";
                CoreUI.notice.danger(errorMessage);
                if (typeof fail === 'function') {
                  fail();
                }
              } else {
                coreTokens.setAccessToken(response.access_token);
                coreTokens.setRefreshToken(response.refresh_token);
                if (typeof success === 'function') {
                  success();
                }
              }
            }).fail(function (response) {
              var errorMessage = '';
              if (response.responseJSON && response.responseJSON.error_message) {
                errorMessage = response.responseJSON.error_message;
              } else {
                errorMessage = $("<div>" + response.responseText + "</div>").text();
              }
              errorMessage = errorMessage || 'Ошибка. Попробуйте позже, либо обратитесь к администратору';
              CoreUI.notice.danger(errorMessage);
              if (typeof fail === 'function') {
                fail();
              }
            });
          case 17:
          case "end":
            return _context.stop();
        }
      }, _callee);
    }));
    function refreshToken(_x, _x2) {
      return _refreshToken.apply(this, arguments);
    }
    return refreshToken;
  }(),
  /**
   * Получение аутентификации
   * @param accessToken
   * @returns {boolean}
   */
  setAccessToken: function setAccessToken(accessToken) {
    localStorage.setItem('core3_access_token', accessToken);
    var tokenData = coreTools.jwtDecode(coreTokens.getAccessToken());
    var dateExpired = new Date(tokenData.exp * 1000);
    if (dateExpired > new Date()) {
      var expires = "; expires=" + dateExpired.toUTCString();
      document.cookie = "Core-Access-Token=" + accessToken + expires + "; path=/" + coreMain.options.basePath;
    }
  },
  /**
   * Получение аутентификации
   * @param refreshToken
   * @returns {boolean}
   */
  setRefreshToken: function setRefreshToken(refreshToken) {
    localStorage.setItem('core3_refresh_token', refreshToken);
  },
  /**
   * Получение аутентификации
   * @returns {String|boolean}
   */
  getAccessToken: function getAccessToken() {
    var authToken = localStorage.getItem('core3_access_token');
    if (!authToken) {
      coreTokens.clearAccessToken();
      authToken = false;
    }
    return authToken;
  },
  /**
   * Получение аутентификации
   * @returns {String|boolean}
   */
  getRefreshToken: function getRefreshToken() {
    var refreshToken = localStorage.getItem('core3_refresh_token');
    if (!refreshToken) {
      coreTokens.clearRefreshToken();
      refreshToken = false;
    }
    return refreshToken;
  },
  /**
   * Очистка аутентификации
   */
  clearAccessToken: function clearAccessToken() {
    localStorage.removeItem('core3_access_token');
    document.cookie = 'Core-Access-Token=; Path=/' + coreMain.options.basePath + '; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
  },
  /**
   * Очистка аутентификации
   */
  clearRefreshToken: function clearRefreshToken() {
    localStorage.removeItem('core3_refresh_token');
  }
};

var coreMain = {
  activePage: null,
  options: {
    basePath: 'core3'
  },
  /**
   *
   */
  install: {
    event: null,
    promise: null
  },
  /**
   *
   */
  _hashChangeCallbacks: [],
  /**
   * @param pageName
   */
  viewPage: function viewPage(pageName) {
    if (Core[pageName]) {
      var pageContent = Core[pageName].getPageContent();
      $('.main').append('<div class="page page-' + pageName + '">' + pageContent + '</div>');
      Core[pageName].init();
      coreMain.activePage = pageName;
      var $otherPages = $('.main > .page:not(.page-' + pageName + ')');
      if ($otherPages[0]) {
        $otherPages.fadeOut('fast', function () {
          $otherPages.remove();
          $('.main > .page-' + pageName).fadeIn('fast');
        });
      } else {
        $('.main > .page-' + pageName).fadeIn('fast');
      }
    } else {
      CoreUI.alert.danger('Ошибка', 'Страница ' + pageName + ' не найдена');
    }
  },
  /**
   * @param eventName
   * @param callback
   */
  on: function on(eventName, callback) {
    if (eventName === 'hashchange') {
      coreMain._hashChangeCallbacks.push(callback);
    }
  },
  /**
   *
   */
  hashChange: function hashChange() {
    if (coreMain._hashChangeCallbacks.length > 0) {
      for (var i = 0; i < coreMain._hashChangeCallbacks.length; i++) {
        coreMain._hashChangeCallbacks[i]();
      }
    }
  },
  /**
   * @param text
   * @param options
   * @private
   */
  _: function _(text, options) {
    return text;
  }
};
$(function () {
  // Событие установки
  coreMain.install.promise = new Promise(function (resolve, reject) {
    window.addEventListener('beforeinstallprompt', function (event) {
      event.preventDefault();
      coreMain.install.event = event;
      resolve(event);
    });
  });
  var accessToken = coreTokens.getAccessToken();
  if (!accessToken) {
    coreMain.viewPage('auth');
  } else {
    coreTokens.refreshToken(function () {
      coreTokens.initRefresh();
      coreMain.viewPage('menu');
    }, function () {
      coreMain.viewPage('auth');
    });
  }
  if ("onhashchange" in window) {
    window.onhashchange = coreMain.hashChange;
  }
});

var coreTpl = Object.create(null);
coreTpl['menu/loader.html'] = '<div id="loader"> <div role="progressbar" class="mdc-linear-progress loader-progress" aria-label="Example Progress Bar" aria-valuemin="0" aria-valuemax="1" aria-valuenow="0"> <div class="mdc-linear-progress__buffer"> <div class="mdc-linear-progress__buffer-bar"></div> <div class="mdc-linear-progress__buffer-dots"></div> </div> <div class="mdc-linear-progress__bar mdc-linear-progress__primary-bar"> <span class="mdc-linear-progress__bar-inner"></span> </div> <div class="mdc-linear-progress__bar mdc-linear-progress__secondary-bar"> <span class="mdc-linear-progress__bar-inner"></span> </div> </div> <div class="loader-block"></div> </div>';
coreTpl['menu/main.html'] = '<header class="mdc-top-app-bar mdc-top-app-bar--fixed app-bar"> <div class="mdc-top-app-bar__row"> <section class="mdc-top-app-bar__section mdc-top-app-bar__section--align-start"> <button class="mdc-ripple-surface open-menu"><i class="fa-solid fa-bars"></i></button> <div class="header-title-container"> <span class="mdc-top-app-bar__title"></span> <span class="mdc-top-app-bar__subtitle"></span> </div> </section> <section class="mdc-top-app-bar__section mdc-top-app-bar__section--align-end" role="toolbar"></section> </div> </header> <aside class="menu-drawer"> <div class="menu-drawer__content"> <div class="menu-drawer__header"> <a class="module-home" href="#/"> <span class="fa-solid fa-house"></span> <h3 class="system-title"></h3> </a> </div> <ul class="menu-list level-1"></ul> </div> </aside> <div class="menu-drawer-scrim"></div> <div class="menu-drawer-swipe"></div> <div class="menu-drawer-app"> <main class="main-content"> <div class="main-wrapper"></div> </main> </div>';
coreTpl['menu/module.html'] = '<li class="menu-list-item core-module core-module-<%= module.name %> <% if (module.sections && module.sections.length > 0) { %>menu-item-nested<% } %>"> <div class="item-control"> <a href="#/<%= module.name %>/<%= module.index %>" class="mdc-ripple-surface" data-module="<%= module.name %>" data-section="<%= module.index %>"> <% if (module.icon) { %> <i class="<%= module.icon %>"></i> <% } else { %> <span class="module-icon-letter"><%= module.title.trim().substring(0, 1) %></span> <% } %> <span class="menu-list-item__text"><%= module.title %></span> </a> <% if (module.sections && module.sections.length > 0) { %> <button class="menu-icon-button mdc-ripple-surface"><i class="fa-solid fa-sort-down"></i></button> <% } %> </div> <ul class="menu-list level-2"> <li class="menu-list-item core-module-section-index"> <a href="#/<%= module.name %>/<%= module.index %>" class="mdc-ripple-surface" data-module="<%= module.name %>" data-section="<%= module.index %>"> <%= module.title %> </a> </li> <% if (module.sections && module.sections.length > 0) { %> <% module.sections.forEach(function(section) { %> <li class="menu-list-item core-module-section core-module-<%= module.name %>-<%= section.name %>"> <a href="#/<%= module.name %>/<%= section.name %>" class="mdc-ripple-surface" data-module="<%= module.name %>" data-section="<%= section.name %>"> <span class="menu-list-item__text"><%= section.title %></span> </a> </li> <% }); %> <% } %> </ul> </li>';
coreTpl['menu/navbar.html'] = '<ul class="navbar-nav"> <li class="nav-item dropdown cabinet-user"> <button class="btn btn-link text-dark dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown"> <% if (user.avatar) { %> <img src="<%= user.avatar %>" alt="avatar" class="rounded-circle" loading="lazy"/> <% } else { %> <i class="fa-solid fa-circle-user"></i> <% } %> </button> <ul class="dropdown-menu shadow"> <li class="cabinet-user-info"> <b class="cabinet-user-name"><%= user.name %></b><br> <span class="cabinet-user-login"><%= user.login %></span> </li> <li> <hr class="dropdown-divider"/> </li> <li> <a class="dropdown-item menu-logout" href="#"> <i class="fa-solid fa-arrow-right-from-bracket"></i> Выйти </a> </li> </ul> </li> </ul>';
coreTpl['menu/preloader.html'] = '<div id="preloader"> <div class="loading-lock"></div> <div class="loading-block"> <div class="spinner-border text-secondary"> <span class="visually-hidden"></span> </div> <div class="loading-text"><%= text %></div> </div> </div>';
coreTpl['auth/main.html'] = '<div class="container container-login" style="display: none"> <div class="mdc-card"> <div class="mdc-card__content"> <img src="" alt="logo" class="logo" style="display: none"> <form class="mb-5" novalidate> <span class="text-danger"></span> <div class="form-controls mb-5"> <div class="mb-3 text-start"> <label class="form-label" for="auth-login">Логин или Email</label> <div class="control-icon position-relative"> <input type="text" name="login" class="form-control" id="auth-login" required> <i class="bi bi-person-fill"></i> </div> </div> <div class="mb-5 text-start"> <label class="form-label" for="auth-password">Пароль</label> <div class="control-icon position-relative"> <input type="password" name="password" class="form-control" id="auth-password" required> <i class="bi bi-shield-lock"></i> </div> </div> <button class="btn btn-primary w-100 py-2" type="submit">Войти</button> </div> </form> <div class="links-container"> <a class="install-button" style="display: none">Установить</a> <a href="#/registration" class="reg-button">Регистрация</a> </div> </div> </div> </div> <div class="container container-registration" style="display: none"> <div class="mdc-card"> <div class="mdc-card__content"> <img src="" alt="logo" class="logo" style="display: none"> <p class="mdc-typography--headline5">Регистрация</p> <div class="text-danger mdc-typography--subtitle2"></div> <div class="text-success mdc-typography--subtitle2"></div> <form class="mb-5" novalidate> <div class="form-controls mb-5"> <div class="mb-3 text-start"> <label class="form-label" for="registration-name">Имя</label> <input type="text" name="name" class="form-control" id="registration-name" required> </div> <div class="mb-3 text-start"> <label class="form-label" for="registration-email">Email</label> <input type="email" name="email" class="form-control" id="registration-email" required> </div> <div class="mb-3 text-start"> <label class="form-label" for="registration-pass">Пароль</label> <input type="password" name="password" class="form-control" id="registration-pass" required> </div> <div class="mb-5 text-start"> <label class="form-label" for="registration-pass2">Пароль еще раз</label> <input type="password" class="form-control" id="registration-pass2" required> </div> <button class="btn btn-primary w-100 py-2" type="submit">Зарегистрироваться</button> </div> </form> <div class="links-container"> <a class="install-button" style="display: none">Установить</a> <a href="#" class="login-button">Войти</a> </div> </div> </div> </div>';

/*
 * JavaScript MD5
 * https://github.com/blueimp/JavaScript-MD5
 *
 * Copyright 2011, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 *
 * Based on
 * A JavaScript implementation of the RSA Data Security, Inc. MD5 Message
 * Digest Algorithm, as defined in RFC 1321.
 * Version 2.2 Copyright (C) Paul Johnston 1999 - 2009
 * Other contributors: Greg Holt, Andrew Kepert, Ydnar, Lostinet
 * Distributed under the BSD License
 * See http://pajhome.org.uk/crypt/md5 for more info.
 */

/*
 * Add integers, wrapping at 2^32. This uses 16-bit operations internally
 * to work around bugs in some JS interpreters.
 */
const safeAdd = (x, y) => {
  let lsw = (x & 0xFFFF) + (y & 0xFFFF);
  return (x >> 16) + (y >> 16) + (lsw >> 16) << 16 | lsw & 0xFFFF;
};

/*
 * Bitwise rotate a 32-bit number to the left.
 */
const bitRotateLeft = (num, cnt) => num << cnt | num >>> 32 - cnt;

/*
 * These functions implement the four basic operations the algorithm uses.
 */
const md5cmn = (q, a, b, x, s, t) => safeAdd(bitRotateLeft(safeAdd(safeAdd(a, q), safeAdd(x, t)), s), b);
const md5ff = (a, b, c, d, x, s, t) => md5cmn(b & c | ~b & d, a, b, x, s, t);
const md5gg = (a, b, c, d, x, s, t) => md5cmn(b & d | c & ~d, a, b, x, s, t);
const md5hh = (a, b, c, d, x, s, t) => md5cmn(b ^ c ^ d, a, b, x, s, t);
const md5ii = (a, b, c, d, x, s, t) => md5cmn(c ^ (b | ~d), a, b, x, s, t);
const firstChunk = (chunks, x, i) => {
    let [a, b, c, d] = chunks;
    a = md5ff(a, b, c, d, x[i + 0], 7, -680876936);
    d = md5ff(d, a, b, c, x[i + 1], 12, -389564586);
    c = md5ff(c, d, a, b, x[i + 2], 17, 606105819);
    b = md5ff(b, c, d, a, x[i + 3], 22, -1044525330);
    a = md5ff(a, b, c, d, x[i + 4], 7, -176418897);
    d = md5ff(d, a, b, c, x[i + 5], 12, 1200080426);
    c = md5ff(c, d, a, b, x[i + 6], 17, -1473231341);
    b = md5ff(b, c, d, a, x[i + 7], 22, -45705983);
    a = md5ff(a, b, c, d, x[i + 8], 7, 1770035416);
    d = md5ff(d, a, b, c, x[i + 9], 12, -1958414417);
    c = md5ff(c, d, a, b, x[i + 10], 17, -42063);
    b = md5ff(b, c, d, a, x[i + 11], 22, -1990404162);
    a = md5ff(a, b, c, d, x[i + 12], 7, 1804603682);
    d = md5ff(d, a, b, c, x[i + 13], 12, -40341101);
    c = md5ff(c, d, a, b, x[i + 14], 17, -1502002290);
    b = md5ff(b, c, d, a, x[i + 15], 22, 1236535329);
    return [a, b, c, d];
  };
const secondChunk = (chunks, x, i) => {
    let [a, b, c, d] = chunks;
    a = md5gg(a, b, c, d, x[i + 1], 5, -165796510);
    d = md5gg(d, a, b, c, x[i + 6], 9, -1069501632);
    c = md5gg(c, d, a, b, x[i + 11], 14, 643717713);
    b = md5gg(b, c, d, a, x[i], 20, -373897302);
    a = md5gg(a, b, c, d, x[i + 5], 5, -701558691);
    d = md5gg(d, a, b, c, x[i + 10], 9, 38016083);
    c = md5gg(c, d, a, b, x[i + 15], 14, -660478335);
    b = md5gg(b, c, d, a, x[i + 4], 20, -405537848);
    a = md5gg(a, b, c, d, x[i + 9], 5, 568446438);
    d = md5gg(d, a, b, c, x[i + 14], 9, -1019803690);
    c = md5gg(c, d, a, b, x[i + 3], 14, -187363961);
    b = md5gg(b, c, d, a, x[i + 8], 20, 1163531501);
    a = md5gg(a, b, c, d, x[i + 13], 5, -1444681467);
    d = md5gg(d, a, b, c, x[i + 2], 9, -51403784);
    c = md5gg(c, d, a, b, x[i + 7], 14, 1735328473);
    b = md5gg(b, c, d, a, x[i + 12], 20, -1926607734);
    return [a, b, c, d];
  };
const thirdChunk = (chunks, x, i) => {
    let [a, b, c, d] = chunks;
    a = md5hh(a, b, c, d, x[i + 5], 4, -378558);
    d = md5hh(d, a, b, c, x[i + 8], 11, -2022574463);
    c = md5hh(c, d, a, b, x[i + 11], 16, 1839030562);
    b = md5hh(b, c, d, a, x[i + 14], 23, -35309556);
    a = md5hh(a, b, c, d, x[i + 1], 4, -1530992060);
    d = md5hh(d, a, b, c, x[i + 4], 11, 1272893353);
    c = md5hh(c, d, a, b, x[i + 7], 16, -155497632);
    b = md5hh(b, c, d, a, x[i + 10], 23, -1094730640);
    a = md5hh(a, b, c, d, x[i + 13], 4, 681279174);
    d = md5hh(d, a, b, c, x[i], 11, -358537222);
    c = md5hh(c, d, a, b, x[i + 3], 16, -722521979);
    b = md5hh(b, c, d, a, x[i + 6], 23, 76029189);
    a = md5hh(a, b, c, d, x[i + 9], 4, -640364487);
    d = md5hh(d, a, b, c, x[i + 12], 11, -421815835);
    c = md5hh(c, d, a, b, x[i + 15], 16, 530742520);
    b = md5hh(b, c, d, a, x[i + 2], 23, -995338651);
    return [a, b, c, d];
  };
const fourthChunk = (chunks, x, i) => {
    let [a, b, c, d] = chunks;
    a = md5ii(a, b, c, d, x[i], 6, -198630844);
    d = md5ii(d, a, b, c, x[i + 7], 10, 1126891415);
    c = md5ii(c, d, a, b, x[i + 14], 15, -1416354905);
    b = md5ii(b, c, d, a, x[i + 5], 21, -57434055);
    a = md5ii(a, b, c, d, x[i + 12], 6, 1700485571);
    d = md5ii(d, a, b, c, x[i + 3], 10, -1894986606);
    c = md5ii(c, d, a, b, x[i + 10], 15, -1051523);
    b = md5ii(b, c, d, a, x[i + 1], 21, -2054922799);
    a = md5ii(a, b, c, d, x[i + 8], 6, 1873313359);
    d = md5ii(d, a, b, c, x[i + 15], 10, -30611744);
    c = md5ii(c, d, a, b, x[i + 6], 15, -1560198380);
    b = md5ii(b, c, d, a, x[i + 13], 21, 1309151649);
    a = md5ii(a, b, c, d, x[i + 4], 6, -145523070);
    d = md5ii(d, a, b, c, x[i + 11], 10, -1120210379);
    c = md5ii(c, d, a, b, x[i + 2], 15, 718787259);
    b = md5ii(b, c, d, a, x[i + 9], 21, -343485551);
    return [a, b, c, d];
  };
/*
 * Calculate the MD5 of an array of little-endian words, and a bit length.
 */
const binlMD5 = (x, len) => {
  /* append padding */
  x[len >> 5] |= 0x80 << len % 32;
  x[(len + 64 >>> 9 << 4) + 14] = len;
  let commands = [firstChunk, secondChunk, thirdChunk, fourthChunk],
    initialChunks = [1732584193, -271733879, -1732584194, 271733878];
  return Array.from({
    length: Math.floor(x.length / 16) + 1
  }, (v, i) => i * 16).reduce((chunks, i) => commands.reduce((newChunks, apply) => apply(newChunks, x, i), chunks.slice()).map((chunk, index) => safeAdd(chunk, chunks[index])), initialChunks);
};

/*
 * Convert an array of little-endian words to a string
 */
const binl2rstr = input => Array(input.length * 4).fill(8).reduce((output, k, i) => output + String.fromCharCode(input[i * k >> 5] >>> i * k % 32 & 0xFF), '');

/*
 * Convert a raw string to an array of little-endian words
 * Characters >255 have their high-byte silently ignored.
 */
const rstr2binl = input => Array.from(input).map(i => i.charCodeAt(0)).reduce((output, cc, i) => {
  let resp = output.slice();
  resp[i * 8 >> 5] |= (cc & 0xFF) << i * 8 % 32;
  return resp;
}, []);

/*
 * Calculate the MD5 of a raw string
 */
const rstrMD5 = string => binl2rstr(binlMD5(rstr2binl(string), string.length * 8));
/*
 * Calculate the HMAC-MD5, of a key and some data (raw strings)
 */
const strHMACMD5 = (key, data) => {
  let bkey = rstr2binl(key),
    ipad = Array(16).fill(undefined ^ 0x36363636),
    opad = Array(16).fill(undefined ^ 0x5C5C5C5C);
  if (bkey.length > 16) {
    bkey = binlMD5(bkey, key.length * 8);
  }
  bkey.forEach((k, i) => {
    ipad[i] = k ^ 0x36363636;
    opad[i] = k ^ 0x5C5C5C5C;
  });
  return binl2rstr(binlMD5(opad.concat(binlMD5(ipad.concat(rstr2binl(data)), 512 + data.length * 8)), 512 + 128));
};

/*
 * Convert a raw string to a hex string
 */
const rstr2hex = input => {
  const hexTab = pos => '0123456789abcdef'.charAt(pos);
  return Array.from(input).map(c => c.charCodeAt(0)).reduce((output, x, i) => output + hexTab(x >>> 4 & 0x0F) + hexTab(x & 0x0F), '');
};

/*
 * Encode a string as utf-8
 */

const str2rstrUTF8 = unicodeString => {
  if (typeof unicodeString !== 'string') throw new TypeError('parameter ‘unicodeString’ is not a string');
  const cc = c => c.charCodeAt(0);
  return unicodeString.replace(/[\u0080-\u07ff]/g,
  // U+0080 - U+07FF => 2 bytes 110yyyyy, 10zzzzzz
  c => String.fromCharCode(0xc0 | cc(c) >> 6, 0x80 | cc(c) & 0x3f)).replace(/[\u0800-\uffff]/g,
  // U+0800 - U+FFFF => 3 bytes 1110xxxx, 10yyyyyy, 10zzzzzz
  c => String.fromCharCode(0xe0 | cc(c) >> 12, 0x80 | cc(c) >> 6 & 0x3F, 0x80 | cc(c) & 0x3f));
};

/*
 * Take string arguments and return either raw or hex encoded strings
 */
const rawMD5 = s => rstrMD5(str2rstrUTF8(s));
const hexMD5 = s => rstr2hex(rawMD5(s));
const rawHMACMD5 = (k, d) => strHMACMD5(str2rstrUTF8(k), str2rstrUTF8(d));
const hexHMACMD5 = (k, d) => rstr2hex(rawHMACMD5(k, d));
var MD5 = ((string, key, raw) => {
  if (!key) {
    if (!raw) {
      return hexMD5(string);
    }
    return rawMD5(string);
  }
  if (!raw) {
    return hexHMACMD5(key, string);
  }
  return rawHMACMD5(key, string);
});

!function (t, e) {
  "object" == typeof exports && "object" == typeof module ? module.exports = e() : "function" == typeof define && define.amd ? define("@material/ripple", [], e) : "object" == typeof exports ? exports.ripple = e() : (t.mdc = t.mdc || {}, t.mdc.ripple = e());
}(window, function () {
  return i = {}, r.m = n = {
    0: function (t, e, n) {
      "use strict";

      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.MDCFoundation = void 0;
      var i = (Object.defineProperty(r, "cssClasses", {
        get: function () {
          return {};
        },
        enumerable: !1,
        configurable: !0
      }), Object.defineProperty(r, "strings", {
        get: function () {
          return {};
        },
        enumerable: !1,
        configurable: !0
      }), Object.defineProperty(r, "numbers", {
        get: function () {
          return {};
        },
        enumerable: !1,
        configurable: !0
      }), Object.defineProperty(r, "defaultAdapter", {
        get: function () {
          return {};
        },
        enumerable: !1,
        configurable: !0
      }), r.prototype.init = function () {}, r.prototype.destroy = function () {}, r);
      function r(t) {
        void 0 === t && (t = {}), this.adapter = t;
      }
      e.MDCFoundation = i, e.default = i;
    },
    1: function (t, e, n) {
      "use strict";

      var r = this && this.__read || function (t, e) {
          var n = "function" == typeof Symbol && t[Symbol.iterator];
          if (!n) return t;
          var i,
            r,
            a = n.call(t),
            o = [];
          try {
            for (; (void 0 === e || 0 < e--) && !(i = a.next()).done;) o.push(i.value);
          } catch (t) {
            r = {
              error: t
            };
          } finally {
            try {
              i && !i.done && (n = a.return) && n.call(a);
            } finally {
              if (r) throw r.error;
            }
          }
          return o;
        },
        a = this && this.__spreadArray || function (t, e) {
          for (var n = 0, i = e.length, r = t.length; n < i; n++, r++) t[r] = e[n];
          return t;
        };
      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.MDCComponent = void 0;
      var i = n(0),
        o = (s.attachTo = function (t) {
          return new s(t, new i.MDCFoundation({}));
        }, s.prototype.initialize = function () {
          for (var t = [], e = 0; e < arguments.length; e++) t[e] = arguments[e];
        }, s.prototype.getDefaultFoundation = function () {
          throw new Error("Subclasses must override getDefaultFoundation to return a properly configured foundation class");
        }, s.prototype.initialSyncWithDOM = function () {}, s.prototype.destroy = function () {
          this.foundation.destroy();
        }, s.prototype.listen = function (t, e, n) {
          this.root.addEventListener(t, e, n);
        }, s.prototype.unlisten = function (t, e, n) {
          this.root.removeEventListener(t, e, n);
        }, s.prototype.emit = function (t, e, n) {
          var i;
          void 0 === n && (n = !1), "function" == typeof CustomEvent ? i = new CustomEvent(t, {
            bubbles: n,
            detail: e
          }) : (i = document.createEvent("CustomEvent")).initCustomEvent(t, n, !1, e), this.root.dispatchEvent(i);
        }, s);
      function s(t, e) {
        for (var n = [], i = 2; i < arguments.length; i++) n[i - 2] = arguments[i];
        this.root = t, this.initialize.apply(this, a([], r(n))), this.foundation = void 0 === e ? this.getDefaultFoundation() : e, this.foundation.init(), this.initialSyncWithDOM();
      }
      e.MDCComponent = o, e.default = o;
    },
    2: function (t, e, n) {
      "use strict";

      function i(t, e) {
        return (t.matches || t.webkitMatchesSelector || t.msMatchesSelector).call(t, e);
      }
      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.estimateScrollWidth = e.matches = e.closest = void 0, e.closest = function (t, e) {
        if (t.closest) return t.closest(e);
        for (var n = t; n;) {
          if (i(n, e)) return n;
          n = n.parentElement;
        }
        return null;
      }, e.matches = i, e.estimateScrollWidth = function (t) {
        var e = t;
        if (null !== e.offsetParent) return e.scrollWidth;
        var n = e.cloneNode(!0);
        n.style.setProperty("position", "absolute"), n.style.setProperty("transform", "translate(-9999px, -9999px)"), document.documentElement.appendChild(n);
        var i = n.scrollWidth;
        return document.documentElement.removeChild(n), i;
      };
    },
    211: function (t, e, n) {
      "use strict";

      var i = this && this.__createBinding || (Object.create ? function (t, e, n, i) {
          void 0 === i && (i = n), Object.defineProperty(t, i, {
            enumerable: !0,
            get: function () {
              return e[n];
            }
          });
        } : function (t, e, n, i) {
          void 0 === i && (i = n), t[i] = e[n];
        }),
        r = this && this.__setModuleDefault || (Object.create ? function (t, e) {
          Object.defineProperty(t, "default", {
            enumerable: !0,
            value: e
          });
        } : function (t, e) {
          t.default = e;
        }),
        a = this && this.__importStar || function (t) {
          if (t && t.__esModule) return t;
          var e = {};
          if (null != t) for (var n in t) "default" !== n && Object.prototype.hasOwnProperty.call(t, n) && i(e, t, n);
          return r(e, t), e;
        },
        o = this && this.__exportStar || function (t, e) {
          for (var n in t) "default" === n || Object.prototype.hasOwnProperty.call(e, n) || i(e, t, n);
        };
      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.util = void 0;
      var s = a(n(3));
      e.util = s, o(n(212), e), o(n(6), e), o(n(7), e), o(n(4), e), o(n(213), e);
    },
    212: function (t, e, n) {
      "use strict";

      Object.defineProperty(e, "__esModule", {
        value: !0
      });
    },
    213: function (t, e, n) {
      "use strict";

      Object.defineProperty(e, "__esModule", {
        value: !0
      });
    },
    3: function (t, e, n) {
      "use strict";

      var o;
      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.getNormalizedEventCoords = e.supportsCssVariables = void 0, e.supportsCssVariables = function (t, e) {
        void 0 === e && (e = !1);
        var n,
          i = t.CSS;
        if ("boolean" == typeof o && !e) return o;
        if (!(i && "function" == typeof i.supports)) return !1;
        var r = i.supports("--css-vars", "yes"),
          a = i.supports("(--css-vars: yes)") && i.supports("color", "#00000000");
        return n = r || a, e || (o = n), n;
      }, e.getNormalizedEventCoords = function (t, e, n) {
        if (!t) return {
          x: 0,
          y: 0
        };
        var i,
          r,
          a = e.x,
          o = e.y,
          s = a + n.left,
          u = o + n.top;
        if ("touchstart" === t.type) {
          var c = t;
          i = c.changedTouches[0].pageX - s, r = c.changedTouches[0].pageY - u;
        } else {
          var d = t;
          i = d.pageX - s, r = d.pageY - u;
        }
        return {
          x: i,
          y: r
        };
      };
    },
    4: function (t, e, n) {
      "use strict";

      var i,
        r = this && this.__extends || (i = function (t, e) {
          return (i = Object.setPrototypeOf || {
            __proto__: []
          } instanceof Array && function (t, e) {
            t.__proto__ = e;
          } || function (t, e) {
            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
          })(t, e);
        }, function (t, e) {
          if ("function" != typeof e && null !== e) throw new TypeError("Class extends value " + String(e) + " is not a constructor or null");
          function n() {
            this.constructor = t;
          }
          i(t, e), t.prototype = null === e ? Object.create(e) : (n.prototype = e.prototype, new n());
        }),
        a = this && this.__assign || function () {
          return (a = Object.assign || function (t) {
            for (var e, n = 1, i = arguments.length; n < i; n++) for (var r in e = arguments[n]) Object.prototype.hasOwnProperty.call(e, r) && (t[r] = e[r]);
            return t;
          }).apply(this, arguments);
        },
        o = this && this.__values || function (t) {
          var e = "function" == typeof Symbol && Symbol.iterator,
            n = e && t[e],
            i = 0;
          if (n) return n.call(t);
          if (t && "number" == typeof t.length) return {
            next: function () {
              return t && i >= t.length && (t = void 0), {
                value: t && t[i++],
                done: !t
              };
            }
          };
          throw new TypeError(e ? "Object is not iterable." : "Symbol.iterator is not defined.");
        };
      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.MDCRippleFoundation = void 0;
      var s,
        u = n(0),
        c = n(7),
        d = n(3),
        l = ["touchstart", "pointerdown", "mousedown", "keydown"],
        p = ["touchend", "pointerup", "mouseup", "contextmenu"],
        f = [],
        v = (s = u.MDCFoundation, r(h, s), Object.defineProperty(h, "cssClasses", {
          get: function () {
            return c.cssClasses;
          },
          enumerable: !1,
          configurable: !0
        }), Object.defineProperty(h, "strings", {
          get: function () {
            return c.strings;
          },
          enumerable: !1,
          configurable: !0
        }), Object.defineProperty(h, "numbers", {
          get: function () {
            return c.numbers;
          },
          enumerable: !1,
          configurable: !0
        }), Object.defineProperty(h, "defaultAdapter", {
          get: function () {
            return {
              addClass: function () {},
              browserSupportsCssVars: function () {
                return !0;
              },
              computeBoundingRect: function () {
                return {
                  top: 0,
                  right: 0,
                  bottom: 0,
                  left: 0,
                  width: 0,
                  height: 0
                };
              },
              containsEventTarget: function () {
                return !0;
              },
              deregisterDocumentInteractionHandler: function () {},
              deregisterInteractionHandler: function () {},
              deregisterResizeHandler: function () {},
              getWindowPageOffset: function () {
                return {
                  x: 0,
                  y: 0
                };
              },
              isSurfaceActive: function () {
                return !0;
              },
              isSurfaceDisabled: function () {
                return !0;
              },
              isUnbounded: function () {
                return !0;
              },
              registerDocumentInteractionHandler: function () {},
              registerInteractionHandler: function () {},
              registerResizeHandler: function () {},
              removeClass: function () {},
              updateCssVariable: function () {}
            };
          },
          enumerable: !1,
          configurable: !0
        }), h.prototype.init = function () {
          var t = this,
            e = this.supportsPressRipple();
          if (this.registerRootHandlers(e), e) {
            var n = h.cssClasses,
              i = n.ROOT,
              r = n.UNBOUNDED;
            requestAnimationFrame(function () {
              t.adapter.addClass(i), t.adapter.isUnbounded() && (t.adapter.addClass(r), t.layoutInternal());
            });
          }
        }, h.prototype.destroy = function () {
          var t = this;
          if (this.supportsPressRipple()) {
            this.activationTimer && (clearTimeout(this.activationTimer), this.activationTimer = 0, this.adapter.removeClass(h.cssClasses.FG_ACTIVATION)), this.fgDeactivationRemovalTimer && (clearTimeout(this.fgDeactivationRemovalTimer), this.fgDeactivationRemovalTimer = 0, this.adapter.removeClass(h.cssClasses.FG_DEACTIVATION));
            var e = h.cssClasses,
              n = e.ROOT,
              i = e.UNBOUNDED;
            requestAnimationFrame(function () {
              t.adapter.removeClass(n), t.adapter.removeClass(i), t.removeCssVars();
            });
          }
          this.deregisterRootHandlers(), this.deregisterDeactivationHandlers();
        }, h.prototype.activate = function (t) {
          this.activateImpl(t);
        }, h.prototype.deactivate = function () {
          this.deactivateImpl();
        }, h.prototype.layout = function () {
          var t = this;
          this.layoutFrame && cancelAnimationFrame(this.layoutFrame), this.layoutFrame = requestAnimationFrame(function () {
            t.layoutInternal(), t.layoutFrame = 0;
          });
        }, h.prototype.setUnbounded = function (t) {
          var e = h.cssClasses.UNBOUNDED;
          t ? this.adapter.addClass(e) : this.adapter.removeClass(e);
        }, h.prototype.handleFocus = function () {
          var t = this;
          requestAnimationFrame(function () {
            return t.adapter.addClass(h.cssClasses.BG_FOCUSED);
          });
        }, h.prototype.handleBlur = function () {
          var t = this;
          requestAnimationFrame(function () {
            return t.adapter.removeClass(h.cssClasses.BG_FOCUSED);
          });
        }, h.prototype.supportsPressRipple = function () {
          return this.adapter.browserSupportsCssVars();
        }, h.prototype.defaultActivationState = function () {
          return {
            activationEvent: void 0,
            hasDeactivationUXRun: !1,
            isActivated: !1,
            isProgrammatic: !1,
            wasActivatedByPointer: !1,
            wasElementMadeActive: !1
          };
        }, h.prototype.registerRootHandlers = function (t) {
          var e, n;
          if (t) {
            try {
              for (var i = o(l), r = i.next(); !r.done; r = i.next()) {
                var a = r.value;
                this.adapter.registerInteractionHandler(a, this.activateHandler);
              }
            } catch (t) {
              e = {
                error: t
              };
            } finally {
              try {
                r && !r.done && (n = i.return) && n.call(i);
              } finally {
                if (e) throw e.error;
              }
            }
            this.adapter.isUnbounded() && this.adapter.registerResizeHandler(this.resizeHandler);
          }
          this.adapter.registerInteractionHandler("focus", this.focusHandler), this.adapter.registerInteractionHandler("blur", this.blurHandler);
        }, h.prototype.registerDeactivationHandlers = function (t) {
          var e, n;
          if ("keydown" === t.type) this.adapter.registerInteractionHandler("keyup", this.deactivateHandler);else try {
            for (var i = o(p), r = i.next(); !r.done; r = i.next()) {
              var a = r.value;
              this.adapter.registerDocumentInteractionHandler(a, this.deactivateHandler);
            }
          } catch (t) {
            e = {
              error: t
            };
          } finally {
            try {
              r && !r.done && (n = i.return) && n.call(i);
            } finally {
              if (e) throw e.error;
            }
          }
        }, h.prototype.deregisterRootHandlers = function () {
          var e, t;
          try {
            for (var n = o(l), i = n.next(); !i.done; i = n.next()) {
              var r = i.value;
              this.adapter.deregisterInteractionHandler(r, this.activateHandler);
            }
          } catch (t) {
            e = {
              error: t
            };
          } finally {
            try {
              i && !i.done && (t = n.return) && t.call(n);
            } finally {
              if (e) throw e.error;
            }
          }
          this.adapter.deregisterInteractionHandler("focus", this.focusHandler), this.adapter.deregisterInteractionHandler("blur", this.blurHandler), this.adapter.isUnbounded() && this.adapter.deregisterResizeHandler(this.resizeHandler);
        }, h.prototype.deregisterDeactivationHandlers = function () {
          var e, t;
          this.adapter.deregisterInteractionHandler("keyup", this.deactivateHandler);
          try {
            for (var n = o(p), i = n.next(); !i.done; i = n.next()) {
              var r = i.value;
              this.adapter.deregisterDocumentInteractionHandler(r, this.deactivateHandler);
            }
          } catch (t) {
            e = {
              error: t
            };
          } finally {
            try {
              i && !i.done && (t = n.return) && t.call(n);
            } finally {
              if (e) throw e.error;
            }
          }
        }, h.prototype.removeCssVars = function () {
          var e = this,
            n = h.strings;
          Object.keys(n).forEach(function (t) {
            0 === t.indexOf("VAR_") && e.adapter.updateCssVariable(n[t], null);
          });
        }, h.prototype.activateImpl = function (t) {
          var e = this;
          if (!this.adapter.isSurfaceDisabled()) {
            var n = this.activationState;
            if (!n.isActivated) {
              var i = this.previousActivationEvent;
              i && void 0 !== t && i.type !== t.type || (n.isActivated = !0, n.isProgrammatic = void 0 === t, n.activationEvent = t, n.wasActivatedByPointer = !n.isProgrammatic && void 0 !== t && ("mousedown" === t.type || "touchstart" === t.type || "pointerdown" === t.type), void 0 !== t && 0 < f.length && f.some(function (t) {
                return e.adapter.containsEventTarget(t);
              }) ? this.resetActivationState() : (void 0 !== t && (f.push(t.target), this.registerDeactivationHandlers(t)), n.wasElementMadeActive = this.checkElementMadeActive(t), n.wasElementMadeActive && this.animateActivation(), requestAnimationFrame(function () {
                f = [], n.wasElementMadeActive || void 0 === t || " " !== t.key && 32 !== t.keyCode || (n.wasElementMadeActive = e.checkElementMadeActive(t), n.wasElementMadeActive && e.animateActivation()), n.wasElementMadeActive || (e.activationState = e.defaultActivationState());
              })));
            }
          }
        }, h.prototype.checkElementMadeActive = function (t) {
          return void 0 === t || "keydown" !== t.type || this.adapter.isSurfaceActive();
        }, h.prototype.animateActivation = function () {
          var t = this,
            e = h.strings,
            n = e.VAR_FG_TRANSLATE_START,
            i = e.VAR_FG_TRANSLATE_END,
            r = h.cssClasses,
            a = r.FG_DEACTIVATION,
            o = r.FG_ACTIVATION,
            s = h.numbers.DEACTIVATION_TIMEOUT_MS;
          this.layoutInternal();
          var u = "",
            c = "";
          if (!this.adapter.isUnbounded()) {
            var d = this.getFgTranslationCoordinates(),
              l = d.startPoint,
              p = d.endPoint;
            u = l.x + "px, " + l.y + "px", c = p.x + "px, " + p.y + "px";
          }
          this.adapter.updateCssVariable(n, u), this.adapter.updateCssVariable(i, c), clearTimeout(this.activationTimer), clearTimeout(this.fgDeactivationRemovalTimer), this.rmBoundedActivationClasses(), this.adapter.removeClass(a), this.adapter.computeBoundingRect(), this.adapter.addClass(o), this.activationTimer = setTimeout(function () {
            t.activationTimerCallback();
          }, s);
        }, h.prototype.getFgTranslationCoordinates = function () {
          var t,
            e = this.activationState,
            n = e.activationEvent;
          return {
            startPoint: t = {
              x: (t = e.wasActivatedByPointer ? d.getNormalizedEventCoords(n, this.adapter.getWindowPageOffset(), this.adapter.computeBoundingRect()) : {
                x: this.frame.width / 2,
                y: this.frame.height / 2
              }).x - this.initialSize / 2,
              y: t.y - this.initialSize / 2
            },
            endPoint: {
              x: this.frame.width / 2 - this.initialSize / 2,
              y: this.frame.height / 2 - this.initialSize / 2
            }
          };
        }, h.prototype.runDeactivationUXLogicIfReady = function () {
          var t = this,
            e = h.cssClasses.FG_DEACTIVATION,
            n = this.activationState,
            i = n.hasDeactivationUXRun,
            r = n.isActivated;
          !i && r || !this.activationAnimationHasEnded || (this.rmBoundedActivationClasses(), this.adapter.addClass(e), this.fgDeactivationRemovalTimer = setTimeout(function () {
            t.adapter.removeClass(e);
          }, c.numbers.FG_DEACTIVATION_MS));
        }, h.prototype.rmBoundedActivationClasses = function () {
          var t = h.cssClasses.FG_ACTIVATION;
          this.adapter.removeClass(t), this.activationAnimationHasEnded = !1, this.adapter.computeBoundingRect();
        }, h.prototype.resetActivationState = function () {
          var t = this;
          this.previousActivationEvent = this.activationState.activationEvent, this.activationState = this.defaultActivationState(), setTimeout(function () {
            return t.previousActivationEvent = void 0;
          }, h.numbers.TAP_DELAY_MS);
        }, h.prototype.deactivateImpl = function () {
          var t = this,
            e = this.activationState;
          if (e.isActivated) {
            var n = a({}, e);
            e.isProgrammatic ? (requestAnimationFrame(function () {
              t.animateDeactivation(n);
            }), this.resetActivationState()) : (this.deregisterDeactivationHandlers(), requestAnimationFrame(function () {
              t.activationState.hasDeactivationUXRun = !0, t.animateDeactivation(n), t.resetActivationState();
            }));
          }
        }, h.prototype.animateDeactivation = function (t) {
          var e = t.wasActivatedByPointer,
            n = t.wasElementMadeActive;
          (e || n) && this.runDeactivationUXLogicIfReady();
        }, h.prototype.layoutInternal = function () {
          var t = this;
          this.frame = this.adapter.computeBoundingRect();
          var e = Math.max(this.frame.height, this.frame.width);
          this.maxRadius = this.adapter.isUnbounded() ? e : Math.sqrt(Math.pow(t.frame.width, 2) + Math.pow(t.frame.height, 2)) + h.numbers.PADDING;
          var n = Math.floor(e * h.numbers.INITIAL_ORIGIN_SCALE);
          this.adapter.isUnbounded() && n % 2 != 0 ? this.initialSize = n - 1 : this.initialSize = n, this.fgScale = "" + this.maxRadius / this.initialSize, this.updateLayoutCssVars();
        }, h.prototype.updateLayoutCssVars = function () {
          var t = h.strings,
            e = t.VAR_FG_SIZE,
            n = t.VAR_LEFT,
            i = t.VAR_TOP,
            r = t.VAR_FG_SCALE;
          this.adapter.updateCssVariable(e, this.initialSize + "px"), this.adapter.updateCssVariable(r, this.fgScale), this.adapter.isUnbounded() && (this.unboundedCoords = {
            left: Math.round(this.frame.width / 2 - this.initialSize / 2),
            top: Math.round(this.frame.height / 2 - this.initialSize / 2)
          }, this.adapter.updateCssVariable(n, this.unboundedCoords.left + "px"), this.adapter.updateCssVariable(i, this.unboundedCoords.top + "px"));
        }, h);
      function h(t) {
        var e = s.call(this, a(a({}, h.defaultAdapter), t)) || this;
        return e.activationAnimationHasEnded = !1, e.activationTimer = 0, e.fgDeactivationRemovalTimer = 0, e.fgScale = "0", e.frame = {
          width: 0,
          height: 0
        }, e.initialSize = 0, e.layoutFrame = 0, e.maxRadius = 0, e.unboundedCoords = {
          left: 0,
          top: 0
        }, e.activationState = e.defaultActivationState(), e.activationTimerCallback = function () {
          e.activationAnimationHasEnded = !0, e.runDeactivationUXLogicIfReady();
        }, e.activateHandler = function (t) {
          e.activateImpl(t);
        }, e.deactivateHandler = function () {
          e.deactivateImpl();
        }, e.focusHandler = function () {
          e.handleFocus();
        }, e.blurHandler = function () {
          e.handleBlur();
        }, e.resizeHandler = function () {
          e.layout();
        }, e;
      }
      e.MDCRippleFoundation = v, e.default = v;
    },
    5: function (t, e, n) {
      "use strict";

      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.applyPassive = void 0, e.applyPassive = function (t) {
        return void 0 === t && (t = window), !!function (t) {
          void 0 === t && (t = window);
          var e = !1;
          try {
            var n = {
                get passive() {
                  return !(e = !0);
                }
              },
              i = function () {};
            t.document.addEventListener("test", i, n), t.document.removeEventListener("test", i, n);
          } catch (t) {
            e = !1;
          }
          return e;
        }(t) && {
          passive: !0
        };
      };
    },
    6: function (t, e, n) {
      "use strict";

      var i,
        r = this && this.__extends || (i = function (t, e) {
          return (i = Object.setPrototypeOf || {
            __proto__: []
          } instanceof Array && function (t, e) {
            t.__proto__ = e;
          } || function (t, e) {
            for (var n in e) Object.prototype.hasOwnProperty.call(e, n) && (t[n] = e[n]);
          })(t, e);
        }, function (t, e) {
          if ("function" != typeof e && null !== e) throw new TypeError("Class extends value " + String(e) + " is not a constructor or null");
          function n() {
            this.constructor = t;
          }
          i(t, e), t.prototype = null === e ? Object.create(e) : (n.prototype = e.prototype, new n());
        }),
        a = this && this.__createBinding || (Object.create ? function (t, e, n, i) {
          void 0 === i && (i = n), Object.defineProperty(t, i, {
            enumerable: !0,
            get: function () {
              return e[n];
            }
          });
        } : function (t, e, n, i) {
          void 0 === i && (i = n), t[i] = e[n];
        }),
        o = this && this.__setModuleDefault || (Object.create ? function (t, e) {
          Object.defineProperty(t, "default", {
            enumerable: !0,
            value: e
          });
        } : function (t, e) {
          t.default = e;
        }),
        s = this && this.__importStar || function (t) {
          if (t && t.__esModule) return t;
          var e = {};
          if (null != t) for (var n in t) "default" !== n && Object.prototype.hasOwnProperty.call(t, n) && a(e, t, n);
          return o(e, t), e;
        };
      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.MDCRipple = void 0;
      var u,
        c = n(1),
        d = n(5),
        l = n(2),
        p = n(4),
        f = s(n(3)),
        v = (u = c.MDCComponent, r(h, u), h.attachTo = function (t, e) {
          void 0 === e && (e = {
            isUnbounded: void 0
          });
          var n = new h(t);
          return void 0 !== e.isUnbounded && (n.unbounded = e.isUnbounded), n;
        }, h.createAdapter = function (n) {
          return {
            addClass: function (t) {
              return n.root.classList.add(t);
            },
            browserSupportsCssVars: function () {
              return f.supportsCssVariables(window);
            },
            computeBoundingRect: function () {
              return n.root.getBoundingClientRect();
            },
            containsEventTarget: function (t) {
              return n.root.contains(t);
            },
            deregisterDocumentInteractionHandler: function (t, e) {
              return document.documentElement.removeEventListener(t, e, d.applyPassive());
            },
            deregisterInteractionHandler: function (t, e) {
              return n.root.removeEventListener(t, e, d.applyPassive());
            },
            deregisterResizeHandler: function (t) {
              return window.removeEventListener("resize", t);
            },
            getWindowPageOffset: function () {
              return {
                x: window.pageXOffset,
                y: window.pageYOffset
              };
            },
            isSurfaceActive: function () {
              return l.matches(n.root, ":active");
            },
            isSurfaceDisabled: function () {
              return Boolean(n.disabled);
            },
            isUnbounded: function () {
              return Boolean(n.unbounded);
            },
            registerDocumentInteractionHandler: function (t, e) {
              return document.documentElement.addEventListener(t, e, d.applyPassive());
            },
            registerInteractionHandler: function (t, e) {
              return n.root.addEventListener(t, e, d.applyPassive());
            },
            registerResizeHandler: function (t) {
              return window.addEventListener("resize", t);
            },
            removeClass: function (t) {
              return n.root.classList.remove(t);
            },
            updateCssVariable: function (t, e) {
              return n.root.style.setProperty(t, e);
            }
          };
        }, Object.defineProperty(h.prototype, "unbounded", {
          get: function () {
            return Boolean(this.isUnbounded);
          },
          set: function (t) {
            this.isUnbounded = Boolean(t), this.setUnbounded();
          },
          enumerable: !1,
          configurable: !0
        }), h.prototype.activate = function () {
          this.foundation.activate();
        }, h.prototype.deactivate = function () {
          this.foundation.deactivate();
        }, h.prototype.layout = function () {
          this.foundation.layout();
        }, h.prototype.getDefaultFoundation = function () {
          return new p.MDCRippleFoundation(h.createAdapter(this));
        }, h.prototype.initialSyncWithDOM = function () {
          var t = this.root;
          this.isUnbounded = "mdcRippleIsUnbounded" in t.dataset;
        }, h.prototype.setUnbounded = function () {
          this.foundation.setUnbounded(Boolean(this.isUnbounded));
        }, h);
      function h() {
        var t = null !== u && u.apply(this, arguments) || this;
        return t.disabled = !1, t;
      }
      e.MDCRipple = v;
    },
    7: function (t, e, n) {
      "use strict";

      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.numbers = e.strings = e.cssClasses = void 0, e.cssClasses = {
        BG_FOCUSED: "mdc-ripple-upgraded--background-focused",
        FG_ACTIVATION: "mdc-ripple-upgraded--foreground-activation",
        FG_DEACTIVATION: "mdc-ripple-upgraded--foreground-deactivation",
        ROOT: "mdc-ripple-upgraded",
        UNBOUNDED: "mdc-ripple-upgraded--unbounded"
      }, e.strings = {
        VAR_FG_SCALE: "--mdc-ripple-fg-scale",
        VAR_FG_SIZE: "--mdc-ripple-fg-size",
        VAR_FG_TRANSLATE_END: "--mdc-ripple-fg-translate-end",
        VAR_FG_TRANSLATE_START: "--mdc-ripple-fg-translate-start",
        VAR_LEFT: "--mdc-ripple-left",
        VAR_TOP: "--mdc-ripple-top"
      }, e.numbers = {
        DEACTIVATION_TIMEOUT_MS: 225,
        FG_DEACTIVATION_MS: 150,
        INITIAL_ORIGIN_SCALE: .6,
        PADDING: 10,
        TAP_DELAY_MS: 300
      };
    }
  }, r.c = i, r.d = function (t, e, n) {
    r.o(t, e) || Object.defineProperty(t, e, {
      enumerable: !0,
      get: n
    });
  }, r.r = function (t) {
    "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(t, Symbol.toStringTag, {
      value: "Module"
    }), Object.defineProperty(t, "__esModule", {
      value: !0
    });
  }, r.t = function (e, t) {
    if (1 & t && (e = r(e)), 8 & t) return e;
    if (4 & t && "object" == typeof e && e && e.__esModule) return e;
    var n = Object.create(null);
    if (r.r(n), Object.defineProperty(n, "default", {
      enumerable: !0,
      value: e
    }), 2 & t && "string" != typeof e) for (var i in e) r.d(n, i, function (t) {
      return e[t];
    }.bind(null, i));
    return n;
  }, r.n = function (t) {
    var e = t && t.__esModule ? function () {
      return t.default;
    } : function () {
      return t;
    };
    return r.d(e, "a", e), e;
  }, r.o = function (t, e) {
    return Object.prototype.hasOwnProperty.call(t, e);
  }, r.p = "", r(r.s = 211);
  function r(t) {
    if (i[t]) return i[t].exports;
    var e = i[t] = {
      i: t,
      l: !1,
      exports: {}
    };
    return n[t].call(e.exports, e, e.exports, r), e.l = !0, e.exports;
  }
  var n, i;
});

var coreAuth = {
  /**
   * Получение страницы входа и регистрации
   * @returns {*}
   */
  getPageContent: function getPageContent() {
    return coreTpl['auth/main.html'];
  },
  /**
   * Инициализация страницы входа и регистрации
   */
  init: function init() {
    var that = this;

    // Инициализация кнопок
    var buttons = document.querySelectorAll('.page-auth .mdc-button');
    var _iterator = _createForOfIteratorHelper(buttons),
      _step;
    try {
      for (_iterator.s(); !(_step = _iterator.n()).done;) {
        var button = _step.value;
        new mdc.ripple.MDCRipple(button);
      }
    } catch (err) {
      _iterator.e(err);
    } finally {
      _iterator.f();
    }
    $('.container-login form').on('submit', function () {
      coreAuth.login(this);
      return false;
    });
    $('.container-registration form').on('submit', function () {
      coreAuth.registration(this);
      return false;
    });
    var conf = localStorage.getItem('core3_conf');
    if (typeof conf === 'string') {
      try {
        conf = JSON.parse(conf);
        if (typeof conf.name === 'string') {
          $('head title').text(conf.name);
        }
        if (typeof conf.logo === 'string') {
          this._setLogo(conf.logo);
        }
        if (_typeof(conf.theme) === 'object') {
          this._setTheme(conf.theme);
        }
      } catch (e) {}
    }
    coreAuth.loadConfig().then(function (conf) {
      localStorage.setItem('core3_conf', JSON.stringify(conf));
      if (typeof conf.name === 'string') {
        $('head title').text(conf.name);
      }
      if (typeof conf.logo === 'string') {
        that._setLogo(conf.logo);
      } else {
        that._setLogo('');
      }
      if (_typeof(conf.theme) === 'object') {
        that._setTheme(conf.theme);
      }
    });
    coreAuth.viewActualContainer();

    // Установка
    var install = function install(event) {
      event.preventDefault();
      var button = $('.page-auth .install-button');
      if (event.platforms.includes('web')) {
        button.show();
        button.on('click', function () {
          event.prompt();
        });
      }
      event.userChoice.then(function (choiceResult) {
        switch (choiceResult.outcome) {
          case "accepted":
            button.hide();
            break;
          case "dismissed":
            // ignore
            break;
        }
      });
    };
    if (coreMain.install.event) {
      install(coreMain.install.event);
    } else {
      coreMain.install.promise.then(install);
    }
  },
  /**
   * Показ текущего контейнера
   */
  viewActualContainer: function viewActualContainer() {
    var params = coreTools.getParams();
    var authPanel = params.module;
    if (['login', 'registration', 'registration_complete'].indexOf(authPanel) === -1) {
      authPanel = 'login';
    }
    coreAuth._viewContainer(authPanel);
  },
  /**
   * @param action
   */
  preloader: function preloader(action) {
    var $btn = $('.page-auth button[type=submit]:visible');
    switch (action) {
      case 'show':
        $btn.attr("disabled", "disabled");
        if ($btn.find('.spinner-border').length === 0) {
          $btn.prepend('<div class="spinner-border spinner-border-sm"></div> ');
        }
        break;
      case 'hide':
        $btn.find('.spinner-border').remove();
        $btn.removeAttr("disabled");
        break;
    }
  },
  /**
   * Получение конфигурации
   * @return {Promise}
   */
  loadConfig: function loadConfig() {
    return new Promise(function (resolve, reject) {
      $.ajax({
        url: coreMain.options.basePath + "/conf",
        method: "GET",
        dataType: "json",
        success: function success(response) {
          resolve(response);
        }
      });
    });
  },
  /**
   * @param form
   * @returns {Promise<boolean>}
   */
  login: function () {
    var _login = _asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee(form) {
      var fp;
      return _regeneratorRuntime().wrap(function _callee$(_context) {
        while (1) switch (_context.prev = _context.next) {
          case 0:
            if (form.checkValidity()) {
              _context.next = 5;
              break;
            }
            $(form).addClass('was-validated');
            return _context.abrupt("return", false);
          case 5:
            $(form).removeClass('was-validated');
          case 6:
            coreAuth.preloader('show');
            $('.page-auth form .text-danger').text('');
            _context.next = 10;
            return coreTools.getFingerprint();
          case 10:
            fp = _context.sent;
            if (fp) {
              _context.next = 15;
              break;
            }
            coreAuth.preloader('hide');
            $('.page-auth form .text-danger').text('Не удалось получить отпечаток');
            return _context.abrupt("return", false);
          case 15:
            $.ajax({
              url: coreMain.options.basePath + "/auth/login",
              method: "POST",
              dataType: "json",
              contentType: "application/json; charset=utf-8",
              data: JSON.stringify({
                login: $('[name=login]', form).val(),
                password: MD5($('[name=password]', form).val()),
                fp: fp
              }),
              success: function success(response) {
                if (typeof response.access_token !== 'string' || typeof response.refresh_token !== 'string' || !response.access_token || !response.refresh_token) {
                  var errorMessage = response.error_message || "Ошибка. Попробуйте позже, либо обратитесь к администратору";
                  $('.page-auth form .text-danger').text(errorMessage);
                } else {
                  $('.page-auth form .text-danger').text('');
                  coreTokens.setAccessToken(response.access_token);
                  coreTokens.setRefreshToken(response.refresh_token);
                  $('.page-auth [name=login]').val('');
                  $('.page-auth [name=password]').val('');
                  coreMain.viewPage('menu');
                  coreTokens.initRefresh();
                }
              },
              error: function error(response) {
                coreAuth.preloader('hide');
                var errorMessage = '';
                if (response.status === 0) {
                  errorMessage = 'Проверьте подключение к интернету';
                } else if (response.responseJSON && response.responseJSON.error_message) {
                  errorMessage = response.responseJSON.error_message;
                } else {
                  errorMessage = $("<div>" + response.responseText + "</div>").text();
                }
                errorMessage = errorMessage || 'Ошибка. Попробуйте позже, либо обратитесь к администратору';
                $('.container-login .text-danger').text(errorMessage);
              },
              complete: function complete(jqXHR, textStatus) {
                coreAuth.preloader('hide');
              }
            });
          case 16:
          case "end":
            return _context.stop();
        }
      }, _callee);
    }));
    function login(_x) {
      return _login.apply(this, arguments);
    }
    return login;
  }(),
  /**
   *
   */
  logout: function logout() {
    $.ajax({
      url: coreMain.options.basePath + '/auth/logout',
      method: "PUT",
      headers: {
        'Access-Token': coreTokens.getAccessToken()
      },
      dataType: "json",
      success: function success(response) {
        coreTokens.clearAccessToken();
        coreTokens.deinitRefresh();
        coreMain.viewPage('auth');
        $('.page-menu > aside .menu-logout').removeClass('mdc-list-item--activated');
      },
      error: function error(response) {
        if (response.status === 0) {
          CoreUI.alert.danger('Ошибка', 'Проверьте подключение к интернету');
        } else {
          CoreUI.alert.danger('Ошибка', 'Обновите приложение или обратитесь к администратору');
        }
      }
    });
  },
  /**
   * @param form
   */
  registration: function registration(form) {
    if (!form.checkValidity()) {
      $(form).addClass('was-validated');
      return false;
    } else {
      $(form).removeClass('was-validated');
    }
    coreAuth.preloader('show');
    $('.container-registration .text-danger').text('');
    $.ajax({
      url: coreMain.options.basePath + "/auth/registration/email",
      dataType: "json",
      method: "POST",
      data: $(form).serialize(),
      success: function success(response) {
        coreAuth.preloader('hide');
        if (typeof response.access_token !== 'string' || typeof response.refresh_token !== 'string' || !response.access_token || !response.refresh_token) {
          var errorMessage = response.error_message || "Ошибка. Попробуйте позже, либо обратитесь к администратору";
          $('.container-registration .text-danger').text(errorMessage);
        } else {
          $('.page-auth form .text-danger').text('');
          coreTokens.setAccessToken(response.access_token);
          coreTokens.setRefreshToken(response.refresh_token);
          $('.page-auth [name=login]').val('');
          $('.page-auth [name=password]').val('');
          coreMain.viewPage('menu');
          coreTokens.initRefresh();
        }
      },
      error: function error(response) {
        coreAuth.preloader('hide');
        var errorMessage = '';
        if (response.status === 0) {
          errorMessage = 'Проверьте подключение к интернету';
        } else if (response.responseJSON && response.responseJSON.error_message) {
          errorMessage = response.responseJSON.error_message;
        } else {
          errorMessage = $(response.responseText).text();
        }
        errorMessage = errorMessage || 'Ошибка. Попробуйте позже, либо обратитесь к администратору';
        $('.container-registration .text-danger').text(errorMessage);
      },
      complete: function complete(jqXHR, textStatus) {
        coreAuth.preloader('hide');
      }
    });
  },
  /**
   * @param form
   * @constructor
   */
  registrationComplete: function registrationComplete(form) {
    var pass1 = $("[name=password]", form).val();
    var pass2 = $("[name=password2]", form).val();
    if (!pass1 || !pass2) {
      $('.container-registration_complete .text-danger').text('Введите пароль');
      return false;
    }
    if (pass1 !== pass2) {
      $('.container-registration_complete .text-danger').text('Пароли не совпадают').show();
      return false;
    }
    coreAuth.preloader('show');
    $('.container-registration_complete .text-danger').text('');
    var params = coreTools.getParams();
    $.ajax({
      url: coreMain.options.basePath + "/auth/registration/email/check",
      dataType: "json",
      method: "POST",
      data: {
        key: params.query.key,
        password: MD5(form.password.value)
      },
      success: function success(data) {
        coreAuth.preloader('hide');
        if (data.status === 'success') {
          $('.container-registration_complete .text-success').html(data.message).css('margin-bottom', '50px');
          $(form).hide();
        } else {
          $('.container-registration_complete .text-danger').text(data.error_message);
        }
      },
      error: function error(response) {
        coreAuth.preloader('hide');
        var errorMessage = '';
        if (response.status === 0) {
          errorMessage = 'Ошибка. Проверьте подключение к интернету';
        } else {
          errorMessage = 'Ошибка. Попробуйте позже, либо обратитесь к администратору';
        }
        $('.container-registration_complete .text-danger').text(errorMessage);
      }
    });
  },
  /**
   * Показ указанного контейнера
   * @param name
   */
  _viewContainer: function _viewContainer(name) {
    $('.page-auth > .container').hide();
    $('.page-auth > .container-' + name).fadeIn('fast');
  },
  /**
   * Установка логотипа
   * @param {string} logo
   * @private
   */
  _setLogo: function _setLogo(logo) {
    if (logo) {
      $('.page-auth img.logo').attr('src', logo).show();
    } else {
      $('.page-auth img.logo').hide();
    }
  },
  /**
   * Установка темы
   * @param {object} theme
   * @private
   */
  _setTheme: function _setTheme(theme) {
    var styles = [];
    if (_typeof(theme.login) === 'object' && typeof theme.login.bg_video === 'string' && theme.login.bg_video) {
      if (!$('.page.page-auth > video')[0]) {
        $('.page.page-auth').prepend('<video autoplay muted loop><source src="' + theme.login.bg_video + '" type="video/mp4"></video>');
      }
    }
    if (_typeof(theme.login) === 'object' && typeof theme.login.bg_img === 'string' && theme.login.bg_img) {
      styles.push('--login-bg:url("' + theme.login.bg_img + '");');
    } else if (_typeof(theme.login) === 'object' && typeof theme.login.bg_color === 'string' && theme.login.bg_color) {
      styles.push('--login-bg: ' + theme.login.bg_color + ';');
    }
    if (styles.length > 0) {
      var content = ':root{' + styles.join('') + '}';
      var coreTheme = $('head #theme-login');
      if (!coreTheme[0] || content !== coreTheme.html()) {
        if (coreTheme[0]) {
          coreTheme.remove();
        }
        $('head').append('<style id="theme-login">' + content + '</style>');
      }
    }
  }
};
$(function () {
  coreMain.on('hashchange', function () {
    if ($('.page-auth')[0]) {
      coreAuth.viewActualContainer();
    }
  });
});

(function (f) {
  if (typeof exports === "object" && typeof module !== "undefined") {
    module.exports = f();
  } else if (typeof define === "function" && define.amd) {
    define([], f);
  } else {
    var g;
    if (typeof window !== "undefined") {
      g = window;
    } else if (typeof global !== "undefined") {
      g = global;
    } else if (typeof self !== "undefined") {
      g = self;
    } else {
      g = this;
    }
    g.ejs = f();
  }
})(function () {
  return function () {
    function r(e, n, t) {
      function o(i, f) {
        if (!n[i]) {
          if (!e[i]) {
            var c = "function" == typeof require && require;
            if (!f && c) return c(i, !0);
            if (u) return u(i, !0);
            var a = new Error("Cannot find module '" + i + "'");
            throw a.code = "MODULE_NOT_FOUND", a;
          }
          var p = n[i] = {
            exports: {}
          };
          e[i][0].call(p.exports, function (r) {
            var n = e[i][1][r];
            return o(n || r);
          }, p, p.exports, r, e, n, t);
        }
        return n[i].exports;
      }
      for (var u = "function" == typeof require && require, i = 0; i < t.length; i++) o(t[i]);
      return o;
    }
    return r;
  }()({
    1: [function (require, module, exports) {
      "use strict";

      var fs = require("fs");
      var path = require("path");
      var utils = require("./utils");
      var scopeOptionWarned = false;
      var _VERSION_STRING = require("../package.json").version;
      var _DEFAULT_OPEN_DELIMITER = "<";
      var _DEFAULT_CLOSE_DELIMITER = ">";
      var _DEFAULT_DELIMITER = "%";
      var _DEFAULT_LOCALS_NAME = "locals";
      var _NAME = "ejs";
      var _REGEX_STRING = "(<%%|%%>|<%=|<%-|<%_|<%#|<%|%>|-%>|_%>)";
      var _OPTS_PASSABLE_WITH_DATA = ["delimiter", "scope", "context", "debug", "compileDebug", "client", "_with", "rmWhitespace", "strict", "filename", "async"];
      var _OPTS_PASSABLE_WITH_DATA_EXPRESS = _OPTS_PASSABLE_WITH_DATA.concat("cache");
      var _BOM = /^\uFEFF/;
      var _JS_IDENTIFIER = /^[a-zA-Z_$][0-9a-zA-Z_$]*$/;
      exports.cache = utils.cache;
      exports.fileLoader = fs.readFileSync;
      exports.localsName = _DEFAULT_LOCALS_NAME;
      exports.promiseImpl = new Function("return this;")().Promise;
      exports.resolveInclude = function (name, filename, isDir) {
        var dirname = path.dirname;
        var extname = path.extname;
        var resolve = path.resolve;
        var includePath = resolve(isDir ? filename : dirname(filename), name);
        var ext = extname(name);
        if (!ext) {
          includePath += ".ejs";
        }
        return includePath;
      };
      function resolvePaths(name, paths) {
        var filePath;
        if (paths.some(function (v) {
          filePath = exports.resolveInclude(name, v, true);
          return fs.existsSync(filePath);
        })) {
          return filePath;
        }
      }
      function getIncludePath(path, options) {
        var includePath;
        var filePath;
        var views = options.views;
        var match = /^[A-Za-z]+:\\|^\//.exec(path);
        if (match && match.length) {
          path = path.replace(/^\/*/, "");
          if (Array.isArray(options.root)) {
            includePath = resolvePaths(path, options.root);
          } else {
            includePath = exports.resolveInclude(path, options.root || "/", true);
          }
        } else {
          if (options.filename) {
            filePath = exports.resolveInclude(path, options.filename);
            if (fs.existsSync(filePath)) {
              includePath = filePath;
            }
          }
          if (!includePath && Array.isArray(views)) {
            includePath = resolvePaths(path, views);
          }
          if (!includePath && typeof options.includer !== "function") {
            throw new Error('Could not find the include file "' + options.escapeFunction(path) + '"');
          }
        }
        return includePath;
      }
      function handleCache(options, template) {
        var func;
        var filename = options.filename;
        var hasTemplate = arguments.length > 1;
        if (options.cache) {
          if (!filename) {
            throw new Error("cache option requires a filename");
          }
          func = exports.cache.get(filename);
          if (func) {
            return func;
          }
          if (!hasTemplate) {
            template = fileLoader(filename).toString().replace(_BOM, "");
          }
        } else if (!hasTemplate) {
          if (!filename) {
            throw new Error("Internal EJS error: no file name or template " + "provided");
          }
          template = fileLoader(filename).toString().replace(_BOM, "");
        }
        func = exports.compile(template, options);
        if (options.cache) {
          exports.cache.set(filename, func);
        }
        return func;
      }
      function tryHandleCache(options, data, cb) {
        var result;
        if (!cb) {
          if (typeof exports.promiseImpl == "function") {
            return new exports.promiseImpl(function (resolve, reject) {
              try {
                result = handleCache(options)(data);
                resolve(result);
              } catch (err) {
                reject(err);
              }
            });
          } else {
            throw new Error("Please provide a callback function");
          }
        } else {
          try {
            result = handleCache(options)(data);
          } catch (err) {
            return cb(err);
          }
          cb(null, result);
        }
      }
      function fileLoader(filePath) {
        return exports.fileLoader(filePath);
      }
      function includeFile(path, options) {
        var opts = utils.shallowCopy(utils.createNullProtoObjWherePossible(), options);
        opts.filename = getIncludePath(path, opts);
        if (typeof options.includer === "function") {
          var includerResult = options.includer(path, opts.filename);
          if (includerResult) {
            if (includerResult.filename) {
              opts.filename = includerResult.filename;
            }
            if (includerResult.template) {
              return handleCache(opts, includerResult.template);
            }
          }
        }
        return handleCache(opts);
      }
      function rethrow(err, str, flnm, lineno, esc) {
        var lines = str.split("\n");
        var start = Math.max(lineno - 3, 0);
        var end = Math.min(lines.length, lineno + 3);
        var filename = esc(flnm);
        var context = lines.slice(start, end).map(function (line, i) {
          var curr = i + start + 1;
          return (curr == lineno ? " >> " : "    ") + curr + "| " + line;
        }).join("\n");
        err.path = filename;
        err.message = (filename || "ejs") + ":" + lineno + "\n" + context + "\n\n" + err.message;
        throw err;
      }
      function stripSemi(str) {
        return str.replace(/;(\s*$)/, "$1");
      }
      exports.compile = function compile(template, opts) {
        var templ;
        if (opts && opts.scope) {
          if (!scopeOptionWarned) {
            console.warn("`scope` option is deprecated and will be removed in EJS 3");
            scopeOptionWarned = true;
          }
          if (!opts.context) {
            opts.context = opts.scope;
          }
          delete opts.scope;
        }
        templ = new Template(template, opts);
        return templ.compile();
      };
      exports.render = function (template, d, o) {
        var data = d || utils.createNullProtoObjWherePossible();
        var opts = o || utils.createNullProtoObjWherePossible();
        if (arguments.length == 2) {
          utils.shallowCopyFromList(opts, data, _OPTS_PASSABLE_WITH_DATA);
        }
        return handleCache(opts, template)(data);
      };
      exports.renderFile = function () {
        var args = Array.prototype.slice.call(arguments);
        var filename = args.shift();
        var cb;
        var opts = {
          filename: filename
        };
        var data;
        var viewOpts;
        if (typeof arguments[arguments.length - 1] == "function") {
          cb = args.pop();
        }
        if (args.length) {
          data = args.shift();
          if (args.length) {
            utils.shallowCopy(opts, args.pop());
          } else {
            if (data.settings) {
              if (data.settings.views) {
                opts.views = data.settings.views;
              }
              if (data.settings["view cache"]) {
                opts.cache = true;
              }
              viewOpts = data.settings["view options"];
              if (viewOpts) {
                utils.shallowCopy(opts, viewOpts);
              }
            }
            utils.shallowCopyFromList(opts, data, _OPTS_PASSABLE_WITH_DATA_EXPRESS);
          }
          opts.filename = filename;
        } else {
          data = utils.createNullProtoObjWherePossible();
        }
        return tryHandleCache(opts, data, cb);
      };
      exports.Template = Template;
      exports.clearCache = function () {
        exports.cache.reset();
      };
      function Template(text, opts) {
        opts = opts || utils.createNullProtoObjWherePossible();
        var options = utils.createNullProtoObjWherePossible();
        this.templateText = text;
        this.mode = null;
        this.truncate = false;
        this.currentLine = 1;
        this.source = "";
        options.client = opts.client || false;
        options.escapeFunction = opts.escape || opts.escapeFunction || utils.escapeXML;
        options.compileDebug = opts.compileDebug !== false;
        options.debug = !!opts.debug;
        options.filename = opts.filename;
        options.openDelimiter = opts.openDelimiter || exports.openDelimiter || _DEFAULT_OPEN_DELIMITER;
        options.closeDelimiter = opts.closeDelimiter || exports.closeDelimiter || _DEFAULT_CLOSE_DELIMITER;
        options.delimiter = opts.delimiter || exports.delimiter || _DEFAULT_DELIMITER;
        options.strict = opts.strict || false;
        options.context = opts.context;
        options.cache = opts.cache || false;
        options.rmWhitespace = opts.rmWhitespace;
        options.root = opts.root;
        options.includer = opts.includer;
        options.outputFunctionName = opts.outputFunctionName;
        options.localsName = opts.localsName || exports.localsName || _DEFAULT_LOCALS_NAME;
        options.views = opts.views;
        options.async = opts.async;
        options.destructuredLocals = opts.destructuredLocals;
        options.legacyInclude = typeof opts.legacyInclude != "undefined" ? !!opts.legacyInclude : true;
        if (options.strict) {
          options._with = false;
        } else {
          options._with = typeof opts._with != "undefined" ? opts._with : true;
        }
        this.opts = options;
        this.regex = this.createRegex();
      }
      Template.modes = {
        EVAL: "eval",
        ESCAPED: "escaped",
        RAW: "raw",
        COMMENT: "comment",
        LITERAL: "literal"
      };
      Template.prototype = {
        createRegex: function () {
          var str = _REGEX_STRING;
          var delim = utils.escapeRegExpChars(this.opts.delimiter);
          var open = utils.escapeRegExpChars(this.opts.openDelimiter);
          var close = utils.escapeRegExpChars(this.opts.closeDelimiter);
          str = str.replace(/%/g, delim).replace(/</g, open).replace(/>/g, close);
          return new RegExp(str);
        },
        compile: function () {
          var src;
          var fn;
          var opts = this.opts;
          var prepended = "";
          var appended = "";
          var escapeFn = opts.escapeFunction;
          var ctor;
          var sanitizedFilename = opts.filename ? JSON.stringify(opts.filename) : "undefined";
          if (!this.source) {
            this.generateSource();
            prepended += '  var __output = "";\n' + "  function __append(s) { if (s !== undefined && s !== null) __output += s }\n";
            if (opts.outputFunctionName) {
              if (!_JS_IDENTIFIER.test(opts.outputFunctionName)) {
                throw new Error("outputFunctionName is not a valid JS identifier.");
              }
              prepended += "  var " + opts.outputFunctionName + " = __append;" + "\n";
            }
            if (opts.localsName && !_JS_IDENTIFIER.test(opts.localsName)) {
              throw new Error("localsName is not a valid JS identifier.");
            }
            if (opts.destructuredLocals && opts.destructuredLocals.length) {
              var destructuring = "  var __locals = (" + opts.localsName + " || {}),\n";
              for (var i = 0; i < opts.destructuredLocals.length; i++) {
                var name = opts.destructuredLocals[i];
                if (!_JS_IDENTIFIER.test(name)) {
                  throw new Error("destructuredLocals[" + i + "] is not a valid JS identifier.");
                }
                if (i > 0) {
                  destructuring += ",\n  ";
                }
                destructuring += name + " = __locals." + name;
              }
              prepended += destructuring + ";\n";
            }
            if (opts._with !== false) {
              prepended += "  with (" + opts.localsName + " || {}) {" + "\n";
              appended += "  }" + "\n";
            }
            appended += "  return __output;" + "\n";
            this.source = prepended + this.source + appended;
          }
          if (opts.compileDebug) {
            src = "var __line = 1" + "\n" + "  , __lines = " + JSON.stringify(this.templateText) + "\n" + "  , __filename = " + sanitizedFilename + ";" + "\n" + "try {" + "\n" + this.source + "} catch (e) {" + "\n" + "  rethrow(e, __lines, __filename, __line, escapeFn);" + "\n" + "}" + "\n";
          } else {
            src = this.source;
          }
          if (opts.client) {
            src = "escapeFn = escapeFn || " + escapeFn.toString() + ";" + "\n" + src;
            if (opts.compileDebug) {
              src = "rethrow = rethrow || " + rethrow.toString() + ";" + "\n" + src;
            }
          }
          if (opts.strict) {
            src = '"use strict";\n' + src;
          }
          if (opts.debug) {
            console.log(src);
          }
          if (opts.compileDebug && opts.filename) {
            src = src + "\n" + "//# sourceURL=" + sanitizedFilename + "\n";
          }
          try {
            if (opts.async) {
              try {
                ctor = new Function("return (async function(){}).constructor;")();
              } catch (e) {
                if (e instanceof SyntaxError) {
                  throw new Error("This environment does not support async/await");
                } else {
                  throw e;
                }
              }
            } else {
              ctor = Function;
            }
            fn = new ctor(opts.localsName + ", escapeFn, include, rethrow", src);
          } catch (e) {
            if (e instanceof SyntaxError) {
              if (opts.filename) {
                e.message += " in " + opts.filename;
              }
              e.message += " while compiling ejs\n\n";
              e.message += "If the above error is not helpful, you may want to try EJS-Lint:\n";
              e.message += "https://github.com/RyanZim/EJS-Lint";
              if (!opts.async) {
                e.message += "\n";
                e.message += "Or, if you meant to create an async function, pass `async: true` as an option.";
              }
            }
            throw e;
          }
          var returnedFn = opts.client ? fn : function anonymous(data) {
            var include = function (path, includeData) {
              var d = utils.shallowCopy(utils.createNullProtoObjWherePossible(), data);
              if (includeData) {
                d = utils.shallowCopy(d, includeData);
              }
              return includeFile(path, opts)(d);
            };
            return fn.apply(opts.context, [data || utils.createNullProtoObjWherePossible(), escapeFn, include, rethrow]);
          };
          if (opts.filename && typeof Object.defineProperty === "function") {
            var filename = opts.filename;
            var basename = path.basename(filename, path.extname(filename));
            try {
              Object.defineProperty(returnedFn, "name", {
                value: basename,
                writable: false,
                enumerable: false,
                configurable: true
              });
            } catch (e) {}
          }
          return returnedFn;
        },
        generateSource: function () {
          var opts = this.opts;
          if (opts.rmWhitespace) {
            this.templateText = this.templateText.replace(/[\r\n]+/g, "\n").replace(/^\s+|\s+$/gm, "");
          }
          this.templateText = this.templateText.replace(/[ \t]*<%_/gm, "<%_").replace(/_%>[ \t]*/gm, "_%>");
          var self = this;
          var matches = this.parseTemplateText();
          var d = this.opts.delimiter;
          var o = this.opts.openDelimiter;
          var c = this.opts.closeDelimiter;
          if (matches && matches.length) {
            matches.forEach(function (line, index) {
              var closing;
              if (line.indexOf(o + d) === 0 && line.indexOf(o + d + d) !== 0) {
                closing = matches[index + 2];
                if (!(closing == d + c || closing == "-" + d + c || closing == "_" + d + c)) {
                  throw new Error('Could not find matching close tag for "' + line + '".');
                }
              }
              self.scanLine(line);
            });
          }
        },
        parseTemplateText: function () {
          var str = this.templateText;
          var pat = this.regex;
          var result = pat.exec(str);
          var arr = [];
          var firstPos;
          while (result) {
            firstPos = result.index;
            if (firstPos !== 0) {
              arr.push(str.substring(0, firstPos));
              str = str.slice(firstPos);
            }
            arr.push(result[0]);
            str = str.slice(result[0].length);
            result = pat.exec(str);
          }
          if (str) {
            arr.push(str);
          }
          return arr;
        },
        _addOutput: function (line) {
          if (this.truncate) {
            line = line.replace(/^(?:\r\n|\r|\n)/, "");
            this.truncate = false;
          }
          if (!line) {
            return line;
          }
          line = line.replace(/\\/g, "\\\\");
          line = line.replace(/\n/g, "\\n");
          line = line.replace(/\r/g, "\\r");
          line = line.replace(/"/g, '\\"');
          this.source += '    ; __append("' + line + '")' + "\n";
        },
        scanLine: function (line) {
          var self = this;
          var d = this.opts.delimiter;
          var o = this.opts.openDelimiter;
          var c = this.opts.closeDelimiter;
          var newLineCount = 0;
          newLineCount = line.split("\n").length - 1;
          switch (line) {
            case o + d:
            case o + d + "_":
              this.mode = Template.modes.EVAL;
              break;
            case o + d + "=":
              this.mode = Template.modes.ESCAPED;
              break;
            case o + d + "-":
              this.mode = Template.modes.RAW;
              break;
            case o + d + "#":
              this.mode = Template.modes.COMMENT;
              break;
            case o + d + d:
              this.mode = Template.modes.LITERAL;
              this.source += '    ; __append("' + line.replace(o + d + d, o + d) + '")' + "\n";
              break;
            case d + d + c:
              this.mode = Template.modes.LITERAL;
              this.source += '    ; __append("' + line.replace(d + d + c, d + c) + '")' + "\n";
              break;
            case d + c:
            case "-" + d + c:
            case "_" + d + c:
              if (this.mode == Template.modes.LITERAL) {
                this._addOutput(line);
              }
              this.mode = null;
              this.truncate = line.indexOf("-") === 0 || line.indexOf("_") === 0;
              break;
            default:
              if (this.mode) {
                switch (this.mode) {
                  case Template.modes.EVAL:
                  case Template.modes.ESCAPED:
                  case Template.modes.RAW:
                    if (line.lastIndexOf("//") > line.lastIndexOf("\n")) {
                      line += "\n";
                    }
                }
                switch (this.mode) {
                  case Template.modes.EVAL:
                    this.source += "    ; " + line + "\n";
                    break;
                  case Template.modes.ESCAPED:
                    this.source += "    ; __append(escapeFn(" + stripSemi(line) + "))" + "\n";
                    break;
                  case Template.modes.RAW:
                    this.source += "    ; __append(" + stripSemi(line) + ")" + "\n";
                    break;
                  case Template.modes.COMMENT:
                    break;
                  case Template.modes.LITERAL:
                    this._addOutput(line);
                    break;
                }
              } else {
                this._addOutput(line);
              }
          }
          if (self.opts.compileDebug && newLineCount) {
            this.currentLine += newLineCount;
            this.source += "    ; __line = " + this.currentLine + "\n";
          }
        }
      };
      exports.escapeXML = utils.escapeXML;
      exports.__express = exports.renderFile;
      exports.VERSION = _VERSION_STRING;
      exports.name = _NAME;
      if (typeof window != "undefined") {
        window.ejs = exports;
      }
    }, {
      "../package.json": 6,
      "./utils": 2,
      fs: 3,
      path: 4
    }],
    2: [function (require, module, exports) {
      "use strict";

      var regExpChars = /[|\\{}()[\]^$+*?.]/g;
      var hasOwnProperty = Object.prototype.hasOwnProperty;
      var hasOwn = function (obj, key) {
        return hasOwnProperty.apply(obj, [key]);
      };
      exports.escapeRegExpChars = function (string) {
        if (!string) {
          return "";
        }
        return String(string).replace(regExpChars, "\\$&");
      };
      var _ENCODE_HTML_RULES = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&#34;",
        "'": "&#39;"
      };
      var _MATCH_HTML = /[&<>'"]/g;
      function encode_char(c) {
        return _ENCODE_HTML_RULES[c] || c;
      }
      var escapeFuncStr = "var _ENCODE_HTML_RULES = {\n" + '      "&": "&amp;"\n' + '    , "<": "&lt;"\n' + '    , ">": "&gt;"\n' + '    , \'"\': "&#34;"\n' + '    , "\'": "&#39;"\n' + "    }\n" + "  , _MATCH_HTML = /[&<>'\"]/g;\n" + "function encode_char(c) {\n" + "  return _ENCODE_HTML_RULES[c] || c;\n" + "};\n";
      exports.escapeXML = function (markup) {
        return markup == undefined ? "" : String(markup).replace(_MATCH_HTML, encode_char);
      };
      function escapeXMLToString() {
        return Function.prototype.toString.call(this) + ";\n" + escapeFuncStr;
      }
      try {
        if (typeof Object.defineProperty === "function") {
          Object.defineProperty(exports.escapeXML, "toString", {
            value: escapeXMLToString
          });
        } else {
          exports.escapeXML.toString = escapeXMLToString;
        }
      } catch (err) {
        console.warn("Unable to set escapeXML.toString (is the Function prototype frozen?)");
      }
      exports.shallowCopy = function (to, from) {
        from = from || {};
        if (to !== null && to !== undefined) {
          for (var p in from) {
            if (!hasOwn(from, p)) {
              continue;
            }
            if (p === "__proto__" || p === "constructor") {
              continue;
            }
            to[p] = from[p];
          }
        }
        return to;
      };
      exports.shallowCopyFromList = function (to, from, list) {
        list = list || [];
        from = from || {};
        if (to !== null && to !== undefined) {
          for (var i = 0; i < list.length; i++) {
            var p = list[i];
            if (typeof from[p] != "undefined") {
              if (!hasOwn(from, p)) {
                continue;
              }
              if (p === "__proto__" || p === "constructor") {
                continue;
              }
              to[p] = from[p];
            }
          }
        }
        return to;
      };
      exports.cache = {
        _data: {},
        set: function (key, val) {
          this._data[key] = val;
        },
        get: function (key) {
          return this._data[key];
        },
        remove: function (key) {
          delete this._data[key];
        },
        reset: function () {
          this._data = {};
        }
      };
      exports.hyphenToCamel = function (str) {
        return str.replace(/-[a-z]/g, function (match) {
          return match[1].toUpperCase();
        });
      };
      exports.createNullProtoObjWherePossible = function () {
        if (typeof Object.create == "function") {
          return function () {
            return Object.create(null);
          };
        }
        if (!({
          __proto__: null
        } instanceof Object)) {
          return function () {
            return {
              __proto__: null
            };
          };
        }
        return function () {
          return {};
        };
      }();
    }, {}],
    3: [function (require, module, exports) {}, {}],
    4: [function (require, module, exports) {
      (function (process) {
        function normalizeArray(parts, allowAboveRoot) {
          var up = 0;
          for (var i = parts.length - 1; i >= 0; i--) {
            var last = parts[i];
            if (last === ".") {
              parts.splice(i, 1);
            } else if (last === "..") {
              parts.splice(i, 1);
              up++;
            } else if (up) {
              parts.splice(i, 1);
              up--;
            }
          }
          if (allowAboveRoot) {
            for (; up--; up) {
              parts.unshift("..");
            }
          }
          return parts;
        }
        exports.resolve = function () {
          var resolvedPath = "",
            resolvedAbsolute = false;
          for (var i = arguments.length - 1; i >= -1 && !resolvedAbsolute; i--) {
            var path = i >= 0 ? arguments[i] : process.cwd();
            if (typeof path !== "string") {
              throw new TypeError("Arguments to path.resolve must be strings");
            } else if (!path) {
              continue;
            }
            resolvedPath = path + "/" + resolvedPath;
            resolvedAbsolute = path.charAt(0) === "/";
          }
          resolvedPath = normalizeArray(filter(resolvedPath.split("/"), function (p) {
            return !!p;
          }), !resolvedAbsolute).join("/");
          return (resolvedAbsolute ? "/" : "") + resolvedPath || ".";
        };
        exports.normalize = function (path) {
          var isAbsolute = exports.isAbsolute(path),
            trailingSlash = substr(path, -1) === "/";
          path = normalizeArray(filter(path.split("/"), function (p) {
            return !!p;
          }), !isAbsolute).join("/");
          if (!path && !isAbsolute) {
            path = ".";
          }
          if (path && trailingSlash) {
            path += "/";
          }
          return (isAbsolute ? "/" : "") + path;
        };
        exports.isAbsolute = function (path) {
          return path.charAt(0) === "/";
        };
        exports.join = function () {
          var paths = Array.prototype.slice.call(arguments, 0);
          return exports.normalize(filter(paths, function (p, index) {
            if (typeof p !== "string") {
              throw new TypeError("Arguments to path.join must be strings");
            }
            return p;
          }).join("/"));
        };
        exports.relative = function (from, to) {
          from = exports.resolve(from).substr(1);
          to = exports.resolve(to).substr(1);
          function trim(arr) {
            var start = 0;
            for (; start < arr.length; start++) {
              if (arr[start] !== "") break;
            }
            var end = arr.length - 1;
            for (; end >= 0; end--) {
              if (arr[end] !== "") break;
            }
            if (start > end) return [];
            return arr.slice(start, end - start + 1);
          }
          var fromParts = trim(from.split("/"));
          var toParts = trim(to.split("/"));
          var length = Math.min(fromParts.length, toParts.length);
          var samePartsLength = length;
          for (var i = 0; i < length; i++) {
            if (fromParts[i] !== toParts[i]) {
              samePartsLength = i;
              break;
            }
          }
          var outputParts = [];
          for (var i = samePartsLength; i < fromParts.length; i++) {
            outputParts.push("..");
          }
          outputParts = outputParts.concat(toParts.slice(samePartsLength));
          return outputParts.join("/");
        };
        exports.sep = "/";
        exports.delimiter = ":";
        exports.dirname = function (path) {
          if (typeof path !== "string") path = path + "";
          if (path.length === 0) return ".";
          var code = path.charCodeAt(0);
          var hasRoot = code === 47;
          var end = -1;
          var matchedSlash = true;
          for (var i = path.length - 1; i >= 1; --i) {
            code = path.charCodeAt(i);
            if (code === 47) {
              if (!matchedSlash) {
                end = i;
                break;
              }
            } else {
              matchedSlash = false;
            }
          }
          if (end === -1) return hasRoot ? "/" : ".";
          if (hasRoot && end === 1) {
            return "/";
          }
          return path.slice(0, end);
        };
        function basename(path) {
          if (typeof path !== "string") path = path + "";
          var start = 0;
          var end = -1;
          var matchedSlash = true;
          var i;
          for (i = path.length - 1; i >= 0; --i) {
            if (path.charCodeAt(i) === 47) {
              if (!matchedSlash) {
                start = i + 1;
                break;
              }
            } else if (end === -1) {
              matchedSlash = false;
              end = i + 1;
            }
          }
          if (end === -1) return "";
          return path.slice(start, end);
        }
        exports.basename = function (path, ext) {
          var f = basename(path);
          if (ext && f.substr(-1 * ext.length) === ext) {
            f = f.substr(0, f.length - ext.length);
          }
          return f;
        };
        exports.extname = function (path) {
          if (typeof path !== "string") path = path + "";
          var startDot = -1;
          var startPart = 0;
          var end = -1;
          var matchedSlash = true;
          var preDotState = 0;
          for (var i = path.length - 1; i >= 0; --i) {
            var code = path.charCodeAt(i);
            if (code === 47) {
              if (!matchedSlash) {
                startPart = i + 1;
                break;
              }
              continue;
            }
            if (end === -1) {
              matchedSlash = false;
              end = i + 1;
            }
            if (code === 46) {
              if (startDot === -1) startDot = i;else if (preDotState !== 1) preDotState = 1;
            } else if (startDot !== -1) {
              preDotState = -1;
            }
          }
          if (startDot === -1 || end === -1 || preDotState === 0 || preDotState === 1 && startDot === end - 1 && startDot === startPart + 1) {
            return "";
          }
          return path.slice(startDot, end);
        };
        function filter(xs, f) {
          if (xs.filter) return xs.filter(f);
          var res = [];
          for (var i = 0; i < xs.length; i++) {
            if (f(xs[i], i, xs)) res.push(xs[i]);
          }
          return res;
        }
        var substr = "ab".substr(-1) === "b" ? function (str, start, len) {
          return str.substr(start, len);
        } : function (str, start, len) {
          if (start < 0) start = str.length + start;
          return str.substr(start, len);
        };
      }).call(this, require("_process"));
    }, {
      _process: 5
    }],
    5: [function (require, module, exports) {
      var process = module.exports = {};
      var cachedSetTimeout;
      var cachedClearTimeout;
      function defaultSetTimout() {
        throw new Error("setTimeout has not been defined");
      }
      function defaultClearTimeout() {
        throw new Error("clearTimeout has not been defined");
      }
      (function () {
        try {
          if (typeof setTimeout === "function") {
            cachedSetTimeout = setTimeout;
          } else {
            cachedSetTimeout = defaultSetTimout;
          }
        } catch (e) {
          cachedSetTimeout = defaultSetTimout;
        }
        try {
          if (typeof clearTimeout === "function") {
            cachedClearTimeout = clearTimeout;
          } else {
            cachedClearTimeout = defaultClearTimeout;
          }
        } catch (e) {
          cachedClearTimeout = defaultClearTimeout;
        }
      })();
      function runTimeout(fun) {
        if (cachedSetTimeout === setTimeout) {
          return setTimeout(fun, 0);
        }
        if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
          cachedSetTimeout = setTimeout;
          return setTimeout(fun, 0);
        }
        try {
          return cachedSetTimeout(fun, 0);
        } catch (e) {
          try {
            return cachedSetTimeout.call(null, fun, 0);
          } catch (e) {
            return cachedSetTimeout.call(this, fun, 0);
          }
        }
      }
      function runClearTimeout(marker) {
        if (cachedClearTimeout === clearTimeout) {
          return clearTimeout(marker);
        }
        if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
          cachedClearTimeout = clearTimeout;
          return clearTimeout(marker);
        }
        try {
          return cachedClearTimeout(marker);
        } catch (e) {
          try {
            return cachedClearTimeout.call(null, marker);
          } catch (e) {
            return cachedClearTimeout.call(this, marker);
          }
        }
      }
      var queue = [];
      var draining = false;
      var currentQueue;
      var queueIndex = -1;
      function cleanUpNextTick() {
        if (!draining || !currentQueue) {
          return;
        }
        draining = false;
        if (currentQueue.length) {
          queue = currentQueue.concat(queue);
        } else {
          queueIndex = -1;
        }
        if (queue.length) {
          drainQueue();
        }
      }
      function drainQueue() {
        if (draining) {
          return;
        }
        var timeout = runTimeout(cleanUpNextTick);
        draining = true;
        var len = queue.length;
        while (len) {
          currentQueue = queue;
          queue = [];
          while (++queueIndex < len) {
            if (currentQueue) {
              currentQueue[queueIndex].run();
            }
          }
          queueIndex = -1;
          len = queue.length;
        }
        currentQueue = null;
        draining = false;
        runClearTimeout(timeout);
      }
      process.nextTick = function (fun) {
        var args = new Array(arguments.length - 1);
        if (arguments.length > 1) {
          for (var i = 1; i < arguments.length; i++) {
            args[i - 1] = arguments[i];
          }
        }
        queue.push(new Item(fun, args));
        if (queue.length === 1 && !draining) {
          runTimeout(drainQueue);
        }
      };
      function Item(fun, array) {
        this.fun = fun;
        this.array = array;
      }
      Item.prototype.run = function () {
        this.fun.apply(null, this.array);
      };
      process.title = "browser";
      process.browser = true;
      process.env = {};
      process.argv = [];
      process.version = "";
      process.versions = {};
      function noop() {}
      process.on = noop;
      process.addListener = noop;
      process.once = noop;
      process.off = noop;
      process.removeListener = noop;
      process.removeAllListeners = noop;
      process.emit = noop;
      process.prependListener = noop;
      process.prependOnceListener = noop;
      process.listeners = function (name) {
        return [];
      };
      process.binding = function (name) {
        throw new Error("process.binding is not supported");
      };
      process.cwd = function () {
        return "/";
      };
      process.chdir = function (dir) {
        throw new Error("process.chdir is not supported");
      };
      process.umask = function () {
        return 0;
      };
    }, {}],
    6: [function (require, module, exports) {
      module.exports = {
        name: "ejs",
        description: "Embedded JavaScript templates",
        keywords: ["template", "engine", "ejs"],
        version: "3.1.8",
        author: "Matthew Eernisse <mde@fleegix.org> (http://fleegix.org)",
        license: "Apache-2.0",
        bin: {
          ejs: "./bin/cli.js"
        },
        main: "./lib/ejs.js",
        jsdelivr: "ejs.min.js",
        unpkg: "ejs.min.js",
        repository: {
          type: "git",
          url: "git://github.com/mde/ejs.git"
        },
        bugs: "https://github.com/mde/ejs/issues",
        homepage: "https://github.com/mde/ejs",
        dependencies: {
          jake: "^10.8.5"
        },
        devDependencies: {
          browserify: "^16.5.1",
          eslint: "^6.8.0",
          "git-directory-deploy": "^1.5.1",
          jsdoc: "^4.0.2",
          "lru-cache": "^4.0.1",
          mocha: "^10.2.0",
          "uglify-js": "^3.3.16"
        },
        engines: {
          node: ">=0.10.0"
        },
        scripts: {
          test: "mocha -u tdd"
        }
      };
    }, {}]
  }, {}, [1])(1);
});

!function (t, e) {
  "object" == typeof exports && "object" == typeof module ? module.exports = e() : "function" == typeof define && define.amd ? define("@material/linear-progress", [], e) : "object" == typeof exports ? exports["linear-progress"] = e() : (t.mdc = t.mdc || {}, t.mdc["linear-progress"] = e());
}(window, function () {
  return n = {}, o.m = r = {
    0: function (t, e, r) {
      "use strict";

      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.MDCFoundation = void 0;
      var n = (Object.defineProperty(o, "cssClasses", {
        get: function () {
          return {};
        },
        enumerable: !1,
        configurable: !0
      }), Object.defineProperty(o, "strings", {
        get: function () {
          return {};
        },
        enumerable: !1,
        configurable: !0
      }), Object.defineProperty(o, "numbers", {
        get: function () {
          return {};
        },
        enumerable: !1,
        configurable: !0
      }), Object.defineProperty(o, "defaultAdapter", {
        get: function () {
          return {};
        },
        enumerable: !1,
        configurable: !0
      }), o.prototype.init = function () {}, o.prototype.destroy = function () {}, o);
      function o(t) {
        void 0 === t && (t = {}), this.adapter = t;
      }
      e.MDCFoundation = n, e.default = n;
    },
    1: function (t, e, r) {
      "use strict";

      var o = this && this.__read || function (t, e) {
          var r = "function" == typeof Symbol && t[Symbol.iterator];
          if (!r) return t;
          var n,
            o,
            i = r.call(t),
            s = [];
          try {
            for (; (void 0 === e || 0 < e--) && !(n = i.next()).done;) s.push(n.value);
          } catch (t) {
            o = {
              error: t
            };
          } finally {
            try {
              n && !n.done && (r = i.return) && r.call(i);
            } finally {
              if (o) throw o.error;
            }
          }
          return s;
        },
        i = this && this.__spreadArray || function (t, e) {
          for (var r = 0, n = e.length, o = t.length; r < n; r++, o++) t[o] = e[r];
          return t;
        };
      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.MDCComponent = void 0;
      var n = r(0),
        s = (a.attachTo = function (t) {
          return new a(t, new n.MDCFoundation({}));
        }, a.prototype.initialize = function () {
          for (var t = [], e = 0; e < arguments.length; e++) t[e] = arguments[e];
        }, a.prototype.getDefaultFoundation = function () {
          throw new Error("Subclasses must override getDefaultFoundation to return a properly configured foundation class");
        }, a.prototype.initialSyncWithDOM = function () {}, a.prototype.destroy = function () {
          this.foundation.destroy();
        }, a.prototype.listen = function (t, e, r) {
          this.root.addEventListener(t, e, r);
        }, a.prototype.unlisten = function (t, e, r) {
          this.root.removeEventListener(t, e, r);
        }, a.prototype.emit = function (t, e, r) {
          var n;
          void 0 === r && (r = !1), "function" == typeof CustomEvent ? n = new CustomEvent(t, {
            bubbles: r,
            detail: e
          }) : (n = document.createEvent("CustomEvent")).initCustomEvent(t, r, !1, e), this.root.dispatchEvent(n);
        }, a);
      function a(t, e) {
        for (var r = [], n = 2; n < arguments.length; n++) r[n - 2] = arguments[n];
        this.root = t, this.initialize.apply(this, i([], o(r))), this.foundation = void 0 === e ? this.getDefaultFoundation() : e, this.foundation.init(), this.initialSyncWithDOM();
      }
      e.MDCComponent = s, e.default = s;
    },
    10: function (t, e, r) {
      "use strict";

      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.getCorrectEventName = e.getCorrectPropertyName = void 0;
      var s = {
          animation: {
            prefixed: "-webkit-animation",
            standard: "animation"
          },
          transform: {
            prefixed: "-webkit-transform",
            standard: "transform"
          },
          transition: {
            prefixed: "-webkit-transition",
            standard: "transition"
          }
        },
        a = {
          animationend: {
            cssProperty: "animation",
            prefixed: "webkitAnimationEnd",
            standard: "animationend"
          },
          animationiteration: {
            cssProperty: "animation",
            prefixed: "webkitAnimationIteration",
            standard: "animationiteration"
          },
          animationstart: {
            cssProperty: "animation",
            prefixed: "webkitAnimationStart",
            standard: "animationstart"
          },
          transitionend: {
            cssProperty: "transition",
            prefixed: "webkitTransitionEnd",
            standard: "transitionend"
          }
        };
      function u(t) {
        return Boolean(t.document) && "function" == typeof t.document.createElement;
      }
      e.getCorrectPropertyName = function (t, e) {
        if (u(t) && e in s) {
          var r = t.document.createElement("div"),
            n = s[e],
            o = n.standard,
            i = n.prefixed;
          return o in r.style ? o : i;
        }
        return e;
      }, e.getCorrectEventName = function (t, e) {
        if (u(t) && e in a) {
          var r = t.document.createElement("div"),
            n = a[e],
            o = n.standard,
            i = n.prefixed;
          return n.cssProperty in r.style ? o : i;
        }
        return e;
      };
    },
    197: function (t, e, r) {
      "use strict";

      var n = this && this.__createBinding || (Object.create ? function (t, e, r, n) {
          void 0 === n && (n = r), Object.defineProperty(t, n, {
            enumerable: !0,
            get: function () {
              return e[r];
            }
          });
        } : function (t, e, r, n) {
          void 0 === n && (n = r), t[n] = e[r];
        }),
        o = this && this.__exportStar || function (t, e) {
          for (var r in t) "default" === r || Object.prototype.hasOwnProperty.call(e, r) || n(e, t, r);
        };
      Object.defineProperty(e, "__esModule", {
        value: !0
      }), o(r(198), e), o(r(57), e), o(r(46), e), o(r(45), e), o(r(199), e);
    },
    198: function (t, e, r) {
      "use strict";

      Object.defineProperty(e, "__esModule", {
        value: !0
      });
    },
    199: function (t, e, r) {
      "use strict";

      Object.defineProperty(e, "__esModule", {
        value: !0
      });
    },
    45: function (t, e, r) {
      "use strict";

      var n,
        o = this && this.__extends || (n = function (t, e) {
          return (n = Object.setPrototypeOf || {
            __proto__: []
          } instanceof Array && function (t, e) {
            t.__proto__ = e;
          } || function (t, e) {
            for (var r in e) Object.prototype.hasOwnProperty.call(e, r) && (t[r] = e[r]);
          })(t, e);
        }, function (t, e) {
          if ("function" != typeof e && null !== e) throw new TypeError("Class extends value " + String(e) + " is not a constructor or null");
          function r() {
            this.constructor = t;
          }
          n(t, e), t.prototype = null === e ? Object.create(e) : (r.prototype = e.prototype, new r());
        }),
        i = this && this.__assign || function () {
          return (i = Object.assign || function (t) {
            for (var e, r = 1, n = arguments.length; r < n; r++) for (var o in e = arguments[r]) Object.prototype.hasOwnProperty.call(e, o) && (t[o] = e[o]);
            return t;
          }).apply(this, arguments);
        },
        a = this && this.__values || function (t) {
          var e = "function" == typeof Symbol && Symbol.iterator,
            r = e && t[e],
            n = 0;
          if (r) return r.call(t);
          if (t && "number" == typeof t.length) return {
            next: function () {
              return t && n >= t.length && (t = void 0), {
                value: t && t[n++],
                done: !t
              };
            }
          };
          throw new TypeError(e ? "Object is not iterable." : "Symbol.iterator is not defined.");
        };
      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.MDCLinearProgressFoundation = void 0;
      var s,
        u = r(10),
        c = r(0),
        f = r(46),
        l = (s = c.MDCFoundation, o(d, s), Object.defineProperty(d, "cssClasses", {
          get: function () {
            return f.cssClasses;
          },
          enumerable: !1,
          configurable: !0
        }), Object.defineProperty(d, "strings", {
          get: function () {
            return f.strings;
          },
          enumerable: !1,
          configurable: !0
        }), Object.defineProperty(d, "defaultAdapter", {
          get: function () {
            return {
              addClass: function () {},
              attachResizeObserver: function () {
                return null;
              },
              forceLayout: function () {},
              getWidth: function () {
                return 0;
              },
              hasClass: function () {
                return !1;
              },
              setBufferBarStyle: function () {
                return null;
              },
              setPrimaryBarStyle: function () {
                return null;
              },
              setStyle: function () {},
              removeAttribute: function () {},
              removeClass: function () {},
              setAttribute: function () {}
            };
          },
          enumerable: !1,
          configurable: !0
        }), d.prototype.init = function () {
          var s = this;
          this.determinate = !this.adapter.hasClass(f.cssClasses.INDETERMINATE_CLASS), this.adapter.addClass(f.cssClasses.ANIMATION_READY_CLASS), this.progress = 0, this.buffer = 1, this.observer = this.adapter.attachResizeObserver(function (t) {
            var e, r;
            if (!s.determinate) try {
              for (var n = a(t), o = n.next(); !o.done; o = n.next()) {
                var i = o.value;
                i.contentRect && s.calculateAndSetDimensions(i.contentRect.width);
              }
            } catch (t) {
              e = {
                error: t
              };
            } finally {
              try {
                o && !o.done && (r = n.return) && r.call(n);
              } finally {
                if (e) throw e.error;
              }
            }
          }), !this.determinate && this.observer && this.calculateAndSetDimensions(this.adapter.getWidth());
        }, d.prototype.setDeterminate = function (t) {
          if (this.determinate = t, this.determinate) return this.adapter.removeClass(f.cssClasses.INDETERMINATE_CLASS), this.adapter.setAttribute(f.strings.ARIA_VALUENOW, this.progress.toString()), this.adapter.setAttribute(f.strings.ARIA_VALUEMAX, "1"), this.adapter.setAttribute(f.strings.ARIA_VALUEMIN, "0"), this.setPrimaryBarProgress(this.progress), void this.setBufferBarProgress(this.buffer);
          this.observer && this.calculateAndSetDimensions(this.adapter.getWidth()), this.adapter.addClass(f.cssClasses.INDETERMINATE_CLASS), this.adapter.removeAttribute(f.strings.ARIA_VALUENOW), this.adapter.removeAttribute(f.strings.ARIA_VALUEMAX), this.adapter.removeAttribute(f.strings.ARIA_VALUEMIN), this.setPrimaryBarProgress(1), this.setBufferBarProgress(1);
        }, d.prototype.isDeterminate = function () {
          return this.determinate;
        }, d.prototype.setProgress = function (t) {
          this.progress = t, this.determinate && (this.setPrimaryBarProgress(t), this.adapter.setAttribute(f.strings.ARIA_VALUENOW, t.toString()));
        }, d.prototype.getProgress = function () {
          return this.progress;
        }, d.prototype.setBuffer = function (t) {
          this.buffer = t, this.determinate && this.setBufferBarProgress(t);
        }, d.prototype.getBuffer = function () {
          return this.buffer;
        }, d.prototype.open = function () {
          this.adapter.removeClass(f.cssClasses.CLOSED_CLASS), this.adapter.removeClass(f.cssClasses.CLOSED_ANIMATION_OFF_CLASS), this.adapter.removeAttribute(f.strings.ARIA_HIDDEN);
        }, d.prototype.close = function () {
          this.adapter.addClass(f.cssClasses.CLOSED_CLASS), this.adapter.setAttribute(f.strings.ARIA_HIDDEN, "true");
        }, d.prototype.isClosed = function () {
          return this.adapter.hasClass(f.cssClasses.CLOSED_CLASS);
        }, d.prototype.handleTransitionEnd = function () {
          this.adapter.hasClass(f.cssClasses.CLOSED_CLASS) && this.adapter.addClass(f.cssClasses.CLOSED_ANIMATION_OFF_CLASS);
        }, d.prototype.destroy = function () {
          s.prototype.destroy.call(this), this.observer && this.observer.disconnect();
        }, d.prototype.restartAnimation = function () {
          this.adapter.removeClass(f.cssClasses.ANIMATION_READY_CLASS), this.adapter.forceLayout(), this.adapter.addClass(f.cssClasses.ANIMATION_READY_CLASS);
        }, d.prototype.setPrimaryBarProgress = function (t) {
          var e = "scaleX(" + t + ")",
            r = "undefined" != typeof window ? u.getCorrectPropertyName(window, "transform") : "transform";
          this.adapter.setPrimaryBarStyle(r, e);
        }, d.prototype.setBufferBarProgress = function (t) {
          var e = 100 * t + "%";
          this.adapter.setBufferBarStyle(f.strings.FLEX_BASIS, e);
        }, d.prototype.calculateAndSetDimensions = function (t) {
          var e = t * f.animationDimensionPercentages.PRIMARY_HALF,
            r = t * f.animationDimensionPercentages.PRIMARY_FULL,
            n = t * f.animationDimensionPercentages.SECONDARY_QUARTER,
            o = t * f.animationDimensionPercentages.SECONDARY_HALF,
            i = t * f.animationDimensionPercentages.SECONDARY_FULL;
          this.adapter.setStyle("--mdc-linear-progress-primary-half", e + "px"), this.adapter.setStyle("--mdc-linear-progress-primary-half-neg", -e + "px"), this.adapter.setStyle("--mdc-linear-progress-primary-full", r + "px"), this.adapter.setStyle("--mdc-linear-progress-primary-full-neg", -r + "px"), this.adapter.setStyle("--mdc-linear-progress-secondary-quarter", n + "px"), this.adapter.setStyle("--mdc-linear-progress-secondary-quarter-neg", -n + "px"), this.adapter.setStyle("--mdc-linear-progress-secondary-half", o + "px"), this.adapter.setStyle("--mdc-linear-progress-secondary-half-neg", -o + "px"), this.adapter.setStyle("--mdc-linear-progress-secondary-full", i + "px"), this.adapter.setStyle("--mdc-linear-progress-secondary-full-neg", -i + "px"), this.restartAnimation();
        }, d);
      function d(t) {
        var e = s.call(this, i(i({}, d.defaultAdapter), t)) || this;
        return e.observer = null, e;
      }
      e.MDCLinearProgressFoundation = l, e.default = l;
    },
    46: function (t, e, r) {
      "use strict";

      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.animationDimensionPercentages = e.strings = e.cssClasses = void 0, e.cssClasses = {
        CLOSED_CLASS: "mdc-linear-progress--closed",
        CLOSED_ANIMATION_OFF_CLASS: "mdc-linear-progress--closed-animation-off",
        INDETERMINATE_CLASS: "mdc-linear-progress--indeterminate",
        REVERSED_CLASS: "mdc-linear-progress--reversed",
        ANIMATION_READY_CLASS: "mdc-linear-progress--animation-ready"
      }, e.strings = {
        ARIA_HIDDEN: "aria-hidden",
        ARIA_VALUEMAX: "aria-valuemax",
        ARIA_VALUEMIN: "aria-valuemin",
        ARIA_VALUENOW: "aria-valuenow",
        BUFFER_BAR_SELECTOR: ".mdc-linear-progress__buffer-bar",
        FLEX_BASIS: "flex-basis",
        PRIMARY_BAR_SELECTOR: ".mdc-linear-progress__primary-bar"
      }, e.animationDimensionPercentages = {
        PRIMARY_HALF: .8367142,
        PRIMARY_FULL: 2.00611057,
        SECONDARY_QUARTER: .37651913,
        SECONDARY_HALF: .84386165,
        SECONDARY_FULL: 1.60277782
      };
    },
    57: function (t, e, r) {
      "use strict";

      var n,
        o = this && this.__extends || (n = function (t, e) {
          return (n = Object.setPrototypeOf || {
            __proto__: []
          } instanceof Array && function (t, e) {
            t.__proto__ = e;
          } || function (t, e) {
            for (var r in e) Object.prototype.hasOwnProperty.call(e, r) && (t[r] = e[r]);
          })(t, e);
        }, function (t, e) {
          if ("function" != typeof e && null !== e) throw new TypeError("Class extends value " + String(e) + " is not a constructor or null");
          function r() {
            this.constructor = t;
          }
          n(t, e), t.prototype = null === e ? Object.create(e) : (r.prototype = e.prototype, new r());
        });
      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.MDCLinearProgress = void 0;
      var i,
        s = r(1),
        a = r(45),
        u = (i = s.MDCComponent, o(c, i), c.attachTo = function (t) {
          return new c(t);
        }, Object.defineProperty(c.prototype, "determinate", {
          set: function (t) {
            this.foundation.setDeterminate(t);
          },
          enumerable: !1,
          configurable: !0
        }), Object.defineProperty(c.prototype, "progress", {
          set: function (t) {
            this.foundation.setProgress(t);
          },
          enumerable: !1,
          configurable: !0
        }), Object.defineProperty(c.prototype, "buffer", {
          set: function (t) {
            this.foundation.setBuffer(t);
          },
          enumerable: !1,
          configurable: !0
        }), c.prototype.open = function () {
          this.foundation.open();
        }, c.prototype.close = function () {
          this.foundation.close();
        }, c.prototype.initialSyncWithDOM = function () {
          var t = this;
          this.root.addEventListener("transitionend", function () {
            t.foundation.handleTransitionEnd();
          });
        }, c.prototype.getDefaultFoundation = function () {
          var n = this,
            t = {
              addClass: function (t) {
                n.root.classList.add(t);
              },
              forceLayout: function () {
                n.root.getBoundingClientRect();
              },
              setBufferBarStyle: function (t, e) {
                var r = n.root.querySelector(a.MDCLinearProgressFoundation.strings.BUFFER_BAR_SELECTOR);
                r && r.style.setProperty(t, e);
              },
              setPrimaryBarStyle: function (t, e) {
                var r = n.root.querySelector(a.MDCLinearProgressFoundation.strings.PRIMARY_BAR_SELECTOR);
                r && r.style.setProperty(t, e);
              },
              hasClass: function (t) {
                return n.root.classList.contains(t);
              },
              removeAttribute: function (t) {
                n.root.removeAttribute(t);
              },
              removeClass: function (t) {
                n.root.classList.remove(t);
              },
              setAttribute: function (t, e) {
                n.root.setAttribute(t, e);
              },
              setStyle: function (t, e) {
                n.root.style.setProperty(t, e);
              },
              attachResizeObserver: function (t) {
                var e = window.ResizeObserver;
                if (e) {
                  var r = new e(t);
                  return r.observe(n.root), r;
                }
                return null;
              },
              getWidth: function () {
                return n.root.offsetWidth;
              }
            };
          return new a.MDCLinearProgressFoundation(t);
        }, c);
      function c() {
        return null !== i && i.apply(this, arguments) || this;
      }
      e.MDCLinearProgress = u;
    }
  }, o.c = n, o.d = function (t, e, r) {
    o.o(t, e) || Object.defineProperty(t, e, {
      enumerable: !0,
      get: r
    });
  }, o.r = function (t) {
    "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(t, Symbol.toStringTag, {
      value: "Module"
    }), Object.defineProperty(t, "__esModule", {
      value: !0
    });
  }, o.t = function (e, t) {
    if (1 & t && (e = o(e)), 8 & t) return e;
    if (4 & t && "object" == typeof e && e && e.__esModule) return e;
    var r = Object.create(null);
    if (o.r(r), Object.defineProperty(r, "default", {
      enumerable: !0,
      value: e
    }), 2 & t && "string" != typeof e) for (var n in e) o.d(r, n, function (t) {
      return e[t];
    }.bind(null, n));
    return r;
  }, o.n = function (t) {
    var e = t && t.__esModule ? function () {
      return t.default;
    } : function () {
      return t;
    };
    return o.d(e, "a", e), e;
  }, o.o = function (t, e) {
    return Object.prototype.hasOwnProperty.call(t, e);
  }, o.p = "", o(o.s = 197);
  function o(t) {
    if (n[t]) return n[t].exports;
    var e = n[t] = {
      i: t,
      l: !1,
      exports: {}
    };
    return r[t].call(e.exports, e, e.exports, o), e.l = !0, e.exports;
  }
  var r, n;
});

!function (t, e) {
  "object" == typeof exports && "object" == typeof module ? module.exports = e() : "function" == typeof define && define.amd ? define("@material/circular-progress", [], e) : "object" == typeof exports ? exports["circular-progress"] = e() : (t.mdc = t.mdc || {}, t.mdc["circular-progress"] = e());
}(window, function () {
  return n = {}, o.m = r = {
    0: function (t, e, r) {
      "use strict";

      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.MDCFoundation = void 0;
      var n = (Object.defineProperty(o, "cssClasses", {
        get: function () {
          return {};
        },
        enumerable: !1,
        configurable: !0
      }), Object.defineProperty(o, "strings", {
        get: function () {
          return {};
        },
        enumerable: !1,
        configurable: !0
      }), Object.defineProperty(o, "numbers", {
        get: function () {
          return {};
        },
        enumerable: !1,
        configurable: !0
      }), Object.defineProperty(o, "defaultAdapter", {
        get: function () {
          return {};
        },
        enumerable: !1,
        configurable: !0
      }), o.prototype.init = function () {}, o.prototype.destroy = function () {}, o);
      function o(t) {
        void 0 === t && (t = {}), this.adapter = t;
      }
      e.MDCFoundation = n, e.default = n;
    },
    1: function (t, e, r) {
      "use strict";

      var o = this && this.__read || function (t, e) {
          var r = "function" == typeof Symbol && t[Symbol.iterator];
          if (!r) return t;
          var n,
            o,
            i = r.call(t),
            s = [];
          try {
            for (; (void 0 === e || 0 < e--) && !(n = i.next()).done;) s.push(n.value);
          } catch (t) {
            o = {
              error: t
            };
          } finally {
            try {
              n && !n.done && (r = i.return) && r.call(i);
            } finally {
              if (o) throw o.error;
            }
          }
          return s;
        },
        i = this && this.__spreadArray || function (t, e) {
          for (var r = 0, n = e.length, o = t.length; r < n; r++, o++) t[o] = e[r];
          return t;
        };
      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.MDCComponent = void 0;
      var n = r(0),
        s = (u.attachTo = function (t) {
          return new u(t, new n.MDCFoundation({}));
        }, u.prototype.initialize = function () {
          for (var t = [], e = 0; e < arguments.length; e++) t[e] = arguments[e];
        }, u.prototype.getDefaultFoundation = function () {
          throw new Error("Subclasses must override getDefaultFoundation to return a properly configured foundation class");
        }, u.prototype.initialSyncWithDOM = function () {}, u.prototype.destroy = function () {
          this.foundation.destroy();
        }, u.prototype.listen = function (t, e, r) {
          this.root.addEventListener(t, e, r);
        }, u.prototype.unlisten = function (t, e, r) {
          this.root.removeEventListener(t, e, r);
        }, u.prototype.emit = function (t, e, r) {
          var n;
          void 0 === r && (r = !1), "function" == typeof CustomEvent ? n = new CustomEvent(t, {
            bubbles: r,
            detail: e
          }) : (n = document.createEvent("CustomEvent")).initCustomEvent(t, r, !1, e), this.root.dispatchEvent(n);
        }, u);
      function u(t, e) {
        for (var r = [], n = 2; n < arguments.length; n++) r[n - 2] = arguments[n];
        this.root = t, this.initialize.apply(this, i([], o(r))), this.foundation = void 0 === e ? this.getDefaultFoundation() : e, this.foundation.init(), this.initialSyncWithDOM();
      }
      e.MDCComponent = s, e.default = s;
    },
    168: function (t, e, r) {
      "use strict";

      var n = this && this.__createBinding || (Object.create ? function (t, e, r, n) {
          void 0 === n && (n = r), Object.defineProperty(t, n, {
            enumerable: !0,
            get: function () {
              return e[r];
            }
          });
        } : function (t, e, r, n) {
          void 0 === n && (n = r), t[n] = e[r];
        }),
        o = this && this.__exportStar || function (t, e) {
          for (var r in t) "default" === r || Object.prototype.hasOwnProperty.call(e, r) || n(e, t, r);
        };
      Object.defineProperty(e, "__esModule", {
        value: !0
      }), o(r(169), e), o(r(170), e), o(r(90), e), o(r(89), e);
    },
    169: function (t, e, r) {
      "use strict";

      Object.defineProperty(e, "__esModule", {
        value: !0
      });
    },
    170: function (t, e, r) {
      "use strict";

      var n,
        o = this && this.__extends || (n = function (t, e) {
          return (n = Object.setPrototypeOf || {
            __proto__: []
          } instanceof Array && function (t, e) {
            t.__proto__ = e;
          } || function (t, e) {
            for (var r in e) Object.prototype.hasOwnProperty.call(e, r) && (t[r] = e[r]);
          })(t, e);
        }, function (t, e) {
          if ("function" != typeof e && null !== e) throw new TypeError("Class extends value " + String(e) + " is not a constructor or null");
          function r() {
            this.constructor = t;
          }
          n(t, e), t.prototype = null === e ? Object.create(e) : (r.prototype = e.prototype, new r());
        });
      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.MDCCircularProgress = void 0;
      var i,
        s = r(1),
        u = r(89),
        a = (i = s.MDCComponent, o(c, i), c.prototype.initialize = function () {
          this.determinateCircle = this.root.querySelector(u.MDCCircularProgressFoundation.strings.DETERMINATE_CIRCLE_SELECTOR);
        }, c.attachTo = function (t) {
          return new c(t);
        }, Object.defineProperty(c.prototype, "determinate", {
          set: function (t) {
            this.foundation.setDeterminate(t);
          },
          enumerable: !1,
          configurable: !0
        }), Object.defineProperty(c.prototype, "progress", {
          set: function (t) {
            this.foundation.setProgress(t);
          },
          enumerable: !1,
          configurable: !0
        }), Object.defineProperty(c.prototype, "isClosed", {
          get: function () {
            return this.foundation.isClosed();
          },
          enumerable: !1,
          configurable: !0
        }), c.prototype.open = function () {
          this.foundation.open();
        }, c.prototype.close = function () {
          this.foundation.close();
        }, c.prototype.getDefaultFoundation = function () {
          var r = this,
            t = {
              addClass: function (t) {
                r.root.classList.add(t);
              },
              getDeterminateCircleAttribute: function (t) {
                return r.determinateCircle.getAttribute(t);
              },
              hasClass: function (t) {
                return r.root.classList.contains(t);
              },
              removeClass: function (t) {
                r.root.classList.remove(t);
              },
              removeAttribute: function (t) {
                r.root.removeAttribute(t);
              },
              setAttribute: function (t, e) {
                r.root.setAttribute(t, e);
              },
              setDeterminateCircleAttribute: function (t, e) {
                r.determinateCircle.setAttribute(t, e);
              }
            };
          return new u.MDCCircularProgressFoundation(t);
        }, c);
      function c() {
        return null !== i && i.apply(this, arguments) || this;
      }
      e.MDCCircularProgress = a;
    },
    89: function (t, e, r) {
      "use strict";

      var n,
        o = this && this.__extends || (n = function (t, e) {
          return (n = Object.setPrototypeOf || {
            __proto__: []
          } instanceof Array && function (t, e) {
            t.__proto__ = e;
          } || function (t, e) {
            for (var r in e) Object.prototype.hasOwnProperty.call(e, r) && (t[r] = e[r]);
          })(t, e);
        }, function (t, e) {
          if ("function" != typeof e && null !== e) throw new TypeError("Class extends value " + String(e) + " is not a constructor or null");
          function r() {
            this.constructor = t;
          }
          n(t, e), t.prototype = null === e ? Object.create(e) : (r.prototype = e.prototype, new r());
        }),
        i = this && this.__assign || function () {
          return (i = Object.assign || function (t) {
            for (var e, r = 1, n = arguments.length; r < n; r++) for (var o in e = arguments[r]) Object.prototype.hasOwnProperty.call(e, o) && (t[o] = e[o]);
            return t;
          }).apply(this, arguments);
        };
      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.MDCCircularProgressFoundation = void 0;
      var s,
        u = r(0),
        a = r(90),
        c = (s = u.MDCFoundation, o(l, s), Object.defineProperty(l, "cssClasses", {
          get: function () {
            return a.cssClasses;
          },
          enumerable: !1,
          configurable: !0
        }), Object.defineProperty(l, "strings", {
          get: function () {
            return a.strings;
          },
          enumerable: !1,
          configurable: !0
        }), Object.defineProperty(l, "defaultAdapter", {
          get: function () {
            return {
              addClass: function () {},
              getDeterminateCircleAttribute: function () {
                return null;
              },
              hasClass: function () {
                return !1;
              },
              removeClass: function () {},
              removeAttribute: function () {},
              setAttribute: function () {},
              setDeterminateCircleAttribute: function () {}
            };
          },
          enumerable: !1,
          configurable: !0
        }), l.prototype.init = function () {
          this.closed = this.adapter.hasClass(a.cssClasses.CLOSED_CLASS), this.determinate = !this.adapter.hasClass(a.cssClasses.INDETERMINATE_CLASS), this.progress = 0, this.determinate && this.adapter.setAttribute(a.strings.ARIA_VALUENOW, this.progress.toString()), this.radius = Number(this.adapter.getDeterminateCircleAttribute(a.strings.RADIUS));
        }, l.prototype.setDeterminate = function (t) {
          this.determinate = t, this.determinate ? (this.adapter.removeClass(a.cssClasses.INDETERMINATE_CLASS), this.setProgress(this.progress)) : (this.adapter.addClass(a.cssClasses.INDETERMINATE_CLASS), this.adapter.removeAttribute(a.strings.ARIA_VALUENOW));
        }, l.prototype.isDeterminate = function () {
          return this.determinate;
        }, l.prototype.setProgress = function (t) {
          if (this.progress = t, this.determinate) {
            var e = (1 - this.progress) * (2 * Math.PI * this.radius);
            this.adapter.setDeterminateCircleAttribute(a.strings.STROKE_DASHOFFSET, "" + e), this.adapter.setAttribute(a.strings.ARIA_VALUENOW, this.progress.toString());
          }
        }, l.prototype.getProgress = function () {
          return this.progress;
        }, l.prototype.open = function () {
          this.closed = !1, this.adapter.removeClass(a.cssClasses.CLOSED_CLASS), this.adapter.removeAttribute(a.strings.ARIA_HIDDEN);
        }, l.prototype.close = function () {
          this.closed = !0, this.adapter.addClass(a.cssClasses.CLOSED_CLASS), this.adapter.setAttribute(a.strings.ARIA_HIDDEN, "true");
        }, l.prototype.isClosed = function () {
          return this.closed;
        }, l);
      function l(t) {
        return s.call(this, i(i({}, l.defaultAdapter), t)) || this;
      }
      e.MDCCircularProgressFoundation = c, e.default = c;
    },
    90: function (t, e, r) {
      "use strict";

      Object.defineProperty(e, "__esModule", {
        value: !0
      }), e.strings = e.cssClasses = void 0, e.cssClasses = {
        INDETERMINATE_CLASS: "mdc-circular-progress--indeterminate",
        CLOSED_CLASS: "mdc-circular-progress--closed"
      }, e.strings = {
        ARIA_HIDDEN: "aria-hidden",
        ARIA_VALUENOW: "aria-valuenow",
        DETERMINATE_CIRCLE_SELECTOR: ".mdc-circular-progress__determinate-circle",
        RADIUS: "r",
        STROKE_DASHOFFSET: "stroke-dashoffset"
      };
    }
  }, o.c = n, o.d = function (t, e, r) {
    o.o(t, e) || Object.defineProperty(t, e, {
      enumerable: !0,
      get: r
    });
  }, o.r = function (t) {
    "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(t, Symbol.toStringTag, {
      value: "Module"
    }), Object.defineProperty(t, "__esModule", {
      value: !0
    });
  }, o.t = function (e, t) {
    if (1 & t && (e = o(e)), 8 & t) return e;
    if (4 & t && "object" == typeof e && e && e.__esModule) return e;
    var r = Object.create(null);
    if (o.r(r), Object.defineProperty(r, "default", {
      enumerable: !0,
      value: e
    }), 2 & t && "string" != typeof e) for (var n in e) o.d(r, n, function (t) {
      return e[t];
    }.bind(null, n));
    return r;
  }, o.n = function (t) {
    var e = t && t.__esModule ? function () {
      return t.default;
    } : function () {
      return t;
    };
    return o.d(e, "a", e), e;
  }, o.o = function (t, e) {
    return Object.prototype.hasOwnProperty.call(t, e);
  }, o.p = "", o(o.s = 168);
  function o(t) {
    if (n[t]) return n[t].exports;
    var e = n[t] = {
      i: t,
      l: !1,
      exports: {}
    };
    return r[t].call(e.exports, e, e.exports, o), e.l = !0, e.exports;
  }
  var r, n;
});

var coreMenu = {
  _user: null,
  _system: null,
  _modules: null,
  _events: {},
  /**
   * Получение страницы кабинета
   * @returns {*}
   */
  getPageContent: function getPageContent() {
    return coreTpl['menu/main.html'];
  },
  /**
   * Инициализация
   */
  init: function init() {
    // Нужно для первого открытия страницы
    if (window.screen.width > 600 && localStorage.getItem('core3_drawer_toggle') === '1') {
      $('.page-menu').addClass('drawer-toggle');
      $('.page-menu .menu-drawer').css('transition', 'none 0s ease 0s');
      $('.page-menu .mdc-top-app-bar').css('transition', 'none 0s ease 0s');
    }
    var conf = localStorage.getItem('core3_conf');
    if (typeof conf === 'string') {
      try {
        conf = JSON.parse(conf);
        if (_typeof(conf.theme) === 'object') {
          this._setTheme(conf.theme);
        }
      } catch (e) {}
    }
    coreMenu.preloader.show();

    // Инициализация кнопок
    var buttons = document.querySelectorAll('.page-menu .mdc-button');
    var _iterator = _createForOfIteratorHelper(buttons),
      _step;
    try {
      for (_iterator.s(); !(_step = _iterator.n()).done;) {
        var button = _step.value;
        new mdc.ripple.MDCRipple(button);
      }
    } catch (err) {
      _iterator.e(err);
    } finally {
      _iterator.f();
    }
    coreMenu._initInstall();
    $('.page-menu .main-content .main-wrapper').html('');

    // Добавление токена при любом ajax запросе
    $(document).ajaxSend(function (event, jqxhr, settings) {
      if (settings.url.indexOf(settings.url) === 0) {
        var accessToken = coreTokens.getAccessToken();
        if (accessToken) {
          jqxhr.setRequestHeader('Access-Token', accessToken);
        }
      }
    });
    $.ajax({
      url: coreMain.options.basePath + '/cabinet',
      method: "GET",
      dataType: "json",
      success: function success(response) {
        if (_typeof(response.user) !== 'object' || typeof response.user.id !== 'number' || typeof response.user.login !== 'string' || typeof response.user.name !== 'string' || typeof response.user.avatar !== 'string' || _typeof(response.system) !== 'object' || typeof response.system.name !== 'string' || _typeof(response.modules) !== 'object') {
          console.warn(response);
          CoreUI.alert.danger('Ошибка', 'Попробуйте обновить страницу или обратитесь к администратору');
        } else {
          coreMenu._user = response.user;
          coreMenu._system = response.system;
          coreMenu._modules = response.modules;
          coreMenu._renderMenu();
          coreMenu._initComponents(response.system.conf);
          coreMenu.preloader.hide();
          var uri = location.hash.substring(1) !== '' && location.hash.substring(1) !== '/' ? '/mod' + location.hash.substring(1) : '/home';
          coreMenu.load(uri);
        }
      },
      error: function error(response) {
        if (response.status === 403) {
          coreTokens.clearAccessToken();
          coreMain.viewPage('auth');
        } else if (response.status === 0) {
          CoreUI.alert.danger('Ошибка', 'Проверьте подключение к интернету');
        } else {
          CoreUI.alert.danger('Ошибка', 'Обновите приложение или обратитесь к администратору');
        }
      }
    });
  },
  /**
   *
   */
  toggleFullscreen: function toggleFullscreen() {
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen();
    } else {
      if (document.exitFullscreen) {
        document.exitFullscreen();
      }
    }
  },
  /**
   * Перезагрузка содержимого страницы
   */
  reload: function reload() {
    coreMenu.load('/mod' + location.hash.substring(1));
  },
  /**
   * Загрузка содержимого модуля
   * @param url
   */
  load: function load(url) {
    url = url || '/home';
    coreMenu.preloader.show();
    $.ajax({
      url: coreMain.options.basePath + url,
      method: "GET",
      dataType: 'text',
      success: function success(response, textStatus, jqXHR) {
        coreMenu.preloader.hide();
        var params = coreTools.getParams(url);
        coreMenu._setActiveModule(params.module, params.section);
        var contentType = jqXHR.getResponseHeader('Content-type');
        var contents = [];

        // Обработка json
        if (/^application\/json/.test(contentType)) {
          try {
            var responseObj = JSON.parse(response);
            if (_typeof(responseObj) === 'object' && responseObj.hasOwnProperty('_buffer') && responseObj._buffer !== '') {
              contents.push(responseObj._buffer);
            }
            var renderContents = coreMenu._renderContent(responseObj);
            $.each(renderContents, function (i, contentItem) {
              contents.push(contentItem);
            });
          } catch (e) {
            contents = [response];
            console.warn(e);
          }
        } else {
          contents = [response];
        }
        var mainContainer = $('.page-menu .main-content .main-wrapper');
        mainContainer.empty();
        $.each(contents, function (key, content) {
          mainContainer.append(content);
        });
        mainContainer.css({
          'opacity': '0',
          'margin-top': '15px'
        }).animate({
          marginTop: 0,
          opacity: 1
        }, {
          duration: 235,
          specialEasing: {
            width: "linear",
            height: "easeOutBounce"
          },
          complete: function complete() {
            $(this).css({
              'opacity': '',
              'margin-top': ''
            });
          }
        });
        coreMenu._trigger('shown.load.core3', this, [url]);
      },
      error: function error(response) {
        coreMenu.preloader.hide();
        if (response.status === 403) {
          coreTokens.clearAccessToken();
          coreMain.viewPage('auth');
        } else if (response.status === 0) {
          CoreUI.alert.danger('Ошибка', 'Проверьте подключение к интернету');
        } else {
          CoreUI.alert.danger('Ошибка', 'Обновите приложение или обратитесь к администратору');
        }
      }
    });
  },
  /**
   * @param action
   * @param options
   * @returns {boolean}
   */
  loader: {
    /**
     * @param options
     */
    show: function show(options) {
      if ($('#loader')[0]) {
        return false;
      }
      $('.page-menu > header').append(coreTpl['menu/loader.html']);
      var loaderElement = $('#loader .loader-progress');
      var linearProgress = new mdc['linear-progress'].MDCLinearProgress(loaderElement[0]);
      linearProgress.determinate = false;
    },
    /**
     *
     */
    hide: function hide() {
      $('#loader').remove();
    }
  },
  /**
   * @param action
   * @param options
   * @returns {boolean}
   */
  preloader: {
    /**
     * @param options
     * @returns {boolean}
     */
    show: function show(options) {
      if ($('#preloader')[0]) {
        this.hide();
      }
      options = _typeof(options) === 'object' ? options : {};
      $('.page-menu').prepend(ejs.render(coreTpl['menu/preloader.html'], {
        text: options.text || 'Загрузка...'
      }));
    },
    /**
     *
     */
    hide: function hide() {
      $('#preloader').fadeOut('fast', function () {
        $(this).remove();
      });
    }
  },
  /**
   * @param eventName
   * @param callback
   * @param context
   * @param singleExec
   */
  on: function on(eventName, callback, context, singleExec) {
    if (_typeof(this._events[eventName]) !== 'object') {
      this._events[eventName] = [];
    }
    this._events[eventName].push({
      context: context || this,
      callback: callback,
      singleExec: singleExec
    });
  },
  /**
   * Сборка содержимого
   * @param data
   * @return {*[]}
   * @private
   */
  _renderContent: function _renderContent(data) {
    var that = this;
    var result = [];
    if (typeof data === 'string' || typeof data === 'bigint' || typeof data === 'number' || _typeof(data) === 'symbol') {
      result.push(data);
    } else if (data instanceof Object) {
      if (!Array.isArray(data)) {
        data = [data];
      }
      for (var i = 0; i < data.length; i++) {
        if (typeof data[i] === 'string') {
          result.push(data[i]);
        } else {
          if (!Array.isArray(data[i]) && data[i].hasOwnProperty('component') && data[i].component.substring(0, 6) === 'coreui') {
            var name = data[i].component.split('.')[1];
            if (CoreUI.hasOwnProperty(name) && that.isObject(CoreUI[name])) {
              var instance = CoreUI[name].create(data[i]);
              result.push(instance.render());
              this.on('shown.load.core3', instance.initEvents, instance, true);
            }
          } else {
            result.push(JSON.stringify(data[i]));
          }
        }
      }
    } else {
      result.push(JSON.stringify(data));
    }
    return result;
  },
  /**
   * Проверка на объект
   * @param value
   */
  isObject: function isObject(value) {
    return _typeof(value) === 'object' && !Array.isArray(value) && value !== null;
  },
  /**
   *
   * @param name
   * @param context
   * @param params
   */
  _trigger: function _trigger(name, context, params) {
    if (this._events.hasOwnProperty(name) && this._events[name].length > 0) {
      for (var i = 0; i < this._events[name].length; i++) {
        var callback = this._events[name][i].callback;
        context = this._events[name][i].context || context;
        callback.apply(context, params);
        if (this._events[name][i].singleExec) {
          this._events[name].splice(i, 1);
          i--;
        }
      }
    }
  },
  /**
   *
   */
  _renderMenu: function _renderMenu() {
    $('.page-menu .system-title').text(coreMenu._system.name);
    if (_typeof(coreMenu._system.conf) === 'object') {
      localStorage.setItem('core3_conf', JSON.stringify(coreMenu._system.conf));
      if (_typeof(coreMenu._system.conf.theme) === 'object') {
        this._setTheme(coreMenu._system.conf.theme);
      }
    }
    if (Object.values(coreMenu._modules).length > 0) {
      var params = coreTools.getParams();
      $('.page-menu > aside .menu-list.level-1').empty();
      $.each(coreMenu._modules, function (key, module) {
        if (typeof module.name !== 'string' || !module.name || typeof module.title !== 'string' || !module.title) {
          CoreUI.notice.danger('Не удалось показать некоторые модули из за ошибок!');
          return true;
        }
        module.index = 'index';
        if (!module.isset_index_page && module.sections.length > 0) {
          $.each(module.sections, function (key, section) {
            module.index = section.name;
            return false;
          });
        }
        $('.page-menu > aside .menu-list.level-1').append(ejs.render(coreTpl['menu/module.html'], {
          module: module
        }));
        $('.page-menu > aside .core-module.core-module-' + module.name).hover(function () {
          var level2 = $('.level-2', this);
          if (level2[0]) {
            level2.css('top', $(this).offset().top);
          }
        });
      });
      coreMenu._setActiveModule(params.module, params.section);
      var menuItems = document.querySelectorAll('.page-menu .menu-list-item a');
      var _iterator2 = _createForOfIteratorHelper(menuItems),
        _step2;
      try {
        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
          var menuItem = _step2.value;
          new mdc.ripple.MDCRipple(menuItem);
          $(menuItem).on('click', function (event) {
            if (event.button === 0 && !event.ctrlKey) {
              var module = $(this).data('module');
              var section = $(this).data('section');
              if (location.hash.substring(1) === '/' + module + '/' + section) {
                if (window.screen.width < 600) {
                  coreMenu._drawerToggle();
                }
                coreMenu.load('/mod/' + module + '/' + section);
              }
            }
          });
        }
      } catch (err) {
        _iterator2.e(err);
      } finally {
        _iterator2.f();
      }
      var _buttons = document.querySelectorAll('.page-menu .menu-list-item .menu-icon-button');
      var _iterator3 = _createForOfIteratorHelper(_buttons),
        _step3;
      try {
        for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
          var button = _step3.value;
          new mdc.ripple.MDCRipple(button);
          $(button).on('click', function () {
            $(this).parent().parent().toggleClass('menu-item-nested-open');
          });
        }
      } catch (err) {
        _iterator3.e(err);
      } finally {
        _iterator3.f();
      }
    }
    $('.page-menu .mdc-top-app-bar__section--align-end').empty();
    $('.page-menu .mdc-top-app-bar__section--align-end').append(ejs.render(coreTpl['menu/navbar.html'], {
      user: coreMenu._user
    }));

    // Выход
    $('.page-menu .menu-logout').on('click', function (e) {
      e.preventDefault();
      CoreUI.alert.warning('Уверены, что хотите выйти?', '', {
        btnAcceptText: "Да",
        btnAcceptColor: "#F57C00",
        btnAcceptEvent: coreAuth.logout,
        btnRejectText: "Отмена"
      });
    });
    $('.page-menu .open-menu, .page-menu .menu-drawer-scrim').on('click', function () {
      coreMenu._drawerToggle();
    });
    $('.page-menu .module-home').on('click', function (event) {
      if (event.button === 0 && !event.ctrlKey) {
        coreMenu.load('/home');
        if (window.screen.width < 600) {
          coreMenu._drawerToggle();
          console.log(11);
        }
      }
    });
    var buttons = document.querySelectorAll('.page-menu .mdc-ripple-surface');
    var _iterator4 = _createForOfIteratorHelper(buttons),
      _step4;
    try {
      for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
        var _button = _step4.value;
        new mdc.ripple.MDCRipple(_button);
      }
    } catch (err) {
      _iterator4.e(err);
    } finally {
      _iterator4.f();
    }
    coreMenu._initSwipe($(".page-menu .menu-drawer-swipe")[0], function (direction) {
      if (direction === "right") {
        coreMenu._drawerToggle();
      } else if (direction === "left") {
        coreMenu._drawerToggle();
      }
    });
  },
  /**
   * Инициализация компонентов
   * @param {object} conf
   * @private
   */
  _initComponents: function _initComponents(conf) {
    CoreUI.table.setSettings({
      lang: conf.lang
    });
    CoreUI.form.setSettings({
      lang: conf.lang
    });
  },
  /**
   * @param moduleName
   * @param sectionName
   */
  _setActiveModule: function _setActiveModule(moduleName, sectionName) {
    $('.page-menu > aside .core-module').removeClass('menu-module-index--activated').removeClass('menu-module--activated');
    $('.page-menu > aside .core-module-section').removeClass('menu-module-section--activated');
    $('.page-menu > aside .core-module-section-index').removeClass('menu-module-section--activated');
    $('.page-menu > aside .core-module-' + moduleName).addClass('menu-module--activated').addClass('menu-item-nested-open');
    if (sectionName === 'index') {
      $('.page-menu > aside .core-module.core-module-' + moduleName).addClass('menu-module-index--activated');
      $('.page-menu > aside .core-module-' + moduleName + ' .core-module-section-index').addClass('menu-module-section--activated');
    }
    $('.page-menu > aside .core-module-' + moduleName + '-' + sectionName).addClass('menu-module-section--activated');
    if (!moduleName && !sectionName) {
      $('.page-menu .module-home').addClass('active');
    } else {
      $('.page-menu .module-home').removeClass('active');
    }

    /**
     * @param moduleName
     * @param sectionName
     * @returns {*[]}
     */
    function getModuleTitles(moduleName, sectionName) {
      var title = [];
      $.each(coreMenu._modules, function (key, module) {
        if (module.name === moduleName) {
          title.push(module.title);
          if (module.sections && module.sections.length > 0) {
            $.each(module.sections, function (key, section) {
              if (section.name === sectionName) {
                title.push(section.title);
                return false;
              }
            });
          }
          return false;
        }
      });
      return title;
    }
    var titles = getModuleTitles(moduleName, sectionName);
    $('header .mdc-top-app-bar__title').text(titles[0] || '');
    $('header .mdc-top-app-bar__subtitle').text(titles[1] || '');
    var title = coreMenu._system.name;
    title += titles.hasOwnProperty(0) ? ' - ' + titles[0] + (titles.hasOwnProperty(1) ? ' - ' + titles[1] : '') : '';
    $('head title').text(title);
  },
  /**
   * @param target
   * @param callback
   */
  _initSwipe: function _initSwipe(target, callback) {
    document.addEventListener('touchstart', handleTouchStart, false);
    document.addEventListener('touchmove', handleTouchMove, false);
    var xDown = null;
    var yDown = null;

    /**
     * @param evt
     */
    function handleTouchStart(evt) {
      xDown = evt.touches[0].clientX;
      yDown = evt.touches[0].clientY;
    }

    /**
     * @param evt
     */
    function handleTouchMove(evt) {
      if (!xDown || !yDown) {
        return;
      }
      var xUp = evt.touches[0].clientX;
      var yUp = evt.touches[0].clientY;
      var xDiff = xDown - xUp;
      var yDiff = yDown - yUp;
      if (Math.abs(xDiff) > Math.abs(yDiff)) {
        /*most significant*/
        if (xDiff > 0) {
          if (target === evt.target) {
            callback('left');
          }
        } else {
          if (target === evt.target) {
            callback('right');
          }
        }
      } else {
        if (yDiff > 0) {
          if (target === evt.target) {
            callback('up');
          }
        } else {
          if (target === evt.target) {
            callback('down');
          }
        }
      }
      xDown = null;
      yDown = null;
    }
  },
  /**
   * @private
   */
  _drawerToggle: function _drawerToggle() {
    // Нужно для первого открытия страницы
    $('.page-menu .menu-drawer').css('transition', '');
    $('.page-menu .mdc-top-app-bar').css('transition', '');
    var menu = $('.page.page-menu');
    if (menu.hasClass('drawer-toggle')) {
      localStorage.setItem('core3_drawer_toggle', 0);
    } else {
      localStorage.setItem('core3_drawer_toggle', 1);
    }
    menu.toggleClass('drawer-toggle');
  },
  /**
   * Установка
   */
  _initInstall: function _initInstall() {
    var install = function install(event) {
      event.preventDefault();
      var button = $('.page-menu .install-button');
      if (event.platforms.includes('web')) {
        button.show();
        button.on('click', function () {
          event.prompt();
        });
      }
      event.userChoice.then(function (choiceResult) {
        switch (choiceResult.outcome) {
          case "accepted":
            button.hide();
            break;
          case "dismissed":
            button.css('opacity', '0.7');
            break;
        }
      });
    };
    if (coreMain.install.event) {
      install(coreMain.install.event);
    } else {
      coreMain.install.promise.then(install);
    }
  },
  /**
   * Установка темы
   * @param {object} theme
   * @private
   */
  _setTheme: function _setTheme(theme) {
    var styles = [];
    if (_typeof(theme.main) === 'object' && typeof theme.main.bg_color === 'string' && theme.main.bg_color) {
      styles.push('--menu-drawer: ' + theme.main.bg_color + ';');
    }
    if (_typeof(theme.main) === 'object' && typeof theme.main.text_color === 'string' && theme.main.text_color) {
      styles.push('--menu-drawer-text:' + theme.main.text_color + ';');
    }
    if (styles.length > 0) {
      var content = ':root{' + styles.join('') + '}';
      var coreTheme = $('head #theme-main');
      if (!coreTheme[0] || content !== coreTheme.html()) {
        if (coreTheme[0]) {
          coreTheme.remove();
        }
        $('head').append('<style id="theme-main">' + content + '</style>');
      }
    }
  }
};
$(function () {
  coreMain.on('hashchange', function () {
    if ($('.page.page-menu')[0]) {
      if (window.screen.width < 600 && $('.page.page-menu.drawer-toggle')[0]) {
        coreMenu._drawerToggle();
      }
      coreMenu.load('/mod' + location.hash.substring(1));
    }
  });
});

/**
 * @property {object} _table
 */
var coreUiTableInstance = /*#__PURE__*/function () {
  /**
   * @param {object} table
   */
  function coreUiTableInstance(table) {
    _classCallCheck(this, coreUiTableInstance);
    if (_typeof(table) !== 'object' || Array.isArray(table) || table === null) {
      throw new Error('Ошибка инициализации таблицы');
    }
    this._table = table;
  }

  /**
   * Запрос на удаление выбранных записей
   * @param {string}   url
   * @param {function} callbackSuccess
   */
  _createClass(coreUiTableInstance, [{
    key: "deleteSelected",
    value: function deleteSelected(url, callbackSuccess) {
      var recordsId = this._table.getSelectedRecordsId();
      if (recordsId.length === 0) {
        CoreUI.notice.warning(Core$1._('Нужно выбрать хотя бы одну запись'));
        return;
      }
      CoreUI.alert.warning(Core$1._("Удалить выбранные записи?"), Core$1._('Количество: ') + ' ' + recordsId.length, {
        btnRejectText: Core$1._("Отмена"),
        btnAcceptText: Core$1._("Да"),
        btnAcceptColor: "#F57C00",
        btnAcceptEvent: function btnAcceptEvent() {
          Core$1.menu.preloader.show();
          $.ajax({
            url: url,
            method: 'delete',
            dataType: 'json',
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify({
              id: recordsId
            }),
            success: function success(response) {
              if (response.status !== 'success') {
                CoreUI.alert.danger(response.error_message || Core$1._("Ошибка. Попробуйте обновить страницу и выполнить удаление еще раз."));
              } else {
                CoreUI.notice.info(Core$1._('Выбранные записи удалены'));
                if (callbackSuccess && typeof callbackSuccess == 'function') {
                  callbackSuccess();
                }
              }
            },
            error: function error(response) {
              CoreUI.alert.danger(Core$1._("Ошибка. Попробуйте обновить страницу и выполнить удаление еще раз."));
            },
            complete: function complete() {
              Core$1.menu.preloader.hide();
            }
          });
        }
      });
    }

    /**
     * Переключение состояния у записи
     * @param {string} url
     * @param {string} checked
     * @param {string} id
     * @param {string} questionY
     * @param {string} questionN
     */
  }, {
    key: "switch",
    value: function _switch(url, checked, id, questionY, questionN) {
      var question;
      var isChecked = $(checked).is(':checked');
      if (isChecked) {
        question = questionY || "Активировать запись?";
      } else {
        question = questionN || "Деактивировать запись?";
      }
      CoreUI.alert.create({
        type: 'warning',
        title: question,
        btnRejectText: Core$1._("Отмена"),
        btnAcceptText: Core$1._("Да"),
        btnAcceptColor: "#F57C00",
        btnAcceptEvent: function btnAcceptEvent() {
          Core$1.menu.loader.show();
          $.ajax({
            url: url.replace('[id]', id),
            method: 'patch',
            dataType: 'json',
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify({
              checked: isChecked ? 'Y' : 'N'
            }),
            success: function success(response) {
              if (response.status !== 'success') {
                $(checked).prop('checked', !isChecked);
                CoreUI.notice.danger(response.error_message || Core$1._("Ошибка. Попробуйте обновить страницу и выполните это действие еще раз."));
              }
            },
            error: function error(response) {
              $(checked).prop('checked', !isChecked);
              CoreUI.notice.danger(Core$1._("Ошибка. Попробуйте обновить страницу и выполните это действие еще раз."));
            },
            complete: function complete() {
              Core$1.menu.loader.hide();
            }
          });
        },
        btnRejectEvent: function btnRejectEvent() {
          $(checked).prop('checked', !isChecked);
        },
        btnCancelEvent: function btnCancelEvent() {
          $(checked).prop('checked', !isChecked);
        }
      });
    }
  }]);
  return coreUiTableInstance;
}();

var coreUiTable = {
  /**
   * Получение таблицы ядра
   * @param tableId
   */
  get: function get(tableId) {
    var table = CoreUI.table.get(tableId);
    if (!table) {
      throw new Error('Не удалось найти таблицу с id' + table);
    }
    return new coreUiTableInstance(table);
  }
};

var coreUiFormInstance = /*#__PURE__*/_createClass(
/**
 * @param {object} form
 */
function coreUiFormInstance(form) {
  _classCallCheck(this, coreUiFormInstance);
  if (_typeof(form) !== 'object' || Array.isArray(form) || form === null) {
    throw new Error('Ошибка инициализации формы');
  }
  this._form = form;
});

var coreUiTable$1 = {
  /**
   * Получение таблицы ядра
   * @param formId
   */
  get: function get(formId) {
    var form = CoreUI.form.get(formId);
    if (!form) {
      throw new Error('Не удалось найти форму с id' + formId);
    }
    return new coreUiFormInstance(form);
  }
};

var Core$1 = {
  _settings: {
    lang: 'en'
  },
  main: coreMain,
  auth: coreAuth,
  menu: coreMenu,
  tools: coreTools,
  ui: {
    table: coreUiTable,
    form: coreUiTable$1
  },
  lang: {},
  /**
   * Перевод
   * @param  {string} text
   * @return {string}
   */
  _: function _(text) {
    var lang = {};
    if (this._settings.lang && this.lang.hasOwnProperty(this._settings.lang) && _typeof(this.lang[this._settings.lang]) === 'object' && this.lang[this._settings.lang] !== null) {
      lang = this.lang[this._settings.lang];
    }
    return lang.hasOwnProperty(text) ? lang[text] : text;
  },
  /**
   * Установка настроек
   * @param {object} settings
   */
  setSettings: function setSettings(settings) {
    this._settings = $.extend({}, this._settings, settings);
  },
  /**
   * Получение значения настройки
   * @param {string} name
   */
  getSetting: function getSetting(name) {
    var value = null;
    if (this._settings.hasOwnProperty(name)) {
      value = this._settings[name];
    }
    return value;
  }
};

Core$1.lang.en = {
  'Вход': 'Login'
};

return Core$1;

})));
