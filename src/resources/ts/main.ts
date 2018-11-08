declare const $: any;
declare const Redux;
declare const ReduxThunk;

// helper functions
const h = {
    catSorter: ({name}, {name: name2}) => 
        name.toLowerCase() < name2.toLowerCase()
            ? -1
            : name2.toLowerCase() < name.toLowerCase()
                ? 1
                : 0
    ,

    // takes a node and provides a function that can be used to check equality
    isNode: node => n => (n === node),

    // is node but not equality
    notIsNode: node => n => (n !== node),

    // provide vals of object instead of keys
    vals: obj => Object.keys(obj).map(k => obj[k]),

    // take our dom template and fill in relavent bits
    fillLiTemplate: (template, name, depth, nodeExpanded, canExpand, toggleExpandFn, labelText, labelClass) => template.clone()
        // set li text
        .find('.txt').text(name).closest('li')
        
        // set the indent level
        .find('.indent').addClass('indent-' + depth).closest('li')

        // unhide the right expander
        .find('.expander').addClass(nodeExpanded
            ? 'minus'
            : canExpand
                ? 'plus'
                : ''
        ).closest('li')
        
        // handle expand / minimize
        .find('.expander')
            .click(toggleExpandFn)
            .closest('li')
        .find('.label').text(labelText).addClass(labelClass).closest('li'),


    filterData: filter => {
        filter = (filter || '').toLowerCase();
        let itemMatches = item => item.name.toLowerCase().indexOf(filter) !== -1

        return categoryObj => {
            let ret = {}

            Object.keys(categoryObj).forEach(function(key) {
                let cat = { ...categoryObj[key] }

                if (cat.name.indexOf(filter) !== -1) {
                    // whole thing visible so leave it alone
                    ret[key] = cat;
                    return;
                }

                // filter items
                if (cat.items && !cat.items.every(itemMatches)) {
                    cat.items = cat.items.filter(itemMatches);
                }

                // filter sub categories
                if (cat.subCats) {
                    cat.subCats = h.filterData(filter)(cat.subCats);
                }

                if (cat.items.length) {
                    ret[key] = cat;
                }
            })

            return ret;
        }
    },
    flatNodes: node => !node.category_ID && !node.equipment_ID
        ? [].concat.apply(null, h.vals(node).map(h.flatNodes))
        : [ 
            node,
            ...h.vals(node.subCats || {}).map(h.flatNodes),
            ...h.vals(node.items || [])
        ]
}

const reducers = (state, action) => {
    state = state || {
        fitler: '',
        expanded: [],
        loading: true,
        error: false,
    };

    if (action.type === 'SET_ERROR') return { 
        ...state, 
        error: action.error, loading: false 
    }

    if (action.type === 'FILTER_CHANGE') {
        let filtered = !action.filter.trim() ? state.data : h.filterData(action.filter)(state.data)

        return {
            ...state,
            filter: action.filter,
            visData: filtered,
            expanded: action.filter.trim() ? h.flatNodes(filtered) : [],
        }
    }

    if (action.type === 'EXPAND_NODE') return {
        ...state,
        expanded: state.expanded.concat(action.node)
    }

    if (action.type === 'MINIMIZE_NODE') return {
        ...state,
        expanded: state.expanded.filter(h.notIsNode(action.node))
    }

    // lets fill in the available for categories
    if (action.type === 'SET_CLUB_DATA') return {
        ...state,
        data: action.data, 
        visData: h.filterData(action.filter)(action.data) 
    }

    if (action.type === 'SET_FETCHING_CLUB_DATA') return {
        ...state,
        loading: action.loading 
    }

    return state;
}

const store = Redux.createStore(reducers, Redux.applyMiddleware(ReduxThunk.default));
// just a helper fn wrapper to avoid more lamdas
store.dispatcher = obj => () => store.dispatch(obj)

$(document).ready(() => {
    let doms$ = {
        rest: $('.equipment-inventory-container .rest'),
        error: $('.equipment-inventory-container .error'),
        filter: $('.equip-filter'),
        loader: $('.equipment-inventory-container .loading'),
        template: $($('.equip-inv-line-template').text()),
        tree: $('.equipment-inventory-container .treeview'),
    }

    // filter change handler
    doms$.filter.on('keyup', ({ target: { value: filter } }) => store.dispatch({ type: 'FILTER_CHANGE', filter }))

    // update the dom when state changes
    store.subscribe(() => {
        const { filter, visData, expanded, loading, error } = store.getState();

        // we have an error so just display that
        if (error) {
            doms$.rest.addClass('hide');
            doms$.error.removeClass('hide').find('.text').text(error);
            return;
        }

        // if for some reason we progromatically change 
        // the filter put it into the filter box
        if (doms$.filter.val() != filter) {
            doms$.filter.val(filter);
        }

        doms$.loader.toggleClass('hide', !loading);

        doms$.filter.attr('disabled', loading);

        if (loading) return;

        let ul = $('<ul/>').addClass('tree-container');

        let insertDom = depth => node => {
            let nodeExpanded = expanded.some(h.isNode(node))
            let canExpand = (node.items || []).length || node.subCats

            // append to dom
            ul.append(h.fillLiTemplate(
                doms$.template,
                node.name,
                depth,
                nodeExpanded,
                canExpand,
                store.dispatcher({ type: nodeExpanded ? 'MINIMIZE_NODE' : 'EXPAND_NODE', node: node }),
                node.status ? node.statusText : node.availableCount + ' items available for checkout',
                node.status ? 'label-status-' + node.status : 'label-success'
            ));

            // repeat recursively
            if (expanded.some(h.isNode(node))) {
                let nextDepth = depth + 1;

                h.vals(node.subCats || {}).sort(h.catSorter).map(insertDom(nextDepth));                    
    
                (node.items || []).map(insertDom(nextDepth));
            }
        }

        h.vals(visData).sort(h.catSorter).map(insertDom(0))

        doms$.tree.html(ul);
    })

    // start our initial data request
    store.dispatch(dispatch => {
        dispatch({ type: 'FILTER_CHANGE', filter: '' });
        dispatch({ type: 'SET_FETCHING_CLUB_DATA', loading: true })

        let descender = obj => {
            let available = obj.items.filter(i => {
                return i.status == 1;
            }).length;

            if (obj.subCats) {
                Object.keys(obj.subCats).forEach(catKey => {
                    obj.subCats[catKey] = { ...descender(obj.subCats[catKey]) }

                    available += obj.subCats[catKey].availableCount + available;
                })
            }

			obj.availableCount = available;

			if (!obj.status) {
				obj.status = available > 0 ? 1 : 2
				obj.statusText = obj.availableCount + ' items available for rental'
			}

            return obj;
        }

        $.get((localStorage.getItem('cmApiServer') || window['cmApiServer']) + '/api/equipment/status', { structured: 1 })
            .then(res => {
                h.vals(res).forEach(descender);
                return res;
            })
            .then(res => {
                dispatch({ type: 'SET_CLUB_DATA', data: res })
                dispatch({ type: 'SET_FETCHING_CLUB_DATA', loading: false })
            })
            .catch(e => {
                dispatch({ type: 'SET_ERROR', error: 'Something went wrong while retrieving data.' })
            })
    })
})
