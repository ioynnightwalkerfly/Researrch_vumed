/* assets/js/script_globe.js */

var canvas, scene, renderer, data;

// Cache DOM selectors
var container = document.getElementsByClassName('js-globe')[0];

if (!container) {
    console.error("Globe container (.js-globe) not found!");
} else {

    // Object for country HTML elements and variables
    var elements = {};

    // Three group objects
    var groups = {
        main: null, // A group containing everything
        globe: null, // A group containing the globe sphere (and globe dots)
        globeDots: null, // A group containing the globe dots
        lines: null, // A group containing the lines between each country
        lineDots: null // A group containing the line dots
    };

    // Map properties for creation and rendering
    var props = {
        mapSize: {
            width: 2048 / 2,
            height: 1024 / 2
        },
        globeRadius: 200,
        dotsAmount: 20,
        startingCountry: 'hongkong',
        colours: {
            globeDots: 'rgb(255, 255, 255)',
            lines: new THREE.Color('#18FFFF'),
            lineDots: new THREE.Color('#18FFFF')
        },
        alphas: {
            globe: 0.7,
            lines: 0.5
        }
    };

    // Angles used for animating the camera
    var camera = {
        object: null,
        controls: null,
        angles: {
            current: { azimuthal: null, polar: null },
            target: { azimuthal: null, polar: null }
        }
    };

    // Booleans and values for animations
    var animations = {
        finishedIntro: false,
        dots: {
            current: 0,
            total: 170,
            points: []
        },
        globe: {
            current: 0,
            total: 80,
        },
        countries: {
            active: false,
            animating: false,
            current: 0,
            total: 120,
            selected: null,
            index: null,
            timeout: null,
            initialDuration: 5000,
            duration: 2000
        }
    };

    var isHidden = false;

    /* SETUP */
    function getData() {
        // Using the same URL as example, usually better to host locally but for now use CDN
        var request = new XMLHttpRequest();
        request.open('GET', 'assets/js/globe-points.json', true);
        request.onload = function () {
            if (request.status >= 200 && request.status < 400) {
                data = JSON.parse(request.responseText);
                setupScene();
            } else {
                showFallback();
            }
        };
        request.onerror = showFallback;
        request.send();
    }

    function showFallback() {
        console.log('Globe Error: WebGL not supported or Data fetch failed.');
    }

    function setupScene() {
        canvas = container.getElementsByClassName('js-canvas')[0];

        scene = new THREE.Scene();
        renderer = new THREE.WebGLRenderer({
            canvas: canvas,
            antialias: true,
            alpha: true,
            shadowMapEnabled: false
        });

        // Use container size
        renderer.setSize(container.clientWidth, container.clientHeight);
        renderer.setPixelRatio(1);
        renderer.setClearColor(0x000000, 0);

        groups.main = new THREE.Group();
        groups.main.name = 'Main';

        groups.lines = new THREE.Group();
        groups.lines.name = 'Lines';
        groups.main.add(groups.lines);

        groups.lineDots = new THREE.Group();
        groups.lineDots.name = 'Dots';
        groups.main.add(groups.lineDots);

        scene.add(groups.main);

        addCamera();
        addControls();
        addGlobe();

        render();
        animate();

        var canvasResizeBehaviour = function () {
            // container might resize, we need to match it
            var width = container.clientWidth;
            var height = container.clientHeight;

            camera.object.aspect = width / height;
            camera.object.updateProjectionMatrix();
            renderer.setSize(width, height);
        };

        window.addEventListener('resize', canvasResizeBehaviour);
        canvasResizeBehaviour();
    }

    /* CAMERA AND CONTROLS */
    function addCamera() {
        var width = container.clientWidth;
        var height = container.clientHeight;
        camera.object = new THREE.PerspectiveCamera(60, width / height, 1, 10000);
        camera.object.position.z = props.globeRadius * 2.6;
    }

    function addControls() {
        camera.controls = new THREE.OrbitControls(camera.object, canvas);
        camera.controls.enableKeys = false;
        camera.controls.enablePan = false;
        camera.controls.enableZoom = false;
        camera.controls.enableDamping = false;
        camera.controls.enableRotate = true; // Let user rotate!
        camera.controls.autoRotate = true;
        camera.controls.autoRotateSpeed = 2; // Slower rotation

        camera.angles.current.azimuthal = -Math.PI;
        camera.angles.current.polar = 180;
    }

    /* RENDERING */
    function render() {
        renderer.render(scene, camera.object);
    }

    function animate() {
        requestAnimationFrame(animate);

        if (groups.globeDots) {
            introAnimate();
        }

        if (animations.finishedIntro === true) {
            animateDots();
        }

        // Auto rotation via controls
        camera.controls.update();

        render();
    }

    /* GLOBE */
    function addGlobe() {
        var textureLoader = new THREE.TextureLoader();
        textureLoader.setCrossOrigin(true);
        var radius = props.globeRadius - (props.globeRadius * 0.02);
        var segments = 64;
        var rings = 64;

        // Make gradient texture manually
        var canvasSize = 128;
        var textureCanvas = document.createElement('canvas');
        textureCanvas.width = canvasSize;
        textureCanvas.height = canvasSize;
        var canvasContext = textureCanvas.getContext('2d');
        canvasContext.rect(0, 0, canvasSize, canvasSize);
        var canvasGradient = canvasContext.createLinearGradient(0, 0, 0, canvasSize);
        canvasGradient.addColorStop(0, 'rgba(0,0,0,0.02)'); // Use 0 instead of 1 duplicate
        canvasGradient.addColorStop(1, 'rgba(0,0,0,0.2)'); // Darker for visibility
        canvasContext.fillStyle = canvasGradient;
        canvasContext.fill();

        var texture = new THREE.Texture(textureCanvas);
        texture.needsUpdate = true;

        var geometry = new THREE.SphereGeometry(radius, segments, rings);
        var material = new THREE.MeshBasicMaterial({
            map: texture,
            transparent: true,
            opacity: 0
        });
        globe = new THREE.Mesh(geometry, material);

        groups.globe = new THREE.Group();
        groups.globe.name = 'Globe';
        groups.globe.add(globe);
        groups.main.add(groups.globe);

        addGlobeDots();
    }

    function addGlobeDots() {
        var geometry = new THREE.Geometry();
        var canvasSize = 16;
        var halfSize = canvasSize / 2;
        var textureCanvas = document.createElement('canvas');
        textureCanvas.width = canvasSize;
        textureCanvas.height = canvasSize;
        var canvasContext = textureCanvas.getContext('2d');
        canvasContext.beginPath();
        canvasContext.arc(halfSize, halfSize, halfSize, 0, 2 * Math.PI);
        canvasContext.fillStyle = props.colours.globeDots;
        canvasContext.fill();

        var texture = new THREE.Texture(textureCanvas);
        texture.needsUpdate = true;

        var material = new THREE.PointsMaterial({
            map: texture,
            size: props.globeRadius / 100, // Slightly larger
            transparent: true,
            opacity: 0.8
        });

        var addDot = function (targetX, targetY) {
            var point = new THREE.Vector3(0, 0, 0);
            geometry.vertices.push(point);
            var result = returnSphericalCoordinates(targetX, targetY);
            animations.dots.points.push(new THREE.Vector3(result.x, result.y, result.z));
        };

        for (var i = 0; i < data.points.length; i++) {
            addDot(data.points[i].x, data.points[i].y);
        }
        for (var country in data.countries) {
            addDot(data.countries[country].x, data.countries[country].y);
        }

        groups.globeDots = new THREE.Points(geometry, material);
        groups.globe.add(groups.globeDots);
    }

    /* COUNTRY LINES AND DOTS */
    function addLineDots() {
        var radius = props.globeRadius / 120;
        var segments = 16;
        var rings = 16;
        var geometry = new THREE.SphereGeometry(radius, segments, rings);
        var material = new THREE.MeshBasicMaterial({
            color: props.colours.lineDots
        });
        var returnLineDot = function () {
            return new THREE.Mesh(geometry, material);
        };

        for (var i = 0; i < props.dotsAmount; i++) {
            var targetDot = returnLineDot();
            targetDot.visible = false;
            targetDot._pathIndex = null;
            targetDot._path = null;
            groups.lineDots.add(targetDot);
        }
    }

    function assignDotsToRandomLine(target) {
        if (!animations.countries.selected || !animations.countries.selected.children) return;

        var randomLine = Math.random() * (animations.countries.selected.children.length - 1);
        randomLine = animations.countries.selected.children[randomLine.toFixed(0)];
        if (randomLine) {
            target._path = randomLine._path;
        }
    }

    function reassignDotsToNewLines() {
        for (var i = 0; i < groups.lineDots.children.length; i++) {
            var target = groups.lineDots.children[i];
            if (target._path !== null && target._pathIndex !== null) {
                assignDotsToRandomLine(target);
            }
        }
    }

    function animateDots() {
        for (var i = 0; i < groups.lineDots.children.length; i++) {
            var dot = groups.lineDots.children[i];
            if (dot._path === null) {
                var seed = Math.random();
                if (seed > 0.99) {
                    assignDotsToRandomLine(dot);
                    dot._pathIndex = 0;
                }
            }
            else if (dot._path !== null && dot._pathIndex < dot._path.length - 1) {
                if (dot.visible === false) { dot.visible = true; }
                dot.position.x = dot._path[dot._pathIndex].x;
                dot.position.y = dot._path[dot._pathIndex].y;
                dot.position.z = dot._path[dot._pathIndex].z;
                dot._pathIndex++;
            }
            else {
                dot.visible = false;
                dot._path = null;
            }
        }
    }

    /* INTRO ANIMATIONS */
    // Easings
    var easeInOutCubic = function (t) { return t < 0.5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1; };
    var easeOutCubic = function (t) { return (--t) * t * t + 1; };
    var easeInOutQuad = function (t) { return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t; };

    function introAnimate() {
        if (animations.dots.current <= animations.dots.total) {
            var points = groups.globeDots.geometry.vertices;
            var totalLength = points.length;

            for (var i = 0; i < totalLength; i++) {
                var dotProgress = easeInOutCubic(animations.dots.current / animations.dots.total);
                dotProgress = dotProgress + (dotProgress * (i / totalLength));
                if (dotProgress > 1) dotProgress = 1;

                points[i].x = animations.dots.points[i].x * dotProgress;
                points[i].y = animations.dots.points[i].y * dotProgress;
                points[i].z = animations.dots.points[i].z * dotProgress;

                // Animate Camera First Dot
                if (i === 0) {
                    var azimuthalDifference = (camera.angles.current.azimuthal - camera.angles.target.azimuthal) * dotProgress;
                    azimuthalDifference = camera.angles.current.azimuthal - azimuthalDifference;
                    camera.controls.setAzimuthalAngle(azimuthalDifference);

                    var polarDifference = (camera.angles.current.polar - camera.angles.target.polar) * dotProgress;
                    polarDifference = camera.angles.current.polar - polarDifference;
                    // camera.controls.setPolarAngle(polarDifference);
                }
            }
            animations.dots.current++;
            groups.globeDots.geometry.verticesNeedUpdate = true;

            var wrapper = document.querySelector('.svg-wrapper');
            if (wrapper) wrapper.classList.add('active');
        }

        if (animations.dots.current >= (animations.dots.total * 0.65) && animations.globe.current <= animations.globe.total) {
            var globeProgress = easeOutCubic(animations.globe.current / animations.globe.total);
            globe.material.opacity = props.alphas.globe * globeProgress;
            animations.globe.current++;
        }

        if (animations.countries.active === true && animations.finishedIntro === false) {
            animations.finishedIntro = true;
            animations.countries.timeout = setTimeout(showNextCountry, animations.countries.initialDuration);
            addLineDots();
        }
    }

    /* COUNTRY CYCLE */
    function changeCountry(key, init) {
        if (animations.countries.selected !== undefined) {
            animations.countries.selected.visible = false;
        }

        for (var name in elements) {
            if (name === key) {
                elements[name].element.classList.add('active');
            } else {
                elements[name].element.classList.remove('active');
            }
        }

        animations.countries.selected = groups.lines.getObjectByName(key);
        // Safety check: if lines were never generated, selected is undefined
        if (animations.countries.selected) {
            animations.countries.selected.visible = true;
        }

        if (init !== true) {
            camera.angles.current.azimuthal = camera.controls.getAzimuthalAngle();
            camera.angles.current.polar = camera.controls.getPolarAngle();

            if (data.countries[key]) {
                var targetAngles = returnCameraAngles(data.countries[key].x, data.countries[key].y);
                camera.angles.target.azimuthal = targetAngles.azimuthal;
                camera.angles.target.polar = targetAngles.polar;
            }

            animations.countries.animating = true;
            reassignDotsToNewLines();
        }
    }

    function animateCountryCycle() {
        if (animations.countries.current <= animations.countries.total) {
            var progress = easeInOutQuad(animations.countries.current / animations.countries.total);

            var azimuthalDifference = (camera.angles.current.azimuthal - camera.angles.target.azimuthal) * progress;
            azimuthalDifference = camera.angles.current.azimuthal - azimuthalDifference;
            camera.controls.setAzimuthalAngle(azimuthalDifference);

            var polarDifference = (camera.angles.current.polar - camera.angles.target.polar) * progress;
            polarDifference = camera.angles.current.polar - polarDifference;
            // camera.controls.setPolarAngle(polarDifference);

            animations.countries.current++;
        } else {
            animations.countries.animating = false;
            animations.countries.current = 0;
            animations.countries.timeout = setTimeout(showNextCountry, animations.countries.duration);
        }
    }

    function showNextCountry() {
        if (!data.countries) return;

        animations.countries.index++;
        if (animations.countries.index >= Object.keys(data.countries).length) {
            animations.countries.index = 0;
        }
        var key = Object.keys(data.countries)[animations.countries.index];
        changeCountry(key, false);
    }

    // Missing helper functions
    function returnCameraAngles(latitude, longitude) {
        var targetAzimuthalAngle = ((latitude - props.mapSize.width) / props.mapSize.width) * Math.PI;
        targetAzimuthalAngle = targetAzimuthalAngle + (Math.PI / 2);
        targetAzimuthalAngle = targetAzimuthalAngle + 0.1;
        var targetPolarAngle = (longitude / (props.mapSize.height * 2)) * Math.PI;
        return { azimuthal: targetAzimuthalAngle, polar: targetPolarAngle };
    }

    function returnSphericalCoordinates(latitude, longitude) {
        latitude = ((latitude - props.mapSize.width) / props.mapSize.width) * -180;
        longitude = ((longitude - props.mapSize.height) / props.mapSize.height) * -90;
        var radius = Math.cos(longitude / 180 * Math.PI) * props.globeRadius;
        var targetX = Math.cos(latitude / 180 * Math.PI) * radius;
        var targetY = Math.sin(longitude / 180 * Math.PI) * props.globeRadius;
        var targetZ = Math.sin(latitude / 180 * Math.PI) * radius;
        return { x: targetX, y: targetY, z: targetZ };
    }

    // Init Logic
    if (window.WebGLRenderingContext) {
        getData();
    } else {
        showFallback();
    }
}
