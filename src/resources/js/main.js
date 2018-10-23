var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var h = {
    catSorter: function (_a, _b) {
        var name = _a.name;
        var name2 = _b.name;
        return name.toLowerCase() < name2.toLowerCase()
            ? -1
            : name2.toLowerCase() < name.toLowerCase()
                ? 1
                : 0;
    },
    isNode: function (node) { return function (n) { return (n === node); }; },
    notIsNode: function (node) { return function (n) { return (n !== node); }; },
    vals: function (obj) { return Object.keys(obj).map(function (k) { return obj[k]; }); },
    fillLiTemplate: function (template, name, depth, nodeExpanded, canExpand, toggleExpandFn, labelText, labelClass) { return template.clone()
        .find('.txt').text(name).closest('li')
        .find('.indent').addClass('indent-' + depth).closest('li')
        .find('.expander').addClass(nodeExpanded
        ? 'minus'
        : canExpand
            ? 'plus'
            : '').closest('li')
        .find('.expander')
        .click(toggleExpandFn)
        .closest('li')
        .find('.label').text(labelText).addClass(labelClass).closest('li'); },
    filterData: function (filter) {
        filter = (filter || '').toLowerCase();
        var itemMatches = function (item) { return item.name.toLowerCase().indexOf(filter) !== -1; };
        return function (categoryObj) {
            var ret = {};
            Object.keys(categoryObj).forEach(function (key) {
                var cat = __assign({}, categoryObj[key]);
                if (cat.name.indexOf(filter) !== -1) {
                    ret[key] = cat;
                    return;
                }
                if (cat.items && !cat.items.every(itemMatches)) {
                    cat.items = cat.items.filter(itemMatches);
                }
                if (cat.subCats) {
                    cat.subCats = h.filterData(filter)(cat.subCats);
                }
                if (cat.items.length) {
                    ret[key] = cat;
                }
            });
            return ret;
        };
    },
    flatNodes: function (node) { return !node.category_ID && !node.equipment_ID
        ? [].concat.apply(null, h.vals(node).map(h.flatNodes))
        : [
            node
        ].concat(h.vals(node.subCats || {}).map(h.flatNodes), h.vals(node.items || [])); }
};
var reducers = function (state, action) {
    state = state || {
        fitler: '',
        expanded: [],
        loading: true,
        error: false
    };
    if (action.type === 'SET_ERROR')
        return __assign({}, state, { error: action.error, loading: false });
    if (action.type === 'FILTER_CHANGE') {
        var filtered = !action.filter.trim() ? state.data : h.filterData(action.filter)(state.data);
        return __assign({}, state, { filter: action.filter, visData: filtered, expanded: action.filter.trim() ? h.flatNodes(filtered) : [] });
    }
    if (action.type === 'EXPAND_NODE')
        return __assign({}, state, { expanded: state.expanded.concat(action.node) });
    if (action.type === 'MINIMIZE_NODE')
        return __assign({}, state, { expanded: state.expanded.filter(h.notIsNode(action.node)) });
    if (action.type === 'SET_CLUB_DATA')
        return __assign({}, state, { data: action.data, visData: h.filterData(action.filter)(action.data) });
    if (action.type === 'SET_FETCHING_CLUB_DATA')
        return __assign({}, state, { loading: action.loading });
    return state;
};
var store = Redux.createStore(reducers, Redux.applyMiddleware(ReduxThunk["default"]));
store.dispatcher = function (obj) { return function () { return store.dispatch(obj); }; };
$(document).ready(function () {
    var doms$ = {
        rest: $('.equipment-inventory-container .rest'),
        error: $('.equipment-inventory-container .error'),
        filter: $('.equip-filter'),
        loader: $('.equipment-inventory-container .loading'),
        template: $($('.equip-inv-line-template').text()),
        tree: $('.equipment-inventory-container .treeview')
    };
    doms$.filter.on('keyup', function (_a) {
        var filter = _a.target.value;
        return store.dispatch({ type: 'FILTER_CHANGE', filter: filter });
    });
    store.subscribe(function () {
        var _a = store.getState(), filter = _a.filter, visData = _a.visData, expanded = _a.expanded, loading = _a.loading, error = _a.error;
        if (error) {
            doms$.rest.addClass('hide');
            doms$.error.removeClass('hide').find('.text').text(error);
            return;
        }
        if (doms$.filter.val() != filter) {
            doms$.filter.val(filter);
        }
        doms$.loader.toggleClass('hide', !loading);
        doms$.filter.attr('disabled', loading);
        if (loading)
            return;
        var ul = $('<ul/>').addClass('tree-container');
        var insertDom = function (depth) { return function (node) {
            var nodeExpanded = expanded.some(h.isNode(node));
            var canExpand = (node.items || []).length || node.subCats;
            ul.append(h.fillLiTemplate(doms$.template, node.name, depth, nodeExpanded, canExpand, store.dispatcher({ type: nodeExpanded ? 'MINIMIZE_NODE' : 'EXPAND_NODE', node: node }), node.status ? node.statusText : node.availableCount + ' items available for checkout', node.status ? 'label-status-' + node.status : 'label-success'));
            if (expanded.some(h.isNode(node))) {
                var nextDepth = depth + 1;
                h.vals(node.subCats || {}).sort(h.catSorter).map(insertDom(nextDepth));
                (node.items || []).map(insertDom(nextDepth));
            }
        }; };
        h.vals(visData).sort(h.catSorter).map(insertDom(0));
        doms$.tree.html(ul);
    });
    store.dispatch(function (dispatch) {
        dispatch({ type: 'FILTER_CHANGE', filter: '' });
        dispatch({ type: 'SET_FETCHING_CLUB_DATA', loading: true });
        var descender = function (obj) {
            var available = obj.items.filter(function (i) {
                return i.status == 1;
            }).length;
            if (obj.subCats) {
                Object.keys(obj.subCats).forEach(function (catKey) {
                    obj.subCats[catKey] = __assign({}, descender(obj.subCats[catKey]));
                    available += obj.subCats[catKey].availableCount + available;
                });
            }
            obj.availableCount = available;
            if (!obj.status) {
                obj.status = available > 0 ? 1 : 2;
                obj.statusText = obj.availableCount + ' items available for rental';
            }
            return obj;
        };
        $.get((localStorage.getItem('cmApiServer') || window['cmApiServer']) + '/api/equipment/status', { structured: 1 })
            .then(function (res) {
            h.vals(res).forEach(descender);
            return res;
        })
            .then(function (res) {
            dispatch({ type: 'SET_CLUB_DATA', data: res });
            dispatch({ type: 'SET_FETCHING_CLUB_DATA', loading: false });
        })["catch"](function (e) {
            dispatch({ type: 'SET_ERROR', error: 'Something went wrong while retreiving data.' });
        });
    });
});
//# sourceMappingURL=main.js.map