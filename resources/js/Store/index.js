import Vue from 'vue';
import Vuex from 'vuex';
import ModuleUser from './ModuleUser';
import ModuleAdmin from './ModuleAdmin';
Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        user: {
            namespaced: true,
            state: ModuleUser.state,
            mutations: ModuleUser.mutations,
            actions: ModuleUser.actions,
            getters: ModuleUser.getters,
        },

        admin: {
            namespaced: true,
            state: ModuleAdmin.state,
            mutations: ModuleAdmin.mutations,
            actions: ModuleAdmin.actions,
            getters: ModuleAdmin.getters,
        },
    }
});
