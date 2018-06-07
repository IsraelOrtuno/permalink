class Compiler {
    constructor(sources) {
        this.setSources({})
        this.wrap = true
    }

    setSources(sources) {
        this.sources = sources
    }

    compile(subject, wrap = false) {
        // return ''
        console.log(subject)
        // let matches = subject.match(/\{\{(.*?)\}\}/gi)

        let regex = /\{\{(.*?)\}\}/gi;
        let matches = regex.exec(subject);

        return subject.replace(regex, (match, contents, offset, input_string) => {
                return this.evaluate(match)
            }
        );

        // return matches.map(match => {
        //     return this.evaluate(match)
        // })
    }

    evaluate(__value) {
        let result
        __value = __value.replace('{{', '').replace('}}', '').replace('$', '').replace('->', '.').trim()

        try {
            result = eval('this.sources.' + __value)
        } catch (e) {
            result = __value
        }

        if (this.wrap) {
            result = `<span>${result}</span>`
        }

        return result
    }
}

export default Compiler