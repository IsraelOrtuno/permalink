import store from './store'
import Compiler from "./compiler"

window.compiler = new Compiler({})

export default {
    props: ['data', 'store'],

    data() {
        return {
            privateState: {},
            sharedState: store.state
        }
    },

    beforeMount() {
        this.$set(this.sharedState, 'slug', this.store.slug)
        this.$set(this.sharedState, 'meta', this.store.meta)
        // this.sharedState.meta = this.store.meta
        // store.state = this.store
        compiler.setSources(this.data)
    }
}