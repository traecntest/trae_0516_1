export class CadEngine {
    constructor() {
        this.worker = null
        this.callbacks = {}
        this.initWorker()
    }

    initWorker() {
        if (this.worker) return
        
        this.worker = new Worker('/cad-core/jscad-worker.js')
        
        this.worker.onmessage = (e) => {
            const { type, result, error } = e.data
            
            if (type === 'error') {
                console.error('CAD Engine Error:', error)
                if (this.errorCallback) {
                    this.errorCallback(error)
                }
                return
            }
            
            if (this.resultCallback) {
                this.resultCallback(result)
            }
        }
        
        this.worker.onerror = (error) => {
            console.error('Worker error:', error)
            if (this.errorCallback) {
                this.errorCallback(error.message)
            }
        }
    }

    onResult(callback) {
        this.resultCallback = callback
    }

    onError(callback) {
        this.errorCallback = callback
    }

    createBox(options = {}) {
        this.worker.postMessage({
            type: 'createBox',
            data: options
        })
    }

    createSphere(options = {}) {
        this.worker.postMessage({
            type: 'createSphere',
            data: options
        })
    }

    createCylinder(options = {}) {
        this.worker.postMessage({
            type: 'createCylinder',
            data: options
        })
    }

    createTorus(options = {}) {
        this.worker.postMessage({
            type: 'createTorus',
            data: options
        })
    }

    createText(options = {}) {
        this.worker.postMessage({
            type: 'createText',
            data: options
        })
    }

    extrude(options = {}) {
        this.worker.postMessage({
            type: 'extrude',
            data: options
        })
    }

    booleanUnion(shapes) {
        this.worker.postMessage({
            type: 'booleanUnion',
            data: { shapes }
        })
    }

    booleanSubtract(a, b) {
        this.worker.postMessage({
            type: 'booleanSubtract',
            data: { a, b }
        })
    }

    booleanIntersect(a, b) {
        this.worker.postMessage({
            type: 'booleanIntersect',
            data: { a, b }
        })
    }

    translate(shape, offset) {
        this.worker.postMessage({
            type: 'translate',
            data: { shape, offset }
        })
    }

    rotate(shape, angles) {
        this.worker.postMessage({
            type: 'rotate',
            data: { shape, angles }
        })
    }

    scale(shape, factors) {
        this.worker.postMessage({
            type: 'scale',
            data: { shape, factors }
        })
    }

    mirror(shape, axis) {
        this.worker.postMessage({
            type: 'mirror',
            data: { shape, axis }
        })
    }

    hull(shapes) {
        this.worker.postMessage({
            type: 'hull',
            data: { shapes }
        })
    }

    measure(shape) {
        this.worker.postMessage({
            type: 'measure',
            data: { shape }
        })
    }

    generateStl(shape) {
        this.worker.postMessage({
            type: 'generateStl',
            data: { shape }
        })
    }

    generateStep(shape) {
        this.worker.postMessage({
            type: 'generateStep',
            data: { shape }
        })
    }

    compileCode(code) {
        this.worker.postMessage({
            type: 'compileCode',
            data: { code }
        })
    }

    destroy() {
        if (this.worker) {
            this.worker.terminate()
            this.worker = null
        }
    }
}
