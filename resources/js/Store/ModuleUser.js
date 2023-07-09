export default {
    state: () => ({
        notifications: {
            loader: false,
            data: [],
            unRead: 0,
        },
    }),
    mutations: {},
    actions: {
        notificationsList({ state }) {
            if (state.notifications.loader) return;
            state.notifications.loader = true;
            return new Promise(async (resolve, reject) => {
                try {
                    const response = await axios.get(route('api.user.notifications.index'));
                    const notifications = response.data.notifications;
                    state.notifications.data = notifications;
                    state.notifications.unRead = notifications.filter(($item) => !$item.read_at).length;
                    resolve();
                } catch (error) {
                    this._vm.$showErrors(error);
                    reject();
                } finally { state.notifications.loader = false }
            });
        },
    },
    getters: {}
}
