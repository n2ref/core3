(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
  typeof define === 'function' && define.amd ? define(factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.Core = factory());
})(this, (function () { 'use strict';

  function _regeneratorRuntime() {
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

  /******************************************************************************
  Copyright (c) Microsoft Corporation.

  Permission to use, copy, modify, and/or distribute this software for any
  purpose with or without fee is hereby granted.

  THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
  REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
  AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
  INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
  LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
  OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
  PERFORMANCE OF THIS SOFTWARE.
  ***************************************************************************** */
  /* global Reflect, Promise, SuppressedError, Symbol */

  var extendStatics = function (d, b) {
    extendStatics = Object.setPrototypeOf || {
      __proto__: []
    } instanceof Array && function (d, b) {
      d.__proto__ = b;
    } || function (d, b) {
      for (var p in b) if (Object.prototype.hasOwnProperty.call(b, p)) d[p] = b[p];
    };
    return extendStatics(d, b);
  };
  function __extends(d, b) {
    if (typeof b !== "function" && b !== null) throw new TypeError("Class extends value " + String(b) + " is not a constructor or null");
    extendStatics(d, b);
    function __() {
      this.constructor = d;
    }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
  }
  var __assign = function () {
    __assign = Object.assign || function __assign(t) {
      for (var s, i = 1, n = arguments.length; i < n; i++) {
        s = arguments[i];
        for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
      }
      return t;
    };
    return __assign.apply(this, arguments);
  };
  function __awaiter(thisArg, _arguments, P, generator) {
    function adopt(value) {
      return value instanceof P ? value : new P(function (resolve) {
        resolve(value);
      });
    }
    return new (P || (P = Promise))(function (resolve, reject) {
      function fulfilled(value) {
        try {
          step(generator.next(value));
        } catch (e) {
          reject(e);
        }
      }
      function rejected(value) {
        try {
          step(generator["throw"](value));
        } catch (e) {
          reject(e);
        }
      }
      function step(result) {
        result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected);
      }
      step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
  }
  function __generator(thisArg, body) {
    var _ = {
        label: 0,
        sent: function () {
          if (t[0] & 1) throw t[1];
          return t[1];
        },
        trys: [],
        ops: []
      },
      f,
      y,
      t,
      g;
    return g = {
      next: verb(0),
      "throw": verb(1),
      "return": verb(2)
    }, typeof Symbol === "function" && (g[Symbol.iterator] = function () {
      return this;
    }), g;
    function verb(n) {
      return function (v) {
        return step([n, v]);
      };
    }
    function step(op) {
      if (f) throw new TypeError("Generator is already executing.");
      while (g && (g = 0, op[0] && (_ = 0)), _) try {
        if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
        if (y = 0, t) op = [op[0] & 2, t.value];
        switch (op[0]) {
          case 0:
          case 1:
            t = op;
            break;
          case 4:
            _.label++;
            return {
              value: op[1],
              done: false
            };
          case 5:
            _.label++;
            y = op[1];
            op = [0];
            continue;
          case 7:
            op = _.ops.pop();
            _.trys.pop();
            continue;
          default:
            if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) {
              _ = 0;
              continue;
            }
            if (op[0] === 3 && (!t || op[1] > t[0] && op[1] < t[3])) {
              _.label = op[1];
              break;
            }
            if (op[0] === 6 && _.label < t[1]) {
              _.label = t[1];
              t = op;
              break;
            }
            if (t && _.label < t[2]) {
              _.label = t[2];
              _.ops.push(op);
              break;
            }
            if (t[2]) _.ops.pop();
            _.trys.pop();
            continue;
        }
        op = body.call(thisArg, _);
      } catch (e) {
        op = [6, e];
        y = 0;
      } finally {
        f = t = 0;
      }
      if (op[0] & 5) throw op[1];
      return {
        value: op[0] ? op[1] : void 0,
        done: true
      };
    }
  }
  function __values(o) {
    var s = typeof Symbol === "function" && Symbol.iterator,
      m = s && o[s],
      i = 0;
    if (m) return m.call(o);
    if (o && typeof o.length === "number") return {
      next: function () {
        if (o && i >= o.length) o = void 0;
        return {
          value: o && o[i++],
          done: !o
        };
      }
    };
    throw new TypeError(s ? "Object is not iterable." : "Symbol.iterator is not defined.");
  }
  function __read(o, n) {
    var m = typeof Symbol === "function" && o[Symbol.iterator];
    if (!m) return o;
    var i = m.call(o),
      r,
      ar = [],
      e;
    try {
      while ((n === void 0 || n-- > 0) && !(r = i.next()).done) ar.push(r.value);
    } catch (error) {
      e = {
        error: error
      };
    } finally {
      try {
        if (r && !r.done && (m = i["return"])) m.call(i);
      } finally {
        if (e) throw e.error;
      }
    }
    return ar;
  }

  /** @deprecated */
  function __spreadArrays() {
    for (var s = 0, i = 0, il = arguments.length; i < il; i++) s += arguments[i].length;
    for (var r = Array(s), k = 0, i = 0; i < il; i++) for (var a = arguments[i], j = 0, jl = a.length; j < jl; j++, k++) r[k] = a[j];
    return r;
  }
  function __spreadArray(to, from, pack) {
    if (pack || arguments.length === 2) for (var i = 0, l = from.length, ar; i < l; i++) {
      if (ar || !(i in from)) {
        if (!ar) ar = Array.prototype.slice.call(from, 0, i);
        ar[i] = from[i];
      }
    }
    return to.concat(ar || Array.prototype.slice.call(from));
  }
  typeof SuppressedError === "function" ? SuppressedError : function (error, suppressed, message) {
    var e = new Error(message);
    return e.name = "SuppressedError", e.error = error, e.suppressed = suppressed, e;
  };

  /**
   * FingerprintJS v3.3.3 - Copyright (c) FingerprintJS, Inc, 2022 (https://fingerprintjs.com)
   * Licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) license.
   *
   * This software contains code from open-source projects:
   * MurmurHash3 by Karan Lyons (https://github.com/karanlyons/murmurHash3.js)
   */
  var version = "3.3.3";
  function wait(durationMs, resolveWith) {
    return new Promise(function (resolve) {
      return setTimeout(resolve, durationMs, resolveWith);
    });
  }
  function requestIdleCallbackIfAvailable(fallbackTimeout, deadlineTimeout) {
    if (deadlineTimeout === void 0) {
      deadlineTimeout = Infinity;
    }
    var requestIdleCallback = window.requestIdleCallback;
    if (requestIdleCallback) {
      // The function `requestIdleCallback` loses the binding to `window` here.
      // `globalThis` isn't always equal `window` (see https://github.com/fingerprintjs/fingerprintjs/issues/683).
      // Therefore, an error can occur. `call(window,` prevents the error.
      return new Promise(function (resolve) {
        return requestIdleCallback.call(window, function () {
          return resolve();
        }, {
          timeout: deadlineTimeout
        });
      });
    } else {
      return wait(Math.min(fallbackTimeout, deadlineTimeout));
    }
  }
  function isPromise(value) {
    return value && typeof value.then === 'function';
  }
  /**
   * Calls a maybe asynchronous function without creating microtasks when the function is synchronous.
   * Catches errors in both cases.
   *
   * If just you run a code like this:
   * ```
   * console.time('Action duration')
   * await action()
   * console.timeEnd('Action duration')
   * ```
   * The synchronous function time can be measured incorrectly because another microtask may run before the `await`
   * returns the control back to the code.
   */
  function awaitIfAsync(action, callback) {
    try {
      var returnedValue = action();
      if (isPromise(returnedValue)) {
        returnedValue.then(function (result) {
          return callback(true, result);
        }, function (error) {
          return callback(false, error);
        });
      } else {
        callback(true, returnedValue);
      }
    } catch (error) {
      callback(false, error);
    }
  }
  /**
   * If you run many synchronous tasks without using this function, the JS main loop will be busy and asynchronous tasks
   * (e.g. completing a network request, rendering the page) won't be able to happen.
   * This function allows running many synchronous tasks such way that asynchronous tasks can run too in background.
   */
  function forEachWithBreaks(items, callback, loopReleaseInterval) {
    if (loopReleaseInterval === void 0) {
      loopReleaseInterval = 16;
    }
    return __awaiter(this, void 0, void 0, function () {
      var lastLoopReleaseTime, i, now;
      return __generator(this, function (_a) {
        switch (_a.label) {
          case 0:
            lastLoopReleaseTime = Date.now();
            i = 0;
            _a.label = 1;
          case 1:
            if (!(i < items.length)) return [3 /*break*/, 4];
            callback(items[i], i);
            now = Date.now();
            if (!(now >= lastLoopReleaseTime + loopReleaseInterval)) return [3 /*break*/, 3];
            lastLoopReleaseTime = now;
            // Allows asynchronous actions and microtasks to happen
            return [4 /*yield*/, wait(0)];
          case 2:
            // Allows asynchronous actions and microtasks to happen
            _a.sent();
            _a.label = 3;
          case 3:
            ++i;
            return [3 /*break*/, 1];
          case 4:
            return [2 /*return*/];
        }
      });
    });
  }

  /*
   * Taken from https://github.com/karanlyons/murmurHash3.js/blob/a33d0723127e2e5415056c455f8aed2451ace208/murmurHash3.js
   */
  //
  // Given two 64bit ints (as an array of two 32bit ints) returns the two
  // added together as a 64bit int (as an array of two 32bit ints).
  //
  function x64Add(m, n) {
    m = [m[0] >>> 16, m[0] & 0xffff, m[1] >>> 16, m[1] & 0xffff];
    n = [n[0] >>> 16, n[0] & 0xffff, n[1] >>> 16, n[1] & 0xffff];
    var o = [0, 0, 0, 0];
    o[3] += m[3] + n[3];
    o[2] += o[3] >>> 16;
    o[3] &= 0xffff;
    o[2] += m[2] + n[2];
    o[1] += o[2] >>> 16;
    o[2] &= 0xffff;
    o[1] += m[1] + n[1];
    o[0] += o[1] >>> 16;
    o[1] &= 0xffff;
    o[0] += m[0] + n[0];
    o[0] &= 0xffff;
    return [o[0] << 16 | o[1], o[2] << 16 | o[3]];
  }
  //
  // Given two 64bit ints (as an array of two 32bit ints) returns the two
  // multiplied together as a 64bit int (as an array of two 32bit ints).
  //
  function x64Multiply(m, n) {
    m = [m[0] >>> 16, m[0] & 0xffff, m[1] >>> 16, m[1] & 0xffff];
    n = [n[0] >>> 16, n[0] & 0xffff, n[1] >>> 16, n[1] & 0xffff];
    var o = [0, 0, 0, 0];
    o[3] += m[3] * n[3];
    o[2] += o[3] >>> 16;
    o[3] &= 0xffff;
    o[2] += m[2] * n[3];
    o[1] += o[2] >>> 16;
    o[2] &= 0xffff;
    o[2] += m[3] * n[2];
    o[1] += o[2] >>> 16;
    o[2] &= 0xffff;
    o[1] += m[1] * n[3];
    o[0] += o[1] >>> 16;
    o[1] &= 0xffff;
    o[1] += m[2] * n[2];
    o[0] += o[1] >>> 16;
    o[1] &= 0xffff;
    o[1] += m[3] * n[1];
    o[0] += o[1] >>> 16;
    o[1] &= 0xffff;
    o[0] += m[0] * n[3] + m[1] * n[2] + m[2] * n[1] + m[3] * n[0];
    o[0] &= 0xffff;
    return [o[0] << 16 | o[1], o[2] << 16 | o[3]];
  }
  //
  // Given a 64bit int (as an array of two 32bit ints) and an int
  // representing a number of bit positions, returns the 64bit int (as an
  // array of two 32bit ints) rotated left by that number of positions.
  //
  function x64Rotl(m, n) {
    n %= 64;
    if (n === 32) {
      return [m[1], m[0]];
    } else if (n < 32) {
      return [m[0] << n | m[1] >>> 32 - n, m[1] << n | m[0] >>> 32 - n];
    } else {
      n -= 32;
      return [m[1] << n | m[0] >>> 32 - n, m[0] << n | m[1] >>> 32 - n];
    }
  }
  //
  // Given a 64bit int (as an array of two 32bit ints) and an int
  // representing a number of bit positions, returns the 64bit int (as an
  // array of two 32bit ints) shifted left by that number of positions.
  //
  function x64LeftShift(m, n) {
    n %= 64;
    if (n === 0) {
      return m;
    } else if (n < 32) {
      return [m[0] << n | m[1] >>> 32 - n, m[1] << n];
    } else {
      return [m[1] << n - 32, 0];
    }
  }
  //
  // Given two 64bit ints (as an array of two 32bit ints) returns the two
  // xored together as a 64bit int (as an array of two 32bit ints).
  //
  function x64Xor(m, n) {
    return [m[0] ^ n[0], m[1] ^ n[1]];
  }
  //
  // Given a block, returns murmurHash3's final x64 mix of that block.
  // (`[0, h[0] >>> 1]` is a 33 bit unsigned right shift. This is the
  // only place where we need to right shift 64bit ints.)
  //
  function x64Fmix(h) {
    h = x64Xor(h, [0, h[0] >>> 1]);
    h = x64Multiply(h, [0xff51afd7, 0xed558ccd]);
    h = x64Xor(h, [0, h[0] >>> 1]);
    h = x64Multiply(h, [0xc4ceb9fe, 0x1a85ec53]);
    h = x64Xor(h, [0, h[0] >>> 1]);
    return h;
  }
  //
  // Given a string and an optional seed as an int, returns a 128 bit
  // hash using the x64 flavor of MurmurHash3, as an unsigned hex.
  //
  function x64hash128(key, seed) {
    key = key || '';
    seed = seed || 0;
    var remainder = key.length % 16;
    var bytes = key.length - remainder;
    var h1 = [0, seed];
    var h2 = [0, seed];
    var k1 = [0, 0];
    var k2 = [0, 0];
    var c1 = [0x87c37b91, 0x114253d5];
    var c2 = [0x4cf5ad43, 0x2745937f];
    var i;
    for (i = 0; i < bytes; i = i + 16) {
      k1 = [key.charCodeAt(i + 4) & 0xff | (key.charCodeAt(i + 5) & 0xff) << 8 | (key.charCodeAt(i + 6) & 0xff) << 16 | (key.charCodeAt(i + 7) & 0xff) << 24, key.charCodeAt(i) & 0xff | (key.charCodeAt(i + 1) & 0xff) << 8 | (key.charCodeAt(i + 2) & 0xff) << 16 | (key.charCodeAt(i + 3) & 0xff) << 24];
      k2 = [key.charCodeAt(i + 12) & 0xff | (key.charCodeAt(i + 13) & 0xff) << 8 | (key.charCodeAt(i + 14) & 0xff) << 16 | (key.charCodeAt(i + 15) & 0xff) << 24, key.charCodeAt(i + 8) & 0xff | (key.charCodeAt(i + 9) & 0xff) << 8 | (key.charCodeAt(i + 10) & 0xff) << 16 | (key.charCodeAt(i + 11) & 0xff) << 24];
      k1 = x64Multiply(k1, c1);
      k1 = x64Rotl(k1, 31);
      k1 = x64Multiply(k1, c2);
      h1 = x64Xor(h1, k1);
      h1 = x64Rotl(h1, 27);
      h1 = x64Add(h1, h2);
      h1 = x64Add(x64Multiply(h1, [0, 5]), [0, 0x52dce729]);
      k2 = x64Multiply(k2, c2);
      k2 = x64Rotl(k2, 33);
      k2 = x64Multiply(k2, c1);
      h2 = x64Xor(h2, k2);
      h2 = x64Rotl(h2, 31);
      h2 = x64Add(h2, h1);
      h2 = x64Add(x64Multiply(h2, [0, 5]), [0, 0x38495ab5]);
    }
    k1 = [0, 0];
    k2 = [0, 0];
    switch (remainder) {
      case 15:
        k2 = x64Xor(k2, x64LeftShift([0, key.charCodeAt(i + 14)], 48));
      // fallthrough
      case 14:
        k2 = x64Xor(k2, x64LeftShift([0, key.charCodeAt(i + 13)], 40));
      // fallthrough
      case 13:
        k2 = x64Xor(k2, x64LeftShift([0, key.charCodeAt(i + 12)], 32));
      // fallthrough
      case 12:
        k2 = x64Xor(k2, x64LeftShift([0, key.charCodeAt(i + 11)], 24));
      // fallthrough
      case 11:
        k2 = x64Xor(k2, x64LeftShift([0, key.charCodeAt(i + 10)], 16));
      // fallthrough
      case 10:
        k2 = x64Xor(k2, x64LeftShift([0, key.charCodeAt(i + 9)], 8));
      // fallthrough
      case 9:
        k2 = x64Xor(k2, [0, key.charCodeAt(i + 8)]);
        k2 = x64Multiply(k2, c2);
        k2 = x64Rotl(k2, 33);
        k2 = x64Multiply(k2, c1);
        h2 = x64Xor(h2, k2);
      // fallthrough
      case 8:
        k1 = x64Xor(k1, x64LeftShift([0, key.charCodeAt(i + 7)], 56));
      // fallthrough
      case 7:
        k1 = x64Xor(k1, x64LeftShift([0, key.charCodeAt(i + 6)], 48));
      // fallthrough
      case 6:
        k1 = x64Xor(k1, x64LeftShift([0, key.charCodeAt(i + 5)], 40));
      // fallthrough
      case 5:
        k1 = x64Xor(k1, x64LeftShift([0, key.charCodeAt(i + 4)], 32));
      // fallthrough
      case 4:
        k1 = x64Xor(k1, x64LeftShift([0, key.charCodeAt(i + 3)], 24));
      // fallthrough
      case 3:
        k1 = x64Xor(k1, x64LeftShift([0, key.charCodeAt(i + 2)], 16));
      // fallthrough
      case 2:
        k1 = x64Xor(k1, x64LeftShift([0, key.charCodeAt(i + 1)], 8));
      // fallthrough
      case 1:
        k1 = x64Xor(k1, [0, key.charCodeAt(i)]);
        k1 = x64Multiply(k1, c1);
        k1 = x64Rotl(k1, 31);
        k1 = x64Multiply(k1, c2);
        h1 = x64Xor(h1, k1);
      // fallthrough
    }

    h1 = x64Xor(h1, [0, key.length]);
    h2 = x64Xor(h2, [0, key.length]);
    h1 = x64Add(h1, h2);
    h2 = x64Add(h2, h1);
    h1 = x64Fmix(h1);
    h2 = x64Fmix(h2);
    h1 = x64Add(h1, h2);
    h2 = x64Add(h2, h1);
    return ('00000000' + (h1[0] >>> 0).toString(16)).slice(-8) + ('00000000' + (h1[1] >>> 0).toString(16)).slice(-8) + ('00000000' + (h2[0] >>> 0).toString(16)).slice(-8) + ('00000000' + (h2[1] >>> 0).toString(16)).slice(-8);
  }

  /**
   * Converts an error object to a plain object that can be used with `JSON.stringify`.
   * If you just run `JSON.stringify(error)`, you'll get `'{}'`.
   */
  function errorToObject(error) {
    var _a;
    return __assign({
      name: error.name,
      message: error.message,
      stack: (_a = error.stack) === null || _a === void 0 ? void 0 : _a.split('\n')
    }, error);
  }

  /*
   * This file contains functions to work with pure data only (no browser features, DOM, side effects, etc).
   */
  /**
   * Does the same as Array.prototype.includes but has better typing
   */
  function includes(haystack, needle) {
    for (var i = 0, l = haystack.length; i < l; ++i) {
      if (haystack[i] === needle) {
        return true;
      }
    }
    return false;
  }
  /**
   * Like `!includes()` but with proper typing
   */
  function excludes(haystack, needle) {
    return !includes(haystack, needle);
  }
  /**
   * Be careful, NaN can return
   */
  function toInt(value) {
    return parseInt(value);
  }
  /**
   * Be careful, NaN can return
   */
  function toFloat(value) {
    return parseFloat(value);
  }
  function replaceNaN(value, replacement) {
    return typeof value === 'number' && isNaN(value) ? replacement : value;
  }
  function countTruthy(values) {
    return values.reduce(function (sum, value) {
      return sum + (value ? 1 : 0);
    }, 0);
  }
  function round(value, base) {
    if (base === void 0) {
      base = 1;
    }
    if (Math.abs(base) >= 1) {
      return Math.round(value / base) * base;
    } else {
      // Sometimes when a number is multiplied by a small number, precision is lost,
      // for example 1234 * 0.0001 === 0.12340000000000001, and it's more precise divide: 1234 / (1 / 0.0001) === 0.1234.
      var counterBase = 1 / base;
      return Math.round(value * counterBase) / counterBase;
    }
  }
  /**
   * Parses a CSS selector into tag name with HTML attributes.
   * Only single element selector are supported (without operators like space, +, >, etc).
   *
   * Multiple values can be returned for each attribute. You decide how to handle them.
   */
  function parseSimpleCssSelector(selector) {
    var _a, _b;
    var errorMessage = "Unexpected syntax '" + selector + "'";
    var tagMatch = /^\s*([a-z-]*)(.*)$/i.exec(selector);
    var tag = tagMatch[1] || undefined;
    var attributes = {};
    var partsRegex = /([.:#][\w-]+|\[.+?\])/gi;
    var addAttribute = function (name, value) {
      attributes[name] = attributes[name] || [];
      attributes[name].push(value);
    };
    for (;;) {
      var match = partsRegex.exec(tagMatch[2]);
      if (!match) {
        break;
      }
      var part = match[0];
      switch (part[0]) {
        case '.':
          addAttribute('class', part.slice(1));
          break;
        case '#':
          addAttribute('id', part.slice(1));
          break;
        case '[':
          {
            var attributeMatch = /^\[([\w-]+)([~|^$*]?=("(.*?)"|([\w-]+)))?(\s+[is])?\]$/.exec(part);
            if (attributeMatch) {
              addAttribute(attributeMatch[1], (_b = (_a = attributeMatch[4]) !== null && _a !== void 0 ? _a : attributeMatch[5]) !== null && _b !== void 0 ? _b : '');
            } else {
              throw new Error(errorMessage);
            }
            break;
          }
        default:
          throw new Error(errorMessage);
      }
    }
    return [tag, attributes];
  }
  function ensureErrorWithMessage(error) {
    return error && typeof error === 'object' && 'message' in error ? error : {
      message: error
    };
  }
  /**
   * Loads the given entropy source. Returns a function that gets an entropy component from the source.
   *
   * The result is returned synchronously to prevent `loadSources` from
   * waiting for one source to load before getting the components from the other sources.
   */
  function loadSource(source, sourceOptions) {
    var isFinalResultLoaded = function (loadResult) {
      return typeof loadResult !== 'function';
    };
    var sourceLoadPromise = new Promise(function (resolveLoad) {
      var loadStartTime = Date.now();
      // `awaitIfAsync` is used instead of just `await` in order to measure the duration of synchronous sources
      // correctly (other microtasks won't affect the duration).
      awaitIfAsync(source.bind(null, sourceOptions), function () {
        var loadArgs = [];
        for (var _i = 0; _i < arguments.length; _i++) {
          loadArgs[_i] = arguments[_i];
        }
        var loadDuration = Date.now() - loadStartTime;
        // Source loading failed
        if (!loadArgs[0]) {
          return resolveLoad(function () {
            return {
              error: ensureErrorWithMessage(loadArgs[1]),
              duration: loadDuration
            };
          });
        }
        var loadResult = loadArgs[1];
        // Source loaded with the final result
        if (isFinalResultLoaded(loadResult)) {
          return resolveLoad(function () {
            return {
              value: loadResult,
              duration: loadDuration
            };
          });
        }
        // Source loaded with "get" stage
        resolveLoad(function () {
          return new Promise(function (resolveGet) {
            var getStartTime = Date.now();
            awaitIfAsync(loadResult, function () {
              var getArgs = [];
              for (var _i = 0; _i < arguments.length; _i++) {
                getArgs[_i] = arguments[_i];
              }
              var duration = loadDuration + Date.now() - getStartTime;
              // Source getting failed
              if (!getArgs[0]) {
                return resolveGet({
                  error: ensureErrorWithMessage(getArgs[1]),
                  duration: duration
                });
              }
              // Source getting succeeded
              resolveGet({
                value: getArgs[1],
                duration: duration
              });
            });
          });
        });
      });
    });
    return function getComponent() {
      return sourceLoadPromise.then(function (finalizeSource) {
        return finalizeSource();
      });
    };
  }
  /**
   * Loads the given entropy sources. Returns a function that collects the entropy components.
   *
   * The result is returned synchronously in order to allow start getting the components
   * before the sources are loaded completely.
   *
   * Warning for package users:
   * This function is out of Semantic Versioning, i.e. can change unexpectedly. Usage is at your own risk.
   */
  function loadSources(sources, sourceOptions, excludeSources) {
    var includedSources = Object.keys(sources).filter(function (sourceKey) {
      return excludes(excludeSources, sourceKey);
    });
    var sourceGetters = Array(includedSources.length);
    // Using `forEachWithBreaks` allows asynchronous sources to complete between synchronous sources
    // and measure the duration correctly
    forEachWithBreaks(includedSources, function (sourceKey, index) {
      sourceGetters[index] = loadSource(sources[sourceKey], sourceOptions);
    });
    return function getComponents() {
      return __awaiter(this, void 0, void 0, function () {
        var components, _i, includedSources_1, sourceKey, componentPromises, _loop_1, state_1;
        return __generator(this, function (_a) {
          switch (_a.label) {
            case 0:
              components = {};
              for (_i = 0, includedSources_1 = includedSources; _i < includedSources_1.length; _i++) {
                sourceKey = includedSources_1[_i];
                components[sourceKey] = undefined;
              }
              componentPromises = Array(includedSources.length);
              _loop_1 = function () {
                var hasAllComponentPromises;
                return __generator(this, function (_a) {
                  switch (_a.label) {
                    case 0:
                      hasAllComponentPromises = true;
                      return [4 /*yield*/, forEachWithBreaks(includedSources, function (sourceKey, index) {
                        if (!componentPromises[index]) {
                          // `sourceGetters` may be incomplete at this point of execution because `forEachWithBreaks` is asynchronous
                          if (sourceGetters[index]) {
                            componentPromises[index] = sourceGetters[index]().then(function (component) {
                              return components[sourceKey] = component;
                            });
                          } else {
                            hasAllComponentPromises = false;
                          }
                        }
                      })];
                    case 1:
                      _a.sent();
                      if (hasAllComponentPromises) {
                        return [2 /*return*/, "break"];
                      }
                      return [4 /*yield*/, wait(1)];
                    // Lets the source load loop continue
                    case 2:
                      _a.sent(); // Lets the source load loop continue
                      return [2 /*return*/];
                  }
                });
              };

              _a.label = 1;
            case 1:
              return [5 /*yield**/, _loop_1()];
            case 2:
              state_1 = _a.sent();
              if (state_1 === "break") return [3 /*break*/, 4];
              _a.label = 3;
            case 3:
              return [3 /*break*/, 1];
            case 4:
              return [4 /*yield*/, Promise.all(componentPromises)];
            case 5:
              _a.sent();
              return [2 /*return*/, components];
          }
        });
      });
    };
  }

  /*
   * Functions to help with features that vary through browsers
   */
  /**
   * Checks whether the browser is based on Trident (the Internet Explorer engine) without using user-agent.
   *
   * Warning for package users:
   * This function is out of Semantic Versioning, i.e. can change unexpectedly. Usage is at your own risk.
   */
  function isTrident() {
    var w = window;
    var n = navigator;
    // The properties are checked to be in IE 10, IE 11 and not to be in other browsers in October 2020
    return countTruthy(['MSCSSMatrix' in w, 'msSetImmediate' in w, 'msIndexedDB' in w, 'msMaxTouchPoints' in n, 'msPointerEnabled' in n]) >= 4;
  }
  /**
   * Checks whether the browser is based on EdgeHTML (the pre-Chromium Edge engine) without using user-agent.
   *
   * Warning for package users:
   * This function is out of Semantic Versioning, i.e. can change unexpectedly. Usage is at your own risk.
   */
  function isEdgeHTML() {
    // Based on research in October 2020
    var w = window;
    var n = navigator;
    return countTruthy(['msWriteProfilerMark' in w, 'MSStream' in w, 'msLaunchUri' in n, 'msSaveBlob' in n]) >= 3 && !isTrident();
  }
  /**
   * Checks whether the browser is based on Chromium without using user-agent.
   *
   * Warning for package users:
   * This function is out of Semantic Versioning, i.e. can change unexpectedly. Usage is at your own risk.
   */
  function isChromium() {
    // Based on research in October 2020. Tested to detect Chromium 42-86.
    var w = window;
    var n = navigator;
    return countTruthy(['webkitPersistentStorage' in n, 'webkitTemporaryStorage' in n, n.vendor.indexOf('Google') === 0, 'webkitResolveLocalFileSystemURL' in w, 'BatteryManager' in w, 'webkitMediaStream' in w, 'webkitSpeechGrammar' in w]) >= 5;
  }
  /**
   * Checks whether the browser is based on mobile or desktop Safari without using user-agent.
   * All iOS browsers use WebKit (the Safari engine).
   *
   * Warning for package users:
   * This function is out of Semantic Versioning, i.e. can change unexpectedly. Usage is at your own risk.
   */
  function isWebKit() {
    // Based on research in September 2020
    var w = window;
    var n = navigator;
    return countTruthy(['ApplePayError' in w, 'CSSPrimitiveValue' in w, 'Counter' in w, n.vendor.indexOf('Apple') === 0, 'getStorageUpdates' in n, 'WebKitMediaKeys' in w]) >= 4;
  }
  /**
   * Checks whether the WebKit browser is a desktop Safari.
   *
   * Warning for package users:
   * This function is out of Semantic Versioning, i.e. can change unexpectedly. Usage is at your own risk.
   */
  function isDesktopSafari() {
    var w = window;
    return countTruthy(['safari' in w, !('DeviceMotionEvent' in w), !('ongestureend' in w), !('standalone' in navigator)]) >= 3;
  }
  /**
   * Checks whether the browser is based on Gecko (Firefox engine) without using user-agent.
   *
   * Warning for package users:
   * This function is out of Semantic Versioning, i.e. can change unexpectedly. Usage is at your own risk.
   */
  function isGecko() {
    var _a, _b;
    var w = window;
    // Based on research in September 2020
    return countTruthy(['buildID' in navigator, 'MozAppearance' in ((_b = (_a = document.documentElement) === null || _a === void 0 ? void 0 : _a.style) !== null && _b !== void 0 ? _b : {}), 'onmozfullscreenchange' in w, 'mozInnerScreenX' in w, 'CSSMozDocumentRule' in w, 'CanvasCaptureMediaStream' in w]) >= 4;
  }
  /**
   * Checks whether the browser is based on Chromium version ≥86 without using user-agent.
   * It doesn't check that the browser is based on Chromium, there is a separate function for this.
   */
  function isChromium86OrNewer() {
    // Checked in Chrome 85 vs Chrome 86 both on desktop and Android
    var w = window;
    return countTruthy([!('MediaSettingsRange' in w), 'RTCEncodedAudioFrame' in w, '' + w.Intl === '[object Intl]', '' + w.Reflect === '[object Reflect]']) >= 3;
  }
  /**
   * Checks whether the browser is based on WebKit version ≥606 (Safari ≥12) without using user-agent.
   * It doesn't check that the browser is based on WebKit, there is a separate function for this.
   *
   * @link https://en.wikipedia.org/wiki/Safari_version_history#Release_history Safari-WebKit versions map
   */
  function isWebKit606OrNewer() {
    // Checked in Safari 9–14
    var w = window;
    return countTruthy(['DOMRectList' in w, 'RTCPeerConnectionIceEvent' in w, 'SVGGeometryElement' in w, 'ontransitioncancel' in w]) >= 3;
  }
  /**
   * Checks whether the device is an iPad.
   * It doesn't check that the engine is WebKit and that the WebKit isn't desktop.
   */
  function isIPad() {
    // Checked on:
    // Safari on iPadOS (both mobile and desktop modes): 8, 11, 12, 13, 14
    // Chrome on iPadOS (both mobile and desktop modes): 11, 12, 13, 14
    // Safari on iOS (both mobile and desktop modes): 9, 10, 11, 12, 13, 14
    // Chrome on iOS (both mobile and desktop modes): 9, 10, 11, 12, 13, 14
    // Before iOS 13. Safari tampers the value in "request desktop site" mode since iOS 13.
    if (navigator.platform === 'iPad') {
      return true;
    }
    var s = screen;
    var screenRatio = s.width / s.height;
    return countTruthy(['MediaSource' in window, !!Element.prototype.webkitRequestFullscreen,
    // iPhone 4S that runs iOS 9 matches this. But it won't match the criteria above, so it won't be detected as iPad.
    screenRatio > 0.65 && screenRatio < 1.53]) >= 2;
  }
  /**
   * Warning for package users:
   * This function is out of Semantic Versioning, i.e. can change unexpectedly. Usage is at your own risk.
   */
  function getFullscreenElement() {
    var d = document;
    return d.fullscreenElement || d.msFullscreenElement || d.mozFullScreenElement || d.webkitFullscreenElement || null;
  }
  function exitFullscreen() {
    var d = document;
    // `call` is required because the function throws an error without a proper "this" context
    return (d.exitFullscreen || d.msExitFullscreen || d.mozCancelFullScreen || d.webkitExitFullscreen).call(d);
  }
  /**
   * Checks whether the device runs on Android without using user-agent.
   *
   * Warning for package users:
   * This function is out of Semantic Versioning, i.e. can change unexpectedly. Usage is at your own risk.
   */
  function isAndroid() {
    var isItChromium = isChromium();
    var isItGecko = isGecko();
    // Only 2 browser engines are presented on Android.
    // Actually, there is also Android 4.1 browser, but it's not worth detecting it at the moment.
    if (!isItChromium && !isItGecko) {
      return false;
    }
    var w = window;
    // Chrome removes all words "Android" from `navigator` when desktop version is requested
    // Firefox keeps "Android" in `navigator.appVersion` when desktop version is requested
    return countTruthy(['onorientationchange' in w, 'orientation' in w, isItChromium && !('SharedWorker' in w), isItGecko && /android/i.test(navigator.appVersion)]) >= 2;
  }

  /**
   * A deep description: https://fingerprintjs.com/blog/audio-fingerprinting/
   * Inspired by and based on https://github.com/cozylife/audio-fingerprint
   */
  function getAudioFingerprint() {
    var w = window;
    var AudioContext = w.OfflineAudioContext || w.webkitOfflineAudioContext;
    if (!AudioContext) {
      return -2 /* NotSupported */;
    }
    // In some browsers, audio context always stays suspended unless the context is started in response to a user action
    // (e.g. a click or a tap). It prevents audio fingerprint from being taken at an arbitrary moment of time.
    // Such browsers are old and unpopular, so the audio fingerprinting is just skipped in them.
    // See a similar case explanation at https://stackoverflow.com/questions/46363048/onaudioprocess-not-called-on-ios11#46534088
    if (doesCurrentBrowserSuspendAudioContext()) {
      return -1 /* KnownToSuspend */;
    }

    var hashFromIndex = 4500;
    var hashToIndex = 5000;
    var context = new AudioContext(1, hashToIndex, 44100);
    var oscillator = context.createOscillator();
    oscillator.type = 'triangle';
    oscillator.frequency.value = 10000;
    var compressor = context.createDynamicsCompressor();
    compressor.threshold.value = -50;
    compressor.knee.value = 40;
    compressor.ratio.value = 12;
    compressor.attack.value = 0;
    compressor.release.value = 0.25;
    oscillator.connect(compressor);
    compressor.connect(context.destination);
    oscillator.start(0);
    var _a = startRenderingAudio(context),
      renderPromise = _a[0],
      finishRendering = _a[1];
    var fingerprintPromise = renderPromise.then(function (buffer) {
      return getHash(buffer.getChannelData(0).subarray(hashFromIndex));
    }, function (error) {
      if (error.name === "timeout" /* Timeout */ || error.name === "suspended" /* Suspended */) {
        return -3 /* Timeout */;
      }

      throw error;
    });
    // Suppresses the console error message in case when the fingerprint fails before requested
    fingerprintPromise.catch(function () {
      return undefined;
    });
    return function () {
      finishRendering();
      return fingerprintPromise;
    };
  }
  /**
   * Checks if the current browser is known to always suspend audio context
   */
  function doesCurrentBrowserSuspendAudioContext() {
    return isWebKit() && !isDesktopSafari() && !isWebKit606OrNewer();
  }
  /**
   * Starts rendering the audio context.
   * When the returned function is called, the render process starts finishing.
   */
  function startRenderingAudio(context) {
    var renderTryMaxCount = 3;
    var renderRetryDelay = 500;
    var runningMaxAwaitTime = 500;
    var runningSufficientTime = 5000;
    var finalize = function () {
      return undefined;
    };
    var resultPromise = new Promise(function (resolve, reject) {
      var isFinalized = false;
      var renderTryCount = 0;
      var startedRunningAt = 0;
      context.oncomplete = function (event) {
        return resolve(event.renderedBuffer);
      };
      var startRunningTimeout = function () {
        setTimeout(function () {
          return reject(makeInnerError("timeout" /* Timeout */));
        }, Math.min(runningMaxAwaitTime, startedRunningAt + runningSufficientTime - Date.now()));
      };
      var tryRender = function () {
        try {
          context.startRendering();
          switch (context.state) {
            case 'running':
              startedRunningAt = Date.now();
              if (isFinalized) {
                startRunningTimeout();
              }
              break;
            // Sometimes the audio context doesn't start after calling `startRendering` (in addition to the cases where
            // audio context doesn't start at all). A known case is starting an audio context when the browser tab is in
            // background on iPhone. Retries usually help in this case.
            case 'suspended':
              // The audio context can reject starting until the tab is in foreground. Long fingerprint duration
              // in background isn't a problem, therefore the retry attempts don't count in background. It can lead to
              // a situation when a fingerprint takes very long time and finishes successfully. FYI, the audio context
              // can be suspended when `document.hidden === false` and start running after a retry.
              if (!document.hidden) {
                renderTryCount++;
              }
              if (isFinalized && renderTryCount >= renderTryMaxCount) {
                reject(makeInnerError("suspended" /* Suspended */));
              } else {
                setTimeout(tryRender, renderRetryDelay);
              }
              break;
          }
        } catch (error) {
          reject(error);
        }
      };
      tryRender();
      finalize = function () {
        if (!isFinalized) {
          isFinalized = true;
          if (startedRunningAt > 0) {
            startRunningTimeout();
          }
        }
      };
    });
    return [resultPromise, finalize];
  }
  function getHash(signal) {
    var hash = 0;
    for (var i = 0; i < signal.length; ++i) {
      hash += Math.abs(signal[i]);
    }
    return hash;
  }
  function makeInnerError(name) {
    var error = new Error(name);
    error.name = name;
    return error;
  }

  /**
   * Creates and keeps an invisible iframe while the given function runs.
   * The given function is called when the iframe is loaded and has a body.
   * The iframe allows to measure DOM sizes inside itself.
   *
   * Notice: passing an initial HTML code doesn't work in IE.
   *
   * Warning for package users:
   * This function is out of Semantic Versioning, i.e. can change unexpectedly. Usage is at your own risk.
   */
  function withIframe(action, initialHtml, domPollInterval) {
    var _a, _b, _c;
    if (domPollInterval === void 0) {
      domPollInterval = 50;
    }
    return __awaiter(this, void 0, void 0, function () {
      var d, iframe;
      return __generator(this, function (_d) {
        switch (_d.label) {
          case 0:
            d = document;
            _d.label = 1;
          case 1:
            if (!!d.body) return [3 /*break*/, 3];
            return [4 /*yield*/, wait(domPollInterval)];
          case 2:
            _d.sent();
            return [3 /*break*/, 1];
          case 3:
            iframe = d.createElement('iframe');
            _d.label = 4;
          case 4:
            _d.trys.push([4,, 10, 11]);
            return [4 /*yield*/, new Promise(function (_resolve, _reject) {
              var isComplete = false;
              var resolve = function () {
                isComplete = true;
                _resolve();
              };
              var reject = function (error) {
                isComplete = true;
                _reject(error);
              };
              iframe.onload = resolve;
              iframe.onerror = reject;
              var style = iframe.style;
              style.setProperty('display', 'block', 'important'); // Required for browsers to calculate the layout
              style.position = 'absolute';
              style.top = '0';
              style.left = '0';
              style.visibility = 'hidden';
              if (initialHtml && 'srcdoc' in iframe) {
                iframe.srcdoc = initialHtml;
              } else {
                iframe.src = 'about:blank';
              }
              d.body.appendChild(iframe);
              // WebKit in WeChat doesn't fire the iframe's `onload` for some reason.
              // This code checks for the loading state manually.
              // See https://github.com/fingerprintjs/fingerprintjs/issues/645
              var checkReadyState = function () {
                var _a, _b;
                // The ready state may never become 'complete' in Firefox despite the 'load' event being fired.
                // So an infinite setTimeout loop can happen without this check.
                // See https://github.com/fingerprintjs/fingerprintjs/pull/716#issuecomment-986898796
                if (isComplete) {
                  return;
                }
                // Make sure iframe.contentWindow and iframe.contentWindow.document are both loaded
                // The contentWindow.document can miss in JSDOM (https://github.com/jsdom/jsdom).
                if (((_b = (_a = iframe.contentWindow) === null || _a === void 0 ? void 0 : _a.document) === null || _b === void 0 ? void 0 : _b.readyState) === 'complete') {
                  resolve();
                } else {
                  setTimeout(checkReadyState, 10);
                }
              };
              checkReadyState();
            })];
          case 5:
            _d.sent();
            _d.label = 6;
          case 6:
            if (!!((_b = (_a = iframe.contentWindow) === null || _a === void 0 ? void 0 : _a.document) === null || _b === void 0 ? void 0 : _b.body)) return [3 /*break*/, 8];
            return [4 /*yield*/, wait(domPollInterval)];
          case 7:
            _d.sent();
            return [3 /*break*/, 6];
          case 8:
            return [4 /*yield*/, action(iframe, iframe.contentWindow)];
          case 9:
            return [2 /*return*/, _d.sent()];
          case 10:
            (_c = iframe.parentNode) === null || _c === void 0 ? void 0 : _c.removeChild(iframe);
            return [7 /*endfinally*/];
          case 11:
            return [2 /*return*/];
        }
      });
    });
  }
  /**
   * Creates a DOM element that matches the given selector.
   * Only single element selector are supported (without operators like space, +, >, etc).
   */
  function selectorToElement(selector) {
    var _a = parseSimpleCssSelector(selector),
      tag = _a[0],
      attributes = _a[1];
    var element = document.createElement(tag !== null && tag !== void 0 ? tag : 'div');
    for (var _i = 0, _b = Object.keys(attributes); _i < _b.length; _i++) {
      var name_1 = _b[_i];
      var value = attributes[name_1].join(' ');
      // Changing the `style` attribute can cause a CSP error, therefore we change the `style.cssText` property.
      // https://github.com/fingerprintjs/fingerprintjs/issues/733
      if (name_1 === 'style') {
        addStyleString(element.style, value);
      } else {
        element.setAttribute(name_1, value);
      }
    }
    return element;
  }
  /**
   * Adds CSS styles from a string in such a way that doesn't trigger a CSP warning (unsafe-inline or unsafe-eval)
   */
  function addStyleString(style, source) {
    // We don't use `style.cssText` because browsers must block it when no `unsafe-eval` CSP is presented: https://csplite.com/csp145/#w3c_note
    // Even though the browsers ignore this standard, we don't use `cssText` just in case.
    for (var _i = 0, _a = source.split(';'); _i < _a.length; _i++) {
      var property = _a[_i];
      var match = /^\s*([\w-]+)\s*:\s*(.+?)(\s*!([\w-]+))?\s*$/.exec(property);
      if (match) {
        var name_2 = match[1],
          value = match[2],
          priority = match[4];
        style.setProperty(name_2, value, priority || ''); // The last argument can't be undefined in IE11
      }
    }
  }

  // We use m or w because these two characters take up the maximum width.
  // And we use a LLi so that the same matching fonts can get separated.
  var testString = 'mmMwWLliI0O&1';
  // We test using 48px font size, we may use any size. I guess larger the better.
  var textSize = '48px';
  // A font will be compared against all the three default fonts.
  // And if for any default fonts it doesn't match, then that font is available.
  var baseFonts = ['monospace', 'sans-serif', 'serif'];
  var fontList = [
  // This is android-specific font from "Roboto" family
  'sans-serif-thin', 'ARNO PRO', 'Agency FB', 'Arabic Typesetting', 'Arial Unicode MS', 'AvantGarde Bk BT', 'BankGothic Md BT', 'Batang', 'Bitstream Vera Sans Mono', 'Calibri', 'Century', 'Century Gothic', 'Clarendon', 'EUROSTILE', 'Franklin Gothic', 'Futura Bk BT', 'Futura Md BT', 'GOTHAM', 'Gill Sans', 'HELV', 'Haettenschweiler', 'Helvetica Neue', 'Humanst521 BT', 'Leelawadee', 'Letter Gothic', 'Levenim MT', 'Lucida Bright', 'Lucida Sans', 'Menlo', 'MS Mincho', 'MS Outlook', 'MS Reference Specialty', 'MS UI Gothic', 'MT Extra', 'MYRIAD PRO', 'Marlett', 'Meiryo UI', 'Microsoft Uighur', 'Minion Pro', 'Monotype Corsiva', 'PMingLiU', 'Pristina', 'SCRIPTINA', 'Segoe UI Light', 'Serifa', 'SimHei', 'Small Fonts', 'Staccato222 BT', 'TRAJAN PRO', 'Univers CE 55 Medium', 'Vrinda', 'ZWAdobeF'];
  // kudos to http://www.lalit.org/lab/javascript-css-font-detect/
  function getFonts() {
    // Running the script in an iframe makes it not affect the page look and not be affected by the page CSS. See:
    // https://github.com/fingerprintjs/fingerprintjs/issues/592
    // https://github.com/fingerprintjs/fingerprintjs/issues/628
    return withIframe(function (_, _a) {
      var document = _a.document;
      var holder = document.body;
      holder.style.fontSize = textSize;
      // div to load spans for the default fonts and the fonts to detect
      var spansContainer = document.createElement('div');
      var defaultWidth = {};
      var defaultHeight = {};
      // creates a span where the fonts will be loaded
      var createSpan = function (fontFamily) {
        var span = document.createElement('span');
        var style = span.style;
        style.position = 'absolute';
        style.top = '0';
        style.left = '0';
        style.fontFamily = fontFamily;
        span.textContent = testString;
        spansContainer.appendChild(span);
        return span;
      };
      // creates a span and load the font to detect and a base font for fallback
      var createSpanWithFonts = function (fontToDetect, baseFont) {
        return createSpan("'" + fontToDetect + "'," + baseFont);
      };
      // creates spans for the base fonts and adds them to baseFontsDiv
      var initializeBaseFontsSpans = function () {
        return baseFonts.map(createSpan);
      };
      // creates spans for the fonts to detect and adds them to fontsDiv
      var initializeFontsSpans = function () {
        // Stores {fontName : [spans for that font]}
        var spans = {};
        var _loop_1 = function (font) {
          spans[font] = baseFonts.map(function (baseFont) {
            return createSpanWithFonts(font, baseFont);
          });
        };
        for (var _i = 0, fontList_1 = fontList; _i < fontList_1.length; _i++) {
          var font = fontList_1[_i];
          _loop_1(font);
        }
        return spans;
      };
      // checks if a font is available
      var isFontAvailable = function (fontSpans) {
        return baseFonts.some(function (baseFont, baseFontIndex) {
          return fontSpans[baseFontIndex].offsetWidth !== defaultWidth[baseFont] || fontSpans[baseFontIndex].offsetHeight !== defaultHeight[baseFont];
        });
      };
      // create spans for base fonts
      var baseFontsSpans = initializeBaseFontsSpans();
      // create spans for fonts to detect
      var fontsSpans = initializeFontsSpans();
      // add all the spans to the DOM
      holder.appendChild(spansContainer);
      // get the default width for the three base fonts
      for (var index = 0; index < baseFonts.length; index++) {
        defaultWidth[baseFonts[index]] = baseFontsSpans[index].offsetWidth; // width for the default font
        defaultHeight[baseFonts[index]] = baseFontsSpans[index].offsetHeight; // height for the default font
      }
      // check available fonts
      return fontList.filter(function (font) {
        return isFontAvailable(fontsSpans[font]);
      });
    });
  }
  function getPlugins() {
    var rawPlugins = navigator.plugins;
    if (!rawPlugins) {
      return undefined;
    }
    var plugins = [];
    // Safari 10 doesn't support iterating navigator.plugins with for...of
    for (var i = 0; i < rawPlugins.length; ++i) {
      var plugin = rawPlugins[i];
      if (!plugin) {
        continue;
      }
      var mimeTypes = [];
      for (var j = 0; j < plugin.length; ++j) {
        var mimeType = plugin[j];
        mimeTypes.push({
          type: mimeType.type,
          suffixes: mimeType.suffixes
        });
      }
      plugins.push({
        name: plugin.name,
        description: plugin.description,
        mimeTypes: mimeTypes
      });
    }
    return plugins;
  }

  // https://www.browserleaks.com/canvas#how-does-it-work
  function getCanvasFingerprint() {
    var _a = makeCanvasContext(),
      canvas = _a[0],
      context = _a[1];
    if (!isSupported(canvas, context)) {
      return {
        winding: false,
        geometry: '',
        text: ''
      };
    }
    return {
      winding: doesSupportWinding(context),
      geometry: makeGeometryImage(canvas, context),
      // Text is unstable:
      // https://github.com/fingerprintjs/fingerprintjs/issues/583
      // https://github.com/fingerprintjs/fingerprintjs/issues/103
      // Therefore it's extracted into a separate image.
      text: makeTextImage(canvas, context)
    };
  }
  function makeCanvasContext() {
    var canvas = document.createElement('canvas');
    canvas.width = 1;
    canvas.height = 1;
    return [canvas, canvas.getContext('2d')];
  }
  function isSupported(canvas, context) {
    // TODO: look into: https://developer.mozilla.org/en-US/docs/Web/API/HTMLCanvasElement/toBlob
    return !!(context && canvas.toDataURL);
  }
  function doesSupportWinding(context) {
    // https://web.archive.org/web/20170825024655/http://blogs.adobe.com/webplatform/2013/01/30/winding-rules-in-canvas/
    // https://github.com/Modernizr/Modernizr/blob/master/feature-detects/canvas/winding.js
    context.rect(0, 0, 10, 10);
    context.rect(2, 2, 6, 6);
    return !context.isPointInPath(5, 5, 'evenodd');
  }
  function makeTextImage(canvas, context) {
    // Resizing the canvas cleans it
    canvas.width = 240;
    canvas.height = 60;
    context.textBaseline = 'alphabetic';
    context.fillStyle = '#f60';
    context.fillRect(100, 1, 62, 20);
    context.fillStyle = '#069';
    // It's important to use explicit built-in fonts in order to exclude the affect of font preferences
    // (there is a separate entropy source for them).
    context.font = '11pt "Times New Roman"';
    // The choice of emojis has a gigantic impact on rendering performance (especially in FF).
    // Some newer emojis cause it to slow down 50-200 times.
    // There must be no text to the right of the emoji, see https://github.com/fingerprintjs/fingerprintjs/issues/574
    // A bare emoji shouldn't be used because the canvas will change depending on the script encoding:
    // https://github.com/fingerprintjs/fingerprintjs/issues/66
    // Escape sequence shouldn't be used too because Terser will turn it into a bare unicode.
    var printedText = "Cwm fjordbank gly " + String.fromCharCode(55357, 56835) /* 😃 */;
    context.fillText(printedText, 2, 15);
    context.fillStyle = 'rgba(102, 204, 0, 0.2)';
    context.font = '18pt Arial';
    context.fillText(printedText, 4, 45);
    return save(canvas);
  }
  function makeGeometryImage(canvas, context) {
    // Resizing the canvas cleans it
    canvas.width = 122;
    canvas.height = 110;
    // Canvas blending
    // https://web.archive.org/web/20170826194121/http://blogs.adobe.com/webplatform/2013/01/28/blending-features-in-canvas/
    // http://jsfiddle.net/NDYV8/16/
    context.globalCompositeOperation = 'multiply';
    for (var _i = 0, _a = [['#f2f', 40, 40], ['#2ff', 80, 40], ['#ff2', 60, 80]]; _i < _a.length; _i++) {
      var _b = _a[_i],
        color = _b[0],
        x = _b[1],
        y = _b[2];
      context.fillStyle = color;
      context.beginPath();
      context.arc(x, y, 40, 0, Math.PI * 2, true);
      context.closePath();
      context.fill();
    }
    // Canvas winding
    // https://web.archive.org/web/20130913061632/http://blogs.adobe.com/webplatform/2013/01/30/winding-rules-in-canvas/
    // http://jsfiddle.net/NDYV8/19/
    context.fillStyle = '#f9c';
    context.arc(60, 60, 60, 0, Math.PI * 2, true);
    context.arc(60, 60, 20, 0, Math.PI * 2, true);
    context.fill('evenodd');
    return save(canvas);
  }
  function save(canvas) {
    // TODO: look into: https://developer.mozilla.org/en-US/docs/Web/API/HTMLCanvasElement/toBlob
    return canvas.toDataURL();
  }

  /**
   * This is a crude and primitive touch screen detection. It's not possible to currently reliably detect the availability
   * of a touch screen with a JS, without actually subscribing to a touch event.
   *
   * @see http://www.stucox.com/blog/you-cant-detect-a-touchscreen/
   * @see https://github.com/Modernizr/Modernizr/issues/548
   */
  function getTouchSupport() {
    var n = navigator;
    var maxTouchPoints = 0;
    var touchEvent;
    if (n.maxTouchPoints !== undefined) {
      maxTouchPoints = toInt(n.maxTouchPoints);
    } else if (n.msMaxTouchPoints !== undefined) {
      maxTouchPoints = n.msMaxTouchPoints;
    }
    try {
      document.createEvent('TouchEvent');
      touchEvent = true;
    } catch (_a) {
      touchEvent = false;
    }
    var touchStart = ('ontouchstart' in window);
    return {
      maxTouchPoints: maxTouchPoints,
      touchEvent: touchEvent,
      touchStart: touchStart
    };
  }
  function getOsCpu() {
    return navigator.oscpu;
  }
  function getLanguages() {
    var n = navigator;
    var result = [];
    var language = n.language || n.userLanguage || n.browserLanguage || n.systemLanguage;
    if (language !== undefined) {
      result.push([language]);
    }
    if (Array.isArray(n.languages)) {
      // Starting from Chromium 86, there is only a single value in `navigator.language` in Incognito mode:
      // the value of `navigator.language`. Therefore the value is ignored in this browser.
      if (!(isChromium() && isChromium86OrNewer())) {
        result.push(n.languages);
      }
    } else if (typeof n.languages === 'string') {
      var languages = n.languages;
      if (languages) {
        result.push(languages.split(','));
      }
    }
    return result;
  }
  function getColorDepth() {
    return window.screen.colorDepth;
  }
  function getDeviceMemory() {
    // `navigator.deviceMemory` is a string containing a number in some unidentified cases
    return replaceNaN(toFloat(navigator.deviceMemory), undefined);
  }
  function getScreenResolution() {
    var s = screen;
    // Some browsers return screen resolution as strings, e.g. "1200", instead of a number, e.g. 1200.
    // I suspect it's done by certain plugins that randomize browser properties to prevent fingerprinting.
    // Some browsers even return  screen resolution as not numbers.
    var parseDimension = function (value) {
      return replaceNaN(toInt(value), null);
    };
    var dimensions = [parseDimension(s.width), parseDimension(s.height)];
    dimensions.sort().reverse();
    return dimensions;
  }
  var screenFrameCheckInterval = 2500;
  var roundingPrecision = 10;
  // The type is readonly to protect from unwanted mutations
  var screenFrameBackup;
  var screenFrameSizeTimeoutId;
  /**
   * Starts watching the screen frame size. When a non-zero size appears, the size is saved and the watch is stopped.
   * Later, when `getScreenFrame` runs, it will return the saved non-zero size if the current size is null.
   *
   * This trick is required to mitigate the fact that the screen frame turns null in some cases.
   * See more on this at https://github.com/fingerprintjs/fingerprintjs/issues/568
   */
  function watchScreenFrame() {
    if (screenFrameSizeTimeoutId !== undefined) {
      return;
    }
    var checkScreenFrame = function () {
      var frameSize = getCurrentScreenFrame();
      if (isFrameSizeNull(frameSize)) {
        screenFrameSizeTimeoutId = setTimeout(checkScreenFrame, screenFrameCheckInterval);
      } else {
        screenFrameBackup = frameSize;
        screenFrameSizeTimeoutId = undefined;
      }
    };
    checkScreenFrame();
  }
  function getScreenFrame() {
    var _this = this;
    watchScreenFrame();
    return function () {
      return __awaiter(_this, void 0, void 0, function () {
        var frameSize;
        return __generator(this, function (_a) {
          switch (_a.label) {
            case 0:
              frameSize = getCurrentScreenFrame();
              if (!isFrameSizeNull(frameSize)) return [3 /*break*/, 2];
              if (screenFrameBackup) {
                return [2 /*return*/, __spreadArrays(screenFrameBackup)];
              }
              if (!getFullscreenElement()) return [3 /*break*/, 2];
              // Some browsers set the screen frame to zero when programmatic fullscreen is on.
              // There is a chance of getting a non-zero frame after exiting the fullscreen.
              // See more on this at https://github.com/fingerprintjs/fingerprintjs/issues/568
              return [4 /*yield*/, exitFullscreen()];
            case 1:
              // Some browsers set the screen frame to zero when programmatic fullscreen is on.
              // There is a chance of getting a non-zero frame after exiting the fullscreen.
              // See more on this at https://github.com/fingerprintjs/fingerprintjs/issues/568
              _a.sent();
              frameSize = getCurrentScreenFrame();
              _a.label = 2;
            case 2:
              if (!isFrameSizeNull(frameSize)) {
                screenFrameBackup = frameSize;
              }
              return [2 /*return*/, frameSize];
          }
        });
      });
    };
  }
  /**
   * Sometimes the available screen resolution changes a bit, e.g. 1900x1440 → 1900x1439. A possible reason: macOS Dock
   * shrinks to fit more icons when there is too little space. The rounding is used to mitigate the difference.
   */
  function getRoundedScreenFrame() {
    var _this = this;
    var screenFrameGetter = getScreenFrame();
    return function () {
      return __awaiter(_this, void 0, void 0, function () {
        var frameSize, processSize;
        return __generator(this, function (_a) {
          switch (_a.label) {
            case 0:
              return [4 /*yield*/, screenFrameGetter()];
            case 1:
              frameSize = _a.sent();
              processSize = function (sideSize) {
                return sideSize === null ? null : round(sideSize, roundingPrecision);
              };
              // It might look like I don't know about `for` and `map`.
              // In fact, such code is used to avoid TypeScript issues without using `as`.
              return [2 /*return*/, [processSize(frameSize[0]), processSize(frameSize[1]), processSize(frameSize[2]), processSize(frameSize[3])]];
          }
        });
      });
    };
  }
  function getCurrentScreenFrame() {
    var s = screen;
    // Some browsers return screen resolution as strings, e.g. "1200", instead of a number, e.g. 1200.
    // I suspect it's done by certain plugins that randomize browser properties to prevent fingerprinting.
    //
    // Some browsers (IE, Edge ≤18) don't provide `screen.availLeft` and `screen.availTop`. The property values are
    // replaced with 0 in such cases to not lose the entropy from `screen.availWidth` and `screen.availHeight`.
    return [replaceNaN(toFloat(s.availTop), null), replaceNaN(toFloat(s.width) - toFloat(s.availWidth) - replaceNaN(toFloat(s.availLeft), 0), null), replaceNaN(toFloat(s.height) - toFloat(s.availHeight) - replaceNaN(toFloat(s.availTop), 0), null), replaceNaN(toFloat(s.availLeft), null)];
  }
  function isFrameSizeNull(frameSize) {
    for (var i = 0; i < 4; ++i) {
      if (frameSize[i]) {
        return false;
      }
    }
    return true;
  }
  function getHardwareConcurrency() {
    // sometimes hardware concurrency is a string
    return replaceNaN(toInt(navigator.hardwareConcurrency), undefined);
  }
  function getTimezone() {
    var _a;
    var DateTimeFormat = (_a = window.Intl) === null || _a === void 0 ? void 0 : _a.DateTimeFormat;
    if (DateTimeFormat) {
      var timezone = new DateTimeFormat().resolvedOptions().timeZone;
      if (timezone) {
        return timezone;
      }
    }
    // For browsers that don't support timezone names
    // The minus is intentional because the JS offset is opposite to the real offset
    var offset = -getTimezoneOffset();
    return "UTC" + (offset >= 0 ? '+' : '') + Math.abs(offset);
  }
  function getTimezoneOffset() {
    var currentYear = new Date().getFullYear();
    // The timezone offset may change over time due to daylight saving time (DST) shifts.
    // The non-DST timezone offset is used as the result timezone offset.
    // Since the DST season differs in the northern and the southern hemispheres,
    // both January and July timezones offsets are considered.
    return Math.max(
    // `getTimezoneOffset` returns a number as a string in some unidentified cases
    toFloat(new Date(currentYear, 0, 1).getTimezoneOffset()), toFloat(new Date(currentYear, 6, 1).getTimezoneOffset()));
  }
  function getSessionStorage() {
    try {
      return !!window.sessionStorage;
    } catch (error) {
      /* SecurityError when referencing it means it exists */
      return true;
    }
  }

  // https://bugzilla.mozilla.org/show_bug.cgi?id=781447
  function getLocalStorage() {
    try {
      return !!window.localStorage;
    } catch (e) {
      /* SecurityError when referencing it means it exists */
      return true;
    }
  }
  function getIndexedDB() {
    // IE and Edge don't allow accessing indexedDB in private mode, therefore IE and Edge will have different
    // visitor identifier in normal and private modes.
    if (isTrident() || isEdgeHTML()) {
      return undefined;
    }
    try {
      return !!window.indexedDB;
    } catch (e) {
      /* SecurityError when referencing it means it exists */
      return true;
    }
  }
  function getOpenDatabase() {
    return !!window.openDatabase;
  }
  function getCpuClass() {
    return navigator.cpuClass;
  }
  function getPlatform() {
    // Android Chrome 86 and 87 and Android Firefox 80 and 84 don't mock the platform value when desktop mode is requested
    var platform = navigator.platform;
    // iOS mocks the platform value when desktop version is requested: https://github.com/fingerprintjs/fingerprintjs/issues/514
    // iPad uses desktop mode by default since iOS 13
    // The value is 'MacIntel' on M1 Macs
    // The value is 'iPhone' on iPod Touch
    if (platform === 'MacIntel') {
      if (isWebKit() && !isDesktopSafari()) {
        return isIPad() ? 'iPad' : 'iPhone';
      }
    }
    return platform;
  }
  function getVendor() {
    return navigator.vendor || '';
  }

  /**
   * Checks for browser-specific (not engine specific) global variables to tell browsers with the same engine apart.
   * Only somewhat popular browsers are considered.
   */
  function getVendorFlavors() {
    var flavors = [];
    for (var _i = 0, _a = [
      // Blink and some browsers on iOS
      'chrome',
      // Safari on macOS
      'safari',
      // Chrome on iOS (checked in 85 on 13 and 87 on 14)
      '__crWeb', '__gCrWeb',
      // Yandex Browser on iOS, macOS and Android (checked in 21.2 on iOS 14, macOS and Android)
      'yandex',
      // Yandex Browser on iOS (checked in 21.2 on 14)
      '__yb', '__ybro',
      // Firefox on iOS (checked in 32 on 14)
      '__firefox__',
      // Edge on iOS (checked in 46 on 14)
      '__edgeTrackingPreventionStatistics', 'webkit',
      // Opera Touch on iOS (checked in 2.6 on 14)
      'oprt',
      // Samsung Internet on Android (checked in 11.1)
      'samsungAr',
      // UC Browser on Android (checked in 12.10 and 13.0)
      'ucweb', 'UCShellJava',
      // Puffin on Android (checked in 9.0)
      'puffinDevice']; _i < _a.length; _i++) {
      var key = _a[_i];
      var value = window[key];
      if (value && typeof value === 'object') {
        flavors.push(key);
      }
    }
    return flavors.sort();
  }

  /**
   * navigator.cookieEnabled cannot detect custom or nuanced cookie blocking configurations. For example, when blocking
   * cookies via the Advanced Privacy Settings in IE9, it always returns true. And there have been issues in the past with
   * site-specific exceptions. Don't rely on it.
   *
   * @see https://github.com/Modernizr/Modernizr/blob/master/feature-detects/cookies.js Taken from here
   */
  function areCookiesEnabled() {
    var d = document;
    // Taken from here: https://github.com/Modernizr/Modernizr/blob/master/feature-detects/cookies.js
    // navigator.cookieEnabled cannot detect custom or nuanced cookie blocking configurations. For example, when blocking
    // cookies via the Advanced Privacy Settings in IE9, it always returns true. And there have been issues in the past
    // with site-specific exceptions. Don't rely on it.
    // try..catch because some in situations `document.cookie` is exposed but throws a
    // SecurityError if you try to access it; e.g. documents created from data URIs
    // or in sandboxed iframes (depending on flags/context)
    try {
      // Create cookie
      d.cookie = 'cookietest=1; SameSite=Strict;';
      var result = d.cookie.indexOf('cookietest=') !== -1;
      // Delete cookie
      d.cookie = 'cookietest=1; SameSite=Strict; expires=Thu, 01-Jan-1970 00:00:01 GMT';
      return result;
    } catch (e) {
      return false;
    }
  }

  /**
   * Only single element selector are supported (no operators like space, +, >, etc).
   * `embed` and `position: fixed;` will be considered as blocked anyway because it always has no offsetParent.
   * Avoid `iframe` and anything with `[src=]` because they produce excess HTTP requests.
   *
   * See docs/content_blockers.md to learn how to make the list
   */
  var filters = {
    abpIndo: ['#Iklan-Melayang', '#Kolom-Iklan-728', '#SidebarIklan-wrapper', 'a[title="7naga poker" i]', '[title="ALIENBOLA" i]'],
    abpvn: ['#quangcaomb', '.iosAdsiosAds-layout', '.quangcao', '[href^="https://r88.vn/"]', '[href^="https://zbet.vn/"]'],
    adBlockFinland: ['.mainostila', '.sponsorit', '.ylamainos', 'a[href*="/clickthrgh.asp?"]', 'a[href^="https://app.readpeak.com/ads"]'],
    adBlockPersian: ['#navbar_notice_50', 'a[href^="http://g1.v.fwmrm.net/ad/"]', '.kadr', 'TABLE[width="140px"]', '#divAgahi'],
    adBlockWarningRemoval: ['#adblock-honeypot', '.adblocker-root', '.wp_adblock_detect'],
    adGuardAnnoyances: ['amp-embed[type="zen"]', '.hs-sosyal', '#cookieconsentdiv', 'div[class^="app_gdpr"]', '.as-oil'],
    adGuardBase: ['#ad-after', '#ad-p3', '.BetterJsPopOverlay', '#ad_300X250', '#bannerfloat22'],
    adGuardChinese: [
    // Disabled because not reproducible. Will be replaced during the next filter update.
    // '#piao_div_0[style*="width:140px;"]',
    'a[href*=".ttz5.cn"]', 'a[href*=".yabovip2027.com/"]', '.tm3all2h4b', '.cc5278_banner_ad'],
    adGuardFrench: ['.zonepub', '[class*="_adLeaderboard"]', '[id^="block-xiti_oas-"]', 'a[href^="http://ptapjmp.com/"]', 'a[href^="https://go.alvexo.com/"]'],
    adGuardGerman: ['.banneritemwerbung_head_1', '.boxstartwerbung', '.werbung3', 'a[href^="http://www.eis.de/index.phtml?refid="]', 'a[href^="https://www.tipico.com/?affiliateId="]'],
    adGuardJapanese: ['#kauli_yad_1', '#ad-giftext', '#adsSPRBlock', 'a[href^="http://ad2.trafficgate.net/"]', 'a[href^="http://www.rssad.jp/"]'],
    adGuardMobile: ['amp-auto-ads', '#mgid_iframe', '.amp_ad', 'amp-embed[type="24smi"]', '#mgid_iframe1'],
    adGuardRussian: ['a[href^="https://ya-distrib.ru/r/"]', 'a[href^="https://ad.letmeads.com/"]', '.reclama', 'div[id^="smi2adblock"]', 'div[id^="AdFox_banner_"]'],
    adGuardSocial: ['a[href^="//www.stumbleupon.com/submit?url="]', 'a[href^="//telegram.me/share/url?"]', '.etsy-tweet', '#inlineShare', '.popup-social'],
    adGuardSpanishPortuguese: ['#barraPublicidade', '#Publicidade', '#publiEspecial', '#queTooltip', '[href^="http://ads.glispa.com/"]'],
    adGuardTrackingProtection: ['amp-embed[type="taboola"]', '#qoo-counter', 'a[href^="http://click.hotlog.ru/"]', 'a[href^="http://hitcounter.ru/top/stat.php"]', 'a[href^="http://top.mail.ru/jump"]'],
    adGuardTurkish: ['#backkapat', '#reklami', 'a[href^="http://adserv.ontek.com.tr/"]', 'a[href^="http://izlenzi.com/campaign/"]', 'a[href^="http://www.installads.net/"]'],
    bulgarian: ['td#freenet_table_ads', '#adbody', '#ea_intext_div', '.lapni-pop-over', '#xenium_hot_offers'],
    easyList: ['#AD_banner_bottom', '#Ads_google_02', '#N-ad-article-rightRail-1', '#ad-fullbanner2', '#ad-zone-2'],
    easyListChina: ['a[href*=".wensixuetang.com/"]', 'A[href*="/hth107.com/"]', '.appguide-wrap[onclick*="bcebos.com"]', '.frontpageAdvM', '#taotaole'],
    easyListCookie: ['#adtoniq-msg-bar', '#CoockiesPage', '#CookieModal_cookiemodal', '#DO_CC_PANEL', '#ShowCookie'],
    easyListCzechSlovak: ['#onlajny-stickers', '#reklamni-box', '.reklama-megaboard', '.sklik', '[id^="sklikReklama"]'],
    easyListDutch: ['#advertentie', '#vipAdmarktBannerBlock', '.adstekst', 'a[href^="https://xltube.nl/click/"]', '#semilo-lrectangle'],
    easyListGermany: ['a[href^="http://www.hw-area.com/?dp="]', 'a[href^="https://ads.sunmaker.com/tracking.php?"]', '.werbung-skyscraper2', '.bannergroup_werbung', '.ads_rechts'],
    easyListItaly: ['.box_adv_annunci', '.sb-box-pubbliredazionale', 'a[href^="http://affiliazioniads.snai.it/"]', 'a[href^="https://adserver.html.it/"]', 'a[href^="https://affiliazioniads.snai.it/"]'],
    easyListLithuania: ['.reklamos_tarpas', '.reklamos_nuorodos', 'img[alt="Reklaminis skydelis"]', 'img[alt="Dedikuoti.lt serveriai"]', 'img[alt="Hostingas Serveriai.lt"]'],
    estonian: ['A[href*="http://pay4results24.eu"]'],
    fanboyAnnoyances: ['#feedback-tab', '#taboola-below-article', '.feedburnerFeedBlock', '.widget-feedburner-counter', '[title="Subscribe to our blog"]'],
    fanboyAntiFacebook: ['.util-bar-module-firefly-visible'],
    fanboyEnhancedTrackers: ['.open.pushModal', '#issuem-leaky-paywall-articles-zero-remaining-nag', '#sovrn_container', 'div[class$="-hide"][zoompage-fontsize][style="display: block;"]', '.BlockNag__Card'],
    fanboySocial: ['.td-tags-and-social-wrapper-box', '.twitterContainer', '.youtube-social', 'a[title^="Like us on Facebook"]', 'img[alt^="Share on Digg"]'],
    frellwitSwedish: ['a[href*="casinopro.se"][target="_blank"]', 'a[href*="doktor-se.onelink.me"]', 'article.category-samarbete', 'div.holidAds', 'ul.adsmodern'],
    greekAdBlock: ['A[href*="adman.otenet.gr/click?"]', 'A[href*="http://axiabanners.exodus.gr/"]', 'A[href*="http://interactive.forthnet.gr/click?"]', 'DIV.agores300', 'TABLE.advright'],
    hungarian: ['A[href*="ad.eval.hu"]', 'A[href*="ad.netmedia.hu"]', 'A[href*="daserver.ultraweb.hu"]', '#cemp_doboz', '.optimonk-iframe-container'],
    iDontCareAboutCookies: ['.alert-info[data-block-track*="CookieNotice"]', '.ModuleTemplateCookieIndicator', '.o--cookies--container', '.cookie-msg-info-container', '#cookies-policy-sticky'],
    icelandicAbp: ['A[href^="/framework/resources/forms/ads.aspx"]'],
    latvian: ['a[href="http://www.salidzini.lv/"][style="display: block; width: 120px; height: 40px; overflow: hidden; position: relative;"]', 'a[href="http://www.salidzini.lv/"][style="display: block; width: 88px; height: 31px; overflow: hidden; position: relative;"]'],
    listKr: ['a[href*="//kingtoon.slnk.kr"]', 'a[href*="//playdsb.com/kr"]', 'div.logly-lift-adz', 'div[data-widget_id="ml6EJ074"]', 'ins.daum_ddn_area'],
    listeAr: ['.geminiLB1Ad', '.right-and-left-sponsers', 'a[href*=".aflam.info"]', 'a[href*="booraq.org"]', 'a[href*="dubizzle.com/ar/?utm_source="]'],
    listeFr: ['a[href^="http://promo.vador.com/"]', '#adcontainer_recherche', 'a[href*="weborama.fr/fcgi-bin/"]', '.site-pub-interstitiel', 'div[id^="crt-"][data-criteo-id]'],
    officialPolish: ['#ceneo-placeholder-ceneo-12', '[href^="https://aff.sendhub.pl/"]', 'a[href^="http://advmanager.techfun.pl/redirect/"]', 'a[href^="http://www.trizer.pl/?utm_source"]', 'div#skapiec_ad'],
    ro: ['a[href^="//afftrk.altex.ro/Counter/Click"]', 'a[href^="/magazin/"]', 'a[href^="https://blackfridaysales.ro/trk/shop/"]', 'a[href^="https://event.2performant.com/events/click"]', 'a[href^="https://l.profitshare.ro/"]'],
    ruAd: ['a[href*="//febrare.ru/"]', 'a[href*="//utimg.ru/"]', 'a[href*="://chikidiki.ru"]', '#pgeldiz', '.yandex-rtb-block'],
    thaiAds: ['a[href*=macau-uta-popup]', '#ads-google-middle_rectangle-group', '.ads300s', '.bumq', '.img-kosana'],
    webAnnoyancesUltralist: ['#mod-social-share-2', '#social-tools', '.ctpl-fullbanner', '.zergnet-recommend', '.yt.btn-link.btn-md.btn']
  };
  /**
   * The order of the returned array means nothing (it's always sorted alphabetically).
   *
   * Notice that the source is slightly unstable.
   * Safari provides a 2-taps way to disable all content blockers on a page temporarily.
   * Also content blockers can be disabled permanently for a domain, but it requires 4 taps.
   * So empty array shouldn't be treated as "no blockers", it should be treated as "no signal".
   * If you are a website owner, don't make your visitors want to disable content blockers.
   */
  function getDomBlockers(_a) {
    var debug = (_a === void 0 ? {} : _a).debug;
    return __awaiter(this, void 0, void 0, function () {
      var filterNames, allSelectors, blockedSelectors, activeBlockers;
      var _b;
      return __generator(this, function (_c) {
        switch (_c.label) {
          case 0:
            if (!isApplicable()) {
              return [2 /*return*/, undefined];
            }
            filterNames = Object.keys(filters);
            allSelectors = (_b = []).concat.apply(_b, filterNames.map(function (filterName) {
              return filters[filterName];
            }));
            return [4 /*yield*/, getBlockedSelectors(allSelectors)];
          case 1:
            blockedSelectors = _c.sent();
            if (debug) {
              printDebug(blockedSelectors);
            }
            activeBlockers = filterNames.filter(function (filterName) {
              var selectors = filters[filterName];
              var blockedCount = countTruthy(selectors.map(function (selector) {
                return blockedSelectors[selector];
              }));
              return blockedCount > selectors.length * 0.6;
            });
            activeBlockers.sort();
            return [2 /*return*/, activeBlockers];
        }
      });
    });
  }
  function isApplicable() {
    // Safari (desktop and mobile) and all Android browsers keep content blockers in both regular and private mode
    return isWebKit() || isAndroid();
  }
  function getBlockedSelectors(selectors) {
    var _a;
    return __awaiter(this, void 0, void 0, function () {
      var d, root, elements, blockedSelectors, i, element, holder, i;
      return __generator(this, function (_b) {
        switch (_b.label) {
          case 0:
            d = document;
            root = d.createElement('div');
            elements = new Array(selectors.length);
            blockedSelectors = {} // Set() isn't used just in case somebody need older browser support
            ;

            forceShow(root);
            // First create all elements that can be blocked. If the DOM steps below are done in a single cycle,
            // browser will alternate tree modification and layout reading, that is very slow.
            for (i = 0; i < selectors.length; ++i) {
              element = selectorToElement(selectors[i]);
              holder = d.createElement('div') // Protects from unwanted effects of `+` and `~` selectors of filters
              ;

              forceShow(holder);
              holder.appendChild(element);
              root.appendChild(holder);
              elements[i] = element;
            }
            _b.label = 1;
          case 1:
            if (!!d.body) return [3 /*break*/, 3];
            return [4 /*yield*/, wait(50)];
          case 2:
            _b.sent();
            return [3 /*break*/, 1];
          case 3:
            d.body.appendChild(root);
            try {
              // Then check which of the elements are blocked
              for (i = 0; i < selectors.length; ++i) {
                if (!elements[i].offsetParent) {
                  blockedSelectors[selectors[i]] = true;
                }
              }
            } finally {
              // Then remove the elements
              (_a = root.parentNode) === null || _a === void 0 ? void 0 : _a.removeChild(root);
            }
            return [2 /*return*/, blockedSelectors];
        }
      });
    });
  }
  function forceShow(element) {
    element.style.setProperty('display', 'block', 'important');
  }
  function printDebug(blockedSelectors) {
    var message = 'DOM blockers debug:\n```';
    for (var _i = 0, _a = Object.keys(filters); _i < _a.length; _i++) {
      var filterName = _a[_i];
      message += "\n" + filterName + ":";
      for (var _b = 0, _c = filters[filterName]; _b < _c.length; _b++) {
        var selector = _c[_b];
        message += "\n  " + selector + " " + (blockedSelectors[selector] ? '🚫' : '➡️');
      }
    }
    // console.log is ok here because it's under a debug clause
    // eslint-disable-next-line no-console
    console.log(message + "\n```");
  }

  /**
   * @see https://developer.mozilla.org/en-US/docs/Web/CSS/@media/color-gamut
   */
  function getColorGamut() {
    // rec2020 includes p3 and p3 includes srgb
    for (var _i = 0, _a = ['rec2020', 'p3', 'srgb']; _i < _a.length; _i++) {
      var gamut = _a[_i];
      if (matchMedia("(color-gamut: " + gamut + ")").matches) {
        return gamut;
      }
    }
    return undefined;
  }

  /**
   * @see https://developer.mozilla.org/en-US/docs/Web/CSS/@media/inverted-colors
   */
  function areColorsInverted() {
    if (doesMatch('inverted')) {
      return true;
    }
    if (doesMatch('none')) {
      return false;
    }
    return undefined;
  }
  function doesMatch(value) {
    return matchMedia("(inverted-colors: " + value + ")").matches;
  }

  /**
   * @see https://developer.mozilla.org/en-US/docs/Web/CSS/@media/forced-colors
   */
  function areColorsForced() {
    if (doesMatch$1('active')) {
      return true;
    }
    if (doesMatch$1('none')) {
      return false;
    }
    return undefined;
  }
  function doesMatch$1(value) {
    return matchMedia("(forced-colors: " + value + ")").matches;
  }
  var maxValueToCheck = 100;
  /**
   * If the display is monochrome (e.g. black&white), the value will be ≥0 and will mean the number of bits per pixel.
   * If the display is not monochrome, the returned value will be 0.
   * If the browser doesn't support this feature, the returned value will be undefined.
   *
   * @see https://developer.mozilla.org/en-US/docs/Web/CSS/@media/monochrome
   */
  function getMonochromeDepth() {
    if (!matchMedia('(min-monochrome: 0)').matches) {
      // The media feature isn't supported by the browser
      return undefined;
    }
    // A variation of binary search algorithm can be used here.
    // But since expected values are very small (≤10), there is no sense in adding the complexity.
    for (var i = 0; i <= maxValueToCheck; ++i) {
      if (matchMedia("(max-monochrome: " + i + ")").matches) {
        return i;
      }
    }
    throw new Error('Too high value');
  }

  /**
   * @see https://www.w3.org/TR/mediaqueries-5/#prefers-contrast
   * @see https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-contrast
   */
  function getContrastPreference() {
    if (doesMatch$2('no-preference')) {
      return 0 /* None */;
    }
    // The sources contradict on the keywords. Probably 'high' and 'low' will never be implemented.
    // Need to check it when all browsers implement the feature.
    if (doesMatch$2('high') || doesMatch$2('more')) {
      return 1 /* More */;
    }

    if (doesMatch$2('low') || doesMatch$2('less')) {
      return -1 /* Less */;
    }

    if (doesMatch$2('forced')) {
      return 10 /* ForcedColors */;
    }

    return undefined;
  }
  function doesMatch$2(value) {
    return matchMedia("(prefers-contrast: " + value + ")").matches;
  }

  /**
   * @see https://developer.mozilla.org/en-US/docs/Web/CSS/@media/prefers-reduced-motion
   */
  function isMotionReduced() {
    if (doesMatch$3('reduce')) {
      return true;
    }
    if (doesMatch$3('no-preference')) {
      return false;
    }
    return undefined;
  }
  function doesMatch$3(value) {
    return matchMedia("(prefers-reduced-motion: " + value + ")").matches;
  }

  /**
   * @see https://www.w3.org/TR/mediaqueries-5/#dynamic-range
   */
  function isHDR() {
    if (doesMatch$4('high')) {
      return true;
    }
    if (doesMatch$4('standard')) {
      return false;
    }
    return undefined;
  }
  function doesMatch$4(value) {
    return matchMedia("(dynamic-range: " + value + ")").matches;
  }
  var M = Math; // To reduce the minified code size
  var fallbackFn = function () {
    return 0;
  };
  /**
   * @see https://gitlab.torproject.org/legacy/trac/-/issues/13018
   * @see https://bugzilla.mozilla.org/show_bug.cgi?id=531915
   */
  function getMathFingerprint() {
    // Native operations
    var acos = M.acos || fallbackFn;
    var acosh = M.acosh || fallbackFn;
    var asin = M.asin || fallbackFn;
    var asinh = M.asinh || fallbackFn;
    var atanh = M.atanh || fallbackFn;
    var atan = M.atan || fallbackFn;
    var sin = M.sin || fallbackFn;
    var sinh = M.sinh || fallbackFn;
    var cos = M.cos || fallbackFn;
    var cosh = M.cosh || fallbackFn;
    var tan = M.tan || fallbackFn;
    var tanh = M.tanh || fallbackFn;
    var exp = M.exp || fallbackFn;
    var expm1 = M.expm1 || fallbackFn;
    var log1p = M.log1p || fallbackFn;
    // Operation polyfills
    var powPI = function (value) {
      return M.pow(M.PI, value);
    };
    var acoshPf = function (value) {
      return M.log(value + M.sqrt(value * value - 1));
    };
    var asinhPf = function (value) {
      return M.log(value + M.sqrt(value * value + 1));
    };
    var atanhPf = function (value) {
      return M.log((1 + value) / (1 - value)) / 2;
    };
    var sinhPf = function (value) {
      return M.exp(value) - 1 / M.exp(value) / 2;
    };
    var coshPf = function (value) {
      return (M.exp(value) + 1 / M.exp(value)) / 2;
    };
    var expm1Pf = function (value) {
      return M.exp(value) - 1;
    };
    var tanhPf = function (value) {
      return (M.exp(2 * value) - 1) / (M.exp(2 * value) + 1);
    };
    var log1pPf = function (value) {
      return M.log(1 + value);
    };
    // Note: constant values are empirical
    return {
      acos: acos(0.123124234234234242),
      acosh: acosh(1e308),
      acoshPf: acoshPf(1e154),
      asin: asin(0.123124234234234242),
      asinh: asinh(1),
      asinhPf: asinhPf(1),
      atanh: atanh(0.5),
      atanhPf: atanhPf(0.5),
      atan: atan(0.5),
      sin: sin(-1e300),
      sinh: sinh(1),
      sinhPf: sinhPf(1),
      cos: cos(10.000000000123),
      cosh: cosh(1),
      coshPf: coshPf(1),
      tan: tan(-1e300),
      tanh: tanh(1),
      tanhPf: tanhPf(1),
      exp: exp(1),
      expm1: expm1(1),
      expm1Pf: expm1Pf(1),
      log1p: log1p(10),
      log1pPf: log1pPf(10),
      powPI: powPI(-100)
    };
  }

  /**
   * We use m or w because these two characters take up the maximum width.
   * Also there are a couple of ligatures.
   */
  var defaultText = 'mmMwWLliI0fiflO&1';
  /**
   * Settings of text blocks to measure. The keys are random but persistent words.
   */
  var presets = {
    /**
     * The default font. User can change it in desktop Chrome, desktop Firefox, IE 11,
     * Android Chrome (but only when the size is ≥ than the default) and Android Firefox.
     */
    default: [],
    /** OS font on macOS. User can change its size and weight. Applies after Safari restart. */
    apple: [{
      font: '-apple-system-body'
    }],
    /** User can change it in desktop Chrome and desktop Firefox. */
    serif: [{
      fontFamily: 'serif'
    }],
    /** User can change it in desktop Chrome and desktop Firefox. */
    sans: [{
      fontFamily: 'sans-serif'
    }],
    /** User can change it in desktop Chrome and desktop Firefox. */
    mono: [{
      fontFamily: 'monospace'
    }],
    /**
     * Check the smallest allowed font size. User can change it in desktop Chrome, desktop Firefox and desktop Safari.
     * The height can be 0 in Chrome on a retina display.
     */
    min: [{
      fontSize: '1px'
    }],
    /** Tells one OS from another in desktop Chrome. */
    system: [{
      fontFamily: 'system-ui'
    }]
  };
  /**
   * The result is a dictionary of the width of the text samples.
   * Heights aren't included because they give no extra entropy and are unstable.
   *
   * The result is very stable in IE 11, Edge 18 and Safari 14.
   * The result changes when the OS pixel density changes in Chromium 87. The real pixel density is required to solve,
   * but seems like it's impossible: https://stackoverflow.com/q/1713771/1118709.
   * The "min" and the "mono" (only on Windows) value may change when the page is zoomed in Firefox 87.
   */
  function getFontPreferences() {
    return withNaturalFonts(function (document, container) {
      var elements = {};
      var sizes = {};
      // First create all elements to measure. If the DOM steps below are done in a single cycle,
      // browser will alternate tree modification and layout reading, that is very slow.
      for (var _i = 0, _a = Object.keys(presets); _i < _a.length; _i++) {
        var key = _a[_i];
        var _b = presets[key],
          _c = _b[0],
          style = _c === void 0 ? {} : _c,
          _d = _b[1],
          text = _d === void 0 ? defaultText : _d;
        var element = document.createElement('span');
        element.textContent = text;
        element.style.whiteSpace = 'nowrap';
        for (var _e = 0, _f = Object.keys(style); _e < _f.length; _e++) {
          var name_1 = _f[_e];
          var value = style[name_1];
          if (value !== undefined) {
            element.style[name_1] = value;
          }
        }
        elements[key] = element;
        container.appendChild(document.createElement('br'));
        container.appendChild(element);
      }
      // Then measure the created elements
      for (var _g = 0, _h = Object.keys(presets); _g < _h.length; _g++) {
        var key = _h[_g];
        sizes[key] = elements[key].getBoundingClientRect().width;
      }
      return sizes;
    });
  }
  /**
   * Creates a DOM environment that provides the most natural font available, including Android OS font.
   * Measurements of the elements are zoom-independent.
   * Don't put a content to measure inside an absolutely positioned element.
   */
  function withNaturalFonts(action, containerWidthPx) {
    if (containerWidthPx === void 0) {
      containerWidthPx = 4000;
    }
    /*
     * Requirements for Android Chrome to apply the system font size to a text inside an iframe:
     * - The iframe mustn't have a `display: none;` style;
     * - The text mustn't be positioned absolutely;
     * - The text block must be wide enough.
     *   2560px on some devices in portrait orientation for the biggest font size option (32px);
     * - There must be much enough text to form a few lines (I don't know the exact numbers);
     * - The text must have the `text-size-adjust: none` style. Otherwise the text will scale in "Desktop site" mode;
     *
     * Requirements for Android Firefox to apply the system font size to a text inside an iframe:
     * - The iframe document must have a header: `<meta name="viewport" content="width=device-width, initial-scale=1" />`.
     *   The only way to set it is to use the `srcdoc` attribute of the iframe;
     * - The iframe content must get loaded before adding extra content with JavaScript;
     *
     * https://example.com as the iframe target always inherits Android font settings so it can be used as a reference.
     *
     * Observations on how page zoom affects the measurements:
     * - macOS Safari 11.1, 12.1, 13.1, 14.0: zoom reset + offsetWidth = 100% reliable;
     * - macOS Safari 11.1, 12.1, 13.1, 14.0: zoom reset + getBoundingClientRect = 100% reliable;
     * - macOS Safari 14.0: offsetWidth = 5% fluctuation;
     * - macOS Safari 14.0: getBoundingClientRect = 5% fluctuation;
     * - iOS Safari 9, 10, 11.0, 12.0: haven't found a way to zoom a page (pinch doesn't change layout);
     * - iOS Safari 13.1, 14.0: zoom reset + offsetWidth = 100% reliable;
     * - iOS Safari 13.1, 14.0: zoom reset + getBoundingClientRect = 100% reliable;
     * - iOS Safari 14.0: offsetWidth = 100% reliable;
     * - iOS Safari 14.0: getBoundingClientRect = 100% reliable;
     * - Chrome 42, 65, 80, 87: zoom 1/devicePixelRatio + offsetWidth = 1px fluctuation;
     * - Chrome 42, 65, 80, 87: zoom 1/devicePixelRatio + getBoundingClientRect = 100% reliable;
     * - Chrome 87: offsetWidth = 1px fluctuation;
     * - Chrome 87: getBoundingClientRect = 0.7px fluctuation;
     * - Firefox 48, 51: offsetWidth = 10% fluctuation;
     * - Firefox 48, 51: getBoundingClientRect = 10% fluctuation;
     * - Firefox 52, 53, 57, 62, 66, 67, 68, 71, 75, 80, 84: offsetWidth = width 100% reliable, height 10% fluctuation;
     * - Firefox 52, 53, 57, 62, 66, 67, 68, 71, 75, 80, 84: getBoundingClientRect = width 100% reliable, height 10%
     *   fluctuation;
     * - Android Chrome 86: haven't found a way to zoom a page (pinch doesn't change layout);
     * - Android Firefox 84: font size in accessibility settings changes all the CSS sizes, but offsetWidth and
     *   getBoundingClientRect keep measuring with regular units, so the size reflects the font size setting and doesn't
     *   fluctuate;
     * - IE 11, Edge 18: zoom 1/devicePixelRatio + offsetWidth = 100% reliable;
     * - IE 11, Edge 18: zoom 1/devicePixelRatio + getBoundingClientRect = reflects the zoom level;
     * - IE 11, Edge 18: offsetWidth = 100% reliable;
     * - IE 11, Edge 18: getBoundingClientRect = 100% reliable;
     */
    return withIframe(function (_, iframeWindow) {
      var iframeDocument = iframeWindow.document;
      var iframeBody = iframeDocument.body;
      var bodyStyle = iframeBody.style;
      bodyStyle.width = containerWidthPx + "px";
      bodyStyle.webkitTextSizeAdjust = bodyStyle.textSizeAdjust = 'none';
      // See the big comment above
      if (isChromium()) {
        iframeBody.style.zoom = "" + 1 / iframeWindow.devicePixelRatio;
      } else if (isWebKit()) {
        iframeBody.style.zoom = 'reset';
      }
      // See the big comment above
      var linesOfText = iframeDocument.createElement('div');
      linesOfText.textContent = __spreadArrays(Array(containerWidthPx / 20 << 0)).map(function () {
        return 'word';
      }).join(' ');
      iframeBody.appendChild(linesOfText);
      return action(iframeDocument, iframeBody);
    }, '<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1">');
  }

  /**
   * The list of entropy sources used to make visitor identifiers.
   *
   * This value isn't restricted by Semantic Versioning, i.e. it may be changed without bumping minor or major version of
   * this package.
   */
  var sources = {
    // READ FIRST:
    // See https://github.com/fingerprintjs/fingerprintjs/blob/master/contributing.md#how-to-make-an-entropy-source
    // to learn how entropy source works and how to make your own.
    // The sources run in this exact order.
    // The asynchronous sources are at the start to run in parallel with other sources.
    fonts: getFonts,
    domBlockers: getDomBlockers,
    fontPreferences: getFontPreferences,
    audio: getAudioFingerprint,
    screenFrame: getRoundedScreenFrame,
    osCpu: getOsCpu,
    languages: getLanguages,
    colorDepth: getColorDepth,
    deviceMemory: getDeviceMemory,
    screenResolution: getScreenResolution,
    hardwareConcurrency: getHardwareConcurrency,
    timezone: getTimezone,
    sessionStorage: getSessionStorage,
    localStorage: getLocalStorage,
    indexedDB: getIndexedDB,
    openDatabase: getOpenDatabase,
    cpuClass: getCpuClass,
    platform: getPlatform,
    plugins: getPlugins,
    canvas: getCanvasFingerprint,
    touchSupport: getTouchSupport,
    vendor: getVendor,
    vendorFlavors: getVendorFlavors,
    cookiesEnabled: areCookiesEnabled,
    colorGamut: getColorGamut,
    invertedColors: areColorsInverted,
    forcedColors: areColorsForced,
    monochrome: getMonochromeDepth,
    contrast: getContrastPreference,
    reducedMotion: isMotionReduced,
    hdr: isHDR,
    math: getMathFingerprint
  };
  /**
   * Loads the built-in entropy sources.
   * Returns a function that collects the entropy components to make the visitor identifier.
   */
  function loadBuiltinSources(options) {
    return loadSources(sources, options, []);
  }
  var commentTemplate = '$ if upgrade to Pro: https://fpjs.dev/pro';
  function getConfidence(components) {
    var openConfidenceScore = getOpenConfidenceScore(components);
    var proConfidenceScore = deriveProConfidenceScore(openConfidenceScore);
    return {
      score: openConfidenceScore,
      comment: commentTemplate.replace(/\$/g, "" + proConfidenceScore)
    };
  }
  function getOpenConfidenceScore(components) {
    // In order to calculate the true probability of the visitor identifier being correct, we need to know the number of
    // website visitors (the higher the number, the less the probability because the fingerprint entropy is limited).
    // JS agent doesn't know the number of visitors, so we can only do an approximate assessment.
    if (isAndroid()) {
      return 0.4;
    }
    // Safari (mobile and desktop)
    if (isWebKit()) {
      return isDesktopSafari() ? 0.5 : 0.3;
    }
    var platform = components.platform.value || '';
    // Windows
    if (/^Win/.test(platform)) {
      // The score is greater than on macOS because of the higher variety of devices running Windows.
      // Chrome provides more entropy than Firefox according too
      // https://netmarketshare.com/browser-market-share.aspx?options=%7B%22filter%22%3A%7B%22%24and%22%3A%5B%7B%22platform%22%3A%7B%22%24in%22%3A%5B%22Windows%22%5D%7D%7D%5D%7D%2C%22dateLabel%22%3A%22Trend%22%2C%22attributes%22%3A%22share%22%2C%22group%22%3A%22browser%22%2C%22sort%22%3A%7B%22share%22%3A-1%7D%2C%22id%22%3A%22browsersDesktop%22%2C%22dateInterval%22%3A%22Monthly%22%2C%22dateStart%22%3A%222019-11%22%2C%22dateEnd%22%3A%222020-10%22%2C%22segments%22%3A%22-1000%22%7D
      // So we assign the same score to them.
      return 0.6;
    }
    // macOS
    if (/^Mac/.test(platform)) {
      // Chrome provides more entropy than Safari and Safari provides more entropy than Firefox.
      // Chrome is more popular than Safari and Safari is more popular than Firefox according to
      // https://netmarketshare.com/browser-market-share.aspx?options=%7B%22filter%22%3A%7B%22%24and%22%3A%5B%7B%22platform%22%3A%7B%22%24in%22%3A%5B%22Mac%20OS%22%5D%7D%7D%5D%7D%2C%22dateLabel%22%3A%22Trend%22%2C%22attributes%22%3A%22share%22%2C%22group%22%3A%22browser%22%2C%22sort%22%3A%7B%22share%22%3A-1%7D%2C%22id%22%3A%22browsersDesktop%22%2C%22dateInterval%22%3A%22Monthly%22%2C%22dateStart%22%3A%222019-11%22%2C%22dateEnd%22%3A%222020-10%22%2C%22segments%22%3A%22-1000%22%7D
      // So we assign the same score to them.
      return 0.5;
    }
    // Another platform, e.g. a desktop Linux. It's rare, so it should be pretty unique.
    return 0.7;
  }
  function deriveProConfidenceScore(openConfidenceScore) {
    return round(0.99 + 0.01 * openConfidenceScore, 0.0001);
  }
  function componentsToCanonicalString(components) {
    var result = '';
    for (var _i = 0, _a = Object.keys(components).sort(); _i < _a.length; _i++) {
      var componentKey = _a[_i];
      var component = components[componentKey];
      var value = component.error ? 'error' : JSON.stringify(component.value);
      result += "" + (result ? '|' : '') + componentKey.replace(/([:|\\])/g, '\\$1') + ":" + value;
    }
    return result;
  }
  function componentsToDebugString(components) {
    return JSON.stringify(components, function (_key, value) {
      if (value instanceof Error) {
        return errorToObject(value);
      }
      return value;
    }, 2);
  }
  function hashComponents(components) {
    return x64hash128(componentsToCanonicalString(components));
  }
  /**
   * Makes a GetResult implementation that calculates the visitor id hash on demand.
   * Designed for optimisation.
   */
  function makeLazyGetResult(components) {
    var visitorIdCache;
    // This function runs very fast, so there is no need to make it lazy
    var confidence = getConfidence(components);
    // A plain class isn't used because its getters and setters aren't enumerable.
    return {
      get visitorId() {
        if (visitorIdCache === undefined) {
          visitorIdCache = hashComponents(this.components);
        }
        return visitorIdCache;
      },
      set visitorId(visitorId) {
        visitorIdCache = visitorId;
      },
      confidence: confidence,
      components: components,
      version: version
    };
  }
  /**
   * A delay is required to ensure consistent entropy components.
   * See https://github.com/fingerprintjs/fingerprintjs/issues/254
   * and https://github.com/fingerprintjs/fingerprintjs/issues/307
   * and https://github.com/fingerprintjs/fingerprintjs/commit/945633e7c5f67ae38eb0fea37349712f0e669b18
   */
  function prepareForSources(delayFallback) {
    if (delayFallback === void 0) {
      delayFallback = 50;
    }
    // A proper deadline is unknown. Let it be twice the fallback timeout so that both cases have the same average time.
    return requestIdleCallbackIfAvailable(delayFallback, delayFallback * 2);
  }
  /**
   * The function isn't exported from the index file to not allow to call it without `load()`.
   * The hiding gives more freedom for future non-breaking updates.
   *
   * A factory function is used instead of a class to shorten the attribute names in the minified code.
   * Native private class fields could've been used, but TypeScript doesn't allow them with `"target": "es5"`.
   */
  function makeAgent(getComponents, debug) {
    var creationTime = Date.now();
    return {
      get: function (options) {
        return __awaiter(this, void 0, void 0, function () {
          var startTime, components, result;
          return __generator(this, function (_a) {
            switch (_a.label) {
              case 0:
                startTime = Date.now();
                return [4 /*yield*/, getComponents()];
              case 1:
                components = _a.sent();
                result = makeLazyGetResult(components);
                if (debug || (options === null || options === void 0 ? void 0 : options.debug)) {
                  // console.log is ok here because it's under a debug clause
                  // eslint-disable-next-line no-console
                  console.log("Copy the text below to get the debug data:\n\n```\nversion: " + result.version + "\nuserAgent: " + navigator.userAgent + "\ntimeBetweenLoadAndGet: " + (startTime - creationTime) + "\nvisitorId: " + result.visitorId + "\ncomponents: " + componentsToDebugString(components) + "\n```");
                }
                return [2 /*return*/, result];
            }
          });
        });
      }
    };
  }
  /**
   * Sends an unpersonalized AJAX request to collect installation statistics
   */
  function monitor() {
    // The FingerprintJS CDN (https://github.com/fingerprintjs/cdn) replaces `window.__fpjs_d_m` with `true`
    if (window.__fpjs_d_m || Math.random() >= 0.001) {
      return;
    }
    try {
      var request = new XMLHttpRequest();
      request.open('get', "https://m1.openfpcdn.io/fingerprintjs/v" + version + "/npm-monitoring", true);
      request.send();
    } catch (error) {
      // console.error is ok here because it's an unexpected error handler
      // eslint-disable-next-line no-console
      console.error(error);
    }
  }
  /**
   * Builds an instance of Agent and waits a delay required for a proper operation.
   */
  function load(_a) {
    var _b = _a === void 0 ? {} : _a,
      delayFallback = _b.delayFallback,
      debug = _b.debug,
      _c = _b.monitoring,
      monitoring = _c === void 0 ? true : _c;
    return __awaiter(this, void 0, void 0, function () {
      var getComponents;
      return __generator(this, function (_d) {
        switch (_d.label) {
          case 0:
            if (monitoring) {
              monitor();
            }
            return [4 /*yield*/, prepareForSources(delayFallback)];
          case 1:
            _d.sent();
            getComponents = loadBuiltinSources({
              debug: debug
            });
            return [2 /*return*/, makeAgent(getComponents, debug)];
        }
      });
    });
  }

  // The default export is a syntax sugar (`import * as FP from '...' → import FP from '...'`).
  // It should contain all the public exported values.
  var index = {
    load: load,
    hashComponents: hashComponents,
    componentsToDebugString: componentsToDebugString
  };
  var FingerprintJS = index;

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

  var tpl = Object.create(null);
  tpl['auth/main.html'] = '<div class="container container-login" style="display: none"> <div class="mdc-card"> <div class="mdc-card__content"> <img src="" alt="logo" class="logo" style="display: none"> <form class="mb-5" novalidate> <span class="text-danger"></span> <div class="form-controls mb-5"> <div class="mb-3 text-start"> <label class="form-label" for="auth-login">Логин или Email</label> <div class="control-icon position-relative"> <input type="text" name="login" class="form-control" id="auth-login" required> <i class="bi bi-person-fill"></i> </div> </div> <div class="mb-5 text-start"> <label class="form-label" for="auth-password">Пароль</label> <div class="control-icon position-relative"> <input type="password" name="password" class="form-control" id="auth-password" required> <i class="bi bi-shield-lock"></i> </div> </div> <button class="btn btn-primary w-100 py-2" type="submit">Войти</button> </div> </form> <div class="links-container"> <a class="install-button" style="display: none">Установить</a> <a href="#/registration" class="reg-button">Регистрация</a> </div> </div> </div> </div> <div class="container container-registration" style="display: none"> <div class="mdc-card"> <div class="mdc-card__content"> <img src="" alt="logo" class="logo" style="display: none"> <p class="mdc-typography--headline5">Регистрация</p> <div class="text-danger mdc-typography--subtitle2"></div> <div class="text-success mdc-typography--subtitle2"></div> <form class="mb-5" novalidate> <div class="form-controls mb-5"> <div class="mb-3 text-start"> <label class="form-label" for="registration-name">Имя</label> <input type="text" name="name" class="form-control" id="registration-name" required> </div> <div class="mb-3 text-start"> <label class="form-label" for="registration-email">Email</label> <input type="email" name="email" class="form-control" id="registration-email" required> </div> <div class="mb-3 text-start"> <label class="form-label" for="registration-pass">Пароль</label> <input type="password" name="password" class="form-control" id="registration-pass" required> </div> <div class="mb-5 text-start"> <label class="form-label" for="registration-pass2">Пароль еще раз</label> <input type="password" class="form-control" id="registration-pass2" required> </div> <button class="btn btn-primary w-100 py-2" type="submit">Зарегистрироваться</button> </div> </form> <div class="links-container"> <a class="install-button" style="display: none">Установить</a> <a href="#" class="login-button">Войти</a> </div> </div> </div> </div>';
  tpl['menu/loader.html'] = '<div id="loader"> <div role="progressbar" class="mdc-linear-progress loader-progress" aria-label="Example Progress Bar" aria-valuemin="0" aria-valuemax="1" aria-valuenow="0"> <div class="mdc-linear-progress__buffer"> <div class="mdc-linear-progress__buffer-bar"></div> <div class="mdc-linear-progress__buffer-dots"></div> </div> <div class="mdc-linear-progress__bar mdc-linear-progress__primary-bar"> <span class="mdc-linear-progress__bar-inner"></span> </div> <div class="mdc-linear-progress__bar mdc-linear-progress__secondary-bar"> <span class="mdc-linear-progress__bar-inner"></span> </div> </div> <div class="loader-block"></div> </div>';
  tpl['menu/main.html'] = '<header class="mdc-top-app-bar mdc-top-app-bar--fixed app-bar"> <div class="mdc-top-app-bar__row"> <section class="mdc-top-app-bar__section mdc-top-app-bar__section--align-start"> <button class="mdc-ripple-surface open-menu"><i class="fa-solid fa-bars"></i></button> <div class="header-title-container"> <span class="mdc-top-app-bar__title"></span> <span class="mdc-top-app-bar__subtitle"></span> </div> </section> <section class="mdc-top-app-bar__section mdc-top-app-bar__section--align-end" role="toolbar"></section> </div> </header> <aside class="menu-drawer"> <div class="menu-drawer__content"> <div class="menu-drawer__header"> <a class="module-home" href="#/"> <span class="fa-solid fa-house"></span> <h3 class="system-title"></h3> </a> </div> <ul class="menu-list level-1"></ul> </div> </aside> <div class="menu-drawer-scrim"></div> <div class="menu-drawer-swipe"></div> <div class="menu-drawer-app"> <main class="main-content"> <div class="main-wrapper"></div> </main> </div>';
  tpl['menu/module.html'] = '<li class="menu-list-item core-module core-module-<%= module.name %> <% if (module.sections && module.sections.length > 0) { %>menu-item-nested<% } %>"> <div class="item-control"> <a href="#/<%= module.name %>/<%= module.index %>" class="mdc-ripple-surface" data-module="<%= module.name %>" data-section="<%= module.index %>"> <% if (module.icon) { %> <i class="<%= module.icon %>"></i> <% } else { %> <span class="module-icon-letter"><%= module.title.trim().substring(0, 1) %></span> <% } %> <span class="menu-list-item__text"><%= module.title %></span> </a> <% if (module.sections && module.sections.length > 0) { %> <button class="menu-icon-button mdc-ripple-surface"><i class="fa-solid fa-sort-down"></i></button> <% } %> </div> <ul class="menu-list level-2"> <li class="menu-list-item core-module-section-index"> <a href="#/<%= module.name %>/<%= module.index %>" class="mdc-ripple-surface" data-module="<%= module.name %>" data-section="<%= module.index %>"> <%= module.title %> </a> </li> <% if (module.sections && module.sections.length > 0) { %> <% module.sections.forEach(function(section) { %> <li class="menu-list-item core-module-section core-module-<%= module.name %>-<%= section.name %>"> <a href="#/<%= module.name %>/<%= section.name %>" class="mdc-ripple-surface" data-module="<%= module.name %>" data-section="<%= section.name %>"> <span class="menu-list-item__text"><%= section.title %></span> </a> </li> <% }); %> <% } %> </ul> </li>';
  tpl['menu/navbar.html'] = '<ul class="navbar-nav"> <li class="nav-item dropdown cabinet-user"> <button class="btn btn-link text-dark dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown"> <% if (user.avatar) { %> <img src="<%= user.avatar %>" alt="avatar" class="rounded-circle" loading="lazy"/> <% } else { %> <i class="fa-solid fa-circle-user"></i> <% } %> </button> <ul class="dropdown-menu shadow"> <li class="cabinet-user-info"> <b class="cabinet-user-name"><%= user.name %></b><br> <span class="cabinet-user-login"><%= user.login %></span> </li> <li> <hr class="dropdown-divider"/> </li> <li> <a class="dropdown-item menu-logout" href="#"> <i class="fa-solid fa-arrow-right-from-bracket"></i> Выйти </a> </li> </ul> </li> </ul>';
  tpl['menu/preloader.html'] = '<div id="preloader"> <div class="loading-lock"></div> <div class="loading-block"> <div class="spinner-border text-secondary"> <span class="visually-hidden"></span> </div> <div class="loading-text"><%= text %></div> </div> </div>';

  function commonjsRequire(path) {
  	throw new Error('Could not dynamically require "' + path + '". Please configure the dynamicRequireTargets or/and ignoreDynamicRequires option of @rollup/plugin-commonjs appropriately for this require call to work.');
  }

  var ejs_min = {exports: {}};

  ejs_min.exports;
  (function (module, exports) {
    (function (f) {
      {
        module.exports = f();
      }
    })(function () {
      return function () {
        function r(e, n, t) {
          function o(i, f) {
            if (!n[i]) {
              if (!e[i]) {
                var c = "function" == typeof commonjsRequire && commonjsRequire;
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
          for (var u = "function" == typeof commonjsRequire && commonjsRequire, i = 0; i < t.length; i++) o(t[i]);
          return o;
        }
        return r;
      }()({
        1: [function (require, module, exports) {

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
          function Template(text, optsParam) {
            var opts = utils.hasOwnOnlyObject(optsParam);
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
          exports.hasOwnOnlyObject = function (obj) {
            var o = exports.createNullProtoObjWherePossible();
            for (var p in obj) {
              if (hasOwn(obj, p)) {
                o[p] = obj[p];
              }
            }
            return o;
          };
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
            version: "3.1.9",
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
              test: "npx jake test"
            }
          };
        }, {}]
      }, {}, [1])(1);
    });
  })(ejs_min, ejs_min.exports);
  ejs_min.exports;

  /**
   * Stores result from supportsCssVariables to avoid redundant processing to
   * detect CSS custom variable support.
   */
  var supportsCssVariables_;
  function supportsCssVariables(windowObj, forceRefresh) {
    if (forceRefresh === void 0) {
      forceRefresh = false;
    }
    var CSS = windowObj.CSS;
    var supportsCssVars = supportsCssVariables_;
    if (typeof supportsCssVariables_ === 'boolean' && !forceRefresh) {
      return supportsCssVariables_;
    }
    var supportsFunctionPresent = CSS && typeof CSS.supports === 'function';
    if (!supportsFunctionPresent) {
      return false;
    }
    var explicitlySupportsCssVars = CSS.supports('--css-vars', 'yes');
    // See: https://bugs.webkit.org/show_bug.cgi?id=154669
    // See: README section on Safari
    var weAreFeatureDetectingSafari10plus = CSS.supports('(--css-vars: yes)') && CSS.supports('color', '#00000000');
    supportsCssVars = explicitlySupportsCssVars || weAreFeatureDetectingSafari10plus;
    if (!forceRefresh) {
      supportsCssVariables_ = supportsCssVars;
    }
    return supportsCssVars;
  }
  function getNormalizedEventCoords(evt, pageOffset, clientRect) {
    if (!evt) {
      return {
        x: 0,
        y: 0
      };
    }
    var x = pageOffset.x,
      y = pageOffset.y;
    var documentX = x + clientRect.left;
    var documentY = y + clientRect.top;
    var normalizedX;
    var normalizedY;
    // Determine touch point relative to the ripple container.
    if (evt.type === 'touchstart') {
      var touchEvent = evt;
      normalizedX = touchEvent.changedTouches[0].pageX - documentX;
      normalizedY = touchEvent.changedTouches[0].pageY - documentY;
    } else {
      var mouseEvent = evt;
      normalizedX = mouseEvent.pageX - documentX;
      normalizedY = mouseEvent.pageY - documentY;
    }
    return {
      x: normalizedX,
      y: normalizedY
    };
  }

  /**
   * @license
   * Copyright 2016 Google Inc.
   *
   * Permission is hereby granted, free of charge, to any person obtaining a copy
   * of this software and associated documentation files (the "Software"), to deal
   * in the Software without restriction, including without limitation the rights
   * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   * copies of the Software, and to permit persons to whom the Software is
   * furnished to do so, subject to the following conditions:
   *
   * The above copyright notice and this permission notice shall be included in
   * all copies or substantial portions of the Software.
   *
   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   * THE SOFTWARE.
   */
  var MDCFoundation = /** @class */function () {
    function MDCFoundation(adapter) {
      if (adapter === void 0) {
        adapter = {};
      }
      this.adapter = adapter;
    }
    Object.defineProperty(MDCFoundation, "cssClasses", {
      get: function () {
        // Classes extending MDCFoundation should implement this method to return an object which exports every
        // CSS class the foundation class needs as a property. e.g. {ACTIVE: 'mdc-component--active'}
        return {};
      },
      enumerable: false,
      configurable: true
    });
    Object.defineProperty(MDCFoundation, "strings", {
      get: function () {
        // Classes extending MDCFoundation should implement this method to return an object which exports all
        // semantic strings as constants. e.g. {ARIA_ROLE: 'tablist'}
        return {};
      },
      enumerable: false,
      configurable: true
    });
    Object.defineProperty(MDCFoundation, "numbers", {
      get: function () {
        // Classes extending MDCFoundation should implement this method to return an object which exports all
        // of its semantic numbers as constants. e.g. {ANIMATION_DELAY_MS: 350}
        return {};
      },
      enumerable: false,
      configurable: true
    });
    Object.defineProperty(MDCFoundation, "defaultAdapter", {
      get: function () {
        // Classes extending MDCFoundation may choose to implement this getter in order to provide a convenient
        // way of viewing the necessary methods of an adapter. In the future, this could also be used for adapter
        // validation.
        return {};
      },
      enumerable: false,
      configurable: true
    });
    MDCFoundation.prototype.init = function () {
      // Subclasses should override this method to perform initialization routines (registering events, etc.)
    };
    MDCFoundation.prototype.destroy = function () {
      // Subclasses should override this method to perform de-initialization routines (de-registering events, etc.)
    };
    return MDCFoundation;
  }();

  /**
   * @license
   * Copyright 2016 Google Inc.
   *
   * Permission is hereby granted, free of charge, to any person obtaining a copy
   * of this software and associated documentation files (the "Software"), to deal
   * in the Software without restriction, including without limitation the rights
   * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   * copies of the Software, and to permit persons to whom the Software is
   * furnished to do so, subject to the following conditions:
   *
   * The above copyright notice and this permission notice shall be included in
   * all copies or substantial portions of the Software.
   *
   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   * THE SOFTWARE.
   */
  var MDCComponent = /** @class */function () {
    function MDCComponent(root, foundation) {
      var args = [];
      for (var _i = 2; _i < arguments.length; _i++) {
        args[_i - 2] = arguments[_i];
      }
      this.root = root;
      this.initialize.apply(this, __spreadArray([], __read(args)));
      // Note that we initialize foundation here and not within the constructor's
      // default param so that this.root is defined and can be used within the
      // foundation class.
      this.foundation = foundation === undefined ? this.getDefaultFoundation() : foundation;
      this.foundation.init();
      this.initialSyncWithDOM();
    }
    MDCComponent.attachTo = function (root) {
      // Subclasses which extend MDCBase should provide an attachTo() method that takes a root element and
      // returns an instantiated component with its root set to that element. Also note that in the cases of
      // subclasses, an explicit foundation class will not have to be passed in; it will simply be initialized
      // from getDefaultFoundation().
      return new MDCComponent(root, new MDCFoundation({}));
    };
    /* istanbul ignore next: method param only exists for typing purposes; it does not need to be unit tested */
    MDCComponent.prototype.initialize = function () {
      // Subclasses can override this to do any additional setup work that would be considered part of a
      // "constructor". Essentially, it is a hook into the parent constructor before the foundation is
      // initialized. Any additional arguments besides root and foundation will be passed in here.
    };

    MDCComponent.prototype.getDefaultFoundation = function () {
      // Subclasses must override this method to return a properly configured foundation class for the
      // component.
      throw new Error('Subclasses must override getDefaultFoundation to return a properly configured ' + 'foundation class');
    };
    MDCComponent.prototype.initialSyncWithDOM = function () {
      // Subclasses should override this method if they need to perform work to synchronize with a host DOM
      // object. An example of this would be a form control wrapper that needs to synchronize its internal state
      // to some property or attribute of the host DOM. Please note: this is *not* the place to perform DOM
      // reads/writes that would cause layout / paint, as this is called synchronously from within the constructor.
    };
    MDCComponent.prototype.destroy = function () {
      // Subclasses may implement this method to release any resources / deregister any listeners they have
      // attached. An example of this might be deregistering a resize event from the window object.
      this.foundation.destroy();
    };
    MDCComponent.prototype.listen = function (evtType, handler, options) {
      this.root.addEventListener(evtType, handler, options);
    };
    MDCComponent.prototype.unlisten = function (evtType, handler, options) {
      this.root.removeEventListener(evtType, handler, options);
    };
    /**
     * Fires a cross-browser-compatible custom event from the component root of the given type, with the given data.
     */
    MDCComponent.prototype.emit = function (evtType, evtData, shouldBubble) {
      if (shouldBubble === void 0) {
        shouldBubble = false;
      }
      var evt;
      if (typeof CustomEvent === 'function') {
        evt = new CustomEvent(evtType, {
          bubbles: shouldBubble,
          detail: evtData
        });
      } else {
        evt = document.createEvent('CustomEvent');
        evt.initCustomEvent(evtType, shouldBubble, false, evtData);
      }
      this.root.dispatchEvent(evt);
    };
    return MDCComponent;
  }();

  /**
   * @license
   * Copyright 2019 Google Inc.
   *
   * Permission is hereby granted, free of charge, to any person obtaining a copy
   * of this software and associated documentation files (the "Software"), to deal
   * in the Software without restriction, including without limitation the rights
   * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   * copies of the Software, and to permit persons to whom the Software is
   * furnished to do so, subject to the following conditions:
   *
   * The above copyright notice and this permission notice shall be included in
   * all copies or substantial portions of the Software.
   *
   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   * THE SOFTWARE.
   */
  /**
   * Determine whether the current browser supports passive event listeners, and
   * if so, use them.
   */
  function applyPassive(globalObj) {
    if (globalObj === void 0) {
      globalObj = window;
    }
    return supportsPassiveOption(globalObj) ? {
      passive: true
    } : false;
  }
  function supportsPassiveOption(globalObj) {
    if (globalObj === void 0) {
      globalObj = window;
    }
    // See
    // https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener
    var passiveSupported = false;
    try {
      var options = {
        // This function will be called when the browser
        // attempts to access the passive property.
        get passive() {
          passiveSupported = true;
          return false;
        }
      };
      var handler = function () {};
      globalObj.document.addEventListener('test', handler, options);
      globalObj.document.removeEventListener('test', handler, options);
    } catch (err) {
      passiveSupported = false;
    }
    return passiveSupported;
  }

  /**
   * @license
   * Copyright 2018 Google Inc.
   *
   * Permission is hereby granted, free of charge, to any person obtaining a copy
   * of this software and associated documentation files (the "Software"), to deal
   * in the Software without restriction, including without limitation the rights
   * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   * copies of the Software, and to permit persons to whom the Software is
   * furnished to do so, subject to the following conditions:
   *
   * The above copyright notice and this permission notice shall be included in
   * all copies or substantial portions of the Software.
   *
   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   * THE SOFTWARE.
   */
  function matches(element, selector) {
    var nativeMatches = element.matches || element.webkitMatchesSelector || element.msMatchesSelector;
    return nativeMatches.call(element, selector);
  }

  /**
   * @license
   * Copyright 2016 Google Inc.
   *
   * Permission is hereby granted, free of charge, to any person obtaining a copy
   * of this software and associated documentation files (the "Software"), to deal
   * in the Software without restriction, including without limitation the rights
   * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   * copies of the Software, and to permit persons to whom the Software is
   * furnished to do so, subject to the following conditions:
   *
   * The above copyright notice and this permission notice shall be included in
   * all copies or substantial portions of the Software.
   *
   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   * THE SOFTWARE.
   */
  var cssClasses$1 = {
    // Ripple is a special case where the "root" component is really a "mixin" of sorts,
    // given that it's an 'upgrade' to an existing component. That being said it is the root
    // CSS class that all other CSS classes derive from.
    BG_FOCUSED: 'mdc-ripple-upgraded--background-focused',
    FG_ACTIVATION: 'mdc-ripple-upgraded--foreground-activation',
    FG_DEACTIVATION: 'mdc-ripple-upgraded--foreground-deactivation',
    ROOT: 'mdc-ripple-upgraded',
    UNBOUNDED: 'mdc-ripple-upgraded--unbounded'
  };
  var strings$1 = {
    VAR_FG_SCALE: '--mdc-ripple-fg-scale',
    VAR_FG_SIZE: '--mdc-ripple-fg-size',
    VAR_FG_TRANSLATE_END: '--mdc-ripple-fg-translate-end',
    VAR_FG_TRANSLATE_START: '--mdc-ripple-fg-translate-start',
    VAR_LEFT: '--mdc-ripple-left',
    VAR_TOP: '--mdc-ripple-top'
  };
  var numbers = {
    DEACTIVATION_TIMEOUT_MS: 225,
    FG_DEACTIVATION_MS: 150,
    INITIAL_ORIGIN_SCALE: 0.6,
    PADDING: 10,
    TAP_DELAY_MS: 300 // Delay between touch and simulated mouse events on touch devices
  };

  /**
   * @license
   * Copyright 2016 Google Inc.
   *
   * Permission is hereby granted, free of charge, to any person obtaining a copy
   * of this software and associated documentation files (the "Software"), to deal
   * in the Software without restriction, including without limitation the rights
   * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   * copies of the Software, and to permit persons to whom the Software is
   * furnished to do so, subject to the following conditions:
   *
   * The above copyright notice and this permission notice shall be included in
   * all copies or substantial portions of the Software.
   *
   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   * THE SOFTWARE.
   */
  // Activation events registered on the root element of each instance for activation
  var ACTIVATION_EVENT_TYPES = ['touchstart', 'pointerdown', 'mousedown', 'keydown'];
  // Deactivation events registered on documentElement when a pointer-related down event occurs
  var POINTER_DEACTIVATION_EVENT_TYPES = ['touchend', 'pointerup', 'mouseup', 'contextmenu'];
  // simultaneous nested activations
  var activatedTargets = [];
  var MDCRippleFoundation = /** @class */function (_super) {
    __extends(MDCRippleFoundation, _super);
    function MDCRippleFoundation(adapter) {
      var _this = _super.call(this, __assign(__assign({}, MDCRippleFoundation.defaultAdapter), adapter)) || this;
      _this.activationAnimationHasEnded = false;
      _this.activationTimer = 0;
      _this.fgDeactivationRemovalTimer = 0;
      _this.fgScale = '0';
      _this.frame = {
        width: 0,
        height: 0
      };
      _this.initialSize = 0;
      _this.layoutFrame = 0;
      _this.maxRadius = 0;
      _this.unboundedCoords = {
        left: 0,
        top: 0
      };
      _this.activationState = _this.defaultActivationState();
      _this.activationTimerCallback = function () {
        _this.activationAnimationHasEnded = true;
        _this.runDeactivationUXLogicIfReady();
      };
      _this.activateHandler = function (e) {
        _this.activateImpl(e);
      };
      _this.deactivateHandler = function () {
        _this.deactivateImpl();
      };
      _this.focusHandler = function () {
        _this.handleFocus();
      };
      _this.blurHandler = function () {
        _this.handleBlur();
      };
      _this.resizeHandler = function () {
        _this.layout();
      };
      return _this;
    }
    Object.defineProperty(MDCRippleFoundation, "cssClasses", {
      get: function () {
        return cssClasses$1;
      },
      enumerable: false,
      configurable: true
    });
    Object.defineProperty(MDCRippleFoundation, "strings", {
      get: function () {
        return strings$1;
      },
      enumerable: false,
      configurable: true
    });
    Object.defineProperty(MDCRippleFoundation, "numbers", {
      get: function () {
        return numbers;
      },
      enumerable: false,
      configurable: true
    });
    Object.defineProperty(MDCRippleFoundation, "defaultAdapter", {
      get: function () {
        return {
          addClass: function () {
            return undefined;
          },
          browserSupportsCssVars: function () {
            return true;
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
            return true;
          },
          deregisterDocumentInteractionHandler: function () {
            return undefined;
          },
          deregisterInteractionHandler: function () {
            return undefined;
          },
          deregisterResizeHandler: function () {
            return undefined;
          },
          getWindowPageOffset: function () {
            return {
              x: 0,
              y: 0
            };
          },
          isSurfaceActive: function () {
            return true;
          },
          isSurfaceDisabled: function () {
            return true;
          },
          isUnbounded: function () {
            return true;
          },
          registerDocumentInteractionHandler: function () {
            return undefined;
          },
          registerInteractionHandler: function () {
            return undefined;
          },
          registerResizeHandler: function () {
            return undefined;
          },
          removeClass: function () {
            return undefined;
          },
          updateCssVariable: function () {
            return undefined;
          }
        };
      },
      enumerable: false,
      configurable: true
    });
    MDCRippleFoundation.prototype.init = function () {
      var _this = this;
      var supportsPressRipple = this.supportsPressRipple();
      this.registerRootHandlers(supportsPressRipple);
      if (supportsPressRipple) {
        var _a = MDCRippleFoundation.cssClasses,
          ROOT_1 = _a.ROOT,
          UNBOUNDED_1 = _a.UNBOUNDED;
        requestAnimationFrame(function () {
          _this.adapter.addClass(ROOT_1);
          if (_this.adapter.isUnbounded()) {
            _this.adapter.addClass(UNBOUNDED_1);
            // Unbounded ripples need layout logic applied immediately to set coordinates for both shade and ripple
            _this.layoutInternal();
          }
        });
      }
    };
    MDCRippleFoundation.prototype.destroy = function () {
      var _this = this;
      if (this.supportsPressRipple()) {
        if (this.activationTimer) {
          clearTimeout(this.activationTimer);
          this.activationTimer = 0;
          this.adapter.removeClass(MDCRippleFoundation.cssClasses.FG_ACTIVATION);
        }
        if (this.fgDeactivationRemovalTimer) {
          clearTimeout(this.fgDeactivationRemovalTimer);
          this.fgDeactivationRemovalTimer = 0;
          this.adapter.removeClass(MDCRippleFoundation.cssClasses.FG_DEACTIVATION);
        }
        var _a = MDCRippleFoundation.cssClasses,
          ROOT_2 = _a.ROOT,
          UNBOUNDED_2 = _a.UNBOUNDED;
        requestAnimationFrame(function () {
          _this.adapter.removeClass(ROOT_2);
          _this.adapter.removeClass(UNBOUNDED_2);
          _this.removeCssVars();
        });
      }
      this.deregisterRootHandlers();
      this.deregisterDeactivationHandlers();
    };
    /**
     * @param evt Optional event containing position information.
     */
    MDCRippleFoundation.prototype.activate = function (evt) {
      this.activateImpl(evt);
    };
    MDCRippleFoundation.prototype.deactivate = function () {
      this.deactivateImpl();
    };
    MDCRippleFoundation.prototype.layout = function () {
      var _this = this;
      if (this.layoutFrame) {
        cancelAnimationFrame(this.layoutFrame);
      }
      this.layoutFrame = requestAnimationFrame(function () {
        _this.layoutInternal();
        _this.layoutFrame = 0;
      });
    };
    MDCRippleFoundation.prototype.setUnbounded = function (unbounded) {
      var UNBOUNDED = MDCRippleFoundation.cssClasses.UNBOUNDED;
      if (unbounded) {
        this.adapter.addClass(UNBOUNDED);
      } else {
        this.adapter.removeClass(UNBOUNDED);
      }
    };
    MDCRippleFoundation.prototype.handleFocus = function () {
      var _this = this;
      requestAnimationFrame(function () {
        return _this.adapter.addClass(MDCRippleFoundation.cssClasses.BG_FOCUSED);
      });
    };
    MDCRippleFoundation.prototype.handleBlur = function () {
      var _this = this;
      requestAnimationFrame(function () {
        return _this.adapter.removeClass(MDCRippleFoundation.cssClasses.BG_FOCUSED);
      });
    };
    /**
     * We compute this property so that we are not querying information about the client
     * until the point in time where the foundation requests it. This prevents scenarios where
     * client-side feature-detection may happen too early, such as when components are rendered on the server
     * and then initialized at mount time on the client.
     */
    MDCRippleFoundation.prototype.supportsPressRipple = function () {
      return this.adapter.browserSupportsCssVars();
    };
    MDCRippleFoundation.prototype.defaultActivationState = function () {
      return {
        activationEvent: undefined,
        hasDeactivationUXRun: false,
        isActivated: false,
        isProgrammatic: false,
        wasActivatedByPointer: false,
        wasElementMadeActive: false
      };
    };
    /**
     * supportsPressRipple Passed from init to save a redundant function call
     */
    MDCRippleFoundation.prototype.registerRootHandlers = function (supportsPressRipple) {
      var e_1, _a;
      if (supportsPressRipple) {
        try {
          for (var ACTIVATION_EVENT_TYPES_1 = __values(ACTIVATION_EVENT_TYPES), ACTIVATION_EVENT_TYPES_1_1 = ACTIVATION_EVENT_TYPES_1.next(); !ACTIVATION_EVENT_TYPES_1_1.done; ACTIVATION_EVENT_TYPES_1_1 = ACTIVATION_EVENT_TYPES_1.next()) {
            var evtType = ACTIVATION_EVENT_TYPES_1_1.value;
            this.adapter.registerInteractionHandler(evtType, this.activateHandler);
          }
        } catch (e_1_1) {
          e_1 = {
            error: e_1_1
          };
        } finally {
          try {
            if (ACTIVATION_EVENT_TYPES_1_1 && !ACTIVATION_EVENT_TYPES_1_1.done && (_a = ACTIVATION_EVENT_TYPES_1.return)) _a.call(ACTIVATION_EVENT_TYPES_1);
          } finally {
            if (e_1) throw e_1.error;
          }
        }
        if (this.adapter.isUnbounded()) {
          this.adapter.registerResizeHandler(this.resizeHandler);
        }
      }
      this.adapter.registerInteractionHandler('focus', this.focusHandler);
      this.adapter.registerInteractionHandler('blur', this.blurHandler);
    };
    MDCRippleFoundation.prototype.registerDeactivationHandlers = function (evt) {
      var e_2, _a;
      if (evt.type === 'keydown') {
        this.adapter.registerInteractionHandler('keyup', this.deactivateHandler);
      } else {
        try {
          for (var POINTER_DEACTIVATION_EVENT_TYPES_1 = __values(POINTER_DEACTIVATION_EVENT_TYPES), POINTER_DEACTIVATION_EVENT_TYPES_1_1 = POINTER_DEACTIVATION_EVENT_TYPES_1.next(); !POINTER_DEACTIVATION_EVENT_TYPES_1_1.done; POINTER_DEACTIVATION_EVENT_TYPES_1_1 = POINTER_DEACTIVATION_EVENT_TYPES_1.next()) {
            var evtType = POINTER_DEACTIVATION_EVENT_TYPES_1_1.value;
            this.adapter.registerDocumentInteractionHandler(evtType, this.deactivateHandler);
          }
        } catch (e_2_1) {
          e_2 = {
            error: e_2_1
          };
        } finally {
          try {
            if (POINTER_DEACTIVATION_EVENT_TYPES_1_1 && !POINTER_DEACTIVATION_EVENT_TYPES_1_1.done && (_a = POINTER_DEACTIVATION_EVENT_TYPES_1.return)) _a.call(POINTER_DEACTIVATION_EVENT_TYPES_1);
          } finally {
            if (e_2) throw e_2.error;
          }
        }
      }
    };
    MDCRippleFoundation.prototype.deregisterRootHandlers = function () {
      var e_3, _a;
      try {
        for (var ACTIVATION_EVENT_TYPES_2 = __values(ACTIVATION_EVENT_TYPES), ACTIVATION_EVENT_TYPES_2_1 = ACTIVATION_EVENT_TYPES_2.next(); !ACTIVATION_EVENT_TYPES_2_1.done; ACTIVATION_EVENT_TYPES_2_1 = ACTIVATION_EVENT_TYPES_2.next()) {
          var evtType = ACTIVATION_EVENT_TYPES_2_1.value;
          this.adapter.deregisterInteractionHandler(evtType, this.activateHandler);
        }
      } catch (e_3_1) {
        e_3 = {
          error: e_3_1
        };
      } finally {
        try {
          if (ACTIVATION_EVENT_TYPES_2_1 && !ACTIVATION_EVENT_TYPES_2_1.done && (_a = ACTIVATION_EVENT_TYPES_2.return)) _a.call(ACTIVATION_EVENT_TYPES_2);
        } finally {
          if (e_3) throw e_3.error;
        }
      }
      this.adapter.deregisterInteractionHandler('focus', this.focusHandler);
      this.adapter.deregisterInteractionHandler('blur', this.blurHandler);
      if (this.adapter.isUnbounded()) {
        this.adapter.deregisterResizeHandler(this.resizeHandler);
      }
    };
    MDCRippleFoundation.prototype.deregisterDeactivationHandlers = function () {
      var e_4, _a;
      this.adapter.deregisterInteractionHandler('keyup', this.deactivateHandler);
      try {
        for (var POINTER_DEACTIVATION_EVENT_TYPES_2 = __values(POINTER_DEACTIVATION_EVENT_TYPES), POINTER_DEACTIVATION_EVENT_TYPES_2_1 = POINTER_DEACTIVATION_EVENT_TYPES_2.next(); !POINTER_DEACTIVATION_EVENT_TYPES_2_1.done; POINTER_DEACTIVATION_EVENT_TYPES_2_1 = POINTER_DEACTIVATION_EVENT_TYPES_2.next()) {
          var evtType = POINTER_DEACTIVATION_EVENT_TYPES_2_1.value;
          this.adapter.deregisterDocumentInteractionHandler(evtType, this.deactivateHandler);
        }
      } catch (e_4_1) {
        e_4 = {
          error: e_4_1
        };
      } finally {
        try {
          if (POINTER_DEACTIVATION_EVENT_TYPES_2_1 && !POINTER_DEACTIVATION_EVENT_TYPES_2_1.done && (_a = POINTER_DEACTIVATION_EVENT_TYPES_2.return)) _a.call(POINTER_DEACTIVATION_EVENT_TYPES_2);
        } finally {
          if (e_4) throw e_4.error;
        }
      }
    };
    MDCRippleFoundation.prototype.removeCssVars = function () {
      var _this = this;
      var rippleStrings = MDCRippleFoundation.strings;
      var keys = Object.keys(rippleStrings);
      keys.forEach(function (key) {
        if (key.indexOf('VAR_') === 0) {
          _this.adapter.updateCssVariable(rippleStrings[key], null);
        }
      });
    };
    MDCRippleFoundation.prototype.activateImpl = function (evt) {
      var _this = this;
      if (this.adapter.isSurfaceDisabled()) {
        return;
      }
      var activationState = this.activationState;
      if (activationState.isActivated) {
        return;
      }
      // Avoid reacting to follow-on events fired by touch device after an already-processed user interaction
      var previousActivationEvent = this.previousActivationEvent;
      var isSameInteraction = previousActivationEvent && evt !== undefined && previousActivationEvent.type !== evt.type;
      if (isSameInteraction) {
        return;
      }
      activationState.isActivated = true;
      activationState.isProgrammatic = evt === undefined;
      activationState.activationEvent = evt;
      activationState.wasActivatedByPointer = activationState.isProgrammatic ? false : evt !== undefined && (evt.type === 'mousedown' || evt.type === 'touchstart' || evt.type === 'pointerdown');
      var hasActivatedChild = evt !== undefined && activatedTargets.length > 0 && activatedTargets.some(function (target) {
        return _this.adapter.containsEventTarget(target);
      });
      if (hasActivatedChild) {
        // Immediately reset activation state, while preserving logic that prevents touch follow-on events
        this.resetActivationState();
        return;
      }
      if (evt !== undefined) {
        activatedTargets.push(evt.target);
        this.registerDeactivationHandlers(evt);
      }
      activationState.wasElementMadeActive = this.checkElementMadeActive(evt);
      if (activationState.wasElementMadeActive) {
        this.animateActivation();
      }
      requestAnimationFrame(function () {
        // Reset array on next frame after the current event has had a chance to bubble to prevent ancestor ripples
        activatedTargets = [];
        if (!activationState.wasElementMadeActive && evt !== undefined && (evt.key === ' ' || evt.keyCode === 32)) {
          // If space was pressed, try again within an rAF call to detect :active, because different UAs report
          // active states inconsistently when they're called within event handling code:
          // - https://bugs.chromium.org/p/chromium/issues/detail?id=635971
          // - https://bugzilla.mozilla.org/show_bug.cgi?id=1293741
          // We try first outside rAF to support Edge, which does not exhibit this problem, but will crash if a CSS
          // variable is set within a rAF callback for a submit button interaction (#2241).
          activationState.wasElementMadeActive = _this.checkElementMadeActive(evt);
          if (activationState.wasElementMadeActive) {
            _this.animateActivation();
          }
        }
        if (!activationState.wasElementMadeActive) {
          // Reset activation state immediately if element was not made active.
          _this.activationState = _this.defaultActivationState();
        }
      });
    };
    MDCRippleFoundation.prototype.checkElementMadeActive = function (evt) {
      return evt !== undefined && evt.type === 'keydown' ? this.adapter.isSurfaceActive() : true;
    };
    MDCRippleFoundation.prototype.animateActivation = function () {
      var _this = this;
      var _a = MDCRippleFoundation.strings,
        VAR_FG_TRANSLATE_START = _a.VAR_FG_TRANSLATE_START,
        VAR_FG_TRANSLATE_END = _a.VAR_FG_TRANSLATE_END;
      var _b = MDCRippleFoundation.cssClasses,
        FG_DEACTIVATION = _b.FG_DEACTIVATION,
        FG_ACTIVATION = _b.FG_ACTIVATION;
      var DEACTIVATION_TIMEOUT_MS = MDCRippleFoundation.numbers.DEACTIVATION_TIMEOUT_MS;
      this.layoutInternal();
      var translateStart = '';
      var translateEnd = '';
      if (!this.adapter.isUnbounded()) {
        var _c = this.getFgTranslationCoordinates(),
          startPoint = _c.startPoint,
          endPoint = _c.endPoint;
        translateStart = startPoint.x + "px, " + startPoint.y + "px";
        translateEnd = endPoint.x + "px, " + endPoint.y + "px";
      }
      this.adapter.updateCssVariable(VAR_FG_TRANSLATE_START, translateStart);
      this.adapter.updateCssVariable(VAR_FG_TRANSLATE_END, translateEnd);
      // Cancel any ongoing activation/deactivation animations
      clearTimeout(this.activationTimer);
      clearTimeout(this.fgDeactivationRemovalTimer);
      this.rmBoundedActivationClasses();
      this.adapter.removeClass(FG_DEACTIVATION);
      // Force layout in order to re-trigger the animation.
      this.adapter.computeBoundingRect();
      this.adapter.addClass(FG_ACTIVATION);
      this.activationTimer = setTimeout(function () {
        _this.activationTimerCallback();
      }, DEACTIVATION_TIMEOUT_MS);
    };
    MDCRippleFoundation.prototype.getFgTranslationCoordinates = function () {
      var _a = this.activationState,
        activationEvent = _a.activationEvent,
        wasActivatedByPointer = _a.wasActivatedByPointer;
      var startPoint;
      if (wasActivatedByPointer) {
        startPoint = getNormalizedEventCoords(activationEvent, this.adapter.getWindowPageOffset(), this.adapter.computeBoundingRect());
      } else {
        startPoint = {
          x: this.frame.width / 2,
          y: this.frame.height / 2
        };
      }
      // Center the element around the start point.
      startPoint = {
        x: startPoint.x - this.initialSize / 2,
        y: startPoint.y - this.initialSize / 2
      };
      var endPoint = {
        x: this.frame.width / 2 - this.initialSize / 2,
        y: this.frame.height / 2 - this.initialSize / 2
      };
      return {
        startPoint: startPoint,
        endPoint: endPoint
      };
    };
    MDCRippleFoundation.prototype.runDeactivationUXLogicIfReady = function () {
      var _this = this;
      // This method is called both when a pointing device is released, and when the activation animation ends.
      // The deactivation animation should only run after both of those occur.
      var FG_DEACTIVATION = MDCRippleFoundation.cssClasses.FG_DEACTIVATION;
      var _a = this.activationState,
        hasDeactivationUXRun = _a.hasDeactivationUXRun,
        isActivated = _a.isActivated;
      var activationHasEnded = hasDeactivationUXRun || !isActivated;
      if (activationHasEnded && this.activationAnimationHasEnded) {
        this.rmBoundedActivationClasses();
        this.adapter.addClass(FG_DEACTIVATION);
        this.fgDeactivationRemovalTimer = setTimeout(function () {
          _this.adapter.removeClass(FG_DEACTIVATION);
        }, numbers.FG_DEACTIVATION_MS);
      }
    };
    MDCRippleFoundation.prototype.rmBoundedActivationClasses = function () {
      var FG_ACTIVATION = MDCRippleFoundation.cssClasses.FG_ACTIVATION;
      this.adapter.removeClass(FG_ACTIVATION);
      this.activationAnimationHasEnded = false;
      this.adapter.computeBoundingRect();
    };
    MDCRippleFoundation.prototype.resetActivationState = function () {
      var _this = this;
      this.previousActivationEvent = this.activationState.activationEvent;
      this.activationState = this.defaultActivationState();
      // Touch devices may fire additional events for the same interaction within a short time.
      // Store the previous event until it's safe to assume that subsequent events are for new interactions.
      setTimeout(function () {
        return _this.previousActivationEvent = undefined;
      }, MDCRippleFoundation.numbers.TAP_DELAY_MS);
    };
    MDCRippleFoundation.prototype.deactivateImpl = function () {
      var _this = this;
      var activationState = this.activationState;
      // This can happen in scenarios such as when you have a keyup event that blurs the element.
      if (!activationState.isActivated) {
        return;
      }
      var state = __assign({}, activationState);
      if (activationState.isProgrammatic) {
        requestAnimationFrame(function () {
          _this.animateDeactivation(state);
        });
        this.resetActivationState();
      } else {
        this.deregisterDeactivationHandlers();
        requestAnimationFrame(function () {
          _this.activationState.hasDeactivationUXRun = true;
          _this.animateDeactivation(state);
          _this.resetActivationState();
        });
      }
    };
    MDCRippleFoundation.prototype.animateDeactivation = function (_a) {
      var wasActivatedByPointer = _a.wasActivatedByPointer,
        wasElementMadeActive = _a.wasElementMadeActive;
      if (wasActivatedByPointer || wasElementMadeActive) {
        this.runDeactivationUXLogicIfReady();
      }
    };
    MDCRippleFoundation.prototype.layoutInternal = function () {
      var _this = this;
      this.frame = this.adapter.computeBoundingRect();
      var maxDim = Math.max(this.frame.height, this.frame.width);
      // Surface diameter is treated differently for unbounded vs. bounded ripples.
      // Unbounded ripple diameter is calculated smaller since the surface is expected to already be padded appropriately
      // to extend the hitbox, and the ripple is expected to meet the edges of the padded hitbox (which is typically
      // square). Bounded ripples, on the other hand, are fully expected to expand beyond the surface's longest diameter
      // (calculated based on the diagonal plus a constant padding), and are clipped at the surface's border via
      // `overflow: hidden`.
      var getBoundedRadius = function () {
        var hypotenuse = Math.sqrt(Math.pow(_this.frame.width, 2) + Math.pow(_this.frame.height, 2));
        return hypotenuse + MDCRippleFoundation.numbers.PADDING;
      };
      this.maxRadius = this.adapter.isUnbounded() ? maxDim : getBoundedRadius();
      // Ripple is sized as a fraction of the largest dimension of the surface, then scales up using a CSS scale transform
      var initialSize = Math.floor(maxDim * MDCRippleFoundation.numbers.INITIAL_ORIGIN_SCALE);
      // Unbounded ripple size should always be even number to equally center align.
      if (this.adapter.isUnbounded() && initialSize % 2 !== 0) {
        this.initialSize = initialSize - 1;
      } else {
        this.initialSize = initialSize;
      }
      this.fgScale = "" + this.maxRadius / this.initialSize;
      this.updateLayoutCssVars();
    };
    MDCRippleFoundation.prototype.updateLayoutCssVars = function () {
      var _a = MDCRippleFoundation.strings,
        VAR_FG_SIZE = _a.VAR_FG_SIZE,
        VAR_LEFT = _a.VAR_LEFT,
        VAR_TOP = _a.VAR_TOP,
        VAR_FG_SCALE = _a.VAR_FG_SCALE;
      this.adapter.updateCssVariable(VAR_FG_SIZE, this.initialSize + "px");
      this.adapter.updateCssVariable(VAR_FG_SCALE, this.fgScale);
      if (this.adapter.isUnbounded()) {
        this.unboundedCoords = {
          left: Math.round(this.frame.width / 2 - this.initialSize / 2),
          top: Math.round(this.frame.height / 2 - this.initialSize / 2)
        };
        this.adapter.updateCssVariable(VAR_LEFT, this.unboundedCoords.left + "px");
        this.adapter.updateCssVariable(VAR_TOP, this.unboundedCoords.top + "px");
      }
    };
    return MDCRippleFoundation;
  }(MDCFoundation);

  /**
   * @license
   * Copyright 2016 Google Inc.
   *
   * Permission is hereby granted, free of charge, to any person obtaining a copy
   * of this software and associated documentation files (the "Software"), to deal
   * in the Software without restriction, including without limitation the rights
   * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   * copies of the Software, and to permit persons to whom the Software is
   * furnished to do so, subject to the following conditions:
   *
   * The above copyright notice and this permission notice shall be included in
   * all copies or substantial portions of the Software.
   *
   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   * THE SOFTWARE.
   */
  var MDCRipple = /** @class */function (_super) {
    __extends(MDCRipple, _super);
    function MDCRipple() {
      var _this = _super !== null && _super.apply(this, arguments) || this;
      _this.disabled = false;
      return _this;
    }
    MDCRipple.attachTo = function (root, opts) {
      if (opts === void 0) {
        opts = {
          isUnbounded: undefined
        };
      }
      var ripple = new MDCRipple(root);
      // Only override unbounded behavior if option is explicitly specified
      if (opts.isUnbounded !== undefined) {
        ripple.unbounded = opts.isUnbounded;
      }
      return ripple;
    };
    MDCRipple.createAdapter = function (instance) {
      return {
        addClass: function (className) {
          return instance.root.classList.add(className);
        },
        browserSupportsCssVars: function () {
          return supportsCssVariables(window);
        },
        computeBoundingRect: function () {
          return instance.root.getBoundingClientRect();
        },
        containsEventTarget: function (target) {
          return instance.root.contains(target);
        },
        deregisterDocumentInteractionHandler: function (evtType, handler) {
          return document.documentElement.removeEventListener(evtType, handler, applyPassive());
        },
        deregisterInteractionHandler: function (evtType, handler) {
          return instance.root.removeEventListener(evtType, handler, applyPassive());
        },
        deregisterResizeHandler: function (handler) {
          return window.removeEventListener('resize', handler);
        },
        getWindowPageOffset: function () {
          return {
            x: window.pageXOffset,
            y: window.pageYOffset
          };
        },
        isSurfaceActive: function () {
          return matches(instance.root, ':active');
        },
        isSurfaceDisabled: function () {
          return Boolean(instance.disabled);
        },
        isUnbounded: function () {
          return Boolean(instance.unbounded);
        },
        registerDocumentInteractionHandler: function (evtType, handler) {
          return document.documentElement.addEventListener(evtType, handler, applyPassive());
        },
        registerInteractionHandler: function (evtType, handler) {
          return instance.root.addEventListener(evtType, handler, applyPassive());
        },
        registerResizeHandler: function (handler) {
          return window.addEventListener('resize', handler);
        },
        removeClass: function (className) {
          return instance.root.classList.remove(className);
        },
        updateCssVariable: function (varName, value) {
          return instance.root.style.setProperty(varName, value);
        }
      };
    };
    Object.defineProperty(MDCRipple.prototype, "unbounded", {
      get: function () {
        return Boolean(this.isUnbounded);
      },
      set: function (unbounded) {
        this.isUnbounded = Boolean(unbounded);
        this.setUnbounded();
      },
      enumerable: false,
      configurable: true
    });
    MDCRipple.prototype.activate = function () {
      this.foundation.activate();
    };
    MDCRipple.prototype.deactivate = function () {
      this.foundation.deactivate();
    };
    MDCRipple.prototype.layout = function () {
      this.foundation.layout();
    };
    MDCRipple.prototype.getDefaultFoundation = function () {
      return new MDCRippleFoundation(MDCRipple.createAdapter(this));
    };
    MDCRipple.prototype.initialSyncWithDOM = function () {
      var root = this.root;
      this.isUnbounded = 'mdcRippleIsUnbounded' in root.dataset;
    };
    /**
     * Closure Compiler throws an access control error when directly accessing a
     * protected or private property inside a getter/setter, like unbounded above.
     * By accessing the protected property inside a method, we solve that problem.
     * That's why this function exists.
     */
    MDCRipple.prototype.setUnbounded = function () {
      this.foundation.setUnbounded(Boolean(this.isUnbounded));
    };
    return MDCRipple;
  }(MDCComponent);

  /**
   * @license
   * Copyright 2016 Google Inc.
   *
   * Permission is hereby granted, free of charge, to any person obtaining a copy
   * of this software and associated documentation files (the "Software"), to deal
   * in the Software without restriction, including without limitation the rights
   * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   * copies of the Software, and to permit persons to whom the Software is
   * furnished to do so, subject to the following conditions:
   *
   * The above copyright notice and this permission notice shall be included in
   * all copies or substantial portions of the Software.
   *
   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   * THE SOFTWARE.
   */
  var cssPropertyNameMap = {
    animation: {
      prefixed: '-webkit-animation',
      standard: 'animation'
    },
    transform: {
      prefixed: '-webkit-transform',
      standard: 'transform'
    },
    transition: {
      prefixed: '-webkit-transition',
      standard: 'transition'
    }
  };
  function isWindow(windowObj) {
    return Boolean(windowObj.document) && typeof windowObj.document.createElement === 'function';
  }
  function getCorrectPropertyName(windowObj, cssProperty) {
    if (isWindow(windowObj) && cssProperty in cssPropertyNameMap) {
      var el = windowObj.document.createElement('div');
      var _a = cssPropertyNameMap[cssProperty],
        standard = _a.standard,
        prefixed = _a.prefixed;
      var isStandard = (standard in el.style);
      return isStandard ? standard : prefixed;
    }
    return cssProperty;
  }

  /**
   * @license
   * Copyright 2017 Google Inc.
   *
   * Permission is hereby granted, free of charge, to any person obtaining a copy
   * of this software and associated documentation files (the "Software"), to deal
   * in the Software without restriction, including without limitation the rights
   * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   * copies of the Software, and to permit persons to whom the Software is
   * furnished to do so, subject to the following conditions:
   *
   * The above copyright notice and this permission notice shall be included in
   * all copies or substantial portions of the Software.
   *
   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   * THE SOFTWARE.
   */
  var cssClasses = {
    CLOSED_CLASS: 'mdc-linear-progress--closed',
    CLOSED_ANIMATION_OFF_CLASS: 'mdc-linear-progress--closed-animation-off',
    INDETERMINATE_CLASS: 'mdc-linear-progress--indeterminate',
    REVERSED_CLASS: 'mdc-linear-progress--reversed',
    ANIMATION_READY_CLASS: 'mdc-linear-progress--animation-ready'
  };
  var strings = {
    ARIA_HIDDEN: 'aria-hidden',
    ARIA_VALUEMAX: 'aria-valuemax',
    ARIA_VALUEMIN: 'aria-valuemin',
    ARIA_VALUENOW: 'aria-valuenow',
    BUFFER_BAR_SELECTOR: '.mdc-linear-progress__buffer-bar',
    FLEX_BASIS: 'flex-basis',
    PRIMARY_BAR_SELECTOR: '.mdc-linear-progress__primary-bar'
  };
  // these are percentages pulled from keyframes.scss
  var animationDimensionPercentages = {
    PRIMARY_HALF: .8367142,
    PRIMARY_FULL: 2.00611057,
    SECONDARY_QUARTER: .37651913,
    SECONDARY_HALF: .84386165,
    SECONDARY_FULL: 1.60277782
  };

  /**
   * @license
   * Copyright 2017 Google Inc.
   *
   * Permission is hereby granted, free of charge, to any person obtaining a copy
   * of this software and associated documentation files (the "Software"), to deal
   * in the Software without restriction, including without limitation the rights
   * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   * copies of the Software, and to permit persons to whom the Software is
   * furnished to do so, subject to the following conditions:
   *
   * The above copyright notice and this permission notice shall be included in
   * all copies or substantial portions of the Software.
   *
   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   * THE SOFTWARE.
   */
  var MDCLinearProgressFoundation = /** @class */function (_super) {
    __extends(MDCLinearProgressFoundation, _super);
    function MDCLinearProgressFoundation(adapter) {
      var _this = _super.call(this, __assign(__assign({}, MDCLinearProgressFoundation.defaultAdapter), adapter)) || this;
      _this.observer = null;
      return _this;
    }
    Object.defineProperty(MDCLinearProgressFoundation, "cssClasses", {
      get: function () {
        return cssClasses;
      },
      enumerable: false,
      configurable: true
    });
    Object.defineProperty(MDCLinearProgressFoundation, "strings", {
      get: function () {
        return strings;
      },
      enumerable: false,
      configurable: true
    });
    Object.defineProperty(MDCLinearProgressFoundation, "defaultAdapter", {
      get: function () {
        return {
          addClass: function () {
            return undefined;
          },
          attachResizeObserver: function () {
            return null;
          },
          forceLayout: function () {
            return undefined;
          },
          getWidth: function () {
            return 0;
          },
          hasClass: function () {
            return false;
          },
          setBufferBarStyle: function () {
            return null;
          },
          setPrimaryBarStyle: function () {
            return null;
          },
          setStyle: function () {
            return undefined;
          },
          removeAttribute: function () {
            return undefined;
          },
          removeClass: function () {
            return undefined;
          },
          setAttribute: function () {
            return undefined;
          }
        };
      },
      enumerable: false,
      configurable: true
    });
    MDCLinearProgressFoundation.prototype.init = function () {
      var _this = this;
      this.determinate = !this.adapter.hasClass(cssClasses.INDETERMINATE_CLASS);
      this.adapter.addClass(cssClasses.ANIMATION_READY_CLASS);
      this.progress = 0;
      this.buffer = 1;
      this.observer = this.adapter.attachResizeObserver(function (entries) {
        var e_1, _a;
        if (_this.determinate) {
          return;
        }
        try {
          for (var entries_1 = __values(entries), entries_1_1 = entries_1.next(); !entries_1_1.done; entries_1_1 = entries_1.next()) {
            var entry = entries_1_1.value;
            if (entry.contentRect) {
              _this.calculateAndSetDimensions(entry.contentRect.width);
            }
          }
        } catch (e_1_1) {
          e_1 = {
            error: e_1_1
          };
        } finally {
          try {
            if (entries_1_1 && !entries_1_1.done && (_a = entries_1.return)) _a.call(entries_1);
          } finally {
            if (e_1) throw e_1.error;
          }
        }
      });
      if (!this.determinate && this.observer) {
        this.calculateAndSetDimensions(this.adapter.getWidth());
      }
    };
    MDCLinearProgressFoundation.prototype.setDeterminate = function (isDeterminate) {
      this.determinate = isDeterminate;
      if (this.determinate) {
        this.adapter.removeClass(cssClasses.INDETERMINATE_CLASS);
        this.adapter.setAttribute(strings.ARIA_VALUENOW, this.progress.toString());
        this.adapter.setAttribute(strings.ARIA_VALUEMAX, '1');
        this.adapter.setAttribute(strings.ARIA_VALUEMIN, '0');
        this.setPrimaryBarProgress(this.progress);
        this.setBufferBarProgress(this.buffer);
        return;
      }
      if (this.observer) {
        this.calculateAndSetDimensions(this.adapter.getWidth());
      }
      this.adapter.addClass(cssClasses.INDETERMINATE_CLASS);
      this.adapter.removeAttribute(strings.ARIA_VALUENOW);
      this.adapter.removeAttribute(strings.ARIA_VALUEMAX);
      this.adapter.removeAttribute(strings.ARIA_VALUEMIN);
      this.setPrimaryBarProgress(1);
      this.setBufferBarProgress(1);
    };
    MDCLinearProgressFoundation.prototype.isDeterminate = function () {
      return this.determinate;
    };
    MDCLinearProgressFoundation.prototype.setProgress = function (value) {
      this.progress = value;
      if (this.determinate) {
        this.setPrimaryBarProgress(value);
        this.adapter.setAttribute(strings.ARIA_VALUENOW, value.toString());
      }
    };
    MDCLinearProgressFoundation.prototype.getProgress = function () {
      return this.progress;
    };
    MDCLinearProgressFoundation.prototype.setBuffer = function (value) {
      this.buffer = value;
      if (this.determinate) {
        this.setBufferBarProgress(value);
      }
    };
    MDCLinearProgressFoundation.prototype.getBuffer = function () {
      return this.buffer;
    };
    MDCLinearProgressFoundation.prototype.open = function () {
      this.adapter.removeClass(cssClasses.CLOSED_CLASS);
      this.adapter.removeClass(cssClasses.CLOSED_ANIMATION_OFF_CLASS);
      this.adapter.removeAttribute(strings.ARIA_HIDDEN);
    };
    MDCLinearProgressFoundation.prototype.close = function () {
      this.adapter.addClass(cssClasses.CLOSED_CLASS);
      this.adapter.setAttribute(strings.ARIA_HIDDEN, 'true');
    };
    MDCLinearProgressFoundation.prototype.isClosed = function () {
      return this.adapter.hasClass(cssClasses.CLOSED_CLASS);
    };
    /**
     * Handles the transitionend event emitted after `close()` is called and the
     * opacity fades out. This is so that animations are removed only after the
     * progress indicator is completely hidden.
     */
    MDCLinearProgressFoundation.prototype.handleTransitionEnd = function () {
      if (this.adapter.hasClass(cssClasses.CLOSED_CLASS)) {
        this.adapter.addClass(cssClasses.CLOSED_ANIMATION_OFF_CLASS);
      }
    };
    MDCLinearProgressFoundation.prototype.destroy = function () {
      _super.prototype.destroy.call(this);
      if (this.observer) {
        this.observer.disconnect();
      }
    };
    MDCLinearProgressFoundation.prototype.restartAnimation = function () {
      this.adapter.removeClass(cssClasses.ANIMATION_READY_CLASS);
      this.adapter.forceLayout();
      this.adapter.addClass(cssClasses.ANIMATION_READY_CLASS);
    };
    MDCLinearProgressFoundation.prototype.setPrimaryBarProgress = function (progressValue) {
      var value = "scaleX(" + progressValue + ")";
      // Accessing `window` without a `typeof` check will throw on Node
      // environments.
      var transformProp = typeof window !== 'undefined' ? getCorrectPropertyName(window, 'transform') : 'transform';
      this.adapter.setPrimaryBarStyle(transformProp, value);
    };
    MDCLinearProgressFoundation.prototype.setBufferBarProgress = function (progressValue) {
      var value = progressValue * 100 + "%";
      this.adapter.setBufferBarStyle(strings.FLEX_BASIS, value);
    };
    MDCLinearProgressFoundation.prototype.calculateAndSetDimensions = function (width) {
      var primaryHalf = width * animationDimensionPercentages.PRIMARY_HALF;
      var primaryFull = width * animationDimensionPercentages.PRIMARY_FULL;
      var secondaryQuarter = width * animationDimensionPercentages.SECONDARY_QUARTER;
      var secondaryHalf = width * animationDimensionPercentages.SECONDARY_HALF;
      var secondaryFull = width * animationDimensionPercentages.SECONDARY_FULL;
      this.adapter.setStyle('--mdc-linear-progress-primary-half', primaryHalf + "px");
      this.adapter.setStyle('--mdc-linear-progress-primary-half-neg', -primaryHalf + "px");
      this.adapter.setStyle('--mdc-linear-progress-primary-full', primaryFull + "px");
      this.adapter.setStyle('--mdc-linear-progress-primary-full-neg', -primaryFull + "px");
      this.adapter.setStyle('--mdc-linear-progress-secondary-quarter', secondaryQuarter + "px");
      this.adapter.setStyle('--mdc-linear-progress-secondary-quarter-neg', -secondaryQuarter + "px");
      this.adapter.setStyle('--mdc-linear-progress-secondary-half', secondaryHalf + "px");
      this.adapter.setStyle('--mdc-linear-progress-secondary-half-neg', -secondaryHalf + "px");
      this.adapter.setStyle('--mdc-linear-progress-secondary-full', secondaryFull + "px");
      this.adapter.setStyle('--mdc-linear-progress-secondary-full-neg', -secondaryFull + "px");
      // need to restart animation for custom props to apply to keyframes
      this.restartAnimation();
    };
    return MDCLinearProgressFoundation;
  }(MDCFoundation);

  /**
   * @license
   * Copyright 2017 Google Inc.
   *
   * Permission is hereby granted, free of charge, to any person obtaining a copy
   * of this software and associated documentation files (the "Software"), to deal
   * in the Software without restriction, including without limitation the rights
   * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
   * copies of the Software, and to permit persons to whom the Software is
   * furnished to do so, subject to the following conditions:
   *
   * The above copyright notice and this permission notice shall be included in
   * all copies or substantial portions of the Software.
   *
   * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
   * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
   * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
   * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
   * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
   * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
   * THE SOFTWARE.
   */
  var MDCLinearProgress = /** @class */function (_super) {
    __extends(MDCLinearProgress, _super);
    function MDCLinearProgress() {
      return _super !== null && _super.apply(this, arguments) || this;
    }
    MDCLinearProgress.attachTo = function (root) {
      return new MDCLinearProgress(root);
    };
    Object.defineProperty(MDCLinearProgress.prototype, "determinate", {
      set: function (value) {
        this.foundation.setDeterminate(value);
      },
      enumerable: false,
      configurable: true
    });
    Object.defineProperty(MDCLinearProgress.prototype, "progress", {
      set: function (value) {
        this.foundation.setProgress(value);
      },
      enumerable: false,
      configurable: true
    });
    Object.defineProperty(MDCLinearProgress.prototype, "buffer", {
      set: function (value) {
        this.foundation.setBuffer(value);
      },
      enumerable: false,
      configurable: true
    });
    MDCLinearProgress.prototype.open = function () {
      this.foundation.open();
    };
    MDCLinearProgress.prototype.close = function () {
      this.foundation.close();
    };
    MDCLinearProgress.prototype.initialSyncWithDOM = function () {
      var _this = this;
      this.root.addEventListener('transitionend', function () {
        _this.foundation.handleTransitionEnd();
      });
    };
    MDCLinearProgress.prototype.getDefaultFoundation = function () {
      var _this = this;
      // DO NOT INLINE this variable. For backward compatibility, foundations take
      // a Partial<MDCFooAdapter>. To ensure we don't accidentally omit any
      // methods, we need a separate, strongly typed adapter variable.
      var adapter = {
        addClass: function (className) {
          _this.root.classList.add(className);
        },
        forceLayout: function () {
          _this.root.getBoundingClientRect();
        },
        setBufferBarStyle: function (styleProperty, value) {
          var bufferBar = _this.root.querySelector(MDCLinearProgressFoundation.strings.BUFFER_BAR_SELECTOR);
          if (bufferBar) {
            bufferBar.style.setProperty(styleProperty, value);
          }
        },
        setPrimaryBarStyle: function (styleProperty, value) {
          var primaryBar = _this.root.querySelector(MDCLinearProgressFoundation.strings.PRIMARY_BAR_SELECTOR);
          if (primaryBar) {
            primaryBar.style.setProperty(styleProperty, value);
          }
        },
        hasClass: function (className) {
          return _this.root.classList.contains(className);
        },
        removeAttribute: function (attributeName) {
          _this.root.removeAttribute(attributeName);
        },
        removeClass: function (className) {
          _this.root.classList.remove(className);
        },
        setAttribute: function (attributeName, value) {
          _this.root.setAttribute(attributeName, value);
        },
        setStyle: function (name, value) {
          _this.root.style.setProperty(name, value);
        },
        attachResizeObserver: function (callback) {
          var RO = window.ResizeObserver;
          if (RO) {
            var ro = new RO(callback);
            ro.observe(_this.root);
            return ro;
          }
          return null;
        },
        getWidth: function () {
          return _this.root.offsetWidth;
        }
      };
      return new MDCLinearProgressFoundation(adapter);
    };
    return MDCLinearProgress;
  }(MDCComponent);

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
      return tpl['menu/main.html'];
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
          new MDCRipple(button);
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
        $('.page-menu > header').append(tpl['menu/loader.html']);
        var loaderElement = $('#loader .loader-progress');
        var linearProgress = new MDCLinearProgress(loaderElement[0]);
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
        $('.page-menu').prepend(ejs.render(tpl['menu/preloader.html'], {
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
          $('.page-menu > aside .menu-list.level-1').append(ejs.render(tpl['menu/module.html'], {
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
            new MDCRipple(menuItem);
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
            new MDCRipple(button);
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
      $('.page-menu .mdc-top-app-bar__section--align-end').append(ejs.render(tpl['menu/navbar.html'], {
        user: coreMenu._user
      }));

      // Выход
      $('.page-menu .menu-logout').on('click', function (e) {
        e.preventDefault();
        CoreUI.alert.warning(Core._('Уверены, что хотите выйти?'), '', {
          buttons: [{
            text: Core._('Отмена')
          }, {
            text: Core._('Да'),
            type: 'warning',
            click: coreAuth.logout
          }]
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
          new MDCRipple(_button);
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
      var title = titles.hasOwnProperty(0) ? (titles.hasOwnProperty(1) ? titles[1] + ' - ' : '') + titles[0] : '';
      title = (title ? title + ' - ' : '') + coreMenu._system.name;
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
  document.addEventListener('DOMContentLoaded', function () {
    coreMain.on('hashchange', function () {
      if ($('.page-auth')[0]) {
        coreAuth.viewActualContainer();
      }
      if ($('.page.page-menu')[0]) {
        if (window.screen.width < 600 && $('.page.page-menu.drawer-toggle')[0]) {
          coreMenu._drawerToggle();
        }
        coreMenu.load('/mod' + location.hash.substring(1));
      }
    });

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
  const md5cmn = (q, a, b, x, s, t) => safeAdd(bitRotateLeft(safeAdd(safeAdd(a, q), safeAdd(x, t)), s), b),
    md5ff = (a, b, c, d, x, s, t) => md5cmn(b & c | ~b & d, a, b, x, s, t),
    md5gg = (a, b, c, d, x, s, t) => md5cmn(b & d | c & ~d, a, b, x, s, t),
    md5hh = (a, b, c, d, x, s, t) => md5cmn(b ^ c ^ d, a, b, x, s, t),
    md5ii = (a, b, c, d, x, s, t) => md5cmn(c ^ (b | ~d), a, b, x, s, t);
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
    },
    secondChunk = (chunks, x, i) => {
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
    },
    thirdChunk = (chunks, x, i) => {
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
    },
    fourthChunk = (chunks, x, i) => {
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

  var coreAuth = {
    /**
     * Получение страницы входа и регистрации
     * @returns {*}
     */
    getPageContent: function getPageContent() {
      return tpl['auth/main.html'];
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
          new MDCRipple(button);
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
    return _createClass(coreUiTableInstance, [{
      key: "deleteSelected",
      value: function deleteSelected(url, callbackSuccess) {
        var recordsId = this._table.getSelectedRecordsId();
        if (recordsId.length === 0) {
          CoreUI.notice.warning(Core$1._('Нужно выбрать хотя бы одну запись'));
          return;
        }
        CoreUI.alert.warning(Core$1._("Удалить выбранные записи?"), Core$1._('Количество: ') + ' ' + recordsId.length, {
          buttons: [{
            text: Core$1._("Отмена")
          }, {
            text: Core$1._("Да"),
            type: 'warning',
            click: function click() {
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
                    CoreUI.notice.defalt(Core$1._('Выбранные записи удалены'));
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
          }]
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
        var isAccept = false;
        CoreUI.alert.create({
          type: 'warning',
          title: question,
          onHide: function onHide() {
            if (!isAccept) {
              $(checked).prop('checked', !isChecked);
            }
          },
          buttons: [{
            text: Core$1._("Отмена"),
            click: function click() {
              $(checked).prop('checked', !isChecked);
            }
          }, {
            text: Core$1._("Да"),
            type: 'warning',
            click: function click() {
              Core$1.menu.loader.show();
              isAccept = true;
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
            }
          }]
        });
      }
    }]);
  }();

  var coreUiTable$1 = {
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

  var coreUiTable = {
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
      table: coreUiTable$1,
      form: coreUiTable
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

}));
