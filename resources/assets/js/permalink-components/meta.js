import store from '../store'

export default {
    data() {
        return {
            privateState: {},
            sharedState: store.state
        }
    },

    computed: {
        url() {
            return window.location.protocol + '//' + window.location.host + '/'
        },
        slug: {
            get() {
                return this.sharedState.slug
            },
            set(value) {
                this.sharedState.slug = value
            }
        },
        title: {
            get() {
                return this.sharedState.meta.title
            },
            set(value) {
                this.sharedState.meta.title = value
            }
        },
        description: {
            get() {
                return this.sharedState.meta.description
            },
            set(value) {
                this.sharedState.meta.description = value
            }
        }
    },
}