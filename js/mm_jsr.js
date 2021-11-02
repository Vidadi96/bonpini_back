!(function (t, e) {
    "object" == typeof exports && "undefined" != typeof module ? (module.exports = e()) : "function" == typeof define && define.amd ? define(e) : ((t = "undefined" != typeof globalThis ? globalThis : t || self).JSR = e());
})(this, function () {
    "use strict";
    class t {
        constructor(t) {
            (this.real = t.real), (this.min = t.min), (this.max = t.max);
        }
        clampReal(e, i) {
            return new t(Object.assign(Object.assign({}, this.toData()), { real: Math.min(i, Math.max(e, this.real)) }));
        }
        changeReal(e) {
            return new t(Object.assign(Object.assign({}, this.toData()), { real: e }));
        }
        asReal() {
            return this.real;
        }
        asRatio() {
            const t = (this.real - this.min) / (this.max - this.min);
            return Number.isFinite(t) ? t : 1;
        }
        isExact(t) {
            return this.real === t.real;
        }
        static fromReal(e) {
            return new t(e);
        }
        static fromRatio(e) {
            return new t(Object.assign(Object.assign({}, e), { real: e.ratio * (e.max - e.min) + e.min }));
        }
        toData() {
            return { min: this.min, max: this.max, real: this.real };
        }
    }
    class e {
        constructor(t) {
            (this.config = t.config), (this.onChange = t.onChange), (this.getState = t.getState);
        }
        setRealValue(e, i) {
            this.onChange(e, t.fromReal({ max: this.config.max, min: this.config.min, real: i }));
        }
        setRatioValue(e, i) {
            this.onChange(e, t.fromRatio({ max: this.config.max, min: this.config.min, ratio: i }));
        }
        setClosestRatioValue(e) {
            var i, s;
            const n = this.getState().values;
            let a = ((t, e) => e.reduce((i, s, n) => (Math.abs(e[i] - t) < Math.abs(s - t) ? i : n), 0))(
                e,
                n.map((t) => t.asRatio())
            );
            n[a].asReal() === (null === (i = n[a - 1]) || void 0 === i ? void 0 : i.asReal()) && e < (null === (s = n[a - 1]) || void 0 === s ? void 0 : s.asRatio()) && (a -= 1),
                this.onChange(a, t.fromRatio({ max: this.config.max, min: this.config.min, ratio: e }));
        }
        changeRatioBy(e, i) {
            const s = this.getState().values[e];
            this.onChange(e, t.fromRatio({ max: this.config.max, min: this.config.min, ratio: s.asRatio() + i }));
        }
        changeRealBy(e, i) {
            const s = this.getState().values[e];
            this.onChange(e, t.fromReal({ max: this.config.max, min: this.config.min, real: s.asReal() + i }));
        }
        makeValueRatioOffsetModifier(e) {
            const i = this.getState().values[e].asRatio();
            return (s) => {
                this.onChange(e, t.fromRatio({ max: this.config.max, min: this.config.min, ratio: i + s }));
            };
        }
        static init(t) {
            return new e(t);
        }
    }
    class i extends Error {
        constructor(t, e, i) {
            super(`Invalid ${t}: ${i}. Got: ${e}`);
        }
    }
    const s = (t, e, s) => {
            const n = s(e);
            if (n) throw new i(t, n.value, n.error);
        },
        n = (t) => (e) => !(e instanceof t) && { error: `expected instance of ${t.name}`, value: e },
        a = (t) => !Number.isFinite(t) && { error: "expected number", value: t },
        r = (t) => "string" != typeof t && { error: "expected string", value: t },
        o = (t) => "function" != typeof t && { error: "expected function", value: t },
        l = (t) => !d(t) && { error: "expected literal object", value: t },
        h = (t) => (e) => {
            var i;
            if (!Array.isArray(e)) return { error: "expected array", value: e };
            return null !== (i = e.map(t).find(Boolean)) && void 0 !== i && i;
        },
        d = (t) => "object" == typeof t && null !== t && t.constructor === Object && "[object Object]" === Object.prototype.toString.call(t);
    class c {
        constructor(t) {
            this.attrs = t;
        }
        toDto() {
            return Object.freeze(Object.assign(Object.assign({}, this.attrs), { stepDecimals: this.stepDecimals, valuesCount: this.valuesCount }));
        }
        get max() {
            return this.attrs.max;
        }
        get min() {
            return this.attrs.min;
        }
        get step() {
            return this.attrs.step;
        }
        get stepDecimals() {
            const t = (e) => (0 === e || e >= 1 ? 0 : 1 + t(10 * e));
            return t(this.step);
        }
        get valuesCount() {
            return this.attrs.initialValues.length;
        }
        static createFromInput(t) {
            return (
                s("min", t.min, a),
                s("max", t.max, a),
                s("step", t.step, a),
                s("initialValues", t.initialValues, h(a)),
                s("container", t.container, n(window.HTMLElement)),
                t.limit && (s("limit", t.limit, l), t.limit.min && s("limit.min", t.limit.min, a), t.limit.max && s("limit.min", t.limit.max, a)),
                new c(t)
            );
        }
    }
    class u {
        constructor(t) {
            (this.rafId = null), (this.container = t.container), this.container.classList.add("jsr");
        }
        getContainer() {
            return this.container;
        }
        addChild(t) {
            this.container.appendChild(t);
        }
        positionToRelative(t) {
            return (t - this.container.getBoundingClientRect().left) / this.container.offsetWidth;
        }
        distanceToRelative(t) {
            return t / this.container.offsetWidth;
        }
        render(t) {
            this.rafId && window.cancelAnimationFrame(this.rafId),
                (this.rafId = window.requestAnimationFrame(() => {
                    t.forEach((t) => t()), (this.rafId = null);
                }));
        }
        static init(t) {
            return new u(t);
        }
    }
    class m {
        constructor(t) {
            (this.values = t.values), (this.limit = t.limit);
        }
        updateValues(t) {
            return new m(Object.assign(Object.assign({}, this.toData()), { values: t }));
        }
        changeLimit(t) {
            return new m(Object.assign(Object.assign({}, this.toData()), { limit: t }));
        }
        findChangedValues(t) {
            return t.values.reduce((t, e, i) => (this.values.includes(e) ? t : t.concat(i)), []).sort();
        }
        static fromData(t) {
            return new m(t);
        }
        toDto() {
            return this.toData();
        }
        toData() {
            return { values: this.values, limit: this.limit };
        }
    }
    const g = (t, e, i) => t.map((t, s, n) => (e.includes(s) ? i(t, s, n) : t)),
        f = (t, e, i) => {
            const { values: s } = e,
                n = g(s, i.changedValues, (e, i, s) => {
                    var n, a, r, o;
                    return e
                        .clampReal(
                            null !== (a = null === (n = s[i - 1]) || void 0 === n ? void 0 : n.asReal()) && void 0 !== a ? a : -1 / 0,
                            null !== (o = null === (r = s[i + 1]) || void 0 === r ? void 0 : r.asReal()) && void 0 !== o ? o : 1 / 0
                        )
                        .clampReal(t.min, t.max);
                });
            return e.updateValues(n);
        },
        v = (t, e, i) => {
            const { values: s } = e,
                { step: n } = t,
                a = g(s, i.changedValues, (t) => t.changeReal(Math.round(t.asReal() / n) * n));
            return e.updateValues(a);
        },
        p = (t, e, i) => {
            const { values: s } = e,
                n = s.map((t) => {
                    var i, s, n, a, r, o;
                    return t.clampReal(
                        null !== (n = null === (s = null === (i = e.limit) || void 0 === i ? void 0 : i.min) || void 0 === s ? void 0 : s.asReal()) && void 0 !== n ? n : -1 / 0,
                        null !== (o = null === (r = null === (a = e.limit) || void 0 === a ? void 0 : a.max) || void 0 === r ? void 0 : r.asReal()) && void 0 !== o ? o : 1 / 0
                    );
                });
            return e.updateValues(n);
        };
    class R {
        constructor(e) {
            const i = e.config,
                s = { min: i.min, max: i.max },
                n = i.initialValues.map((e) => t.fromReal(Object.assign(Object.assign({}, s), { real: e }))),
                a = i.limit
                    ? { min: i.limit.min ? t.fromReal(Object.assign(Object.assign({}, s), { real: i.limit.min })) : void 0, max: i.limit.max ? t.fromReal(Object.assign(Object.assign({}, s), { real: i.limit.max })) : void 0 }
                    : void 0;
            (this.config = e.config), (this.state = m.fromData({ values: n, limit: a })), (this.state = this.process(this.state));
        }
        changeLimit(t) {
            const e = this.state.changeLimit(t);
            return (this.state = this.process(e)), this.state.toDto();
        }
        updateValue(t, e) {
            const i = this.state.toDto(),
                s = this.state.updateValues(g(this.state.values, [t], (t) => e));
            return (this.state = this.process(s)), { newState: this.state.toDto(), oldState: i };
        }
        getState() {
            return this.state.toDto();
        }
        process(t) {
            const e = this.state.findChangedValues(t),
                i = [f, p, v];
            return this.internalProcess(i, t, { changedValues: e });
        }
        internalProcess(t, e, i) {
            const [s, ...n] = t,
                a = s(this.config, e, i);
            return n.length ? this.internalProcess(n, a, i) : a;
        }
        static init(t) {
            return new R(t);
        }
    }
    class b {
        constructor(t) {
            (this.valueChangeHandlers = []), (this.enabled = !0), (this.config = c.createFromInput(t.config));
            const i = this.config.toDto();
            (this.stateProcessor = R.init({ config: i })),
                (this.renderer = u.init({ container: i.container })),
                (this.inputHandler = e.init({ config: i, onChange: this.onValueChange.bind(this), getState: this.stateProcessor.getState.bind(this.stateProcessor) })),
                (this.modules = t.modules.map((t) => t.init({ config: i, renderer: this.renderer, input: this.inputHandler }))),
                this.initView();
        }
        addValueChangeHandler(t) {
            return this.valueChangeHandlers.push(t), () => (this.valueChangeHandlers = this.valueChangeHandlers.filter((e) => e !== t));
        }
        enable() {
            this.renderer.getContainer().classList.remove("is-disabled"), (this.enabled = !0);
        }
        disable() {
            this.renderer.getContainer().classList.add("is-disabled"), (this.enabled = !1);
        }
        isEnabled() {
            return this.enabled;
        }
        changeLimit(t) {
            const e = this.stateProcessor.changeLimit(t);
            this.renderState(e);
        }
        produceRealValue(e) {
            return t.fromReal({ real: e, max: this.config.max, min: this.config.min });
        }
        initView() {
            this.modules.forEach((t) => t.initView()), this.renderState(this.stateProcessor.getState());
        }
        onValueChange(t, e) {
            if (!this.enabled) return;
            const { oldState: i, newState: s } = this.stateProcessor.updateValue(t, e);
            this.renderState(s);
            s.values[t].isExact(i.values[t]) ||
                this.valueChangeHandlers.forEach((e) => {
                    e({ index: t, ratio: s.values[t].asRatio(), real: s.values[t].asReal() });
                });
        }
        renderState(t) {
            const e = this.modules.map((e) => e.render(t));
            this.renderer.render(e);
        }
    }
    class x {
        init(t) {
            return (this.renderer = t.renderer), (this.config = t.config), (this.input = t.input), this;
        }
    }
    const y = (t, { onMouseDown: e, onMouseMove: i, onMouseUp: s }) => {
            const n = (t) => {
                    document.removeEventListener("mousemove", a), document.removeEventListener("mouseup", n), s(t);
                },
                a = (t) => {
                    i(t);
                };
            t.addEventListener("mousedown", (t) => {
                document.addEventListener("mouseup", n), document.addEventListener("mousemove", a), e(t);
            });
        },
        w = (t) => (t.length < 2 ? [] : [t.slice(0, 2), ...w(t.slice(1))]),
        C = (t, e = 0) => {
            if (0 === t && 0 === e) return [];
            const i = [];
            for (let s = Math.min(t, e); s <= Math.max(t, e); s += 1) i.push(s);
            return i;
        };
    const L = (t, e, i) => {
            let s = 0,
                n = null;
            const a = ((t, e) => {
                let i = !1;
                return (...s) => {
                    i || (t(...s), setTimeout(() => (i = !1), e));
                };
            })(e, 1e3 / 60);
            y(t, {
                onMouseDown: (t) => {
                    n = t.target;
                    const e = n.getBoundingClientRect();
                    s = t.clientX - e.x - e.width / 2;
                },
                onMouseMove: (t) => {
                    a(t.clientX - s, n);
                },
                onMouseUp: () => {
                    (s = 0), (n = null);
                },
            }),
                ((t, { onTouchDown: e, onTouchMove: i, onTouchUp: s, root: n }) => {
                    let a = null;
                    const r = (t) => {
                            const e = t.changedTouches[0];
                            document.documentElement.classList.remove("jsr_lockscreen"), a && e && (a.removeEventListener("touchmove", o), a.removeEventListener("touchend", r), (a = null), s(e));
                        },
                        o = (t) => {
                            const e = t.changedTouches[0];
                            a && e && i(e);
                        };
                    t.addEventListener("touchstart", (t) => {
                        const i = t.targetTouches.item(0);
                        i && !a && ((a = i.target), a.addEventListener("touchmove", o), a.addEventListener("touchend", r), document.documentElement.classList.add("jsr_lockscreen"), e(i));
                    });
                })(t, {
                    onTouchDown: (t) => {
                        document.documentElement.classList.add("jsr_lockscreen"), (n = t.target);
                        const e = n.getBoundingClientRect();
                        s = t.clientX - e.x - e.width / 2;
                    },
                    onTouchMove: (t) => {
                        a(t.clientX - s, n);
                    },
                    onTouchUp: () => {
                        document.documentElement.classList.remove("jsr_lockscreen"), (s = 0), (n = null);
                    },
                    root: i,
                });
        },
        V = (...t) => t.reduce((t, e) => t + e, 0) / t.length,
        E = (t) => [...new Set(t)];
    const S = (t) => E(t.split("")).join(""),
        j = (t) => (0 === t ? [] : 1 === t ? ["0"] : C(t - 1).map((t) => t.toString())),
        M = (t) => {
            const e = (t) => {
                const i = w(t).map((t) => S(t.join("")));
                return [i, ...(i.length > 1 ? e(i) : [])];
            };
            return e(j(t));
        },
        O = (t, e, i) => {
            const [s, n, ...a] = e;
            return s ? (n ? (i(s, n) ? O([], [...t, S(s + n), ...a], i) : O([...t, s], [n, ...a], i)) : [...t, s]) : t;
        };
    const k = new Map([
            ["ArrowLeft", -1],
            ["ArrowUp", 1],
            ["ArrowRight", 1],
            ["ArrowDown", -1],
        ]),
        T = (t) => {
            var e, i;
            return null !== (i = null === (e = t.classList) || void 0 === e ? void 0 : e.contains("jsr_slider")) && void 0 !== i && i;
        };
    class D {
        constructor(t) {
            s("JSR.modules", t.modules, h(n(x))), (this.engine = new b({ config: t.config, modules: t.modules }));
        }
        setRealValue(t, e) {
            this.engine.inputHandler.setRealValue(t, e);
        }
        setRatioValue(t, e) {
            this.engine.inputHandler.setRatioValue(t, e);
        }
        getRealValue(t) {
            return this.engine.stateProcessor.getState().values[t].asReal();
        }
        getRatioValue(t) {
            return this.engine.stateProcessor.getState().values[t].asRatio();
        }
        onValueChange(t) {
            return this.engine.addValueChangeHandler(t);
        }
        changeLimit(t) {
            s("limit object", t, l),
                t.min && s("limit.min", t.min, a),
                t.max && s("limit.max", t.max, a),
                this.engine.changeLimit({ min: void 0 !== t.min ? this.engine.produceRealValue(t.min) : void 0, max: void 0 !== t.max ? this.engine.produceRealValue(t.max) : void 0 });
        }
        enable() {
            this.engine.enable();
        }
        disable() {
            this.engine.disable();
        }
        isEnabled() {
            return this.engine.isEnabled();
        }
        destroy() {
            this.engine.modules.forEach((t) => t.destroy());
        }
    }
    return (
        (D.Module = x),
        (D.Bar = class extends x {
            constructor() {
                super(...arguments),
                    (this.bars = []),
                    (this.handleClick = (t) => {
                        this.input.setClosestRatioValue(this.renderer.positionToRelative(t.clientX));
                    });
            }
            destroy() {
                this.bars.forEach((t) => t.remove());
            }
            initView() {
                var t, e;
                (this.bars =
                    ((t = this.config.valuesCount - 1),
                    (e = (t) => {
                        const e = document.createElement("div");
                        return e.classList.add("jsr_bar"), (e.dataset.key = (t - 1).toString()), (e.style.left = "0"), (e.style.width = "0"), this.addMoveHandler(e, t - 1), e.addEventListener("click", this.handleClick), e;
                    }),
                    t < 1 ? [] : C(1, t).map(e))),
                    this.bars.forEach((t) => this.renderer.addChild(t));
            }
            render(t) {
                return () => {
                    w(C(t.values.length - 1)).map(([e, i]) => {
                        const s = 100 * t.values[e].asRatio(),
                            n = 100 * (t.values[i].asRatio() - t.values[e].asRatio());
                        (this.bars[e].style.left = `${s}%`), (this.bars[e].style.width = `${n}%`);
                    });
                };
            }
            addMoveHandler(t, e) {
                let i = () => {},
                    s = () => {},
                    n = 0;
                y(t, {
                    onMouseDown: (t) => {
                        (i = this.input.makeValueRatioOffsetModifier(e)), (s = this.input.makeValueRatioOffsetModifier(e + 1)), (n = t.clientX);
                    },
                    onMouseMove: (t) => {
                        const e = this.renderer.distanceToRelative(t.clientX - n);
                        i(e), s(e);
                    },
                    onMouseUp: () => {
                        (i = () => {}), (s = () => {}), (n = 0);
                    },
                });
            }
        }),
        (D.Grid = class extends x {
            constructor(t = {}) {
                super(),
                    (this.handleWindowResize = ((t, e) => {
                        let i;
                        return (...s) => {
                            let n;
                            return (
                                i && clearTimeout(i),
                                (i = setTimeout(() => {
                                    n = t(...s);
                                }, e)),
                                n
                            );
                        };
                    })(() => {
                        this.drawGrid();
                    }, 50)),
                    (this.handleClick = (t) => {
                        this.input.setClosestRatioValue(this.renderer.positionToRelative(t.clientX));
                    }),
                    this.assertSettings(t),
                    (this.settings = Object.assign(
                        {
                            color: "rgba(0, 0, 0, 0.3)",
                            height: 10,
                            fontSize: 10,
                            fontFamily: "sans-serif",
                            textPadding: 5,
                            formatter: String,
                            getLinesCount: ({ containerWidth: t }) => Math.min(100, Math.floor(t / 10)),
                            shouldShowLabel: ({ i: t, linesCount: e }) => 0 === t || t === e || t % 10 == 0,
                        },
                        t
                    ));
            }
            destroy() {
                this.grid.remove(), window.removeEventListener("resize", this.handleWindowResize);
            }
            initView() {
                (this.grid = document.createElement("canvas")),
                    this.grid.classList.add("jsr_grid"),
                    (this.context = this.grid.getContext("2d")),
                    this.renderer.addChild(this.grid),
                    this.drawGrid(),
                    window.addEventListener("resize", this.handleWindowResize),
                    this.grid.addEventListener("click", this.handleClick);
            }
            render(t) {
                return () => {};
            }
            drawGrid() {
                const e = this.renderer.getContainer().offsetWidth,
                    i = this.settings.height + this.settings.fontSize + this.settings.textPadding,
                    s = window.devicePixelRatio || 1,
                    n = this.context,
                    a = this.settings.getLinesCount({ containerWidth: e }),
                    r = 1 / a;
                (this.grid.style.width = `${e}px`),
                    (this.grid.width = e * s),
                    (this.grid.style.height = `${i}px`),
                    (this.grid.height = i * s),
                    n.scale(s, s),
                    n.clearRect(0, 0, e, this.settings.height),
                    n.beginPath(),
                    (n.lineWidth = 1),
                    (n.fillStyle = n.strokeStyle = this.settings.color),
                    (n.font = `${this.settings.fontSize}px ${this.settings.fontFamily}`),
                    (n.textBaseline = "top");
                for (let i = 0; i <= a; i += 1) {
                    let s = i * r * e;
                    (s = Math.round(100 * s) / 100), n.moveTo(s, 0), n.lineTo(s, this.settings.height);
                    if (this.settings.shouldShowLabel({ i: i, linesCount: a })) {
                        n.textAlign = 0 === i ? "left" : i === a ? "right" : "center";
                        const s = t.fromRatio({ ratio: i / a, max: this.config.max, min: this.config.min }),
                            o = s.changeReal(Math.round(s.asReal() / this.config.step) * this.config.step),
                            l = this.settings.formatter(o.asReal());
                        n.fillText(l.toString(), i * r * e, this.settings.height + this.settings.textPadding);
                    }
                }
                n.closePath(), n.stroke();
            }
            assertSettings(t) {
                t.formatter && s("Grid.formatter", t.formatter, o),
                    t.color && s("Grid.color", t.color, r),
                    t.height && s("Grid.height", t.height, a),
                    t.fontSize && s("Grid.fontSize", t.fontSize, a),
                    t.fontFamily && s("Grid.fontFamily", t.fontFamily, r),
                    t.textPadding && s("Grid.textPadding", t.textPadding, a);
            }
        }),
        (D.Label = class extends x {
            constructor(t = {}) {
                super(), (this.labels = new Map()), (this.primaryLabels = []), this.assertSettings(t), (this.settings = Object.assign({ formatter: String }, t));
            }
            destroy() {
                this.labels.forEach((t) => t.el.remove());
            }
            initView() {
                const t = j(this.config.valuesCount),
                    e = [t, ...M(this.config.valuesCount)],
                    i = new Map(
                        e.flat().map((t) => {
                            const e = document.createElement("div");
                            return e.classList.add("jsr_label"), (e.dataset.key = t), (e.style.left = "0"), L(e, (e, i) => this.handleMove(t, e, i), this.renderer.getContainer()), [t, { key: t, el: e }];
                        })
                    );
                (this.primaryLabels = t), (this.labels = i), this.labels.forEach((t) => this.renderer.addChild(t.el));
            }
            render(t) {
                return () => {
                    this.applyValues(t), this.fixExceeding(), this.fixOverlapping();
                };
            }
            applyValues(t) {
                for (const [e, i] of this.labels) {
                    const s = E(e.split("")).map((e) => ({ key: e, value: t.values[Number(e)] })),
                        n = V(...s.map((t) => t.value.asRatio())),
                        a = (t) => this.settings.formatter(Number(t.asReal().toFixed(this.config.stepDecimals)));
                    (i.el.style.left = 100 * n + "%"), (i.el.innerHTML = s.map((t, e) => `\n        <span data-key="${t.key}">\n          ${a(t.value)}\n          ${e < s.length - 1 ? " - " : ""}\n        </span>\n      `).join(""));
                }
            }
            fixOverlapping() {
                const t = O([], this.primaryLabels, this.doLabelsOverlap.bind(this));
                [...this.labels.values()].forEach((e) => {
                    t.includes(e.key) ? e.el.classList.remove("is-hidden") : e.el.classList.add("is-hidden");
                });
            }
            fixExceeding() {
                const t = this.renderer.getContainer().getBoundingClientRect();
                [...this.labels.values()]
                    .map((t) => t.el)
                    .forEach((e) => {
                        const i = e.getBoundingClientRect();
                        if (i.left < t.left) {
                            const s = parseFloat(e.style.left),
                                n = 100 * this.renderer.distanceToRelative(t.left - i.left);
                            e.style.left = `${s + n}%`;
                        }
                        if (i.right > t.right) {
                            const s = parseFloat(e.style.left),
                                n = 100 * this.renderer.distanceToRelative(i.right - t.right);
                            e.style.left = s - n + "%";
                        }
                    });
            }
            handleMove(t, e, i) {
                const s = this.renderer.positionToRelative(e);
                if (1 === t.length) this.input.setRatioValue(Number(t), s);
                else {
                    const t = i.dataset.key;
                    if (!t || t.length > 1) return;
                    this.input.setRatioValue(Number(t), s);
                }
            }
            doLabelsOverlap(t, e) {
                const i = this.labels.get(t).el.getBoundingClientRect(),
                    s = this.labels.get(e).el.getBoundingClientRect();
                return i.right > s.left;
            }
            assertSettings(t) {
                t.formatter && s("Label.formatter", t.formatter, o);
            }
        }),
        (D.Limit = class extends x {
            destroy() {
                this.limit.remove();
            }
            initView() {
                (this.limit = document.createElement("div")), this.limit.classList.add("jsr_limit"), this.renderer.addChild(this.limit);
            }
            render(t) {
                return () => {
                    var e, i, s, n;
                    if (!(null === (e = t.limit) || void 0 === e ? void 0 : e.min) && !(null === (i = t.limit) || void 0 === i ? void 0 : i.max)) return (this.limit.style.left = "0%"), void (this.limit.style.right = "0%");
                    (null === (s = t.limit) || void 0 === s ? void 0 : s.min) ? (this.limit.style.left = 100 * t.limit.min.asRatio() + "%") : (this.limit.style.left = "0%"),
                        (null === (n = t.limit) || void 0 === n ? void 0 : n.max) ? (this.limit.style.right = `calc(100% - ${100 * t.limit.max.asRatio()}%)`) : (this.limit.style.right = "0%");
                };
            }
        }),
        (D.Rail = class extends x {
            constructor() {
                super(...arguments),
                    (this.handleClick = (t) => {
                        this.input.setClosestRatioValue(this.renderer.positionToRelative(t.clientX));
                    });
            }
            destroy() {
                this.rail.remove();
            }
            initView() {
                (this.rail = document.createElement("div")), this.rail.classList.add("jsr_rail"), this.renderer.addChild(this.rail), this.rail.addEventListener("click", this.handleClick);
            }
            render(t) {
                return () => {};
            }
        }),
        (D.Slider = class extends x {
            constructor() {
                super(...arguments), (this.sliders = []), (this.destroyEvents = []);
            }
            destroy() {
                this.sliders.forEach((t) => t.remove()), this.destroyEvents.forEach((t) => t());
            }
            initView() {
                (this.sliders = this.config.initialValues.map((t, e) => {
                    const i = document.createElement("div");
                    return i.classList.add("jsr_slider"), (i.style.left = "0"), (i.dataset.key = e.toString()), (i.tabIndex = 1), L(i, (t) => this.handleMove(e, t), this.renderer.getContainer()), i;
                })),
                    this.sliders.forEach((t) => this.renderer.addChild(t));
                const t = (t) => this.handleKeyboard(t);
                this.config.container.addEventListener("keydown", t), this.destroyEvents.push(() => this.config.container.removeEventListener("keydown", t));
            }
            render(t) {
                return () => {
                    t.values.forEach((t, e) => {
                        this.sliders[e].style.left = 100 * t.asRatio() + "%";
                    });
                };
            }
            handleMove(t, e) {
                this.input.setRatioValue(t, this.renderer.positionToRelative(e));
            }
            handleKeyboard(t) {
                const e = t.target;
                if (e && T(e)) {
                    const i = Number(e.dataset.key),
                        s = k.get(t.code);
                    s && (t.preventDefault(), t.shiftKey ? this.handleKeyboardWithShift(i, s) : t.ctrlKey ? this.handleKeyboardWithCtrl(i, s) : this.handleKeyboardPlain(i, s));
                }
            }
            handleKeyboardWithShift(t, e) {
                this.input.changeRatioBy(t, 0.05 * e);
            }
            handleKeyboardWithCtrl(t, e) {
                this.input.changeRealBy(t, 10 * this.config.step * e);
            }
            handleKeyboardPlain(t, e) {
                this.input.changeRealBy(t, this.config.step * e);
            }
        }),
        D
    );
});
//# sourceMappingURL=index.js.map
