/**
 * |--------------------------------------------------------------------------
 * | Prepare form data plugin
 * |--------------------------------------------------------------------------
 */
import Vue from 'vue';

Vue.use({
    install: (Vue) => {
        Vue.prototype.$prepareData = function (data) {
            const newData = Object.assign({}, data);
            Object.keys(newData).forEach(key => {
                if (!newData[key]) delete newData[key];
            });

            return newData;
        };
    }
});
