importScripts('https://unpkg.com/@jscad/modeling@2.12.0/dist/jscad-modeling.min.js')

const { booleans, colors, extrusions, hulls, math, measurements, meshes, modifiers, paths, primitives, texts, utils } = jscadModeling

self.onmessage = function(e) {
    const { type, data } = e.data
    
    try {
        let result
        
        switch(type) {
            case 'createBox':
                result = createBox(data)
                break
            case 'createSphere':
                result = createSphere(data)
                break
            case 'createCylinder':
                result = createCylinder(data)
                break
            case 'createTorus':
                result = createTorus(data)
                break
            case 'createText':
                result = createText(data)
                break
            case 'extrude':
                result = extrudeShape(data)
                break
            case 'booleanUnion':
                result = booleanUnion(data)
                break
            case 'booleanSubtract':
                result = booleanSubtract(data)
                break
            case 'booleanIntersect':
                result = booleanIntersect(data)
                break
            case 'translate':
                result = translateObject(data)
                break
            case 'rotate':
                result = rotateObject(data)
                break
            case 'scale':
                result = scaleObject(data)
                break
            case 'mirror':
                result = mirrorObject(data)
                break
            case 'hull':
                result = createHull(data)
                break
            case 'measure':
                result = measureObject(data)
                break
            case 'generateStl':
                result = generateStl(data)
                break
            case 'generateStep':
                result = generateStep(data)
                break
            case 'compileCode':
                result = compileCode(data)
                break
            default:
                throw new Error('Unknown command')
        }
        
        self.postMessage({ type: 'success', result })
    } catch(error) {
        self.postMessage({ type: 'error', error: error.message })
    }
}

function createBox({ size = [10, 10, 10], center = [0, 0, 0] }) {
    const box = primitives.cube({ size, center })
    return serializeGeometry(box)
}

function createSphere({ radius = 5, segments = 32 }) {
    const sphere = primitives.sphere({ radius, segments })
    return serializeGeometry(sphere)
}

function createCylinder({ radius = 5, height = 10, segments = 32 }) {
    const cylinder = primitives.cylinder({ radius, height, segments })
    return serializeGeometry(cylinder)
}

function createTorus({ innerRadius = 8, outerRadius = 3, segments = 32 }) {
    const torus = primitives.torus({ innerRadius, outerRadius, segments })
    return serializeGeometry(torus)
}

function createText({ text = 'CAD', size = 5, height = 2 }) {
    const text2d = texts.text({ text, size })
    const extruded = extrusions.extrudeLinear({ height }, text2d)
    return serializeGeometry(extruded)
}

function extrudeShape({ shape, height = 5 }) {
    const extruded = extrusions.extrudeLinear({ height }, shape)
    return serializeGeometry(extruded)
}

function booleanUnion({ shapes }) {
    const parsedShapes = shapes.map(s => parseGeometry(s))
    const result = booleans.union(...parsedShapes)
    return serializeGeometry(result)
}

function booleanSubtract({ a, b }) {
    const shapeA = parseGeometry(a)
    const shapeB = parseGeometry(b)
    const result = booleans.subtract(shapeA, shapeB)
    return serializeGeometry(result)
}

function booleanIntersect({ a, b }) {
    const shapeA = parseGeometry(a)
    const shapeB = parseGeometry(b)
    const result = booleans.intersect(shapeA, shapeB)
    return serializeGeometry(result)
}

function translateObject({ shape, offset }) {
    const parsed = parseGeometry(shape)
    const result = modifiers.translate(offset, parsed)
    return serializeGeometry(result)
}

function rotateObject({ shape, angles }) {
    const parsed = parseGeometry(shape)
    const result = modifiers.rotate(angles, parsed)
    return serializeGeometry(result)
}

function scaleObject({ shape, factors }) {
    const parsed = parseGeometry(shape)
    const result = modifiers.scale(factors, parsed)
    return serializeGeometry(result)
}

function mirrorObject({ shape, axis }) {
    const parsed = parseGeometry(shape)
    const result = modifiers.mirror(axis, parsed)
    return serializeGeometry(result)
}

function createHull({ shapes }) {
    const parsedShapes = shapes.map(s => parseGeometry(s))
    const result = hulls.hull(...parsedShapes)
    return serializeGeometry(result)
}

function measureObject({ shape }) {
    const parsed = parseGeometry(shape)
    const boundingBox = measurements.measureBoundingBox(parsed)
    const centroid = measurements.measureCentroid(parsed)
    const volume = measurements.measureVolume(parsed)
    return { boundingBox, centroid, volume }
}

function generateStl({ shape }) {
    const parsed = parseGeometry(shape)
    const stl = utils.serialize(parsed, 'stl')
    return stl
}

function generateStep({ shape }) {
    const parsed = parseGeometry(shape)
    const step = utils.serialize(parsed, 'step')
    return step
}

function compileCode({ code }) {
    try {
        const func = new Function('modeling', code)
        const result = func(jscadModeling)
        return serializeGeometry(result)
    } catch(e) {
        throw new Error('Code compilation error: ' + e.message)
    }
}

function serializeGeometry(geometry) {
    return JSON.parse(JSON.stringify(geometry))
}

function parseGeometry(data) {
    return data
}
